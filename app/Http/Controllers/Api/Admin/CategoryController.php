<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(name="Admin Categories", description="Admin category management endpoints")
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/admin/categories",
     *      description="List all categories (admin).",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="List of categories"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/admin/categories",
     *      description="Create a new category.",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string", description="Category name (English)"),
     *                  @OA\Property(property="name_ar", type="string", description="Category name (Arabic)"),
     *                  @OA\Property(property="slug", type="string", description="URL slug (auto-generated if empty)"),
     *                  @OA\Property(property="description", type="string", description="Category description"),
     *                  @OA\Property(property="icon", type="string", description="Icon class or path"),
     *                  @OA\Property(property="color", type="string", description="Hex color code"),
     *                  @OA\Property(property="is_featured", type="boolean", description="Mark as featured"),
     *                  @OA\Property(property="is_active", type="boolean", description="Active status"),
     *                  @OA\Property(property="sort_order", type="integer", description="Sort order"),
     *                  @OA\Property(property="thumbnail", type="string", format="binary", description="Thumbnail image (max 2MB)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=201, description="Category created"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/admin/categories/{category}",
     *      description="Get a single category details.",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Category details"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount(['questions' => fn($q) => $q->active()]);

        return response()->json([
            'category' => new CategoryResource($category),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/categories/{category}",
     *      description="Update a category.",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string", description="Category name"),
     *                  @OA\Property(property="name_ar", type="string", description="Category name (Arabic)"),
     *                  @OA\Property(property="slug", type="string", description="URL slug"),
     *                  @OA\Property(property="description", type="string", description="Category description"),
     *                  @OA\Property(property="icon", type="string", description="Icon class or path"),
     *                  @OA\Property(property="color", type="string", description="Hex color code"),
     *                  @OA\Property(property="is_featured", type="boolean", description="Mark as featured"),
     *                  @OA\Property(property="is_active", type="boolean", description="Active status"),
     *                  @OA\Property(property="sort_order", type="integer", description="Sort order"),
     *                  @OA\Property(property="thumbnail", type="string", format="binary", description="Thumbnail image (max 2MB)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Category updated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Delete(
     *      path="/api/admin/categories/{category}",
     *      description="Delete a category.",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Category deleted"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'تم حذف الفئة',
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/categories/{category}/featured",
     *      description="Toggle featured status of a category.",
     *      tags={"Admin Categories"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Featured status toggled"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function toggleFeatured(Category $category): JsonResponse
    {
        $category->update(['is_featured' => !$category->is_featured]);

        return response()->json([
            'message'      => 'تم تحديث حالة التمييز',
            'is_featured'  => $category->fresh()->is_featured,
        ]);
    }
}
