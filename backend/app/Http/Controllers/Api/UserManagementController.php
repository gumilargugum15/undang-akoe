<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserManagementService $users,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'role' => $request->input('role'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            'search' => $request->input('search'),
        ];

        $users = $this->users->list($filters, (int) $request->input('per_page', 15));

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function suspend(Request $request, User $user): JsonResponse
    {
        $user = $this->users->suspend($request->user(), $user);

        return response()->json([
            'message' => 'Pengguna berhasil dinonaktifkan.',
            'data' => new UserResource($user),
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $user = $this->users->activate($user);

        return response()->json([
            'message' => 'Pengguna berhasil diaktifkan kembali.',
            'data' => new UserResource($user),
        ]);
    }

    public function verify(User $user): JsonResponse
    {
        $user = $this->users->verify($user);

        return response()->json([
            'message' => 'Pengguna berhasil diverifikasi.',
            'data' => new UserResource($user),
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $user = $this->users->updateRole($request->user(), $user, $request->validated('role'));

        return response()->json([
            'message' => 'Peran pengguna berhasil diperbarui.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->users->delete($request->user(), $user);

        return response()->json([
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
