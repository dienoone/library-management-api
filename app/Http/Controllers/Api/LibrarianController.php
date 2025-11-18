<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Librarian\UpdateLibrarianRequest;
use App\Http\Resources\LibrarianResource;
use App\Services\LibrarianService;
use Illuminate\Http\Request;

class LibrarianController extends Controller
{
    public function __construct(private LibrarianService $librarianService) {}

    /**
     * Display a listing of librarians
     * 
     * GET /api/librarians
     * Query params: search, order_by, order_direction, per_page, with_user
     */
    public function index(Request $request)
    {
        $librarians = $this->librarianService->getAllLibrariansPaginate($request->all());
        return $this->successWithPagination(LibrarianResource::collection($librarians));
    }

    /**
     * Display the specified librarian
     * 
     * GET /api/librarians/{id}
     */
    public function show(string $id)
    {
        $librarian = $this->librarianService->getLibrarian($id);
        return $this->success(new LibrarianResource($librarian));
    }

    /**
     * Update the specified librarian
     * 
     * PUT/PATCH /api/librarians/{id}
     */
    public function update(UpdateLibrarianRequest $request, string $id)
    {
        $librarian = $this->librarianService->updateLibrarian($id, $request->validated());
        return $this->success(new LibrarianResource($librarian), 'Librarian updated successfully');
    }

    /**
     * Remove the specified librarian
     * 
     * DELETE /api/librarians/{id}
     */
    public function destroy(string $id)
    {
        $this->librarianService->deleteLibrarian($id);
        return $this->noContent('Librarian deleted successfully');
    }

    /**
     * Get all librarians without pagination
     * 
     * GET /api/librarians/all
     */
    public function all()
    {
        $librarians = $this->librarianService->getAll();
        return $this->success(LibrarianResource::collection($librarians));
    }

    /**
     * Search librarians
     * 
     * GET /api/librarians/search?q={query}
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        $librarians = $this->librarianService->search($query);

        return $this->success(
            LibrarianResource::collection($librarians),
            'Search completed successfully'
        );
    }
}
