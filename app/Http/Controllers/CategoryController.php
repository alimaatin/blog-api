<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Return all Categories
     * @unathenticated
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json($categories, 200);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error fetching categories"], 500);
        }
    }
    
    /**
     * Return a Category by ID
     * @unathenticated
     * @throws ModelNotFoundException
     */
    public function find($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->posts->where("state", "accepted");
            return response()->json($category, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Category not found"], 404);
        }
    }

    /**
     * Create a new Category
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "parent_id" => "nullable|integer|exists:categories,id",
        ]);
        
        $category = Category::create($validated);
        return response()->json($category, 201);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error creating category"], 500);
        }
    }

    /**
     * Update a Category
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "parent_id" => "nullable|integer|exists:categories,id",
        ]);

        $category = Category::findOrFail($id);

        $changes = array_filter($validated, function ($value, $key) use ($category) {   
            return $category->$key !== $value;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($changes)) {
            return response()->json(["error" => "No changes detected"], 422);
        }

        $category->update($changes);
            return response()->json(["updated" => array_keys($changes), "category" => $category], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Category not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error updating category"], 500);
        }
    }

    /**
     * Delete a Category
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json($category, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Category not found"], 404);
        } catch (Throwable $e) {
            return response()->json(["error" => "Error deleting category"], 500);
        }
    }

}
