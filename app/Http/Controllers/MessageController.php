<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Messages;
use App\MessagesType;
use App\Classes;
use App\Teams;
use App\User;

use App\Services\MessagesService;

class MessageController extends Controller {

    /**
     * Show Messaging View
     *
     * @param  string $subpage
     * @return resource - View
     */
    public function show($subpage = '') {
        // Return view
        $msgs = MessagesService::getSentMessages($subpage);
        return view('pages.messages', [
            'messages' => $msgs->messages,
            'userlist' => $msgs->userList ?? [],
            'classlist' => $msgs->classList ?? [],
            'teamlist' => $msgs->teamList ?? [],
            'archive_count' => $msgs->archiveCount
        ]);
    }

    /**
     * Send Message
     *
     * @param  Request $request
     * @return Response
     */
    public function send(Request $request) {
        if ($request->input('id') === 0 && $request->input('type') == 1) {
            $result = static::sendAllClasses($request);
            if (is_array($result)) {
                return response()->json(['success' => 'Saved message', 'messages' => $result], 200);
            } else {
                return response()->json(['error' => 'Failed to save message.'], 503);
            }
        } else {
            $m = new Messages;
            $m->type = $request->input('type');
            $m->recipient_id = $request->input('id');
            $m->sender_id = $request->input('sender_id');
            $m->content = $request->input('message');

            if ($m->save()) {
                return response()->json(['success' => 'Successfully saved message.', 'message' => static::getMessage($m->id)], 200);
            } else {
                return response()->json(['error' => 'Failed to save message'], 503);
            }
        }
    }

    /**
     * Update Message
     *
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request) {
        $m = Messages::find($request->input('id'));
        if ($request->has('archive')) {
            $m->timestamps = false;
            $m->archive = $request->archive;
        } else if ($request->message) {
            $m->content = $request->message;
        }
        if ($m->save()) {
            $response = [
                'success' => 'Successfully updated message',
                'date' => $m->updated_at->format('F j, Y'),
                'updated' => $m->updated_at->diffForHumans()
            ];
            return response()->json($response, 200);
        } else {
            return response()->json(['error' => 'Failed to update message'], 503);
        }
    }

    /**
     * Delete message
     *
     * @param  Request $request
     * @return Response
     */
    public function delete(Request $request) {
        $m = Messages::find($request->input('id'));
        if ($m->delete()) {
            return response()->json(['success' => 'Successfully deleted message'], 200);
        } else {
            return response()->json(['error' => 'Failed to delete message'], 503);
        }
    }

    /**
     * Get Single Message Data
     *
     * @param  int $id
     * @return array
     */
    private static function getMessage($id) {
        $msg = Messages::find($id);
        $message['id'] = $msg->id;
        $message['content'] = $msg->content;
        $message['type'] = MessagesType::find($msg->type)->name;
        $message['date'] = $msg->updated_at->format('F j, Y');
        $message['updated'] = $msg->updated_at->diffForHumans();
        switch ($msg->type) {
            case 1: // Class
                $message['name'] = Classes::find($msg->recipient_id)->class_name;
                break;

            case 2: // Team
                $message['name'] = Teams::find($msg->recipient_id)->team_name;
                break;

            case 3: // Student / User
                $message['name'] = User::find($msg->recipient_id)->full_name;
                break;
        }
        return $message;
    }

    /**
     * Send to all classes
     *
     * @param  Request $request
     * @return Response
     */
    private static function sendAllClasses(Request $request) {
        $sender_id = $request->input('sender_id');
        $message = $request->input('message');
        $classIDs = Classes::join('class_members', 'class_members.class_id', 'class.id')
            ->where('class_members.user_id', $sender_id)
            ->whereIn('class_members.role_id', [3, 7])
            ->pluck('class.id')->all();

        foreach ($classIDs as $cid) {
            $m = new Messages;
            $m->type = 1;
            $m->recipient_id = $cid;
            $m->sender_id = $sender_id;
            $m->content = $message;
            $m->save();
            $messages[] = static::getMessage($m->id);
        }

        if (count($messages)) {
            return $messages;
        } else {
            return false;
        }

    }
}
