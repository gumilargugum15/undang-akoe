<?php

namespace App\Services;

use App\Helpers\DailySeriesBuilder;
use App\Models\Guestbook;
use App\Models\Invitation;
use App\Models\InvitationVisit;
use App\Models\User;

/**
 * Cross-cutting reporting queries for the Dashboard — deliberately not a
 * Repository+Service pair like the other modules, since there's no single
 * "Dashboard" resource to CRUD, just read-only aggregates over models that
 * already have their own repositories.
 */
class DashboardService
{
    private const TREND_DAYS = 30;

    /**
     * @return array<string, mixed>
     */
    public function forCustomer(User $user): array
    {
        $invitationIds = Invitation::where('user_id', $user->id)->pluck('id');

        return [
            'total_invitations' => $invitationIds->count(),
            'total_visitors' => (int) Invitation::whereIn('id', $invitationIds)->sum('view_count'),
            'total_guestbook_messages' => Guestbook::whereIn('invitation_id', $invitationIds)->count(),
            'total_attendance' => (int) Guestbook::whereIn('invitation_id', $invitationIds)
                ->where('attendance', 'hadir')
                ->sum('guest_count'),
            'total_gifts_received' => null,
            'gifts_received_note' => 'Pencatatan amplop masuk otomatis belum tersedia — Amplop Digital saat ini hanya menampilkan info rekening/QRIS ke tamu, belum ada konfirmasi transfer masuk.',
            'visitor_chart' => $this->dailyVisitorChart($invitationIds->all()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forAdmin(): array
    {
        return [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_invitations' => Invitation::count(),
            'total_published_invitations' => Invitation::where('status', 'published')->count(),
            'total_visitors' => (int) Invitation::sum('view_count'),
            'total_guestbook_messages' => Guestbook::count(),
            'total_attendance' => (int) Guestbook::where('attendance', 'hadir')->sum('guest_count'),
            'total_revenue' => null,
            'revenue_note' => 'Laporan pendapatan belum tersedia — modul Kelola Pembayaran belum dibangun.',
            'visitor_chart' => $this->dailyVisitorChart(null),
        ];
    }

    /**
     * Zero-filled daily visit counts. Pass an array of invitation IDs to scope
     * to one customer's invitations, or null for the platform-wide total.
     *
     * @param  array<int, int>|null  $invitationIds
     * @return array<int, array{date: string, views: int}>
     */
    private function dailyVisitorChart(?array $invitationIds): array
    {
        $query = InvitationVisit::query()
            ->selectRaw('DATE(visited_at) as date, count(*) as total')
            ->where('visited_at', '>=', now()->subDays(self::TREND_DAYS - 1)->startOfDay());

        if ($invitationIds !== null) {
            $query->whereIn('invitation_id', $invitationIds);
        }

        $counts = $query->groupBy('date')->orderBy('date')->get()->pluck('total', 'date');

        return DailySeriesBuilder::fill($counts, self::TREND_DAYS);
    }
}
