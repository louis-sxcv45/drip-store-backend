<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductController extends Controller {
    public function index() {
        $product = Product::select(
            'id',
            'name_product',
            'price',
            'image',
            'store_id',
        )
        ->with(['store:id,name_store,logo'])
        ->get();

        $listProduct = $product->map(function ($product){
            return [
                'id' => $product->id,
                'name_product' => $product->name_product,
                'price' => $product->price,
                'image' => $product->image,
                'name_store' => $product->store->name ?? 'Unknown Store',
                'logo' => $product->store->logo,
            ];
        });

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $listProduct,
        ], 200);
    }

    public function show($id){
        $product = Product::with([
            'store:id,name_store,logo',
            'productSizes'
        ])->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $productDetails = [
            'id' => $product->id,
            'name_product' => $product->name_product,
            'price' => $product->price,
            'description' => $product->description,
            'category' => $product->category,
            'quantity' => $product->quantity,
            'image' =>  $product->image,
            'name_store' => $product->store->name ?? 'Unknown Store',
            'store_id' => $product->store_id,
	        'logo' => $product->store->logo,
            'product_sizes' => $product->productSizes->map(function ($size){
                return [
                    'id' => $size->id,
                    'size' => $size->size
                ];
            })
        ];

        return response()->json([
            'message' => 'Product details retrieved successfully',
            'data' => $productDetails,
        ], 200);
    }
}