<?php
namespace App\Repositories\Products;
use App\Interfaces\Products\ManageProductsInterface;
use App\Models\ManageProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ManageProductsRepository implements ManageProductsInterface
{
    public function getAllProducts(): array
    {
        $page = request()->get('page', 1); // Get the current page from the request
        $cacheKey = "products_page_{$page}"; // Dynamic cache key based on the page number

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return ManageProduct::where('user_id', Auth::id())
                                ->paginate(10)->toArray();
        });
    }

    public function getProductById(int $id): array
    {
        return ManageProduct::findOrFail($id)->toArray();
    }

    public function storeProduct(array $data): array
    {

        $product = new ManageProduct();
        $product->user_id = $data['user_id'];
        $product->product_name = $data['product_name'];
        $product->slug = $data['slug'];
        $product->product_image = $data['product_image'] ?? null;
        $product->product_price = $data['product_price'];
        $product->brand_name = $data['brand_name'];
        $product->product_discount = $data['product_discount'];
        $product->product_discount_unit = $data['product_discount_unit'];
        $product->product_stock = $data['product_stock'];
        $product->product_description = $data['product_description'];
        $product->product_faqs = $data['product_faqs'] ?? null;
        $product->save();
        //update cache after storing
        $cacheProducts = Cache::get('products', []);
        $cacheProducts[] = $product->toArray();
        Cache::put('products', $cacheProducts, now()->addHours(1));
        //clear cache for the specific product

        return $product->toArray();
    }
    public function updateProduct(int $id, array $data): array
    {
        $product = ManageProduct::findOrFail($id);
        $product->update($data);

        return $product->toArray();
    }

    public function deleteProduct(int $id): bool
    {
        $product = ManageProduct::findOrFail($id);
        //remove old image
        if ($product->product_image) {
            $oldImagePath = getStorageFilePath($product->product_image);
            Storage::disk('public')->delete($oldImagePath);
        }
        return $product->delete();
    }
}
