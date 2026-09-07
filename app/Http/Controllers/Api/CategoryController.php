<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    
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

    public function show(Category $category): JsonResponse
    {
        $category->loadCount(['questions' => fn($q) => $q->active()]);

        return response()->json([
            'category' => new CategoryResource($category),
        ]);
    }
}
