<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\ManageProduct;
use App\Models\Review;
use App\Models\StoreProduct;
use App\Models\User;
use App\Models\WholesalerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Cache configuration
    private const CACHE_TTL = 1800; // 30 minutes
    private const MOST_RATED_CACHE_PREFIX = 'most_rated_reviews';
    private const USER_LATEST_CACHE_PREFIX = 'user_latest_reviews';

    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'mostRatedReviews']]);
        $this->middleware('banned');
        $this->middleware('is.suspended')->except(['index', 'show', 'mostRatedReviews', 'userLatestReviews']);
    }

    /**
     * Generate cache key for reviews
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

    public function index(Request $request)
    {
        $role = (int) $request->input('role');
        $productId = $request->input('product_id');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Build validation rules based on role
        $productRule = $this->ProductRule($role);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role' => 'required|integer|in:' . Role::BRAND->value . ',' . Role::STORE->value . ',' . Role::WHOLESALER->value,
            'product_id' => $productRule,
        ]);
        //validate errors
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey('reviews_index', [
            'role' => $role,
            'product_id' => $productId,
            'page' => $page,
            'per_page' => $perPage
        ]);

        // Try to get from cache first
        $reviews = Cache::tags(['reviews', 'products', 'users'])->remember($cacheKey, self::CACHE_TTL, function () use ($role, $productId, $perPage) {
            return match ($role) {
                Role::BRAND->value => Review::where('manage_product_id', $productId)
                    ->whereNull('store_product_id')
                    ->whereNull('parent_id')
                    ->with(['user:id,first_name,last_name,role,avatar'])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                Role::STORE->value => Review::where('store_product_id', $productId)
                    ->whereNull('parent_id')
                    ->with(['user:id,first_name,last_name,email,role'])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                Role::WHOLESALER->value => Review::where('wholesaler_product_id', $productId)
                    ->whereNull('parent_id')
                    ->with(['user:id,first_name,last_name,email,role'])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                default => null
            };
        });

        if (!$reviews) {
            return response()->error('Invalid role provided.', 400);
        }

        // Return the reviews
        return response()->success($reviews, 'Reviews retrieved successfully.', 200);
    }

    //product rule based on role
    private function ProductRule($role)
    {
        switch ($role) {
            case Role::BRAND->value:
                return 'required|exists:manage_products,id';
            case Role::STORE->value:
                return 'required|exists:store_products,id';
            case Role::WHOLESALER->value:
                return 'required|exists:wholesaler_products,id';
            default:
                return 'required|exists:manage_products,id';
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
    public function store(Request $request)
    {

        $role = (int)$request->input('role');
        // Build validation rules based on role
        $productRule = $this->ProductRule($role);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_id' => $productRule,
            'role' => 'required|integer|in:' . Role::BRAND->value . ',' . Role::STORE->value . ',' . Role::WHOLESALER->value,
            'rating' => $request->filled('parent_id') ? 'nullable|integer|min:1|max:5' : 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:reviews,id',
        ]);
        //validate errors
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        // Create the review
        switch ($role) {
            case Role::BRAND->value:
                $review = Review::create([
                    'user_id' => auth()->id(),
                    'manage_product_id' => $request->input('product_id'),
                    $request->filled('parent_id') ?: 'rating' =>  $request->input('rating'),
                    'comment' => $request->input('comment'),
                    'parent_id' => $request->input('parent_id'),
                ]);
                break;
            case Role::STORE->value:
                $data = $this->getProductAndRegionId($request->input('product_id'), Role::STORE->value);
                $review = Review::create([
                    'user_id' => auth()->id(),
                    'store_product_id' => $request->input('product_id'),
                    'region_id' => $data['region_id'] ?? null,
                    // 'manage_product_id' => $data['manage_product_id'] ?? null,
                    $request->filled('parent_id') ?: 'rating' =>  $request->input('rating'),
                    'comment' => $request->input('comment'),
                    'parent_id' => $request->input('parent_id'),
                ]);
                break;
            case Role::WHOLESALER->value:
                $data = $this->getProductAndRegionId($request->input('product_id'), Role::WHOLESALER->value);
                $review = Review::create([
                    'user_id' => auth()->id(),
                    'wholesaler_product_id' => $request->input('product_id'),
                    'region_id' => $data['region_id'] ?? null,
                    // 'manage_product_id' => $data['manage_product_id'] ?? null,
                    $request->filled('parent_id') ?: 'rating' =>  $request->input('rating'),
                    'comment' => $request->input('comment'),
                    'parent_id' => $request->input('parent_id'),
                ]);
                break;
            default:
                return response()->error('Invalid role provided.', 400);
        }

        // Return the created review
        return response()->success($review, 'Review created successfully.', 201);
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
     ** Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->error('Review not found.', 404);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->error('User not authenticated.', 401);
        }

        // Check if the authenticated user is the owner of the review
        if ($user->role !== Role::ADMIN->value && $review->user_id !== $user->id) {
            return response()->error('You are not authorized to delete this review.', 403);
        }

        // Delete the review
        $review->delete();

        return response()->success(null, 'Review deleted successfully.', 200);
    }



    private function getProductAndRegionId($productId, $role)
    {
        $data = [];
        if ($role === Role::STORE->value) {
            $product = StoreProduct::find($productId);
        } elseif ($role === Role::WHOLESALER->value) {
            $product = WholesalerProduct::find($productId);
        } else {
            $product = null;
        }
        if ($product) {
            if ($product->user_id && User::find($product->user_id)->address) {
                $data['region_id'] = User::find($product->user_id)->address->region_id;
            }
            // dd($product->manageProducts);
            if ($product->product_id && $product->manageProducts) {
                $data['manage_product_id'] = $product->product_id;
            } else {
                $data['manage_product_id'] = null;
            }
            return $data;
        }
        return null;
    }


    //**toggle review like
    public function toggleReviewLike(Review $review)
    {

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->error('User not authenticated.', 401);
            }

            $result = $review->likedByUsers()->toggle($user->id);
            if (count($result['attached']) > 0) {
                return response()->success(null, 'Review liked successfully.', 200);
            } elseif (count($result['detached']) > 0) {
                return response()->success(null, 'Review unliked successfully.', 200);
            }
            return response()->error('No changes made to the review like status.', 400);
        } catch (\Exception $e) {
            return response()->error('An error occurred while toggling review like.', 500, $e->getMessage());
            // Optionally, you can log the exception or handle it further
            //throw $th;
        }
    }


    //**most rated reviews
    public function mostRatedReviews(Request $request)
    {
        $regionId = $request->input('region_id');

        // Generate cache key
        $cacheKey = $this->generateCacheKey(self::MOST_RATED_CACHE_PREFIX, [
            'region_id' => $regionId
        ]);

        // Try to get from cache first
        $mostRatedReviews = Cache::tags(['reviews', 'products', 'users'])->remember($cacheKey, self::CACHE_TTL, function () use ($regionId) {
            $query = Review::whereNotNull('rating')
                ->whereNull('store_product_id')
                ->whereNull('parent_id');

            $query->when($regionId, function ($q) use ($regionId) {
                $q->whereHas('user.address', function ($addressQuery) use ($regionId) {
                    $addressQuery->where('region_id', $regionId);
                });
            });
            return $query->with([
                'manageProducts' => function ($query) {
                    $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
                        ->with('category:id,name');
                },
                'user:id,first_name,last_name,role,avatar'
            ])
                ->withCount(['likedByUsers as like_count', 'replies'])
                ->with('replies')
                ->having('like_count', '>', 0)
                ->orderByDesc('like_count')
                ->take(50)
                ->get();
        });

        if ($mostRatedReviews->isEmpty()) {
            return response()->error('No most rated reviews found.', 404);
        }
        return response()->success($mostRatedReviews, 'Most rated reviews retrieved successfully.', 200);
    }


    //**auth user letest reviews
    public function userLatestReviews(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $user = $userId ? User::find($userId) : Auth::user();
            if (!$user) {
                return response()->error('User not authenticated.', 401);
            }

            // Generate cache key based on user ID
            $actualUserId = $user->id;
            $cacheKey = $this->generateCacheKey(self::USER_LATEST_CACHE_PREFIX, [
                'user_id' => $actualUserId
            ]);

            // Try to get from cache first (shorter TTL for user-specific data)
            $userReview = Cache::tags(['reviews', 'users', 'products'])->remember($cacheKey, 900, function () use ($user) { // 15 minutes
                return $user->allReviews()
                    ->with([
                        'manageProducts' => function ($q) {
                            $q->select('id', 'user_id', 'product_name', 'product_image')
                                ->with(['user:id,first_name,last_name,role,avatar']);
                        },
                        'storeProducts' => function ($q) {
                            $q->select('id', 'user_id', 'product_name', 'product_image')
                                ->with(['user:id,first_name,last_name,role,avatar']);
                        },
                        'wholesalerProducts' => function ($q) {
                            $q->select('id', 'user_id', 'product_name', 'product_image')
                                ->with(['user:id,first_name,last_name,role,avatar']);
                        },
                    ])
                    ->latest()
                    ->take(10)
                    ->get();
            });

            $userReview->transform(function ($review) {
                $product = $review->manageProducts ?: $review->storeProducts ?: $review->wholesalerProducts;

                if ($product) {
                    $review->product = $product;

                    // user data only required fields
                    $user = $product->getRelation('user');
                    if ($user) {
                        $review->product_user = collect($user)->only([
                            'id',
                            'full_name',
                            'role',
                            'role_label',
                            'avatar'
                        ]);
                    } else {
                        $review->product_user = null;
                    }
                } else {
                    $review->product = null;
                    $review->product_user = null;
                }

                return $review;
            });

            $userReview->makeHidden(['manageProducts', 'storeProducts', 'wholesalerProducts']);

            if (!$userReview) {
                return response()->error('No reviews found for the user.', 404);
            }
            return response()->success($userReview, 'User reviews retrieved successfully.', 200);
        } catch (\Exception $e) {
            return response()->error('Error occurred while retrieving user reviews.', 500, $e->getMessage());
        }
    }

    public function myLatestReviews(Request $request)
    {
        try {

            $userId = $request->get('user_id');
            $user = $userId ? User::find($userId) : Auth::user();
            // $user =  Auth::user();
            $perPage = $request->input('per_page', 10);
            // dd($user);
            if (!$user) {
                return response()->error('User not authenticated.', 401);
            }

            $role = $user->role;
            $reviews = match ($role) {
                Role::BRAND->value => $user->reviewsOnManageProducts()
                    ->whereNull('parent_id')
                    ->whereNull('store_product_id')
                    ->with([
                        'manageProducts' => function ($query) {
                            $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
                                ->with('category:id,name');
                        },
                        'user:id,first_name,last_name,role,avatar',
                    ])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                Role::STORE->value => $user->reviewsOnStoreProducts()
                    ->whereNull('parent_id')
                    //  ->whereNull('wholesaler_product_id')
                    ->with([
                        'storeProducts' => function ($query) {
                            $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
                                ->with('category:id,name');
                        },
                        'user:id,first_name,last_name,role,avatar',
                    ])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                Role::WHOLESALER->value => $user->reviewsOnWholesalerProducts()
                    ->whereNull('parent_id')
                    ->with([
                        'wholesalerProducts' => function ($query) {
                            $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
                                ->with('category:id,name');
                        },
                        'user:id,first_name,last_name,role,avatar',
                    ])
                    ->withCount(['likedByUsers as like_count', 'replies'])
                    ->with('replies')
                    ->latest()
                    ->paginate($perPage),
                default => false,
            };

            if (!$reviews) {
                return response()->error('No reviews found for the user.', 404);
            }

            return response()->success($reviews, 'User reviews retrieved successfully.', 200);
        } catch (\Exception $e) {
            return response()->error('Error occurred while retrieving user reviews.', 500, $e->getMessage());
        }
    }
}
