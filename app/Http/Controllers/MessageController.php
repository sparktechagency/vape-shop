<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $chatList = Message::with(['receiver:id,first_name,last_name,avatar', 'sender:id,first_name,last_name,avatar'])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            });

        // Apply search
        if ($search) {
            $chatList->where(function ($query) use ($search) {
                // Filter receiver side
                $query->whereHas('receiver', function ($q) use ($search) {
                    if ($search) {
                        $q->where('first_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                            ->orWhere('email', 'LIKE', '%' . $search . '%');
                    }
                })
                // OR filter sender side
                    ->orWhereHas('sender', function ($q) use ($search) {
                        if ($search) {
                            $q->where('first_name', 'LIKE', '%' . $search . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                                ->orWhere('email', 'LIKE', '%' . $search . '%');
                        }
                    });
            });
        }

        $chatList = $chatList->latest('created_at')->get()->unique(function ($message) use ($userId) {
            return $message->sender_id === $userId
            ? $message->receiver_id
            : $message->sender_id;
        })->values();

        $chatList = $chatList->map(function ($message) use ($userId) {
            $message->user = $message->sender_id === $userId
            ? $message->receiver
            : $message->sender;

            unset($message->sender, $message->receiver);
            return $message;
        });

        return response()->json([
            'status'    => true,
            'chat_list' => $chatList,
        ]);
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
            'data'    => $message], 200);
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
            ->orderBy('created_at', 'asc')
            ->paginate($per_page);

        return response()->json([
            'status'  => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $messages,
        ]);
    }

}
