<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::withoutTrashed()->with('category')->paginate(15);
        return response()->json([
            'success'=> true,
            'data' => $product
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $product = Product::create($data);

        Cache::forget('products.'. '[]');
        return response()->json([
            'success' => true,
            'message'=> 'New product added'
        ], 201);
    }

    public function show($id)
    {
        $product = Product::withTrashed()->with('category')->findOrFail($id);
        return response()->json([
            'success' => true, 
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'inventory'=> 'sometimes|integer|min:0',
        ]);
        $product->update(($data));

        Cache::forget("products.{$id}");
        return response()->json([
            'success'=>true,
            'message'=> 'product updated'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        Cache::forget("products.{$id}");
        return response()->json([
            'success' => true, 
            'message' => 'product soft deleted'
        ]);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json([
            'success' => true, 
            'message' => 'product restored'
        ]);
    }
}
