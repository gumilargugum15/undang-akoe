<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\InvitationVisit;
use App\Repositories\Interfaces\InvitationVisitRepositoryInterface;
use Illuminate\Support\Collection;

class InvitationVisitRepository implements InvitationVisitRepositoryInterface
{
    public function track(Invitation $invitation, array $data): InvitationVisit
    {
        return $invitation->visits()->create($data);
    }

    public function countBy(Invitation $invitation, string $column): Collection
    {
        return $invitation->visits()
            ->selectRaw("{$column}, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();
    }

    public function totalViews(Invitation $invitation): int
    {
        return $invitation->visits()->count();
    }

    public function uniqueVisitors(Invitation $invitation): int
    {
        return $invitation->visits()->distinct('session_id')->count('session_id');
    }

    public function dailyCounts(Invitation $invitation, int $days): Collection
    {
        return $invitation->visits()
            ->selectRaw('DATE(visited_at) as date, count(*) as total')
            ->where('visited_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function topReferrers(Invitation $invitation, int $limit): Collection
    {
        return $invitation->visits()
            ->selectRaw('referrer, count(*) as total')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }
}
