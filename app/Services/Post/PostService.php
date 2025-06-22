<?php
namespace App\Services\Post;
use App\Interfaces\Post\PostInterface;

class PostService
{
    protected $postRepository;

    public function __construct(PostInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createPost(array $data)
    {
        $articleImage = request()->file('article_image') ?? null;
        if ($articleImage) {
            $data['article_image'] = $articleImage->store('articles', 'public');
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
