<?php
namespace App\Services\Products;
use App\Interfaces\Products\ManageProductsInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ManageProductsService
{
    protected $manageProduct;

    public function __construct(ManageProductsInterface $manageProductsRepository)
    {
        $this->manageProduct = $manageProductsRepository;
    }



    //get all products
    /**
     * @return array
     */
    public function getAllProducts(): array
    {
        $role = Auth::user()->role; // Get the user's role
        $page = request()->get('page', 1); // Get the current page
        $cacheKey = "products_{$role}_page_{$page}"; // Include role in the cache key

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->manageProduct->getAllProducts();
        });
    }



    //get product by id
    /**
     * @param int $id
     * @return array
     */
    public function getProductById(int $id): array
    {
        // Check if the product is cached
        $cacheKey = "product_{$id}";
        $product = Cache::remember($cacheKey, now()->addHours(1), function () use ($id) {
            return $this->manageProduct->getProductById($id);
        });

        if (empty($product)) {
            return [];
        }

        return $product;
    }


    //store product
    /**
     * @param array $data
     * @param UploadedFile|null $productImage
     * @return array
     */
    public function storeProduct(array $data, ?UploadedFile $productImage): array
    {
        // Handle image upload if provided
        if ($productImage) {
            $imagePath = $productImage->store('products', 'public');
            $data['product_image'] = $imagePath;
        }

        $data['user_id'] = Auth::id();
        $data['slug'] = generateUniqueSlug(\App\Models\ManageProduct::class, $data['product_name']);

        $product = $this->manageProduct->storeProduct($data);

        // Update paginated cache with role-specific key
        $role = Auth::user()->role;
        $page = request()->get('page', 1);
        $cacheKey = "products_{$role}_page_{$page}";
        $cachedProducts = Cache::get($cacheKey, []);

        if (!empty($cachedProducts['data'])) {
            $cachedProducts['data'][] = $product;
            Cache::put($cacheKey, $cachedProducts, now()->addHours(1));
        }

        return $product;
    }


    //update product
    /**
     * @param int $id
     * @param array $data
     * @param UploadedFile|null $productImage
     * @return array
     */
    public function updateProduct(int $id, array $data, ?UploadedFile $productImage): array
    {

        if ($productImage) {
            // Store the new image first
            $imagePath = $productImage->store('products', 'public');

            // Remove old image if it exists
            if (!empty($data['product_image'])) {
                $oldImagePath = getStorageFilePath($data['product_image']);
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            $data['product_image'] = $imagePath;
        }

        // Clear cache for the specific product
        $role = Auth::user()->role;
        $cacheKey = "product_{$role}_{$id}";
        Cache::forget($cacheKey);

        // Update the product in the repository
        $updatedProduct = $this->manageProduct->updateProduct($id, $data);

        // Update paginated cache with role-specific key
        $page = request()->get('page', 1);
        $cacheKeyPaginated = "products_{$role}_page_{$page}";
        $cachedProducts = Cache::get($cacheKeyPaginated, []);

        if (!empty($cachedProducts['data'])) {
            foreach ($cachedProducts['data'] as &$product) {
                if ($product['id'] === $id) {
                    $product = $updatedProduct;
                    break;
                }
            }
            Cache::put($cacheKeyPaginated, $cachedProducts, now()->addHours(1));
        }

        return $updatedProduct;
    }

    //delete product
    /**
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        $role = Auth::user()->role;

        // Clear cache for the specific product
        $cacheKey = "product_{$role}_{$id}";
        Cache::forget($cacheKey);

        // Update paginated cache with role-specific key
        $page = request()->get('page', 1);
        $cacheKeyPaginated = "products_{$role}_page_{$page}";
        $cachedProducts = Cache::get($cacheKeyPaginated, []);

        if (!empty($cachedProducts['data'])) {
            $cachedProducts['data'] = array_filter($cachedProducts['data'], function ($product) use ($id) {
                return $product['id'] !== $id;
            });
            Cache::put($cacheKeyPaginated, $cachedProducts, now()->addHours(1));
        }

        return $this->manageProduct->deleteProduct($id);
    }
}
