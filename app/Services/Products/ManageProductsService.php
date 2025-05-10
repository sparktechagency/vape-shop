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
        return $this->manageProduct->getAllProducts();
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
        //image upload
        if ($productImage) {
            $imagePath = $productImage->store('products', 'public');
            $data['product_image'] = $imagePath;
        }
        $data['user_id'] = Auth::id();
        $data['slug'] = generateUniqueSlug(\App\Models\ManageProduct::class, $data['product_name']);

        return $this->manageProduct->storeProduct($data);
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
        // Handle image upload if provided
        if ($productImage) {
            // Remove old image if it exists
            if (!empty($data['product_image'])) {
                $oldImagePath = getStorageFilePath($data['product_image']);
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            // Store the new image
            $imagePath = $productImage->store('products', 'public');
            $data['product_image'] = $imagePath;
        }

        // Clear cache for the specific product
        Cache::forget("product_{$id}");

        // Clear paginated cache
        $page = request()->get('page', 1);
        Cache::forget("products_page_{$page}");

        return $this->manageProduct->updateProduct($id, $data);
    }

    //delete product
    /**
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        // Clear cache for the specific product
        Cache::forget("product_{$id}");

        // Clear paginated cache
        $page = request()->get('page', 1);
        Cache::forget("products_page_{$page}");

        return $this->manageProduct->deleteProduct($id);
    }
}
