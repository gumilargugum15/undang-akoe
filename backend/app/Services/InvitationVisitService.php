<?php

namespace App\Services;

use App\Helpers\DailySeriesBuilder;
use App\Helpers\UserAgentParser;
use App\Models\Invitation;
use App\Models\InvitationVisit;
use App\Repositories\Interfaces\InvitationVisitRepositoryInterface;
use Illuminate\Support\Str;

class InvitationVisitService
{
    /**
     * How many days of daily counts to return in the summary (for the
     * visitor trend chart).
     */
    private const TREND_DAYS = 30;

    private const TOP_REFERRERS_LIMIT = 10;

    public function __construct(
        private readonly InvitationVisitRepositoryInterface $visits,
    ) {}

    /**
     * @param  array<string, mixed>  $requestData  ip, user_agent, session_id, referrer
     */
    public function track(Invitation $invitation, array $requestData): InvitationVisit
    {
        $userAgent = $requestData['user_agent'] ?? null;

        $visit = $this->visits->track($invitation, [
            'session_id' => $requestData['session_id'] ?? (string) Str::uuid(),
            'ip_address' => $requestData['ip_address'] ?? null,
            'user_agent' => $userAgent,
            'device' => UserAgentParser::device($userAgent),
            'browser' => UserAgentParser::browser($userAgent),
            'platform' => UserAgentParser::platform($userAgent),
            'referrer' => $requestData['referrer'] ?? null,
            'visited_at' => now(),
        ]);

        $invitation->increment('view_count');

        return $visit;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Invitation $invitation): array
    {
        return [
            'total_views' => $this->visits->totalViews($invitation),
            'unique_visitors' => $this->visits->uniqueVisitors($invitation),
            'devices' => $this->visits->countBy($invitation, 'device')
                ->pluck('total', 'device'),
            'browsers' => $this->visits->countBy($invitation, 'browser')
                ->pluck('total', 'browser'),
            'top_referrers' => $this->visits->topReferrers($invitation, self::TOP_REFERRERS_LIMIT)
                ->map(fn ($row) => [
                    'referrer' => $row->referrer ?: 'Langsung / Tidak diketahui',
                    'total' => $row->total,
                ]),
            'daily' => DailySeriesBuilder::fill(
                $this->visits->dailyCounts($invitation, self::TREND_DAYS)->pluck('total', 'date'),
                self::TREND_DAYS,
            ),
        ];
    }
}
