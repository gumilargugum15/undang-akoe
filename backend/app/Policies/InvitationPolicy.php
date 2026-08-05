<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvitationPolicy
{
    /**
     * Listing is always allowed — scoping to "own invitations only" for
     * customers happens in InvitationRepository::paginate(), not here.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->isOwnedBy($user);
    }

    /**
     * A customer must verify their email before they can create invitations;
     * admins are exempt, consistent with the admin-override pattern below.
     */
    public function create(User $user): Response
    {
        if ($user->isAdmin() || $user->hasVerifiedEmail()) {
            return Response::allow();
        }

        return Response::deny('Silahkan hubungi admin untuk verifikasi email Anda sebelum membuat undangan.');
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->isOwnedBy($user);
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->isOwnedBy($user);
    }

    /**
     * Publish/unpublish is owner-or-admin, same as update — a customer manages
     * their own invitation's lifecycle unless admin has suspended it (enforced
     * in InvitationService::publish(), not here).
     */
    public function publish(User $user, Invitation $invitation): bool
    {
        return $this->update($user, $invitation);
    }

    /**
     * Suspending/reactivating is a moderation action — admin only.
     */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin() || $invitation->isOwnedBy($user);
    }

    public function forceDelete(User $user, Invitation $invitation): bool
    {
        return $user->isAdmin();
    }
}
