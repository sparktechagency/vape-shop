<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\ForumGroupRequest;
use App\Models\ForumGroup;
use App\Services\Forum\ForumGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ForumGroupController extends Controller
{

    protected $forumGroupService;

    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const GROUP_INDEX_CACHE_KEY = 'forum_groups_index';
    private const GROUP_SHOW_CACHE_PREFIX = 'forum_group_show';

    public function __construct(ForumGroupService $forumGroupService)
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('guest')->only(['index', 'show']);
        $this->middleware('banned');
        $this->middleware('check.subscription')->except(['index', 'show']);
        $this->middleware('is.suspended')->except(['index', 'show']);
        $this->forumGroupService = $forumGroupService;
    }

    /**
     * Generate cache key for forum groups
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
        try{
            // Generate cache key including pagination and filtering parameters
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 10);
            $isTrending = request()->boolean('is_trending');
            $isGlobal = request()->boolean('show_front');
            $userIdFilter = request()->get('user_id');
            $isLatest = request()->boolean('is_latest', false);

            $cacheKey = $this->generateCacheKey(self::GROUP_INDEX_CACHE_KEY, [
                'page' => $page,
                'per_page' => $perPage,
                'is_trending' => $isTrending,
                'show_front' => $isGlobal,
                'user_id' => $userIdFilter,
                'is_latest' => $isLatest
            ]);

            // Use cache for forum groups listing with pagination support
            $result = Cache::tags(['forum', 'groups'])->remember($cacheKey, self::CACHE_TTL, function () {
                return $this->forumGroupService->getAllGroups();
            });

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
            // Generate cache key for specific group
            $cacheKey = $this->generateCacheKey(self::GROUP_SHOW_CACHE_PREFIX, ['id' => $id]);

            // Use cache for single group with tags
            $result = Cache::tags(['forum', 'groups'])->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
                return $this->forumGroupService->getGroupById((int)$id);
            });

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
    public function update(ForumGroupRequest $request, ForumGroup $group)
    {
        try{
            $this->authorize('manage', $group);
            $data = $request->validated();
            $result = $this->forumGroupService->updateGroup($group->id, $data);

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
