<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\ForumThreadRequest;
use App\Services\Forum\ForumThreadService;
use Illuminate\Http\Request;

class ForumThreadController extends Controller
{
    protected $forumThreadService;
    public function __construct(ForumThreadService $forumThreadService)
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('guest')->only(['index', 'show']);
        $this->middleware('banned');
        $this->forumThreadService = $forumThreadService;
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
            $threads = $this->forumThreadService->getAllThreads($groupId);
            if (!empty($threads) && isset($threads['data']) && !empty($threads['data'])) {
                return response()->success($threads, 'Threads retrieved successfully', 200);

            }else {
                return response()->error('No threads found for this group', 404);
            }

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
    public function store(ForumThreadRequest $request)
    {
        try {
            $data = $request->validated();
            $thread = $this->forumThreadService->createThread($data);
            return response()->success($thread, 'Thread created successfully', 201 );
        } catch (\Exception $e) {
            return response()->error('Failed to create thread', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            $this->forumThreadService->incrementViewCount((int) $id);
            $thread = $this->forumThreadService->getThreadById($id);
            if (!$thread) {
                return response()->error('Thread not found', 404);
            }
            return response()->success($thread, 'Thread retrieved successfully');
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
            $data = $request->validated();
            $thread = $this->forumThreadService->updateThread($data, (int)$id);
            if (!$thread) {
                return response()->error('Thread not found or update failed', 404);
            }
            return response()->success($thread, 'Thread updated successfully');
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
            $result = $this->forumThreadService->deleteThread($id);
            if ($result) {
                return response()->success(null, 'Thread deleted successfully');
            } else {
                return response()->error('Thread not found or delete failed', 404);
            }
        } catch (\Exception $e) {
            return response()->error('Failed to delete thread', 500, $e->getMessage());
        }
    }
}
