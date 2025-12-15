<?php

namespace App\Services\Post;

use App\Interfaces\Post\PostInterface;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;


class PostService
{
    use FileUploadTrait;
    protected $postRepository;

    public function __construct(PostInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createPost(array $data, Request $request)
    {
        if ($data['content_type'] ?? 'post' === 'article') {

            if ($request->hasFile('image')) {
                $data['article_image_path'] = $this->handleFileUpload(
                    $request,
                    'image',
                    'articles',
                    1920,
                    1080,
                    85,
                    true
                );
            }
        } else {

            $data['image_paths'] = [];

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $imageFile) {
                    $originalFileName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanFileName = \Illuminate\Support\Str::slug($originalFileName);
                    $fileName = time() . '_' . $cleanFileName . '.' . $imageFile->getClientOriginalExtension();

                    $path = Storage::disk('public')->put(
                        'posts',
                        $imageFile
                    );
                    $data['image_paths'][] = $path;
                }
            }
        }
        return $this->postRepository->createPost($data);
    }

    public function updatePost(int $postId, array $data)
    {
        $post = $this->postRepository->getPostById($postId);

        if (!$post) {
            return null;
        }

        $contentType = $data['content_type'] ?? $post->content_type;

        /* ---------- Article Image (Single) ---------- */
        if ($contentType === 'article') {

            if (request()->hasFile('image')) {

                // delete old image if exists
                if ($post->article_image) {
                    $oldPath = getStorageFilePath($post->article_image);
                    Storage::disk('public')->delete($oldPath);
                }

                // upload new image
                $data['article_image'] = $this->handleFileUpload(
                    request(),
                    'image',
                    'articles',
                    1920,
                    1080,
                    85,
                    true
                );
            } else {
                unset($data['article_image']);
            }
        }

        /* ---------- Post Gallery (Multiple) ---------- */
        if ($contentType === 'post' && request()->hasFile('images')) {

            $data['new_gallery_images'] = collect(request()->file('images'))
                ->map(function ($image) {
                    $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $fileName = time() . '_' . \Str::slug($name) . '.' . $image->getClientOriginalExtension();

                    $path = Storage::disk('public')->putFileAs('posts', $image, $fileName);

                    return 'storage/' . $path;
                })
                ->toArray();
        }

        // remove raw file inputs
        unset($data['image'], $data['images']);

        return $this->postRepository->updatePost($postId, $data);
    }


    public function deletePost(int $postId)
    {
        $post = $this->postRepository->getPostById($postId);

        if (!$post) {
            return false;
        }

        /* ---------- Delete Article Image ---------- */
        if ($post->content_type === 'article' && $post->article_image) {
            Storage::disk('public')->delete(
                getStorageFilePath($post->article_image)
            );
        }

        /* ---------- Delete Gallery Images ---------- */
        if ($post->content_type === 'post') {
            foreach ($post->postImages as $image) {
                if ($image->image_path) {
                    Storage::disk('public')->delete(
                        getStorageFilePath($image->image_path)
                    );
                }
            }
        }

        return $this->postRepository->deletePost($postId);
    }


    public function getPostById(int $postId)
    {
        return $this->postRepository->getPostById($postId);
    }

    public function getAllPosts()
    {
        return $this->postRepository->getAllPosts();
    }

    public function getTrendingPosts()
    {
        return $this->postRepository->getTrendingPosts();
    }
}
