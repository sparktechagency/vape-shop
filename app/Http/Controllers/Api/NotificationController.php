<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    // Cache configuration
    private const CACHE_TTL = 300; // 5 minutes for real-time notifications
    private const NOTIFICATIONS_CACHE_PREFIX = 'user_notifications';
    private const STATS_CACHE_PREFIX = 'notification_stats';

    /**
     * Generate cache key for notifications
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(json_encode($params));
        }
        return $key;
    }

    public function index()
    {
       try{
         $perPage = request()->get('per_page', 15); // Default to 15 if not specified
         $userId = Auth::id();

         // Generate cache key
         $cacheKey = $this->generateCacheKey(self::NOTIFICATIONS_CACHE_PREFIX, [
             'user_id' => $userId,
             'per_page' => $perPage,
             'page' => request()->get('page', 1)
         ]);

         // Try to get from cache first
         $notifications = Cache::tags(['notifications', 'users'])->remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
             return Auth::user()->notifications()->paginate($perPage);
         });

        return $notifications;
       }catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to fetch notifications: ' . $e->getMessage()], 500);
        }
        // return NotificationResource::collection($notifications);
    }


    public function stats()
    {
        $userId = Auth::id();

        // Generate cache key
        $cacheKey = $this->generateCacheKey(self::STATS_CACHE_PREFIX, [
            'user_id' => $userId
        ]);

        // Try to get from cache first
        $unreadCount = Cache::tags(['notifications', 'users'])->remember($cacheKey, self::CACHE_TTL, function () {
            return Auth::user()->unreadNotifications()->count();
        });

        return response()->json([
            'ok' => true,
            'unread_count' => $unreadCount,
        ]);
    }


    public function markAsRead(DatabaseNotification $notification)
    {
        try{
            if (Auth::id() !== $notification->notifiable_id) {
                return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
            }

            $notification->markAsRead();

            // Clear notification cache
            $this->clearNotificationCache(Auth::id());

            return response()->json(['ok' => true, 'message' => 'Notification marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to mark notification as read: ' . $e->getMessage()], 500);
        }
    }


    public function markAllAsRead()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                 return response()->json([
                    'ok' => false,
                    'message' => 'User not authenticated.'
                ], 401); // 401 Unauthorized
            }
            $unreadNotifications = $user->unreadNotifications()->get();

            if ($unreadNotifications->isNotEmpty()) {
                $unreadNotifications->markAsRead();
            }

            // Clear notification cache
            $this->clearNotificationCache($user->id);

            return response()->json([
                'ok' => true,
                'message' => 'All unread notifications marked as read.'
            ]);
        } catch (Exception $e) {

            Log::error('Failed to mark all notifications as read: ' . $e->getMessage());

            // Avoid sending detailed exception messages to the client.
            return response()->json([
                'ok' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }


    public function destroy(DatabaseNotification $notification)
    {
        try {
            if (Auth::id() !== $notification->notifiable_id) {
                return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
            }

            $notification->delete();

            // Clear notification cache
            $this->clearNotificationCache(Auth::id());

            return response()->json(['ok' => true, 'message' => 'Notification deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to delete notification: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear notification cache for specific user
     */
    private function clearNotificationCache($userId): void
    {
        Cache::tags(['notifications', 'users'])->flush();
    }
}
