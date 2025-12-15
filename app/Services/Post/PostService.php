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

        /* ---------- Delete selected gallery images ---------- */
        if (!empty($data['deleted_image_ids'])) {

            foreach ($data['deleted_image_ids'] as $imageId) {
                $image = $post->postImages()->find($imageId);

                if ($image) {
                    if ($image->image_path) {
                        $path = getStorageFilePath($image->image_path);
                        Storage::disk('public')->delete($path);
                    }

                    $image->delete();
                }
            }

            unset($data['deleted_image_ids']);
        }

        /* ---------- Add new gallery images ---------- */
        if ($contentType === 'post' && request()->hasFile('images')) {

            $galleryPaths = [];

            foreach (request()->file('images') as $imageFile) {
                $fileName = time() . '_' .
                    \Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) .
                    '.' . $imageFile->getClientOriginalExtension();

                $path = Storage::disk('public')->putFileAs('posts', $imageFile, $fileName);

                // store path with storage prefix

                $galleryPaths[] = $path;
            }

            $data['new_gallery_images'] = $galleryPaths;
        }

        /* ---------- Update article image ---------- */
        if ($contentType === 'article') {

            if (request()->hasFile('image')) {

                if ($post->article_image) {
                    $oldPath = getStorageFilePath($post->article_image);
                    Storage::disk('public')->delete($oldPath);
                }

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
