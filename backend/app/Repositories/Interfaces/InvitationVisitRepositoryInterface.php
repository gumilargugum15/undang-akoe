<?php

namespace App\Repositories\Interfaces;

use App\Models\Invitation;
use App\Models\InvitationVisit;
use Illuminate\Support\Collection;

interface InvitationVisitRepositoryInterface
{
    public function track(Invitation $invitation, array $data): InvitationVisit;

    /**
     * @return Collection<int, object>
     */
    public function countBy(Invitation $invitation, string $column): Collection;

    public function totalViews(Invitation $invitation): int;

    public function uniqueVisitors(Invitation $invitation): int;

    /**
     * @return Collection<int, object>
     */
    public function dailyCounts(Invitation $invitation, int $days): Collection;

    /**
     * @return Collection<int, object>
     */
    public function topReferrers(Invitation $invitation, int $limit): Collection;
}
