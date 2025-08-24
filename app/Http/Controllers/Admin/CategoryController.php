<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\FileUploadTrait;

class CategoryController extends Controller
{
    use FileUploadTrait;

    // Cache TTL
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Clear category related cache
     */
    private function clearCategoryCache()
    {
        CacheService::clearByTags(['categories', 'home', 'products']);
    }





    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        try{
            // Use cache with tags for categories
            $categories = Cache::tags(['categories', 'admin'])->remember('admin_categories_with_counts', self::CACHE_TTL, function () {
                return Category::withCount(['manage_products as brand_products_count', 'store_products', 'wholesale_products'])
                    ->orderBy('id', 'desc')
                    ->get();
            });

            if ($categories->isEmpty()) {
                return response()->error('No categories found.', 404);
            }
            return response()->success($categories, 'Categories retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving categories: ' . $e->getMessage(), 500);
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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }
            //image upload logic
            $image = $request->file('image');
            if ($image) {
                $imagePath = $this->handleFileUpload(
                    $request,
                    'image',
                    'categories',
                    null, // width
                    null, // height
                    90, // quality
                    false // forceWebp
                );
            } else {
                $imagePath = null;
            }

            $category = new Category();
            $category->name = Str::upper($request->input('name'));
            $category->image = $imagePath;
            $category->save();

            if ($category) {
                // Clear category related cache
                $this->clearCategoryCache();
                return response()->success($category, 'Category created successfully.');
            } else {
                return response()->error('Failed to create category.', 500);
            }
        } catch (\Exception $e) {
            return response()->error('An error occurred while creating the category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $category = Category::find($id);
            if (!$category) {
                return response()->error('Category not found.', 404);
            }
            return response()->success($category, 'Category retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving the category: ' . $e->getMessage(), 500);
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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id,
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp,svg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }

            $category = Category::find($id);
            if (!$category) {
                return response()->error('Category not found.', 404);
            }

            if ($request->has('name')) {
                $category->name = Str::upper($request->input('name'));
            }

            if ($request->hasFile('image')) {
                $imagePath = $this->handleFileUpload(
                    $request,
                    'image',
                    'categories',
                    null, // width
                    null, // height
                    90, // quality
                    false // forceWebp
                );
                //remove old image if exists
                if ($category->image) {
                    $this->deleteFile($category->image);
                }
                $category->image = $imagePath;
            }

            $category->save();

            // Clear category related cache after update
            $this->clearCategoryCache();

            return response()->success($category, 'Category updated successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while updating the category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    // $protectedIds = Category::orderBy('id')->limit(6)->pluck('id')->toArray();

    // if (in_array($id, $protectedIds)) {
    //     return response()->error('This category cannot be deleted.', 403);
    // }

    $category = Category::find($id);
    if (!$category) {
        return response()->error('Category not found.', 404);
    }

    //remove image if exists
    if ($category->image) {
        $this->deleteFile($category->image);
    }

    if ($category->delete()) {
        // Clear category related cache after delete
        $this->clearCategoryCache();
        return response()->success(null, 'Category deleted successfully.');
    } else {
        return response()->error('Failed to delete category.', 500);
    }
    }

   //get product by category
    public function getProductsByCategory(Category $category)
    {
        try{
        $perPage = request()->input('per_page', 10);
        $manageProducts = $category->manage_products()->paginate($perPage);
        $storeProducts = $category->store_products()->paginate($perPage);
        $wholesaleProducts = $category->wholesale_products()->paginate($perPage);
        $trendingProducts = $category->trending_products()->paginate($perPage);

        return response()->success([
            'manage_products' => $manageProducts,
            'store_products' => $storeProducts,
            'wholesale_products' => $wholesaleProducts,
            'trending_products' => $trendingProducts,
        ], 'Products retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving products: ' . $e->getMessage(), 500);
        }
    }

}
