<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class WordpressController extends Controller {
    use ActivityLogger;

    public function log(Request $request) {
        $ref = str_replace(\Config::get('app.url'), '', $request->headers->get('referer'));
        ActivityLogger::activity('Viewed ' . $ref);
        //return response(200);
        return response(file_get_contents(public_path('./files/px.gif')))->header('content-type', 'image/gif');
    }
}
