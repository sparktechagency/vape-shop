<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function feed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $userID = $request->input('user_id', Auth::id());
        $user = User::find($userID);

        if (!$user) {
            return response()->error('User not found', 404);
        }

        $followingsIds = $user->following()->pluck('users.id');

        if($followingsIds->isEmpty()) {
            return response()->error(
                'You are not following anyone yet.',
                404
            );
        }

        $post = Post::whereIn('user_id', $followingsIds)
            ->where('content_type', 'post')
            ->with(['user:id,first_name,last_name,avatar,role', 'comments' => function ($query) {
                $query->select('id', 'post_id', 'user_id', 'comment', 'created_at')
                    ->with(['user:id,first_name,last_name,avatar,role','replies']);
            }])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate($perPage);

        if ($post->isEmpty()) {
            return response()->error(
                'No posts found in your feed.',
                404
            );
        }
        $post->getCollection()->makeVisible(['user']);
        return response()->success(
            $post,
            'Feed retrieved successfully.'
        );
    }
}
