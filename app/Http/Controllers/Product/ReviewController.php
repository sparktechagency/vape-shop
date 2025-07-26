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
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'mostRatedReviews']]);
        $this->middleware('banned');
        $this->middleware('is.suspended')->except(['index', 'show', 'mostRatedReviews', 'userLatestReviews']);
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $role = (int) $request->input('role');
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


        $reviews = match ($role) {
            Role::BRAND->value => $reviews = Review::where('manage_product_id', $request->input('product_id'))
                ->whereNull('store_product_id')
                ->whereNull('parent_id')
                ->with(['user:id,first_name,last_name,role,avatar'])
                ->withCount(['likedByUsers as like_count', 'replies'])
                ->with('replies')
                ->latest()
                ->paginate(10),
            Role::STORE->value => Review::where('store_product_id', $request->input('product_id'))
                ->whereNull('parent_id')
                ->with(['user:id,first_name,last_name,email,role'])
                ->withCount(['likedByUsers as like_count', 'replies'])
                ->with('replies')
                ->latest()
                ->paginate(10),
            Role::WHOLESALER->value => Review::where('wholesaler_product_id', $request->input('product_id'))
                ->whereNull('parent_id')
                ->with(['user:id,first_name,last_name,email,role'])
                ->withCount(['likedByUsers as like_count', 'replies'])
                ->with('replies')
                ->latest()
                ->paginate(10),
            default =>  response()->error('Invalid role provided.', 400),
        };

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->error('Review not found.', 404);
        }

        // Check if the authenticated user is the owner of the review
        if ($review->user_id !== auth()->id()) {
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


    //toggle review like
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


    //most rated reviews

    public function mostRatedReviews(Request $request)
    {

        // Fetch most rated reviews based on role

        $mostRatedReviews = Review::whereNotNull('rating')
            ->whereNull('store_product_id')
            ->whereNull('parent_id')
            ->with([
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

        if ($mostRatedReviews->isEmpty()) {
            return response()->error('No most rated reviews found.', 404);
        }
        return response()->success($mostRatedReviews, 'Most rated reviews retrieved successfully.', 200);
    }

    //auth user letest reviews
    public function userLatestReviews(Request $request)
    {
        try {

            $userId = $request->get('user_id');
            $user = $userId ? User::find($userId) : Auth::user();
            // dd($user);
            $userReview = $user ? $user->allReviews()
                ->with([
                    'manageProducts:id,user_id,product_name,product_image',
                    'storeProducts:id,user_id,product_name,product_image',
                    'wholesalerProducts:id,user_id,product_name,product_image'
                ])
                ->latest()
                ->take(10)
                ->get() : null;
            $userReview->transform(function ($review) {
                $review->product = $review->manageProducts ?: $review->storeProducts ?: $review->wholesalerProducts;
                return $review;
            });
            $userReview->makeHidden(['manageProducts', 'storeProducts', 'wholesalerProducts']);
            // dd($userReview);
            if (!$user) {
                return response()->error('User not authenticated.', 401);
            }
            if (!$userReview) {
                return response()->error('No reviews found for the user.', 404);
            }
            return response()->success($userReview, 'User reviews retrieved successfully.', 200);
        } catch (\Exception $e) {
            return response()->error('Error occurred while retrieving user reviews.', 500, $e->getMessage());
        }
    }

    // public function userLatestReviews(Request $request){
    //     try {

    //         $userId = $request->get('user_id');
    //         $user = $userId ? User::find($userId) : Auth::user();
    //         // $user =  Auth::user();
    //         $perPage = $request->input('per_page', 10);
    //         // dd($user);
    //         if (!$user) {
    //             return response()->error('User not authenticated.', 401);
    //         }

    //         $role = $user->role;
    //          $reviews = match($role){
    //             Role::BRAND->value => $user->reviewsOnManageProducts()
    //                         ->whereNull('parent_id')
    //                         ->whereNull('store_product_id')
    //                         ->with([
    //                              'manageProducts' => function ($query) {
    //                                  $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
    //                                  ->with('category:id,name');
    //                              },
    //                              'user:id,first_name,last_name,role,avatar',
    //                          ])
    //                          ->withCount(['likedByUsers as like_count', 'replies'])
    //                          ->with('replies')
    //                          ->latest()
    //                          ->paginate($perPage),
    //             Role::STORE->value => $user->reviewsOnStoreProducts()
    //                         ->whereNull('parent_id')
    //                         //  ->whereNull('wholesaler_product_id')
    //                         ->with([
    //                              'storeProducts' => function ($query) {
    //                                  $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
    //                                  ->with('category:id,name');
    //                              },
    //                              'user:id,first_name,last_name,role,avatar',
    //                          ])
    //                          ->withCount(['likedByUsers as like_count', 'replies'])
    //                          ->with('replies')
    //                          ->latest()
    //                          ->paginate($perPage),
    //             Role::WHOLESALER->value => $user->reviewsOnWholesalerProducts()
    //                         ->whereNull('parent_id')
    //                         ->with([
    //                              'wholesalerProducts' => function ($query) {
    //                                  $query->select('id', 'user_id', 'category_id', 'product_name', 'product_image', 'product_price', 'slug')
    //                                  ->with('category:id,name');
    //                              },
    //                              'user:id,first_name,last_name,role,avatar',
    //                          ])
    //                          ->withCount(['likedByUsers as like_count', 'replies'])
    //                          ->with('replies')
    //                          ->latest()
    //                          ->paginate($perPage),
    //             default => false,
    //          };

    //         if (!$reviews) {
    //             return response()->error('No reviews found for the user.', 404);
    //         }

    //         return response()->success($reviews, 'User reviews retrieved successfully.', 200);
    //     } catch (\Exception $e) {
    //         return response()->error('Error occurred while retrieving user reviews.', 500, $e->getMessage());
    //     }
    // }
}
