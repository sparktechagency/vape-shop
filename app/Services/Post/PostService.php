<?php
namespace App\Services\Post;
use App\Interfaces\Post\PostInterface;
use App\Traits\FileUploadTrait;

class PostService
{
    use FileUploadTrait;
    protected $postRepository;

    public function __construct(PostInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createPost(array $data)
    {
        $articleImage = request()->file('article_image') ?? null;
        if ($articleImage) {
            // $data['article_image'] = $articleImage->store('articles', 'public');
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
            $data['article_image'] = $data['article_image'] = $this->handleFileUpload(
                request(),
                'article_image',
                'posts',
                1920, // width
                1080, // height
                85, // quality
                true // forceWebp
            );

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
