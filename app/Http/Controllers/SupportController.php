<?php

namespace App\Http\Controllers;

use App\Assignment;
use Illuminate\Http\Request;
use Mail;
use App\Mail\SupportEmail;

class SupportController extends Controller {
    /**
     * Show support form
     *
     * @param  string $type
     * @param  string|int $id
     * @return resource - View
     */
    public function show($type = '', $id = 0) {

        if($type === 'screenshot') {
            $a = Assignment::find($id);
            if($a) {
                $message = "Please correct the generated image that is malformed in the assignment named '{$a->assignment_name}' (ID: {$a->id}).";
            }
        }

        return view('pages.support', [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'subject' => $id ? 'bug' : '',
            'message' => $message ?? ''
        ]);
    }

    /**
     * Send Support Email
     *
     * @param  mixed $request
     * @return void
     */
    public function send(Request $request) {
        $message = str_replace("\'", "'", $request->bodyMessage);
        $school = auth()->user()->school->name;
        Mail::to('donotreply@inventionlandinstitute.com')
            ->bcc(\Config::get('mail.admin_notification'))
            ->send(new SupportEmail($request->subject, $request->email, $request->name, $school, $message));
        return redirect()->back()->with('success', 'Message has been sent. We will respond within 1 business day.')->withInput($request->only('bodyMessage'));
    }
}
