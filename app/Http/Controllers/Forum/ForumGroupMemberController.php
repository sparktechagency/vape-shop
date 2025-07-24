<?php
namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ForumGroup;
use App\Models\User;
use Illuminate\Http\Request;

class ForumGroupMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // join privete group
    public function requestToJoin(ForumGroup $group)
    {
        if ($group->type === 'public') {
            return response()->error('This is a public group, you can join directly.', 400);
        }
        $group->members()->syncWithoutDetaching([auth()->id() => ['status' => 'pending']]);
        return response()->success(null, 'Your request to join has been sent.');
    }

    //
    public function listJoinRequests(ForumGroup $group)
    {
        $this->authorize('manage', $group);
        $requests = $group->pendingRequests()->paginate(15);
        return UserResource::collection($requests);
    }

    // approve join request
    public function approveRequest(ForumGroup $group, User $user)
    {
        $this->authorize('manage', $group);
        $group->members()->updateExistingPivot($user->id, ['status' => 'approved']);
        return response()->success(null, 'Join request approved.');
    }
}
