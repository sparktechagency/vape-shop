<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Repositories\CommentsRepository;
use App\Services\CommentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForumCommentController extends Controller
{
    protected $commentsService;
    public function __construct()
    {
        $model = new ForumComment();

        $repository = new CommentsRepository($model);
        $this->commentsService = new CommentsService($repository);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $postId = request()->query('thread_id');
            $modelType = 'forum';
            if(!$postId){
                return response()->error('Thread ID is required', 422);
            }
            $comments = $this->commentsService->getAllComments($postId, $modelType);
            if (!empty($comments) && isset($comments['data']) && !empty($comments['data'])) {
                return response()->success($comments, 'Comments retrieved successfully', 200);
            }else {
                return response()->error('No comments found for this thread', 404);
            }
        }catch (\Exception $e){
            return response()->error('Error occurred while retrieving comments', 500, $e->getMessage());
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
                'thread_id' => 'required|integer|exists:forum_threads,id',
                'comment' => 'required|string|max:500',
                'parent_id' => 'nullable|integer|exists:forum_comments,id',

            ]);

            if($validator->fails()){
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }

            $data = $validator->validated();
            $data['user_id'] = Auth::id();

            $comment = $this->commentsService->createComment($data);

            return response()->success($comment, 'Comment added successfully', 201);
        }catch (\Exception $e){
            return response()->error('Error occurred while adding comment', 500, $e->getMessage());
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
            $comment = $this->commentsService->deleteComment($id);
            if(!$comment){
                return response()->error('Comment not found', 404);
            }
            return response()->success(null, 'Comment deleted successfully');
        }catch (\Exception $e){
            return response()->error('Error occurred while deleting comment', 500, $e->getMessage());
        }
    }
}
