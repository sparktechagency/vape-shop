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
        if ($data['content_type'] === 'article') {

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
        $articleImage = request()->file('article_image') ?? null;
        $data['article_image'] = $articleImage;
        return $this->postRepository->updatePost($postId, $data);
    }

    public function deletePost(int $postId)
    {
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
}
