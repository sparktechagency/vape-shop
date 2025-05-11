<?php
namespace App\Repositories\Products;
use App\Interfaces\Products\ManageProductsInterface;
use App\Models\ManageProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManageProductsRepository implements ManageProductsInterface
{
    //get all products
    /**
     * @return array
     */
    public function getAllProducts(): array
    {
        return ManageProduct::where('user_id', Auth::id())
                            ->paginate(10)
                            ->toArray();
    }


    //get product by id
    /**
     * @param int $id
     * @return array
     */
    public function getProductById(int $id): array
    {
        try {
            return ManageProduct::findOrFail($id)->toArray();
        } catch (\Exception $e) {
            throw new \Exception("Product not found.", 404);
        }
    }


    //store product
    /**
     * @param array $data
     */
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

        return $product->toArray();
    }

    
    //update product
    /**
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateProduct(int $id, array $data): array
    {
        $product = ManageProduct::findOrFail($id);
        $product->update($data);

        return $product->toArray();
    }

    //delete product
    /**
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        $product = ManageProduct::findOrFail($id);

        // Remove old image if it exists
        if ($product->product_image) {
            $oldImagePath = getStorageFilePath($product->product_image);
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        return $product->delete();
    }
}
