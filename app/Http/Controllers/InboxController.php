<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inbox;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InboxController extends Controller
{
    //send a message in inbox
    public function sendMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string|max:1500',
                'parent_id' => 'nullable|exists:inboxes,id', // for replies
            ]);

            if ($validator->fails()) {
                return response()->error('Validation failed', 422, $validator->errors());
            }

            $data = $validator->validated();
            $data['sender_id'] = Auth::id();

            $message = Inbox::create($data);
            return response()->success($message, 'Message sent successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to send message.', 500, $e->getMessage());
        }
    }

    //get inbox by user id
    public function getInboxByUserId($userId)
    {
        try {
            $perPage = request()->input('per_page', 15); // Default to 15 items per page
            $inbox = Inbox::where('receiver_id', $userId)
                ->with(['sender:id,first_name,last_name,avatar,role','receiver:id,first_name,last_name,avatar,role', 'replies.sender:id,first_name,last_name,avatar,role'])
                ->withCount(['replies' => function ($query) {
                    $query->where('parent_id', '!=', null);
                }])
                ->whereNull('parent_id') // Only get top-level messages
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            if( $inbox->isEmpty()) {
                return response()->success([], 'No messages found.');
            }
            return response()->success($inbox, 'Inbox fetched successfully.');
        }

        catch (\Exception $e) {
            return response()->error('Failed to fetch inbox.', 500, $e->getMessage());
        }
    }

    //delete a message
    public function deleteMessage($id)
    {
        try {
            $message = Inbox::find($id);
            if (!$message) {
                return response()->error('Message not found.', 404);
            }
            if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
                return response()->error('Unauthorized to delete this message.', 403);
            }

            $message->delete();
            return response()->success([], 'Message deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->error('Message not found.', 404);
        } catch (\Exception $e) {
            return response()->error('Failed to delete message.', 500, $e->getMessage());
        }
    }
}
