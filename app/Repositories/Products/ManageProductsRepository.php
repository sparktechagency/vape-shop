<?php

namespace App\Repositories\Products;

use App\Enums\UserRole\Role;
use App\Interfaces\Products\ManageProductsInterface;
use App\Models\ManageProduct;
use App\Models\StoreProduct;
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
        if (Auth::user()->role === Role::STORE->value) {
            return StoreProduct::where('user_id', Auth::id())
                // ->with('user')
                ->paginate(10)
                ->toArray();
        }
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


        if (Auth::user()->role === Role::STORE->value) {
            return StoreProduct::findOrFail($id)->toArray();
        } else {

            return ManageProduct::findOrFail($id)->toArray();
        }
    }


    //store product
    /**
     * @param array $data
     */
    public function storeProduct(array $data): array
    {

        // dd(Auth::user()->role);
        if (Auth::user()->role === Role::STORE->value) {
            $product = new StoreProduct();
            $product->user_id = $data['user_id'];
            if (isset($data['product_id']) && $data['product_id']) {
                $manageProduct = ManageProduct::with('user')->findOrFail($data['product_id']);
                // dd($manageProduct->user->first_name);
                if (!$manageProduct) {
                    throw new \Exception("Product not found.", 404);
                }

                $product->product_id = $manageProduct->id;
                $product->category_id = $manageProduct->category_id;
                $product->product_name = $manageProduct->product_name;
                $product->slug = $manageProduct->slug;
                // $product->slug = $data['slug'];
                $product->product_image = $data['product_image'] ?? $manageProduct->product_image;
                $product->brand_id = $manageProduct->user_id ?? null;
                $product->brand_name = $manageProduct->user->first_name;
            }
            $product->product_name = $data['product_name'];
            $product->category_id = $data['category_id'];
            $product->slug = $data['slug'] ?? generateUniqueSlug(StoreProduct::class, $data['product_name']);
            $product->product_price = $data['product_price'];
            $product->product_discount = $data['product_discount'];
            $product->product_discount_unit = $data['product_discount_unit'];
            $product->product_stock = $data['product_stock'];
            $product->product_description = $data['product_description'];
            $product->product_faqs = $data['product_faqs'] ?? null;
            $product->save();
        } else {
            $product = new ManageProduct();
            $product->user_id = $data['user_id'];
            $product->category_id = $data['category_id'] ?? null;

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
        }
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
        if (Auth::user()->role === Role::STORE->value) {
            $product = StoreProduct::findOrFail($id);
            if ($product->product_id) {
                $manageProduct = ManageProduct::findOrFail($product->product_id);
                $data['product_name'] = $manageProduct->product_name;
                $data['slug'] = generateUniqueSlug(StoreProduct::class, $manageProduct->product_name);
                $data['product_image'] =  getStorageFilePath($manageProduct->product_image);

                $data['brand_name'] = $manageProduct->user->first_name;
            }
        } else {
            $product = ManageProduct::findOrFail($id);
        }
        // Remove old image if it exists
        if (!empty($data['product_image'])) {
            $oldImagePath = getStorageFilePath($product->product_image);
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }
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
        if (Auth::user()->role === Role::STORE->value) {
            $product = StoreProduct::findOrFail($id);
        } else {
            $product = ManageProduct::findOrFail($id);
        }

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
