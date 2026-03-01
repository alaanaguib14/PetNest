<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withTrashed()->withCount('products')->paginate(15);
        return response()->json([
            'success' => true, 
            'data' => $categories
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            'name'=> $request->name,
            'description' =>$request->description,
        ]);
        Cache::forget('categories.all');
        return response()->json([
            'success' => true, 
            'data' => $category
        ], 201);
    }
    public function show($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        return response()->json([
            'success'=> true, 
            'data' => $category
        ]);
    }
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($data);

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");

        return response()->json([
            'success'=> true,
            'message'=> 'Category updated successfully'
        ], 200);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        Cache::forget('categories.all');
        Cache::forget("categories.{$id}");

        return response()->json([
            'success' => true,
            'message'=> 'category soft deleted'
        ], 200);
    }
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        Cache::forget('categories.all');

        return response()->json([
            'success' =>true, 
            'message' => 'category restored'
        ]);
    }
}
