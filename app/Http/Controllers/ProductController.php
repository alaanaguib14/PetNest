<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cachingKey = 'products.' . json_encode($request->query());

        $products = Cache::remember($cachingKey, 1800, function()use ($request){
            $query = Product::with('category');

            if($request->filled('cat_id')){
                $query->inCategory($request->category_id);
            }

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            
            $sortBy  = in_array($request->sort_by, ['price', 'name', 'created_at']) 
                       ? $request->sort_by : 'created_at';
            $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortDir);

            return $query->paginate($request->get('per_page', 15));
        });

        return response()->json([
            'success' => true, 
            'data' => $products
        ], 200);
    }

    public function show($id)
    {
        $product = Cache::remember("products.{$id}", 1800, function () use ($id) {
            return Product::with('category')->findOrFail($id);
        });

        return response()->json([
            'success' => true, 
            'data' => $product
        ], 200);
    }
}
