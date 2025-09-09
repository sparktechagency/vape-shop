<?php

namespace App\Repositories\Post;

use App\Enums\UserRole\Role;
use App\Interfaces\Post\PostInterface;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUploadTrait;

class PostRepository implements PostInterface
{
    use FileUploadTrait;
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    //create post
    public function createPost(array $data)
    {
        $content_type = request()->get('content_type', 'post');
        if ($content_type === 'article') {
            $data['content_type'] = 'article';
        } else {
            $data['content_type'] = 'post';
        }
        $data['user_id'] = Auth::id();
        // if ($content_type !== 'article' && (Auth::user()->role === Role::MEMBER->value || Auth::user()->role === Role::ADMIN->value || Auth::user()->role === Role::ASSOCIATION)) {
        //     throw new \Exception('You are not allowed to create a post');
        // }
        return $this->post->create($data);
    }

    //update post
    public function updatePost(int $postId, array $data)
    {
        $user = Auth::user();
        $post = $this->getPostById($postId);

        if ($post) {
            if (isset($data['article_image'])) {
                //remove old image if exists
                $oldImagePath = getStorageFilePath($post->article_image);

                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete(($oldImagePath));
                }
                // $data['article_image'] = $data['article_image']->store('articles', 'public');
                $data['article_image'] = $this->handleFileUpload(
                    request(),
                    'article_image',
                    'articles',
                    1920, // width
                    1080, // height
                    85, // quality
                    true // forceWebp
                );
            }else{
                $data['article_image'] = getStorageFilePath($post->article_image); // keep old image if not updated
            }

            $post->update($data);
            return $post;
        }
        return null;
    }
    public function deletePost(int $postId)
    {
        $post = $this->getPostById($postId);
        //remove old image if exists
        if ($post && $post->article_image) {
            $oldImagePath = getStorageFilePath($post->article_image);
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }
        //delete post
        if ($post) {
            $post->delete();
            return true;
        }
        return false;
    }

    //get post by id
    public function getPostById(int $postId)
    {
        $user = Auth::user();
        if ($user->role === Role::ADMIN->value) {
            return $this->post->where('id', $postId)->first();
        }
        return $this->post->where('user_id', $user->id)
            ->where('id', $postId)
            ->first();
    }
    public function getAllPosts()
    {
        $perPage = request()->input('per_page', 10);
        $content_type = request()->get('content_type', 'post');
        $isGlobal = request()->boolean('is_global');
        $regionId = request()->get('region_id', null);
        $userId = Auth::id();
        // dd($content_type, $isGlobal, $userId);
        if ($content_type === 'article') {
            $query = $this->post->where('content_type', 'article');
        } else {
            $query = $this->post->where('content_type', 'post');
        }
        if (!$isGlobal && $userId) {
            $query->where('user_id', $userId);
        }
        if ($regionId) {
            $query->whereRelation('user.address', 'region_id', $regionId);
        }
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
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
