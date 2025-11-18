<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateAuthorRequest;
use App\Http\Requests\Author\AttachBooksRequest;
use App\Http\Requests\Author\DetachBooksRequest;
use App\Http\Resources\AuthorResource;
use App\Services\AuthorService;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function __construct(private AuthorService $authorService) {}

    /**
     * Display a listing of authors
     * 
     * GET /api/authors
     * Query params: search, order_by, order_direction, per_page, with_books
     */
    public function index(Request $request)
    {
        $authors = $this->authorService->getAllAuthorsPaginate($request->all());
        return $this->successWithPagination(AuthorResource::collection($authors));
    }


    /**
     * Display the specified author
     * 
     * GET /api/authors/{id}
     */
    public function show(string $id)
    {
        $author = $this->authorService->getAuthor($id);
        return $this->success(new AuthorResource($author));
    }

    /**
     * Update the specified author
     * 
     * PUT/PATCH /api/authors/{id}
     */
    public function update(UpdateAuthorRequest $request, string $id)
    {
        $author = $this->authorService->updateAuthor($id, $request->validated());
        return $this->success(new AuthorResource($author), 'Category updated succesffuly');
    }

    /**
     * Remove the specified author
     * 
     * DELETE /api/authors/{id}
     */
    public function destroy(string $id)
    {
        $this->authorService->deleteAuthor($id);
        return $this->noContent('Author deleted successfully');
    }


    /**
     * Get all authors without pagination
     * 
     * GET /api/authors/all
     */
    public function all()
    {
        $categories = $this->authorService->getAll();
        return $this->success(AuthorResource::collection($categories));
    }


    /**
     * Search authors
     * 
     * GET /api/authors/search?q={query}
     */
    public function search(Request $request)
    {

        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        $categories = $this->authorService->search($query);

        return $this->success(
            AuthorResource::collection($categories),
            'Search completed successfully'
        );
    }


    /**
     * Attach books to author
     * 
     * POST /api/authors/{id}/books
     * Body: { "book_ids": [1, 2, 3] }
     */
    public function attachBooks(AttachBooksRequest $request, int $id)
    {

        $author = $this->authorService->attachBooks($id, $request->validated());

        return $this->success(
            new AuthorResource($author),
            'Books attached to $author successfully'
        );
    }

    /**
     * Detach books from author
     * 
     * DELETE /api/authors/{id}/books
     * Body: { "book_ids": [1, 2, 3] }
     */
    public function detachBooks(DetachBooksRequest $request, int $id)
    {
        $author = $this->authorService->detachBooks($id, $request->validated());

        return $this->success(
            new AuthorResource($author),
            'Books detached from category successfully'
        );
    }
}
