<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use Mail;

use App\Traits\DateTimeTrait;

use App\User;
use App\Schools;
use App\SchoolSettings;
use App\SchoolContactInfo;
use App\SchoolTypes;
use App\Mail\SetupEmail;

class SchoolController extends Controller {

    use DateTimeTrait;

    /**
     * School admin page for hybrid teachers
     *
     * @return resource - View
     */
    public function showSchoolAdmin() {
        $s = auth()->user()->school;
        $school_users = $s->users()->with('school', 'role', 'classes', 'permissions')->withoutGlobalScopes()->get();
        $school_users = $school_users->sortBy('last_name')->sortByDesc('role.name')->values();
        return view('school_management.school_hybrid_admin', [
            'school_users' => $school_users,
            'school_code' => $s->school_code,
            'teacher_code' => $s->teacher_code
        ]);
    }

    /**
     * List members page
     *
     * @return resource - View
     */
    public function showSchoolTree() {
        $school_users = auth()->user()->school->users()
            ->with(['classes.members' => fn($q) => $q->where('role_id', '!=', 3)->orderBy('role_id', 'desc')])
            ->whereHas('role', fn($q) => $q->whereIn('role_id', [3, 6]))
            ->get()
            ->sortBy('role.name');
        return view('school_management.schooltree', [
            'school_name' => ucwords(auth()->user()->school->name),
            'school_users' => $school_users
        ]);
    }

    /**
     * Find School
     *
     * @param  Request $request
     * @return Response
     */
    public function findSchool(Request $request) {
        $user = User::where('email', $request->username)->first();
        if (!$user) {
            return response()->json(['error' => 'Username not found.'], 200);
        }

        $school = $user->school;
        if (!$school) {
            return response()->json(['error' => 'School not found.'], 200);
        }

        session()->put('clever_username', $user);
        $response = [
            'success' => 'School found.',
            'school_name' => $school->name,
            'school_id' => $school->id
        ];
        return response()->json($response, 200);
    }

    /**
     * School show create
     *
     * @return resource - View
     */
    public function showCreate() {
        return view('school_management.create_school', [
            'countries' => \Config::get('constants.countries'),
            'standards' => \Config::get('constants.states'),
            'class_types' => \App\ClassType::all()
        ]);
    }

    /**
     * Create school
     *
     * @param  Request $request
     * @return Redirect
     */
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'school_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'school_type' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Missing required fields.')->withErrors($validator)->withInput();
        } else {
            // Create school
            $school = new Schools();
            $school->name = stripslashes($request->input('school_name'));
            $school->esa = $request->esa;
            $school->district = $request->school_district;

            if ($school->save()) {

                // Add allowed class types
                $types = explode(',', $request->school_type);
                //$labels = \DB::table('class_type')->get();
                foreach ($types as $val) {
                    $type = new SchoolTypes();
                    $type->school_id = $school->id;
                    $type->class_type = $val;
                    //$type->name = $labels->firstWhere('id', $val)->name;
                    $type->save();
                }

                // Create school contact info
                $contact = new SchoolContactInfo();
                $contact->title = $request->title;
                $contact->first_name = $request->input('first_name');
                $contact->last_name = $request->input('last_name');
                $contact->email = $request->input('email');
                $contact->phone = $request->input('phone');
                $contact->extension = $request->input('ext');
                $contact->school_id = $school->id;

                if ($contact->save()) {
                    // Create school settings
                    $settings = new SchoolSettings();
                    $settings->school_id = $school->id;
                    $settings->type = 1;
                    $settings->contract_expiration_date = $this->getDateTime($request->contract_expiration_date);
                    $settings->term = $request->term;
                    $settings->standards = $request->standards;

                    if ($request->has('multiple_teachers')) {
                        $settings->multiple_teachers = $request->input('multiple_teachers');
                    }

                    if ($settings->save()) {
                        $school->searchable();
                        // Generate school code
                        $school->generateCode();
                        $name = $request->admin_name ?: $contact->first_name;
                        Mail::to($request->admin_email ?: $contact->email)
                            ->bcc(\Config::get('mail.admin_notification'))
                            ->send(new SetupEmail($school->teacher_code, $school->school_code, $school->name, $name));
                        return redirect('eduadmin/edit/school/' . $school->id)->with('success', 'The school account was successfully generated, and a startup email will be sent to the school administrator.');
                    } else {
                        return redirect()->back()->with('error', 'Failed to save school settings.');
                    }

                } else {
                    return redirect()->back()->with('error', 'Failed to save school contact information.');
                }
            } else {
                return redirect()->back()->with('error', 'Failed to create school.');
            }

        }

    }

    /**
     * Edit School note
     *
     * @param  Request $request
     * @return Response
     */
    public function editNote(Request $request) {
        $school_id = $request->id;
        $contact = SchoolContactInfo::where('school_id', $school_id)->first();
        if (!$contact) {
            return response()->json(['error' => 'Failed to update notes.'], 400);
        }

        $contact->notes = $request->text;
        if ($contact->update()) {
            return response()->json(['success' => 'Successfully updated notes.'], 200);
        }
    }

    /**
     * Edit School
     *
     * @param  Request $request
     * @return Response
     */
    public function edit(Request $request) {
        $school_id = $request->school_id;
        $school = Schools::find($school_id);

        $settings = SchoolSettings::where('school_id', $school_id)->first();

        // If file, do nothing but upload file
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            return static::uploadContract($settings, $request->file('file'));
        }

        // Update school types
        if ($request->has('school_type')) {
            $types = new SchoolTypes();
            $types->class_type = request('school_type', $request->school_type);
            $types->school_id = $school->id;
            $types->save();
            return response()->json(['success' => 'School type successfully added.'], 200);
        }

        if ($request->has('remove_school_type')) {
            $types = SchoolTypes::where([
                ['school_id', $school_id],
                ['class_type', $request->remove_school_type]
            ])->delete();
            return response()->json(['success' => 'School type successfully removed.'], 200);
        }

        // Change school status
        if ($request->has('school_status')) {
            $school->status = $request->input('school_status');
            if ($school->id != config('app.admin_school') && $school->save()) {
                return response()->json(['success' => 'School deactivated.'], 200);
            } else {
                return response()->json(['error' => 'Failed to change school status'], 503);
            }
        }

        $contact = SchoolContactInfo::where('school_id', $school_id)->first();
        $school->name = request('school_name', stripslashes($school->name));
        $school->esa = request('esa', $school->esa);
        $school->district = request('school_district', $school->district);

        $contact->color_code = request('color_code', $contact->color_code);
        $contact->first_name = request('first_name', $contact->first_name);
        $contact->last_name = request('last_name', $contact->last_name);
        $contact->title = request('title', $contact->title);
        $contact->email = request('email', $contact->email);
        $contact->phone = request('phone', $contact->phone);
        $contact->extension = request('ext', $contact->extension);
        $contact->address1 = request('address1', $contact->address1);
        $contact->address2 = request('address2', $contact->address2);
        $contact->city = request('city', $contact->city);
        $contact->state = request('state', $contact->state);
        $contact->zip = request('zip', $contact->zip);
        $contact->country = request('country', $contact->country);
        $contact->superintendent = request('superintendent', $contact->superintendent);
        $contact->principal = request('principal', $contact->principal);
        $contact->admin_contact = request('admin_contact', $contact->admin_contact);

        $contact->last_contacted_date = $request->has('last_contacted_date')
        ? $this->getDateTime($request->last_contacted_date)
        : $settings->last_contacted_date;

        $settings->term = request('term', $settings->term);
        $settings->standards = request('standards', $settings->standards);
        $settings->materials_sent = request('materials_sent', $settings->materials_sent);
        $settings->materials_paid = request('materials_paid', $settings->materials_paid);
        $settings->payment_due = request('payment_due', $settings->payment_due);
        $settings->auto_renewal_sent = request('auto_renewal_sent', $settings->auto_renewal_sent);
        $settings->auto_renewal_received = request('auto_renewal_received', $settings->auto_renewal_received);
        $settings->certified = request('certified', $settings->certified);
        $settings->contest = request('contest', $settings->contest);

        if ($request->has('contract_file')) {
            $settings->contract_file = null;
        }

        if ($request->has('contract_expiration_date')) {
            $settings->contract_expiration_date = $this->getDateTime($request->contract_expiration_date);
            $settings->notified_admin = 0;
        } else {
            $settings->contract_expiration_date = $settings->contract_expiration_date;
        }

        $settings->contract_sent_date = $request->has('contract_sent_date')
        ? $this->getDateTime($request->contract_sent_date)
        : $settings->contract_sent_date;
        $settings->contract_received_date = $request->has('contract_received_date')
        ? $this->getDateTime($request->contract_received_date)
        : $settings->contract_received_date;
        $settings->payment_received_date = $request->has('payment_received_date')
        ? $this->getDateTime($request->payment_received_date)
        : $settings->payment_received_date;
        $settings->certified_date = $request->has('certified_date')
        ? $this->getDateTime($request->certified_date)
        : $settings->certified_date;

        $school_update = $school->update();
        $contact_update = $contact->update();
        $settings_update = $settings->update();

        if ($school_update || $contact_update || $settings_update) {
            return response()->json(['success' => 'The school was successfully updated.'], 200);
        } else {
            return response()->json(['error' => 'Failed save settings.'], 503);
        }
    }

    /**
     * Upload Contract
     *
     * @param  SchoolSettings $settings
     * @param  resource $file
     * @return Response
     */
    public static function uploadContract($settings, $file) {
        $saved_file = \Storage::putFile('contracts/' . date('Y'), $file);
        $saved_file = str_replace('contracts/', '', $saved_file);
        $settings->contract_file = $saved_file;

        $response = [
            'success' => 'Contract has been uploaded.',
            'contract_file' => $settings->contract_file
        ];

        if ($settings->update()) {
            return response()->json($response, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Failed save settings.'], 503);
        }
    }

    /**
     * Delete School (currently only available if school has no users)
     *
     * @param int $school_id
     * @return Redirect
     */
    public function delete($school_id) {
        $school = Schools::find($school_id);
        if (auth()->user()->hasAdminPermission() && !$school->users()->count()) {
            SchoolContactInfo::where('school_id', $school_id)->delete();
            SchoolSettings::where('school_id', $school_id)->delete();
            $name = $school->name;
            $school->delete();
            return redirect('eduadmin/')->with('success', $name . ' has been successfully deleted.');
        } else {
            return redirect()->back()->with('error', 'Unable to delete school');
        }
    }
}
