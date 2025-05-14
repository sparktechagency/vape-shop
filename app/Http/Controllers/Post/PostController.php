<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostRequest;
use App\Services\Post\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{

    protected $postService;
    public function __construct(PostService $postService)
    {
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
                return response()->errorResponse('No posts found', 404);
            }
            return response()->successResponse($posts, 'Posts retrieved successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to retrieve posts', 500, $e->getMessage());
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
            return response()->successResponse($post,'Post created successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to create post', 500, $e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = $this->postService->getPostById($id);
            if (!$post) {
                return response()->errorResponse('Post not found', 404);
            }
            return response()->successResponse($post, 'Post retrieved successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to retrieve post', 500, $e->getMessage());
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
                return response()->errorResponse('Post not found', 404);
            }
            return response()->successResponse($post, 'Post updated successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to update post', 500, $e->getMessage());
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
                return response()->errorResponse('Post not found', 404);
            }
            return response()->successResponse(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            return response()->errorResponse('Failed to delete post', 500, $e->getMessage());
        }
    }
}
