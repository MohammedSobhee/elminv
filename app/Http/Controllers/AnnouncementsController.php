<?php

namespace App\Http\Controllers;

use App\UserSessionData;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller {

    // AnnouncementController unused - here for posterity
    public function show(Request $request) {
        if(auth()->user()->role->slug == 'student') {
            return redirect()->to('dashboard')->with('error', 'Sorry, teachers only. :)');
        }
        // Get post_id of new announcement
        $usersess = UserSessionData::get();

        // Remove announcement id from usersess (mark as read)
        UserSessionData::where('user_id', auth()->user()->id)->update(['announcement' => 0]);

        return view('pages.announcements', [
            'announcement_id' => $usersess->announcement ?? 0
        ]);
    }
}
