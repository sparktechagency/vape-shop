<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\ForumThreadRequest;
use App\Models\ForumGroup;
use App\Models\ForumThread;
use App\Models\FourmLike;
use App\Services\Forum\ForumThreadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumThreadController extends Controller
{
    protected $forumThreadService;
    public function __construct(ForumThreadService $forumThreadService)
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('check.subscription')->except(['index', 'show']);
        $this->middleware('is.suspended')->except(['index', 'show']);
        $this->middleware('guest')->only(['index', 'show']);
        $this->middleware('banned');
        $this->forumThreadService = $forumThreadService;
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     try {
    //         $groupId = request()->query('group_id');
    //         if (!$groupId) {
    //             return response()->error('Group ID is required', 400);
    //         }
    //         $threads = $this->forumThreadService->getAllThreads($groupId);
    //         if (!empty($threads) && isset($threads['data']) && !empty($threads['data'])) {
    //             return response()->success($threads, 'Threads retrieved successfully', 200);

    //         }else {
    //             return response()->error('No threads found for this group', 404);
    //         }

    //     } catch (\Exception $e) {
    //         return response()->error('Failed to retrieve threads', 500, $e->getMessage());
    //     }
    // }

    public function index()
    {
        try {
            $groupId = request()->query('group_id');
            if (!$groupId) {
                return response()->error('Group ID is required', 400);
            }

            // Fetch the group to ensure it exists and the user has permission to view threads
            $group = ForumGroup::findOrFail($groupId);

            // POLICY CHECK: Check if the user can view threads in this group
            $this->authorize('viewAny', [ForumThread::class, $group]);

            $threads = $this->forumThreadService->getAllThreads($groupId);
            if (!empty($threads['data'])) {
                return response()->success($threads, 'Threads retrieved successfully');
            } else {
                return response()->error('No threads found for this group', 404);
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->error('You do not have permission to view threads in this group.', 403);
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve threads', 500, $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(ForumThreadRequest $request)
    // {
    //     try {
    //         $data = $request->validated();
    //         $thread = $this->forumThreadService->createThread($data);
    //         return response()->success($thread, 'Thread created successfully', 201 );
    //     } catch (\Exception $e) {
    //         return response()->error('Failed to create thread', 500, $e->getMessage());
    //     }
    // }


    public function store(ForumThreadRequest $request)
    {
        try {
            $data = $request->validated();
            $group = ForumGroup::findOrFail($data['group_id']);

            // POLICY CHECK: Check if the user can create threads in this group
            $this->authorize('create', [ForumThread::class, $group]);

            $thread = $this->forumThreadService->createThread($data);
            return response()->success($thread, 'Thread created successfully', 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->error('You do not have permission to post in this group.', 403);
        } catch (\Exception $e) {
            return response()->error('Failed to create thread', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     try {

    //         $this->forumThreadService->incrementViewCount((int) $id);
    //         $thread = $this->forumThreadService->getThreadById($id);
    //         if (!$thread) {
    //             return response()->error('Thread not found', 404);
    //         }
    //         return response()->success($thread, 'Thread retrieved successfully');
    //     } catch (\Exception $e) {
    //         return response()->error('Failed to retrieve thread', 500, $e->getMessage());
    //     }
    // }

    public function show(string $id)
    {
        try {
            $thread = $this->forumThreadService->getThreadById($id);
            if (!$thread) {
                return response()->error('Thread not found', 404);
            }

            // POLICY CHECK: Check if the user can view this thread
            $this->authorize('view', $thread);

            $this->forumThreadService->incrementViewCount((int) $id);
            return response()->success($thread->toArray(), 'Thread retrieved successfully');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->error('You do not have permission to view this thread.', 403);
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve thread', 500, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ForumThreadRequest $request, string $id)
    {
        try {
            $thread = ForumThread::findOrFail($id);

            // POLICY CHECK: Check if the user can update this thread
            $this->authorize('update', $thread);

            $data = $request->validated();
            $updatedThread = $this->forumThreadService->updateThread($data, (int)$id);
            return response()->success($updatedThread, 'Thread updated successfully');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->error('You do not have permission to update this thread.', 403);
        } catch (\Exception $e) {
            return response()->error('Failed to update thread', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $thread = ForumThread::findOrFail($id);

            // POLICY CHECK: Check if the user can delete this thread
            $this->authorize('delete', $thread);

            $this->forumThreadService->deleteThread($id);
            return response()->success(null, 'Thread deleted successfully');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->error('You do not have permission to delete this thread.', 403);
        } catch (\Exception $e) {
            return response()->error('Failed to delete thread', 500, $e->getMessage());
        }
    }

    //like a thread
    public function likeUnlikeThread(Request $request, string $id)
    {
        try {
            $user = Auth::user();

            //check if the user has already liked the thread
            $likeExists = FourmLike::where('user_id', $user->id)
                ->where('likeable_type', 'App\Models\ForumThread')
                ->where('likeable_id', $id)
                ->exists();
            //if exists, remove the like
            if ($likeExists) {
                FourmLike::where('user_id', $user->id)
                    ->where('likeable_type', 'App\Models\ForumThread')
                    ->where('likeable_id', $id)
                    ->delete();
                return response()->success(null, 'Thread unliked successfully', 200);
            } else {
                //if not exists, create a new like
                $like = new FourmLike();
                $like->user_id = $user->id;
                $like->likeable_type = 'App\Models\ForumThread';
                $like->likeable_id = $id;
                $like->save();
                return response()->success(null, 'Thread liked successfully', 200);
            }
        } catch (\Exception $e) {
            return response()->error('Failed to like thread', 500, $e->getMessage());
        }
    }
}
