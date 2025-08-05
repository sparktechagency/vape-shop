<?php

namespace App\Services\Products;

use App\Interfaces\Products\ManageProductsInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Traits\FileUploadTrait;

class ManageProductsService
{
    use FileUploadTrait;
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

        return $this->manageProduct->getProductById($id);

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
            // $imagePath = $productImage->store('products', 'public');
            $imagePath = $this->handleFileUpload(
                request(),
                'product_image',
                'products',
                null, // width
                null, // height
                97, // quality
                true // forceWebp
            );
            $data['product_image'] = $imagePath;
        }

        $data['user_id'] = Auth::id();
        if (isset($data['product_name']) && $data['product_name']) {
            $data['slug'] = generateUniqueSlug(\App\Models\ManageProduct::class, $data['product_name']);
        }

        $product = $this->manageProduct->storeProduct($data);

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
            // $imagePath = $productImage->store('products', 'public');
            $imagePath = $this->handleFileUpload(
                request(),
                'product_image',
                'products',
                null, // width
                null, // height
                97, // quality
                true // forceWebp
            );
            // Remove old image if it exists
            if (!empty($data['product_image'])) {
                $oldImagePath = getStorageFilePath($data['product_image']);
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            $data['product_image'] = $imagePath;
        }

        // Update the product in the repository
        $updatedProduct = $this->manageProduct->updateProduct($id, $data);

        return $updatedProduct;
    }

    //delete product
    /**
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        return $this->manageProduct->deleteProduct($id);
    }
}
