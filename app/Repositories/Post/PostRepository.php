<?php

namespace App\Repositories\Post;

use App\Enums\UserRole\Role;
use App\Interfaces\Post\PostInterface;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostRepository implements PostInterface
{
    use FileUploadTrait;
    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    //create post
    public function createPost(array $data, ?Request $request = null)
    {
        $post = DB::transaction(function () use ($data) {

            $postData = [
                'title'     => $data['title'] ?? null,
                'content'   => $data['content'],
                'user_id'   => Auth::id(),
                'content_type'  => $data['content_type'],
                'is_in_gallery' => $data['is_in_gallery'] ?? false,
                'article_image' => null,
            ];

            if ($data['content_type'] === 'article') {
                $postData['article_image'] = $data['article_image_path'] ?? null;
                $post = $this->post->create($postData);
            } else {
                $post = $this->post->create($postData);
                if (!empty($data['image_paths'])) {
                    foreach ($data['image_paths'] as $path) {
                        $post->postImages()->create([
                            'image_path' => $path,
                        ]);
                    }
                }
            }


            return $post;
        });

        if ($post->content_type === 'post') {
            $post->load('postImages');
        }
        return $post;
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
            } else {
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
        $isInGallery = request()->boolean('is_in_gallery', false);
        $userId = Auth::id();
        // dd($content_type, $isGlobal, $userId);
        if ($content_type === 'article') {
            $query = $this->post->where('content_type', 'article');
        } else {
            $query = $this->post->with('postImages')->where('content_type', 'post');
        }
        if (!$isGlobal && $userId) {
            $query->where('user_id', $userId);
        }
        if ($regionId) {
            $query->whereRelation('user.address', 'region_id', $regionId);
        }
        if ($isInGallery) {
            $query->where('is_in_gallery', true);
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


    public function getTrendingPosts()
    {
        $userId = Auth::id();
        $limit = request()->get('limit', 10);

        return $this->post->query()
            ->with('postImages')
            ->withCount('hearts')

            ->withExists(['hearts as is_hearted' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->where('content_type', 'post')
            ->where('is_in_gallery', true)
            ->has('hearts')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('hearts_count', 'desc')

            ->orderBy('created_at', 'desc')

            ->take($limit)
            ->get();
    }
}
