<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberFilterRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Requests\Member\UpdateMemberStatusRequest;
use App\Http\Resources\MemberResource;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private MemberService $memberService) {}

    /**
     * Display a listing of members
     * 
     * GET /api/members
     * Query params: search, status, start_date, end_date, order_by, order_direction, with_borrowings, with_purchases, with_user, per_page
     */
    public function index(MemberFilterRequest $request)
    {
        $members = $this->memberService->getAllMembersPaginate($request->validated());
        return $this->successWithPagination(MemberResource::collection($members));
    }

    /**
     * Display the specified member
     * 
     * GET /api/members/{id}
     */
    public function show(string $id)
    {
        $member = $this->memberService->getMember($id);
        return $this->success(new MemberResource($member));
    }

    /**
     * Update the specified member
     * 
     * PUT/PATCH /api/members/{id}
     */
    public function update(UpdateMemberRequest $request, string $id)
    {
        $member = $this->memberService->updateMember($id, $request->validated());
        return $this->success(new MemberResource($member), 'Member updated successfully');
    }

    /**
     * Remove the specified member
     * 
     * DELETE /api/members/{id}
     */
    public function destroy(string $id)
    {
        $this->memberService->deleteMember($id);
        return $this->noContent('Member deleted successfully');
    }

    /**
     * Get all members without pagination
     * 
     * GET /api/members/all
     */
    public function all()
    {
        $members = $this->memberService->getAll();
        return $this->success(MemberResource::collection($members));
    }

    /**
     * Search members
     * 
     * GET /api/members/search?q={query}
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        $members = $this->memberService->search($query);
        return $this->success(
            MemberResource::collection($members),
            'Search completed successfully'
        );
    }

    /**
     * Get member's borrowings
     * 
     * GET /api/members/{id}/borrowings
     */
    public function getBorrowings(string $id)
    {
        $borrowings = $this->memberService->getMemberBorrowings($id);
        return $this->success($borrowings);
    }

    /**
     * Get member's active borrowings count
     * 
     * GET /api/members/{id}/active-borrowings-count
     */
    public function getActiveBorrowingsCount(string $id)
    {
        $count = $this->memberService->getActiveBorrowingsCount($id);
        return $this->success(['active_borrowings_count' => $count]);
    }

    /**
     * Check if member can borrow
     * 
     * GET /api/members/{id}/can-borrow
     */
    public function canBorrow(string $id)
    {
        $canBorrow = $this->memberService->canBorrow($id);
        return $this->success(['can_borrow' => $canBorrow]);
    }

    /**
     * Update member status
     * 
     * PATCH /api/members/{id}/status
     */
    public function updateStatus(UpdateMemberStatusRequest $request, string $id)
    {
        $member = $this->memberService->updateStatus($id, $request->validated()['status']);
        return $this->success(new MemberResource($member), 'Member status updated successfully');
    }
}
