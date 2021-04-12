<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\VideoConService;

class VideoConController extends Controller {
    /**
     * show temp test page
     *
     * @return resource - View -- not used
     */
    public function show() {
        return view('pages.videocon', [
            'services_available' => VideoConService::getServices(),
            'videoconlist' => VideoConService::channelList() ?? [],
            'conferences' => VideoConService::fetchConferences() ?? []
        ]);
    }

    /**
     * Create, update and send alert
     *
     * @param  Request $request
     * @return void
     */
    public function send(Request $request) {
        return VideoConService::createOrUpdateMeeting($request);
    }

    /**
     * Search Meetings
     *
     * @param  Request $request
     * @return object|null
     */
    public function search(Request $request) {
        return VideoConService::searchMeetings($request);
    }

    /**
     * Delete video conferences
     *
     * @param  Request $request
     * @return Response
     */
    public function delete(Request $request) {
        return VideoConService::deleteMeeting($request);
    }
}
