<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/categories",
     *      description="List all active categories with question counts.",
     *      tags={"Categories"},
     *      @OA\Parameter(ref="#/components/parameters/Accept-Language"),
     *      @OA\Response(response=200, description="List of categories"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::active()->withCount(['questions' => fn($q) => $q->active()]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $categories = $query->orderBy('sort_order')->paginate($request->per_page ?? 20);

        return response()->json([
            'categories' => CategoryResource::collection($categories),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/categories/{id}",
     *      description="Get a single category with questions.",
     *      tags={"Categories"},
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Category details"),
     *      @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount(['questions' => fn($q) => $q->active()]);

        return response()->json([
            'category' => new CategoryResource($category),
        ]);
    }
}
