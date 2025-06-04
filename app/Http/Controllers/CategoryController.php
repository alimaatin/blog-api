<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Return all Categories
     * @unathenticated
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return $this->successResponse($categories, 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error fetching categories", 500);
        }
    }

    /**
     * Return a Category by ID
     * @unathenticated
     * @throws ModelNotFoundException
     */
    public function find(Category $category)
    {
        $category->posts->where("state", "accepted");
        return $this->successResponse($category, 200);
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
        return $this->successResponse($category, 201);
        } catch (Throwable $e) {
            return $this->errorResponse("Error creating category", 500);
        }
    }

    /**
     * Update a Category
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "parent_id" => "nullable|integer|exists:categories,id",
        ]);

        $changes = array_filter($validated, function ($value, $key) use ($category) {
            return $category->$key !== $value;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($changes)) {
            return $this->errorResponse("No changes detected", 422);
        }

        $category->update($changes);
            return $this->successResponse(["updated" => array_keys($changes), "category" => $category], 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse("Category not found", 404);
        } catch (Throwable $e) {
            return $this->errorResponse("Error updating category", 500);
        }
    }

    /**
     * Delete a Category
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return $this->successResponse($category, 200);
        } catch (Throwable $e) {
            return $this->errorResponse("Error deleting category", 500);
        }
    }

}
