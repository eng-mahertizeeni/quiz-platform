<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount(['questions' => fn($q) => $q->active()]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->whereNull('deleted_at')],
            'name_ar'     => 'nullable|string|max:100',
            'slug'        => ['nullable', 'string', 'max:100', Rule::unique('categories', 'slug')->whereNull('deleted_at')],
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        $category = Category::create($validated);

        return response()->json([
            'message'  => 'تم إضافة الفئة بنجاح',
            'category' => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount(['questions' => fn($q) => $q->active()]);

        return response()->json([
            'category' => new CategoryResource($category),
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)->whereNull('deleted_at')],
            'name_ar'     => 'nullable|string|max:100',
            'slug'        => ['nullable', 'string', 'max:100', Rule::unique('categories', 'slug')->ignore($category->id)->whereNull('deleted_at')],
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_featured' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($category->thumbnail) {
                Storage::disk('public')->delete($category->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        $category->update($validated);

        return response()->json([
            'message'  => 'تم تحديث الفئة بنجاح',
            'category' => new CategoryResource($category->fresh()),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'تم حذف الفئة',
        ]);
    }

    public function toggleFeatured(Category $category): JsonResponse
    {
        $category->update(['is_featured' => !$category->is_featured]);

        return response()->json([
            'message'      => 'تم تحديث حالة التمييز',
            'is_featured'  => $category->fresh()->is_featured,
        ]);
    }
}
