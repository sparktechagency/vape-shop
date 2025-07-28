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
        $perPage = request()->query('per_page', 15);
        $this->authorize('manage', $group);
        $requests = $group->pendingRequests()->paginate($perPage);
        if ($requests->isEmpty()) {
            return response()->error('No join requests found.', 404);
        }
        return UserResource::collection($requests)->additional([
            'ok' => true,
            'message' => 'Join requests retrieved successfully',
        ]);
    }

    public function listApprovedMembers(ForumGroup $group)
    {

        $perPage = request()->query('per_page', 15);
        $this->authorize('viewMembers', $group);
        $members = $group->approvedMembers()->paginate($perPage);
        if ($members->isEmpty()) {
            return response()->error('No approved members found.', 404);
        }
        return UserResource::collection($members)->additional([
            'ok' => true,
            'message' => 'Approved members retrieved successfully',
        ]);
    }

    // approve join request
    public function approveRequest(ForumGroup $group, User $user)
    {
        $this->authorize('manage', $group);
        // check if the user has requested to join
        if (!$group->pendingRequests()->where('user_id', $user->id)->exists()) {
            return response()->error('This user has not requested to join the group.', 404);
        }
        $group->members()->updateExistingPivot($user->id, ['status' => 'approved']);
        return response()->success(null, 'Join request approved.');
    }

    public function rejectRequest(ForumGroup $group, User $user)
    {
        $this->authorize('manage', $group);
        // check if the user has requested to join
        if (!$group->pendingRequests()->where('user_id', $user->id)->exists()) {
            return response()->error('This user has not requested to join the group.', 404);
        }
        // detach the user from the group
        $group->members()->detach($user->id);
        return response()->success(null, 'Join request rejected.');
    }

    // remove a member from the group (only by owner)
    public function removeMember(ForumGroup $group, User $user)
    {
        $this->authorize('manage', $group);
        // check if the user is a member of the group
        if (!$group->members()->where('user_id', $user->id)->exists()) {
            return response()->error('This user is not a member of the group.', 404);
        }
        $group->members()->detach($user->id);
        return response()->success(null, 'Member has been removed from the group.');
    }

    // leave a group (by member)
    public function leaveGroup(ForumGroup $group)
    {
        $this->authorize('leave', $group);
        $group->members()->detach(auth()->id());
        return response()->success(null, 'You have left the group.');
    }
}
