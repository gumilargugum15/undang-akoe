<?php

namespace App\Helpers;

/**
 * Lightweight, dependency-free User-Agent sniffing for basic analytics
 * (device/browser/platform buckets). Good enough for dashboard stats —
 * not meant to be a precise UA database like a real parser package.
 */
class UserAgentParser
{
    public static function device(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'other';
        }

        if (preg_match('/iPad|Tablet(?!.*Mobile)/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|iPod|Windows Phone/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/Windows|Macintosh|X11|Linux/i', $userAgent)) {
            return 'desktop';
        }

        return 'other';
    }

    public static function browser(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        // Order matters: Edge/Chrome UAs also contain "Safari", Chrome UAs also contain "OPR" for Opera, etc.
        return match (true) {
            (bool) preg_match('/Edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $userAgent) => 'Opera',
            (bool) preg_match('/Firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/Chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/Safari\//i', $userAgent) => 'Safari',
            (bool) preg_match('/MSIE|Trident/i', $userAgent) => 'Internet Explorer',
            default => 'Lainnya',
        };
    }

    public static function platform(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        return match (true) {
            (bool) preg_match('/Android/i', $userAgent) => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod|iOS/i', $userAgent) => 'iOS',
            (bool) preg_match('/Windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/Macintosh|Mac OS X/i', $userAgent) => 'macOS',
            (bool) preg_match('/Linux/i', $userAgent) => 'Linux',
            default => 'Lainnya',
        };
    }
}
