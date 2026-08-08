<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($filters, $perPage);
    }

    public function suspend(User $actor, User $target): User
    {
        $this->guardAgainstSelf($actor, $target, 'menonaktifkan');

        return $this->users->update($target, ['is_active' => false]);
    }

    public function activate(User $target): User
    {
        return $this->users->update($target, ['is_active' => true]);
    }

    public function verify(User $target): User
    {
        return $this->users->update($target, ['email_verified_at' => now()]);
    }

    public function updateRole(User $actor, User $target, string $role): User
    {
        $this->guardAgainstSelf($actor, $target, 'mengubah peran');

        return $this->users->update($target, ['role' => $role]);
    }

    /**
     * A user who owns invitations can't be deleted outright — `invitations.user_id` cascades on
     * delete, which would silently wipe out everything they've built. Deactivating is the safe
     * equivalent: it blocks login without touching their data.
     */
    public function delete(User $actor, User $target): void
    {
        $this->guardAgainstSelf($actor, $target, 'menghapus');

        if ($target->invitations()->exists()) {
            throw ValidationException::withMessages([
                'user' => ['Pengguna tidak dapat dihapus karena masih memiliki undangan. Nonaktifkan akun ini sebagai gantinya.'],
            ]);
        }

        $this->users->delete($target);
    }

    private function guardAgainstSelf(User $actor, User $target, string $action): void
    {
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => ["Anda tidak dapat {$action} akun Anda sendiri."],
            ]);
        }
    }
}
