<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{

    public function index()
    {
        $perPage = request()->get('per_page', 15); // Default to 15 if not specified
        $notifications = Auth::user()->notifications()->paginate($perPage);
        return $notifications;
        // return NotificationResource::collection($notifications);
    }


    public function stats()
    {
        return response()->json([
            'ok' => true,
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }


    public function markAsRead(DatabaseNotification $notification)
    {

        if (Auth::id() !== $notification->notifiable_id) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $notification->markAsRead();

        return response()->json(['ok' => true, 'message' => 'Notification marked as read.']);
    }


    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true, 'message' => 'All unread notifications marked as read.']);
    }


    public function destroy(DatabaseNotification $notification)
    {

        if (Auth::id() !== $notification->notifiable_id) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $notification->delete();

        return response()->json(null, 204);
    }
}
