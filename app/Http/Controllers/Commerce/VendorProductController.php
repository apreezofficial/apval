<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commerce;

use App\Http\Requests\Commerce\StoreProductRequest;
use App\Http\Requests\Commerce\UpdateInventoryRequest;
use App\Http\Requests\Commerce\UpdateProductRequest;
use App\Models\Product;
use App\Services\Commerce\InventoryService;
use App\Services\Commerce\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VendorProductController
{
    public function __construct(
        private ProductService $productService,
        private InventoryService $inventoryService
    ) {}

    public function index(): JsonResponse
    {
        $products = Product::with('images')
            ->where('vendor_id', Auth::id())
            ->latest()
            ->paginate(20);

        return response()->json(['status' => 'success', 'data' => $products]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(Auth::id(), $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with('images')->where('vendor_id', Auth::id())->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $product]);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::where('vendor_id', Auth::id())->findOrFail($id);
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $updatedProduct,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::where('vendor_id', Auth::id())->findOrFail($id);
        $this->productService->deleteProduct($product);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
    }

    public function updateInventory(UpdateInventoryRequest $request, string $id): JsonResponse
    {
        $product = Product::where('vendor_id', Auth::id())->findOrFail($id);
        $data = $request->validated();

        $updatedProduct = $this->inventoryService->adjustStock(
            $product,
            $data['type'],
            $data['quantity'],
            'manual',
            $data['reason'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory updated successfully',
            'data' => $updatedProduct
        ]);
    }
}
