<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;

final class ProfileService
{
    /**
     * @return array{
     *   favorite_count: int,
     *   reservation_count: int,
     *   review_count: int,
     *   traveler_count: int,
     *   notification_count: int,
     *   completed_bookings: int
     * }
     */
    public function statsForUser(int $userId): array
    {
        $bookingModel = new Booking();

        $messageCount = 0;
        try {
            $messageCount = (new \App\Models\Conversation())->unreadCountForUser($userId);
        } catch (\Throwable) {
            $messageCount = 0;
        }

        return [
            'favorite_count' => (new Favorite())->countForUser($userId),
            'reservation_count' => $bookingModel->countForUser($userId),
            'review_count' => (new Review())->countForUser($userId),
            'traveler_count' => $bookingModel->travelerCountForUser($userId),
            'notification_count' => (new Notification())->unreadCountForUser($userId),
            'message_count' => $messageCount,
            'completed_bookings' => $bookingModel->completedCountForUser($userId),
        ];
    }

    /**
     * @return array{level: int, prefix: string, name: string}
     */
    public function tierForUser(int $userId): array
    {
        return $this->tierFromStats($this->statsForUser($userId));
    }

    /**
     * @param array{completed_bookings?: int} $stats
     * @return array{level: int, prefix: string, name: string}
     */
    public function tierFromStats(array $stats): array
    {
        $completed = (int) ($stats['completed_bookings'] ?? 0);

        if ($completed >= 10) {
            return ['level' => 3, 'prefix' => __('profile.tier_level', ['level' => 3]), 'name' => __('profile.tier_gold')];
        }

        if ($completed >= 3) {
            return ['level' => 2, 'prefix' => __('profile.tier_level', ['level' => 2]), 'name' => __('profile.tier_name')];
        }

        return ['level' => 1, 'prefix' => __('profile.tier_level', ['level' => 1]), 'name' => __('profile.tier_name')];
    }

    /**
     * @return list<array{title: ?string, items: list<array<string, mixed>>}>
     */
    public function menuGroups(int $userId, ?array $user = null, ?array $stats = null): array
    {
        $stats ??= $this->statsForUser($userId);
        $userModel = new User();
        $user ??= $userModel->findById($userId);
        $fullName = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
        $isAgency = $userModel->hasRole($userId, 'agency');
        $isOwner = $isAgency
            || $userModel->hasRole($userId, 'owner')
            || $userModel->hasRole($userId, 'land_seller');

        $groups = [
            [
                'title' => null,
                'items' => [
                    [
                        'icon' => 'bi-person',
                        'label' => __('profile.personal_info'),
                        'url' => url('/profile/personal'),
                        'meta' => $fullName,
                    ],
                    [
                        'icon' => 'bi-shield-lock',
                        'label' => __('profile.security'),
                        'url' => url('/profile/security'),
                        'meta' => mask_email((string) ($user['email'] ?? '')),
                    ],
                    [
                        'icon' => 'bi-people',
                        'label' => __('profile.other_travelers'),
                        'url' => url('/profile/travelers'),
                        'badge' => $stats['traveler_count'] > 0 ? $stats['traveler_count'] : null,
                    ],
                ],
            ],
            [
                'title' => __('profile.preferences'),
                'items' => [
                    [
                        'icon' => 'bi-gear',
                        'label' => __('profile.device_settings'),
                        'url' => url('/profile/preferences?section=device'),
                    ],
                    [
                        'icon' => 'bi-sliders',
                        'label' => __('profile.accessibility'),
                        'url' => url('/profile/preferences?section=accessibility'),
                    ],
                    [
                        'icon' => 'bi-chat-square-text',
                        'label' => __('profile.communication'),
                        'url' => url('/profile/preferences?section=communication'),
                    ],
                ],
            ],
            [
                'title' => __('profile.trip_tracking'),
                'items' => [
                    [
                        'icon' => 'bi-chat-dots',
                        'label' => __('nav.messages'),
                        'url' => url('/messages'),
                        'badge' => ($stats['message_count'] ?? 0) > 0 ? $stats['message_count'] : null,
                    ],
                    [
                        'icon' => 'bi-heart',
                        'label' => __('nav.favorites'),
                        'url' => url('/favorites'),
                        'badge' => $stats['favorite_count'] > 0 ? $stats['favorite_count'] : null,
                    ],
                    [
                        'icon' => 'bi-calendar-check',
                        'label' => __('nav.reservations'),
                        'url' => url('/reservations'),
                        'badge' => $stats['reservation_count'] > 0 ? $stats['reservation_count'] : null,
                    ],
                    [
                        'icon' => 'bi-chat-left-dots',
                        'label' => __('profile.my_reviews'),
                        'url' => url('/profile/reviews'),
                        'badge' => $stats['review_count'] > 0 ? $stats['review_count'] : null,
                    ],
                ],
            ],
            [
                'title' => __('profile.help'),
                'items' => [
                    [
                        'icon' => 'bi-headset',
                        'label' => __('profile.contact_support'),
                        'url' => url('/profile/help'),
                    ],
                    [
                        'icon' => 'bi-globe2',
                        'label' => __('profile.travel_safety'),
                        'url' => url('/profile/safety'),
                    ],
                ],
            ],
            [
                'title' => __('profile.legal'),
                'items' => [
                    [
                        'icon' => 'bi-shield-check',
                        'label' => __('profile.privacy'),
                        'url' => url('/profile/privacy'),
                    ],
                ],
            ],
            [
                'title' => __('profile.discover'),
                'items' => [
                    [
                        'icon' => 'bi-percent',
                        'label' => __('profile.offers'),
                        'url' => url('/properties'),
                        'meta' => __('profile.offers_meta'),
                    ],
                ],
            ],
        ];

        $groups[] = [
            'title' => __('profile.manage_listing'),
            'items' => [
                [
                    'icon' => 'bi-house-add',
                    'label' => $isAgency
                        ? __('profile.agency_space')
                        : ($isOwner ? __('profile.register_listing') : __('nav.add_listing')),
                    'url' => url('/host'),
                    'meta' => $isAgency
                        ? __('profile.agency_meta')
                        : ($isOwner ? __('profile.owner_meta') : __('host.setup_cta')),
                ],
            ],
        ];

        return $groups;
    }
}
