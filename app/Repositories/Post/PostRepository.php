<?php
namespace App\Repositories\Post;
use App\Interfaces\Post\PostInterface;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostRepository implements PostInterface
{
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    //create post
    public function createPost(array $data)
    {
        $data['user_id'] = Auth::id();
        return $this->post->create($data);
    }

    //update post
    public function updatePost(int $postId, array $data)
    {
        $post = $this->getPostById($postId);
        if ($post) {
            $post->update($data);
            return $post;
        }
        return null;
    }
    public function deletePost(int $postId)
    {
        $post = $this->getPostById($postId);
        if ($post) {
            $post->delete();
            return true;
        }
        return false;
    }

    //get post by id
    public function getPostById(int $postId)
    {
        return $this->post->find($postId);
    }
    public function getAllPosts()
    {
        $perPage = request()->input('per_page', 10);
        $userId = Auth::id();
        return $this->post->where('user_id',$userId)
                          ->paginate($perPage);
    }
    public function likePost(int $postId, int $userId)
    {
        $post = $this->getPostById($postId);
        if ($post) {
            return $post->likes()->create(['user_id' => $userId]);
        }
        return null;
    }
}
