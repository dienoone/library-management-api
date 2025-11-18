<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Borrowing\RenewBorrowingRequest;
use App\Http\Requests\Borrowing\ReturnBorrowingRequest;
use App\Http\Requests\Borrowing\StoreBorrowingRequest;
use App\Http\Requests\Borrowing\UpdateBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    public function __construct(private BorrowingService $borrowingService) {}

    /**
     * Display a listing of borrowings
     * 
     * GET /api/borrowings
     * Query params: search, status, member_id, book_id, overdue, borrow_date_from, 
     * borrow_date_to, due_date_from, due_date_to, order_by, order_direction, per_page,
     * with_book, with_member
     */
    public function index(Request $request)
    {
        $borrowings = $this->borrowingService->getAllBorrowingsPaginate($request->all());
        return $this->successWithPagination(BorrowingResource::collection($borrowings));
    }

    /**
     * Store a newly created borrowing
     * 
     * POST /api/borrowings
     */
    public function store(StoreBorrowingRequest $request)
    {
        $borrowing = $this->borrowingService->createBorrowing($request->validated());
        return $this->created(
            new BorrowingResource($borrowing),
            'Book borrowed successfully'
        );
    }

    /**
     * Display the specified borrowing
     * 
     * GET /api/borrowings/{id}
     */
    public function show(string $id)
    {
        $borrowing = $this->borrowingService->getBorrowing($id);
        return $this->success(new BorrowingResource($borrowing));
    }

    /**
     * Update the specified borrowing
     * 
     * PUT/PATCH /api/borrowings/{id}
     */
    public function update(UpdateBorrowingRequest $request, string $id)
    {
        $borrowing = $this->borrowingService->updateBorrowing($id, $request->validated());
        return $this->success(new BorrowingResource($borrowing), 'Borrowing record updated successfully');
    }

    /**
     * Remove the specified borrowing
     * 
     * DELETE /api/borrowings/{id}
     */
    public function destroy(string $id)
    {
        $this->borrowingService->deleteBorrowing($id);
        return $this->noContent('Borrowing record deleted successfully');
    }

    /**
     * Get all borrowings without pagination
     * 
     * GET /api/borrowings/all
     */
    public function all(Request $request)
    {
        $borrowings = $this->borrowingService->getAll();
        return $this->success(BorrowingResource::collection($borrowings));
    }

    /**
     * Renew a borrowing
     * 
     * POST /api/borrowings/{id}/renew
     */
    public function renew(RenewBorrowingRequest $request, string $id)
    {
        $borrowing = $this->borrowingService->renewBorrowing($id);
        return $this->success(
            new BorrowingResource($borrowing),
            'Book renewed successfully. New due date: ' . $borrowing->due_date->format('Y-m-d')
        );
    }

    /**
     * Return a borrowed book
     * 
     * POST /api/borrowings/{id}/return
     */
    public function return(ReturnBorrowingRequest $request, string $id)
    {
        $borrowing = $this->borrowingService->returnBorrowing($id, $request->validated());
        return $this->success(
            new BorrowingResource($borrowing),
            'Book returned successfully'
        );
    }

    /**
     * Get borrowings by member
     * 
     * GET /api/borrowings/member/{memberId}
     * Query params: status, active, per_page
     */
    public function byMember(Request $request, string $memberId)
    {
        $borrowings = $this->borrowingService->getMemberBorrowings($memberId, $request->all());
        return $this->successWithPagination(
            BorrowingResource::collection($borrowings),
            'Member borrowings retrieved successfully'
        );
    }

    /**
     * Get borrowings by book
     * 
     * GET /api/borrowings/book/{bookId}
     * Query params: status, per_page
     */
    public function byBook(Request $request, string $bookId)
    {
        $borrowings = $this->borrowingService->getBookBorrowings($bookId, $request->all());
        return $this->successWithPagination(
            BorrowingResource::collection($borrowings),
            'Book borrowings history retrieved successfully'
        );
    }

    /**
     * Get overdue borrowings
     * 
     * GET /api/borrowings/overdue
     */
    public function overdue()
    {
        $borrowings = $this->borrowingService->getOverdueBorrowings();
        return $this->success(
            BorrowingResource::collection($borrowings),
            'Overdue borrowings retrieved successfully'
        );
    }

    /**
     * Get active borrowings (currently borrowed)
     * 
     * GET /api/borrowings/active
     */
    public function active()
    {
        $borrowings = $this->borrowingService->getActiveBorrowings();
        return $this->success(
            BorrowingResource::collection($borrowings),
            'Active borrowings retrieved successfully'
        );
    }

    /**
     * Update overdue status (for cron job or manual trigger)
     * 
     * POST /api/borrowings/update-overdue-status
     */
    public function updateOverdueStatus()
    {
        $updatedCount = $this->borrowingService->updateOverdueStatus();
        return $this->success(
            ['updated_count' => $updatedCount],
            'Overdue status updated successfully. ' . $updatedCount . ' records updated.'
        );
    }

    /**
     * Search borrowings
     * 
     * GET /api/borrowings/search?q={query}
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        // Use the index method with search filter
        $borrowings = $this->borrowingService->getAllBorrowingsPaginate([
            'search' => $query,
            'per_page' => $request->query('per_page', 15)
        ]);

        return $this->successWithPagination(
            BorrowingResource::collection($borrowings),
            'Search completed successfully'
        );
    }

    /**
     * Get current user's borrowings
     * 
     * GET /api/my-borrowings
     * Query params: status, active, per_page
     */
    public function myBorrowings(Request $request)
    {
        // Assuming the authenticated user's ID is available
        $memberId = Auth::id();

        $borrowings = $this->borrowingService->getMemberBorrowings($memberId, $request->all());
        return $this->successWithPagination(
            BorrowingResource::collection($borrowings),
            'Your borrowings retrieved successfully'
        );
    }

    /**
     * Get borrowing statistics
     * 
     * GET /api/borrowings/statistics
     */
    public function statistics()
    {
        $activeBorrowings = $this->borrowingService->getActiveBorrowings();
        $overdueBorrowings = $this->borrowingService->getOverdueBorrowings();
        $allBorrowings = $this->borrowingService->getAllBorrowingsPaginate(['per_page' => 1]); // Just to get total

        $stats = [
            'total_borrowings' => $allBorrowings->total(),
            'active_borrowings' => $activeBorrowings->count(),
            'overdue_borrowings' => $overdueBorrowings->count(),
            'overdue_percentage' => $allBorrowings->total() > 0
                ? round(($overdueBorrowings->count() / $allBorrowings->total()) * 100, 2)
                : 0,
        ];

        return $this->success($stats, 'Borrowing statistics retrieved successfully');
    }
}
