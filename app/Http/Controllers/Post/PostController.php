<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post;
use App\Services\Post\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{

    protected $postService;
    public function __construct(PostService $postService)
    {
        $this->middleware('jwt.auth')->except(['index', 'show']);
        $this->middleware('check.subscription')->except(['index', 'show']);
        $this->middleware('guest')->only(['index', 'show']);
        $this->middleware('banned');
        $this->postService = $postService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $posts = $this->postService->getAllPosts();
            if ($posts->isEmpty()) {
                return response()->error('No posts found', 404);
            }
            return response()->success($posts, 'Posts retrieved successfully');
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve posts', 500, $e->getMessage());
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
    public function store(PostRequest $request)
    {
        try {
            $data = $request->validated();
            $post = $this->postService->createPost($data);
            return response()->success($post, 'Post created successfully');
        } catch (\Exception $e) {
            return response()->error('Failed to create post', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $contentType = request()->query('content_type', 'post');
            $post = Post::with([
                'user:id,first_name,last_name,role,avatar',

                'comments' => function ($query) {
                    $query->whereNull('parent_id')
                        ->with(['user:id,first_name,last_name,role,avatar']);
                },
                'comments.replies'
            ])
                ->where('id', $id);
            if ($contentType === 'article') {
                $post->where('content_type', 'article');
            } else {
                $post->where('content_type', 'post');
            }
            $post = $post->first();

            if (!$post) {
                $message = $contentType === 'article' ? 'Article not found' : 'Post not found';
                return response()->error($message, 404);
            }
            $post->makeVisible('user');
            return response()->success($post, 'Post retrieved successfully');
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve post', 500, $e->getMessage());
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
    public function update(PostRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            $post = $this->postService->updatePost($id, $data);
            if (!$post) {
                return response()->error('Post not found', 404);
            }
            return response()->success($post, 'Post updated successfully');
        } catch (\Exception $e) {
            return response()->error('Failed to update post', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deleted = $this->postService->deletePost($id);
            if (!$deleted) {
                return response()->error('Post not found', 404);
            }
            return response()->success(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            return response()->error('Failed to delete post', 500, $e->getMessage());
        }
    }

    // get all posts by user
    public function getPostsByUserId($userId){
        try {
            $perPage = request()->input('per_page', 10);
            $posts = Post::with([
                'user:id,first_name,last_name,role,avatar',
                'comments' => function ($query) {
                    $query->whereNull('parent_id')
                        ->with(['user:id,first_name,last_name,role,avatar']);
                },
                'comments.replies'
            ])
            ->where('user_id', $userId)
            ->paginate($perPage);
            if ($posts->isEmpty()) {
                return response()->error('No posts found for this user', 404);
            }
            return response()->success($posts, 'Posts retrieved successfully');

        } catch (\Exception $e) {
            return response()->error('Failed to retrieve posts', 500, $e->getMessage());
        }
    }
}
