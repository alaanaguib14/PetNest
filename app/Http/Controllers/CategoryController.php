<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('categories.all', 3600, function () {
            return Category::withCount('products')->get();
        });

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function show($id)
    {
        $category = Cache::remember("categories.{$id}", 3600, function () use ($id) {
            return Category::withCount('products')->findOrFail($id);
        });

        return response()->json(['success' => true, 'data' => $category]);
    }
}
