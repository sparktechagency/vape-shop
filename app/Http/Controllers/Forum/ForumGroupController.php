<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\ForumGroupRequest;
use App\Services\Forum\ForumGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumGroupController extends Controller
{

    protected $forumGroupService;
    public function __construct(ForumGroupService $forumGroupService)
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('guest')->only(['index', 'show']);
        $this->middleware('banned');
        $this->forumGroupService = $forumGroupService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $result = $this->forumGroupService->getAllGroups();

            if (!empty($result) && isset($result['data']) && !empty($result['data'])) {
                return response()->success(
                    $result,
                    'Groups retrieved successfully',
                    200
                );
            } else {
                return response()->error('No groups found', 404);
            }
        }catch (\Exception $e) {
            return response()->error('Something went wrong', 500, $e->getMessage());
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
    public function store(ForumGroupRequest $request)
    {
        try{
            $data = $request->validated();
            $result = $this->forumGroupService->createGroup($data);
            if ($result) {
                return response()->success(
                    $result,
                    'Group created successfully',
                    201
                );
            } else {
                return response()->error(
                    'Failed to create group',
                    500
                );
            }
        }catch (\Exception $e) {
            return response()->error(
                'Something went wrong',
                500,
                $e->getMessage()
            );
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $result = $this->forumGroupService->getGroupById((int)$id);

            if ($result) {
                return response()->success(
                    $result,
                    'Group retrieved successfully',
                    200
                );
            } else {
                return response()->error('Group not found', 404);
            }
        }catch (\Exception $e) {
            return response()->error('Something went wrong', 500, $e->getMessage());
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
    public function update(ForumGroupRequest $request, string $id)
    {
        try{
            $data = $request->validated();
            $result = $this->forumGroupService->updateGroup((int)$id, $data);

            if ($result) {
                return response()->success(
                    $result,
                    'Group updated successfully',
                    200
                );
            } else {
                return response()->error(
                    'Failed to update group',
                    500);
            }
        }catch (\Exception $e) {
            return response()->error(
                'Something went wrong',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $result = $this->forumGroupService->deleteGroup((int)$id);

            if ($result) {
                return response()->success(
                    null,
                    'Group deleted successfully',
                    200
                );
            } else {
                return response()->error('Failed to delete group', 500);
            }
        }catch (\Exception $e) {
            return response()->error('Something went wrong', 500, $e->getMessage());
        }
    }
}
