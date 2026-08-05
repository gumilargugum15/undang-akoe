<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

/**
 * Zero-fills a daily count series so trend charts don't have gaps on days
 * with no activity. Shared by InvitationVisitService (per-invitation) and
 * DashboardService (per-customer / platform-wide) to avoid duplicating the
 * same fill-the-gaps loop in both places. Callers are responsible for their
 * own querying (via their repository, or directly for cross-cutting reports
 * like the Dashboard) — this only shapes the already-grouped result.
 */
class DailySeriesBuilder
{
    /**
     * @param  Collection<string, int>  $countsByDate  keyed by 'Y-m-d' => count
     * @return array<int, array{date: string, views: int}>
     */
    public static function fill(Collection $countsByDate, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $series[] = [
                'date' => $date,
                'views' => (int) ($countsByDate[$date] ?? 0),
            ];
        }

        return $series;
    }
}
