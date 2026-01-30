<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Requests\UpdateFavoriteRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Consultant;
use App\Models\Favorite;
use App\Repositories\FavoriteRepository;
use Illuminate\Http\Request;
use App\DTOs\FavoriteDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct(protected FavoriteRepository $favorites)
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List authenticated user's favorites
     * GET /api/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 15);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $favorites */
        $favorites = $this->favorites->paginateForUser($user->id, $perPage);

        $favorites->getCollection()->transform(function (Favorite $favorite) {
            return FavoriteDTO::fromModel($favorite)->toArray();
        });

        return $this->collectionResponse($favorites, 'تم جلب قائمة المفضلة بنجاح');
    }

    /**
     * Store a new favorite
     * POST /api/favorites
     */
    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = $request->user();
        $consultantId = (int) $request->input('consultant_id');

        // Ensure consultant exists and active
        $consultant = Consultant::where('id', $consultantId)->where('is_active', true)->first();
        if (!$consultant) {
            $this->throwNotFoundException('المستشار غير موجود أو غير متاح');
        }

        // Prevent duplicates
        if ($this->favorites->existsForUser($user->id, $consultantId)) {
            return $this->successResponse(null, 'المستشار مضاف مسبقاً إلى المفضلة', 200);
        }

        $favorite = DB::transaction(function () use ($user, $consultantId) {
            return $this->favorites->create([
                'user_id' => $user->id,
                'consultant_id' => $consultantId,
            ]);
        });

        $favorite->load(['consultant.user']);

        return $this->createdResponse(FavoriteDTO::fromModel($favorite)->toArray(), 'تمت إضافة المستشار إلى المفضلة');
    }

    /**
     * Update favorite note
     * PUT /api/favorites/{id}
     */
    public function update(UpdateFavoriteRequest $request, int $id): JsonResponse
    {
        $favorite = $this->favorites->findOrFail($id);

        $this->authorizeOwnership($request, $favorite);

        // Optionally allow changing the consultant (ensure no duplicate)
        if ($request->has('consultant_id')) {
            $newConsultantId = (int) $request->input('consultant_id');

            if ($this->favorites->existsForUser($favorite->user_id, $newConsultantId)) {
                return $this->successResponse(null, 'المستشار مضاف مسبقاً إلى المفضلة', 200);
            }

            $favorite->consultant_id = $newConsultantId;
            $favorite->save();
            $favorite->load('consultant.user');
        }

        return $this->updatedResponse(FavoriteDTO::fromModel($favorite)->toArray(), 'تم تحديث المفضلة بنجاح');
    }

    /**
     * Delete favorite
     * DELETE /api/favorites/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $favorite = $this->favorites->findOrFail($id);

        $this->authorizeOwnership($request, $favorite);

        $favorite->delete();

        return $this->deletedResponse('تم حذف المستشار من المفضلة');
    }

    // Using FavoriteDTO::fromModel for payload mapping

    protected function authorizeOwnership(Request $request, Favorite $favorite): void
    {
        $user = $request->user();

        if (!$user || $favorite->user_id !== $user->id) {
            $this->throwUnauthorizedException('غير مصرح لك بتنفيذ هذا الإجراء');
        }
    }
}