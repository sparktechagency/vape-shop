<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Requests\MostFollowersAdRequest;
use App\Models\MostFollowerAd;
use App\Models\User;
use App\Notifications\MostFollowersRequestConfirmation;
use App\Notifications\NewMostFollowersAdRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MostFollowersAdsController extends Controller
{
    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const ADS_REQUEST_CACHE_PREFIX = 'most_followers_ads_request';

    /**
     * Generate cache key for ads
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        if (!empty($params)) {
            $key .= '_' . md5(json_encode($params));
        }
        return $key;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
        $user = Auth::user();
        $perPage = request()->get('per_page', 10);
        $mostFollowersAds = MostFollowerAd::with(['user:id,first_name,last_name,role,avatar','region.country'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        if ($mostFollowersAds->isEmpty()) {
            return response()->error(
                'No most followers ads found.',
                404
            );
        }
        return response()->success(
            $mostFollowersAds,
            'Most followers ads retrieved successfully.'
        );
        } catch (\Exception $e) {
            return response()->error(
                'An error occurred while retrieving most followers ads: ' . $e->getMessage(),
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
    public function store(MostFollowersAdRequest $request)
    {
        DB::beginTransaction();
        try {

            // dd(TrendingProducts::with(['product'])->get());
            // Validate the request data
            $user = Auth::user();
            $validatedData = $request->validated();

            // Create a new trending ad product using the validated data
            $mostFollowersAdRequest = MostFollowerAd::create([
                'user_id' => $user->id,
                'region_id' => $validatedData['region_id'],
                'amount' => $validatedData['amount'],
                'slot' => $validatedData['slot'],
                'status' => 'pending',
                'preferred_duration' => $validatedData['preferred_duration'],
                'requested_at' => now(),
            ]);


            // Return a success response
                //send notification to admin
                $admins = User::where('role', Role::ADMIN->value)->get();
                foreach ($admins as $admin) {
                    Notification::send($admin, new NewMostFollowersAdRequestNotification($mostFollowersAdRequest));
                }

                //notify the user
                $user->notify(new MostFollowersRequestConfirmation($mostFollowersAdRequest));
                DB::commit();
                return response()->success(
                    $mostFollowersAdRequest,
                    $response['message'] ?? 'Most follower ad request submitted successfully.',
                    201
                );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->error(
                'An error occurred while creating most followre ad request: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    //ad requests products
    public function adRequestMostFollower(Request $request)
    {
        $regionId = $request->input('region_id');

        // Generate cache key based on parameters
        $cacheKey = $this->generateCacheKey(self::ADS_REQUEST_CACHE_PREFIX, [
            'region_id' => $regionId
        ]);

        // Use cache for most followers ads with tags
        $adRequestMostFollowers = Cache::tags(['users', 'trending', 'followers', 'ads'])->remember($cacheKey, self::CACHE_TTL, function () use ($regionId) {
            $query = MostFollowerAd::with(['user:id,first_name,last_name,role,cover_photo,avatar'])
                ->where('status', 'approved')
                ->where('is_active', true);

            if ($regionId) {
                $query->where('region_id', $regionId);
            }

            return $query->orderBy('display_order')
                ->take(8)
                ->get();
        });

        if ($adRequestMostFollowers->isEmpty()) {
            return response()->error(
                'No ad requests followers found.',
                404
            );
        }

        return response()->success(
            $adRequestMostFollowers,
            'Ad requests products retrieved successfully.'
        );
    }
}
