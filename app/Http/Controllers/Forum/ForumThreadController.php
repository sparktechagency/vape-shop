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
use Illuminate\Support\Facades\Cache;

class ForumThreadController extends Controller
{
    protected $forumThreadService;

    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const THREAD_INDEX_CACHE_PREFIX = 'forum_threads_index';
    private const THREAD_SHOW_CACHE_PREFIX = 'forum_thread_show';

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
     * Generate cache key for forum threads
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(json_encode($params));
        }
        return $key;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $groupId = request()->query('group_id');
            if (!$groupId) {
                return response()->error('Group ID is required', 400);
            }

            // Fetch the group to ensure it exists and the user has permission to view threads
            $group = ForumGroup::findOrFail($groupId);

            // Generate cache key based on group ID, pagination, and filtering parameters
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 10);
            $isMostViewed = request()->get('is_most_viewed', false);

            $cacheKey = $this->generateCacheKey(self::THREAD_INDEX_CACHE_PREFIX, [
                'group_id' => $groupId,
                'page' => $page,
                'per_page' => $perPage,
                'is_most_viewed' => $isMostViewed
            ]);

            // Use cache for forum threads with tags
            $threads = Cache::tags(['forum', 'threads', 'groups'])->remember($cacheKey, self::CACHE_TTL, function () use ($groupId) {
                return $this->forumThreadService->getAllThreads($groupId);
            });

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
            // $this->authorize('create', [ForumThread::class, $group]);

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
            // Generate cache key for specific thread
            $cacheKey = $this->generateCacheKey(self::THREAD_SHOW_CACHE_PREFIX, ['id' => $id]);

            // Use cache for single thread with tags (shorter TTL due to view count increment)
            $thread = Cache::tags(['forum', 'threads'])->remember($cacheKey, 600, function () use ($id) { // 10 minutes
                return $this->forumThreadService->getThreadById($id);
            });

            if (!$thread) {
                return response()->error('Thread not found', 404);
            }

            // POLICY CHECK: Check if the user can view this thread
            // $this->authorize('view', $thread);

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
            // dd($thread);
            // Check if the user can delete this thread
            $this->authorize('delete', $thread);

            $thread = $this->forumThreadService->deleteThread($id);
            if (!$thread) {
                return response()->error('Thread not found or you do not have permission to delete it', 404);
            }

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
