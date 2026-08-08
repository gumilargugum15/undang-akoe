<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function __construct(
        private readonly InvitationRepositoryInterface $invitations,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        return $this->invitations->paginate($user, $filters, (int) ($filters['per_page'] ?? 15));
    }

    public function create(User $owner, array $data): Invitation
    {
        $data['user_id'] = $owner->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);
        $data['status'] = 'draft';
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->invitations->create($data);
    }

    public function update(Invitation $invitation, array $data): Invitation
    {
        if (array_key_exists('slug', $data) && $data['slug'] !== $invitation->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $invitation->id);
        }

        return $this->invitations->update($invitation, $data);
    }

    public function delete(Invitation $invitation): void
    {
        $this->invitations->delete($invitation);
    }

    /**
     * User-triggered publish action. Only reaches `activate()` for packages that don't
     * require payment (FREE, or a promo with requires_payment=false) — a paid package must
     * go through CheckoutService/TransactionService::markPaid() instead, which calls
     * activate() directly once the transaction is confirmed paid.
     */
    public function publish(Invitation $invitation): Invitation
    {
        if ($invitation->package?->requiresPayment()) {
            throw ValidationException::withMessages([
                'package' => ['Paket ini memerlukan pembayaran. Selesaikan checkout terlebih dahulu.'],
            ]);
        }

        return $this->activate($invitation);
    }

    /**
     * The single place an invitation actually becomes Published — called by publish() for
     * free packages and by TransactionService once a transaction is verified paid. Re-checks
     * suspension and package limits every time (defense-in-depth): a paid transaction doesn't
     * bypass moderation or a since-tightened active-invitation cap.
     */
    public function activate(Invitation $invitation): Invitation
    {
        if ($invitation->status === 'suspended') {
            throw ValidationException::withMessages([
                'status' => ['Undangan yang ditangguhkan admin tidak dapat dipublikasikan.'],
            ]);
        }

        // No package selected at all (it's nullable — a customer can skip choosing one) means
        // no package-derived restrictions apply, same as before packages/payment existed.
        $maxActive = $invitation->package?->limit('max_active_invitations');

        if ($maxActive !== null
            && $this->invitations->countActivePublishedForUser($invitation->user_id, $invitation->id) >= $maxActive) {
            throw ValidationException::withMessages([
                'package' => ["Batas maksimal {$maxActive} undangan aktif untuk paket ini sudah tercapai."],
            ]);
        }

        $durationDays = $invitation->package?->duration_days;

        return $this->invitations->update($invitation, [
            'status' => 'published',
            'published_at' => now(),
            'expires_at' => $durationDays ? now()->addDays($durationDays) : null,
        ]);
    }

    public function unpublish(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, ['status' => 'draft']);
    }

    public function suspend(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, ['status' => 'suspended']);
    }

    /**
     * User-initiated hiding after an event is over — distinct from the job-driven `expired`
     * (time-based) and the admin-only `suspended` (moderation). Doesn't touch expires_at or
     * any transaction history.
     */
    public function archive(Invitation $invitation): Invitation
    {
        if (! in_array($invitation->status, ['published', 'expired'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Hanya undangan yang sudah published atau expired yang dapat diarsipkan.'],
            ]);
        }

        return $this->invitations->update($invitation, ['status' => 'archived']);
    }

    /**
     * Reverts a checkout attempt back to Draft — called by TransactionService when a pending
     * transaction is cancelled by the customer, keeping the filled-in invitation data intact.
     */
    public function returnToDraft(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, [
            'status' => 'draft',
            'current_transaction_id' => null,
        ]);
    }

    public function reactivate(Invitation $invitation): Invitation
    {
        return $this->invitations->update($invitation, [
            'status' => $invitation->published_at ? 'published' : 'draft',
        ]);
    }

    /**
     * Dedicated theme-switch action (spec: POST/PATCH .../change-theme) — resets any prior
     * per-invitation color/font overrides, since they were tuned against the old theme's token
     * set and could look wrong (or simply be irrelevant) against the new one.
     */
    public function changeTheme(Invitation $invitation, int $themeId): Invitation
    {
        if ($invitation->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['Tema hanya bisa diganti selama undangan masih berstatus draft.'],
            ]);
        }

        return $this->invitations->update($invitation, [
            'theme_id' => $themeId,
            'theme_settings' => null,
        ]);
    }

    private function uniqueSlug(string $source, ?int $exceptId = null): string
    {
        $base = Str::slug($source);
        $slug = $base;
        $attempt = 1;

        while ($this->invitations->slugExists($slug, $exceptId)) {
            $attempt++;
            $slug = "{$base}-{$attempt}";
        }

        return $slug;
    }
}
