<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Notifications\ArticleDeleteNotification;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    //get all articles
    public function getAllArticles(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $articles = Post::where('content_type', 'article')
                ->when($search, function ($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate($perPage);

            if ($articles->isEmpty()) {
                return response()->error('No articles found.', 404);
            }
            return response()->success($articles, 'Articles retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve articles', 500, $e->getMessage());
            //throw $th;
        }
    }



    //delete article
    public function deleteArticle($id)
    {
        try {
            $article = Post::where('content_type', 'article')->where('id', $id)->first();
            if (!$article) {
                return response()->error('Article not found', 404);
            }

            //notify the user about the deletion
            $article->user->notify(new ArticleDeleteNotification($article));
            $article->delete();
            return response()->success(null, 'Article deleted successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to delete article', 500, $e->getMessage());
        }
    }
}
