<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Requests\FeaturedAdRequest;
use App\Http\Resources\AdFeaturedResourc;
use App\Models\FeaturedAd;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewFeturedAdRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class FeaturedAdRequestController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware(['jwt.auth', 'check.role:' . Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value])->except(['index', 'show']);
    // }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       try{
         $perPage = request()->get('per_page', 10); // Default to 10 items per page
        $user = Auth::user();
        $featuredAds = FeaturedAd::with(['user:id,first_name,last_name,avatar,role', 'region.country', 'featuredArticle'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        if ($featuredAds->isEmpty()) {
            return response()->error(
                'No featured ads found.',
                404
            );
        }
        return AdFeaturedResourc::collection($featuredAds);
       }catch (\Exception $e) {
           return response()->error(
               'An error occurred while retrieving featured ads: ' . $e->getMessage(),
               500
           );
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
    public function store(FeaturedAdRequest $request)
    {
        try {
            $user = Auth::user();
            $validatedData = $request->validated();

            //check user article availability
            $article = Post::where('id', $validatedData['featured_article_id'])
                ->where('content_type', 'article')
                ->first();
            if($article->user_id !== $user->id) {
                return response()->error('You do not have permission to use this article for featured ad.', 403);
            }

            $feturedAd = FeaturedAd::create([
                'featured_article_id' => $validatedData['featured_article_id'],
                'region_id' => $validatedData['region_id'],
                'preferred_duration' => $validatedData['preferred_duration'],
                'amount' => $validatedData['amount'],
                'slot' => $validatedData['slot'] ?? null,
                'user_id' => $user->id,
                'requested_at' => now(),
            ]);

            //send notification admin
            $admins = User::where('role', Role::ADMIN->value)->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewFeturedAdRequestNotification($feturedAd));
            }

            return response()->success(
                $feturedAd,
                'Featured ad request created successfully.'
            );
        } catch (\Exception $e) {
            return response()->error('An error occurred while creating the featured ad request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
      try{
          $feturedAd = FeaturedAd::with(['user', 'region', 'featuredArticle'])->find($id);
        if (!$feturedAd) {
            return response()->error('Featured ad request not found.', 404);
        }
        return new AdFeaturedResourc($feturedAd);
      }catch (\Exception $e) {
          return response()->error('An error occurred while retrieving the featured ad request: ' . $e->getMessage(), 500);
      }

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
        //
    }
}
