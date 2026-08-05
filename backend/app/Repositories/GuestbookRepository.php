<?php

namespace App\Repositories;

use App\Models\Guestbook;
use App\Models\Invitation;
use App\Repositories\Interfaces\GuestbookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GuestbookRepository implements GuestbookRepositoryInterface
{
    public function paginate(Invitation $invitation, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $invitation->guestbook();

        if (! empty($filters['attendance'])) {
            $query->where('attendance', $filters['attendance']);
        }

        if (isset($filters['is_approved'])) {
            $query->where('is_approved', filter_var($filters['is_approved'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(Invitation $invitation, array $data): Guestbook
    {
        return $invitation->guestbook()->create($data);
    }

    public function summary(Invitation $invitation): array
    {
        $rows = $invitation->guestbook()
            ->selectRaw('attendance, count(*) as submissions, sum(guest_count) as guests')
            ->groupBy('attendance')
            ->get()
            ->keyBy('attendance');

        $hadir = $rows->get('hadir');
        $tidakHadir = $rows->get('tidak_hadir');
        $ragu = $rows->get('ragu');

        return [
            'total_submissions' => (int) $rows->sum('submissions'),
            'hadir' => [
                'submissions' => (int) ($hadir->submissions ?? 0),
                'guests' => (int) ($hadir->guests ?? 0),
            ],
            'tidak_hadir' => [
                'submissions' => (int) ($tidakHadir->submissions ?? 0),
            ],
            'ragu' => [
                'submissions' => (int) ($ragu->submissions ?? 0),
            ],
        ];
    }

    public function delete(Guestbook $guestbook): void
    {
        $guestbook->delete();
    }

    public function setApproval(Guestbook $guestbook, bool $approved): Guestbook
    {
        $guestbook->update(['is_approved' => $approved]);

        return $guestbook->fresh();
    }

    public function publicWall(Invitation $invitation, int $perPage = 15): LengthAwarePaginator
    {
        return $invitation->guestbook()
            ->where('is_approved', true)
            ->latest()
            ->paginate($perPage);
    }
}
