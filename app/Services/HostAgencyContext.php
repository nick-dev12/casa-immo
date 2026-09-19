<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\AuthHelper;
use App\Models\Conversation;
use App\Models\Establishment;

final class HostAgencyContext
{
    /**
     * Contexte shell agence si l'utilisateur a un établissement configuré.
     *
     * @return array{
     *     establishment: array<string, mixed>,
     *     isAgency: bool,
     *     navBadges: array<string, int>
     * }|null
     */
    public static function forUser(int $userId): ?array
    {
        $establishment = (new Establishment())->findByOwnerId($userId);
        if ($establishment === null) {
            return null;
        }

        $navBadges = [
            'listings' => 0,
            'bookings' => 0,
            'messages' => 0,
        ];

        try {
            $stats = (new HostDashboardService())->forOwner($userId)['stats'];
            $navBadges['listings'] = (int) ($stats['listings_draft'] ?? 0) + (int) ($stats['listings_pending'] ?? 0);
            $navBadges['bookings'] = (int) ($stats['bookings_upcoming'] ?? 0);
        } catch (\Throwable) {
            // Badges optionnels — le shell reste utilisable.
        }

        try {
            $navBadges['messages'] = (new Conversation())->unreadCountForUser($userId);
        } catch (\Throwable) {
            $navBadges['messages'] = 0;
        }

        return [
            'establishment' => $establishment,
            'isAgency' => AuthHelper::isAgency($userId),
            'navBadges' => $navBadges,
        ];
    }
}
