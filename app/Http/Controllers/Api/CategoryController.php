<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttachBooksRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\DetachBooksRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    /**
     * Display a listing of categories
     * 
     * GET /api/categories
     * Query params: search, order_by, order_direction, per_page, with_books_count, with_books
     */
    public function index(Request $request)
    {
        $categories = $this->categoryService->getAllCategoriesPaginate($request->all());
        return $this->successWithPagination(CategoryResource::collection($categories));
    }

    /**
     * Store a newly created category
     * 
     * POST /api/categories
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return $this->created(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    /**
     * Display the specified category
     * 
     * GET /api/categories/{id}
     */
    public function show(int $id)
    {
        $category = $this->categoryService->getCategory($id);
        return $this->success(new CategoryResource($category));
    }

    /**
     * Update the specified category
     * 
     * PUT/PATCH /api/categories/{id}
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());
        return $this->success(new CategoryResource($category), 'Category updated succesffuly');
    }

    /**
     * Remove the specified category
     * 
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id)
    {
        $this->categoryService->deleteCategory($id);
        return $this->noContent('Category deleted successfully');
    }

    /**
     * Get all categories without pagination
     * 
     * GET /api/categories/all
     */
    public function all(Request $request)
    {
        $categories = $this->categoryService->getAll();
        return $this->success(CategoryResource::collection($categories));
    }


    /**
     * Search categories
     * 
     * GET /api/categories/search?q={query}
     */
    public function search(Request $request)
    {

        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        $categories = $this->categoryService->search($query);

        return $this->success(
            CategoryResource::collection($categories),
            'Search completed successfully'
        );
    }

    /**
     * Attach books to category
     * 
     * POST /api/categories/{id}/books
     * Body: { "book_ids": [1, 2, 3] }
     */
    public function attachBooks(AttachBooksRequest $request, int $id)
    {

        $category = $this->categoryService->attachBooks($id, $request->validated());

        return $this->success(
            new CategoryResource($category->load('books')),
            'Books attached to category successfully'
        );
    }

    /**
     * Detach books from category
     * 
     * DELETE /api/categories/{id}/books
     * Body: { "book_ids": [1, 2, 3] }
     */
    public function detachBooks(DetachBooksRequest $request, int $id)
    {
        $category = $this->categoryService->detachBooks($id, $request->validated());

        return $this->success(
            new CategoryResource($category->load('books')),
            'Books detached from category successfully'
        );
    }
}
