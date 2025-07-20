<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class MessageController extends Controller
{
    public function searchNewUser(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $users = User::where('id', '!=', Auth::id())
            ->whereNull('banned_at')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Data retrieved successfully',
            'data'    => $users,
        ]);
    }

    public function chatList(Request $request)
    {
        $userId = Auth::id();
        $search = $request->search;

        $chatListQuery = Message::with(['receiver:id,first_name,last_name,avatar', 'sender:id,first_name,last_name,avatar'])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            });

        // Apply search if provided
        if ($search) {
            $chatListQuery->where(function ($query) use ($search, $userId) {
                $query->whereHas('receiver', function ($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%');
                })
                    ->orWhereHas('sender', function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('email', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Get the latest message for each conversation to form the chat list
        $latestMessages = $chatListQuery->latest('created_at')->get()->unique(function ($message) use ($userId) {
            return $message->sender_id === $userId ? $message->receiver_id : $message->sender_id;
        });

        // Get the IDs of the other users in the conversations
        $partnerIds = $latestMessages->map(function ($message) use ($userId) {
            return $message->sender_id === $userId ? $message->receiver_id : $message->sender_id;
        });

         $unreadCounts = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->whereIn('sender_id', $partnerIds)
            ->select('sender_id', DB::raw('count(*) as messages_count'))
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id'); // keyBy makes it easy to look up counts by sender_id


        $chatList = $latestMessages->map(function ($message) use ($userId, $unreadCounts) {

            $partner = $message->sender_id === $userId ? $message->receiver : $message->sender;
            $unreadCount = $unreadCounts->get($partner->id)?->messages_count ?? 0;
            $message->user = $partner;
            $message->unread_messages_count = $unreadCount;
            unset($message->sender, $message->receiver);

            return $message;
        });

        // return $chatList->values();
        return response()->json([
            'status'    => true,
            'chat_list' => $chatList->values(),
        ]); // values() resets array keys
    }

    //mark all messages as read
     public function markAsRead($senderId)
    {
      $userId = Auth::id();
       $read = Message::where('sender_id', $senderId)
               ->where('receiver_id', $userId)
               ->where('is_read', false) // Only update unread messages
               ->update(['is_read' => true]);

        return response()->success(
            $read,
            'Messages marked as read successfully',
            200
        );
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|numeric',
            'message'     => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error', $validator->errors()]);
        }
        $message = Message::create([
            'sender_id'   => Auth::user()->id,
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'is_read'     => 0,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Message saved successfully',
            'data'    => $message
        ], 200);
    }

    public function getMessage(Request $request)
    {
        $per_page  = $request->per_page ?? 10;
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error', $validator->errors()]);
        }

        $messages = Message::where(function ($query) use ($request) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $request->receiver_id);
        })
            ->orWhere(function ($query) use ($request) {
                $query->where('sender_id', $request->receiver_id)
                    ->where('receiver_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page);
        $messages->transform(function ($message) {
            $message->is_sender = $message->sender_id == auth()->id();
            return $message;
        });
        return response()->json([
            'status'  => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $messages,
        ]);
    }
}
