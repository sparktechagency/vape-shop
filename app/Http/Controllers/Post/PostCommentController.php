<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Services\Post\PostCommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostCommentController extends Controller
{
    protected $postCommentService;
    public function __construct(PostCommentService $postCommentService)
    {
        $this->postCommentService = $postCommentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $postId = request()->query('post_id');
            if(!$postId){
                return response()->errorResponse('Post ID is required', 422);
            }
            $comments = $this->postCommentService->getAllComments($postId);
            return response()->successResponse($comments, 'Comments retrieved successfully');
        }catch (\Exception $e){
            return response()->errorResponse('Error occurred while retrieving comments', 500, $e->getMessage());
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
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|integer|exists:posts,id',
                'comment' => 'required|string|max:500',
                'parent_id' => 'nullable|integer|exists:post_comments,id',

            ]);

            if($validator->fails()){
                return response()->errorResponse($validator->errors()->first(), 422, $validator->errors());
            }

            $data = $validator->validated();
            $data['user_id'] = Auth::id();

            $comment = $this->postCommentService->createComment($data);

            return response()->successResponse($comment, 'Comment added successfully', 201);
        }catch (\Exception $e){
            return response()->errorResponse('Error occurred while adding comment', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $comment = $this->postCommentService->deleteComment($id);
            if(!$comment){
                return response()->errorResponse('Comment not found', 404);
            }
            return response()->successResponse(null, 'Comment deleted successfully');
        }catch (\Exception $e){
            return response()->errorResponse('Error occurred while deleting comment', 500, $e->getMessage());
        }
    }
}
