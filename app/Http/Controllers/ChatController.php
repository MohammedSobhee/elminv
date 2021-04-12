<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Events\ChatEvent;
use App\Events\ChatDeleteEvent;
use Illuminate\Http\Request;

use App\Services\ChatService;

/*
|--------------------------------------------------------------------------
| ctype_id - ctype    Chat Types
|--------------------------------------------------------------------------
|
| 1 - class
| 2 - team
| 3 - private
|
 */

class ChatController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Popup window chat index
     *
     * @param  string $ctype
     * @param  int $ctype_id
     * @return resource Chat page for popup
     */
    public function index($ctype, $ctype_id) {
        return view('pages.chat', [
            'messages' => ChatService::fetchMessages($ctype, $ctype_id),
            'ctype' => $ctype,
            'ctype_id' => $ctype_id
        ]);
    }

    /**
     * Fetch all messages
     *
     * @param  string $ctype
     * @param  int $ctype_id
     * @return object List of messages
     */
    public static function fetch($ctype, $ctype_id) {
        return ChatService::fetchMessages($ctype, $ctype_id);
    }

    /**
     * Send Message
     *
     * @param  Request $request
     * @return object - Json object
     */
    public function send(Request $request) {
        $chat = auth()->user()->chats()->create([
            'message' => $request->message,
            'ctype' => $request->ctype,
            'ctype_id' => $request->ctype_id
        ]);
        broadcast(new ChatEvent($chat->load('user'), $request->ctype, $request->ctype_id))->toOthers();

        return ['id' => $chat->id];
    }

    /**
     * Delete messages
     *
     * @param  Request $request
     * @return Response
     */
    public function delete(Request $request) {
        $chat = Chat::find($request->id);
        broadcast(new ChatDeleteEvent($chat->id, $chat->ctype, $chat->ctype_id))->toOthers();

        if ($chat->delete()) {
            return response()->json(['success' => 'Successfully deleted message'], 200);
        } else {
            return response()->json(['error' => 'Failed to message message'], 503);
        }
    }

    /**
     * Download log
     *
     * @param  Request $request
     * @return resource - CSV file of chat log
     */
    public function download(Request $request) {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=chat_log.csv',
            'Expires' => '0',
            'Pragma' => 'public'
        ];

        $list = Chat::where([
            'ctype' => $request->ctype,
            'ctype_id' => $request->ctype_id])
            ->join('users', 'users.id', 'chats.user_id')
            ->select('chats.created_at as timestamp', 'users.name', 'users.nickname', 'chats.message')
            ->get();

        $list = $list->each(function ($item) {
            $item->setAppends([]);
        })->toArray();

        array_unshift($list, array_keys($list[0]));
        $callback = function() use ($list) {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);
    }

}
