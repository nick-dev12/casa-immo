<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use App\Services\ProfileService;

final class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct()
    {
        parent::__construct();
        $this->profileService = new ProfileService();
    }

    public function index(): string
    {
        [$user, $userId] = $this->requireUser();
        $stats = $this->profileService->statsForUser($userId);

        return $this->view('profile/index', [
            'title' => __('profile.title'),
            'isProfile' => true,
            'isAccountPage' => true,
            'user' => $user,
            'stats' => $stats,
            'tier' => $this->profileService->tierFromStats($stats),
            'menuGroups' => $this->profileService->menuGroups($userId, $user, $stats),
        ]);
    }

    public function personal(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('personal', [
            'title' => __('profile.personal_info'),
            'pageTitle' => __('profile.personal_info'),
            'user' => $user,
            'success' => flash('profile_success'),
            'error' => flash('profile_error'),
        ], $user, $userId);
    }

    public function updatePersonal(): never
    {
        [$user, $userId] = $this->requireUser();
        $userModel = new User();

        $result = $userModel->updateProfile(
            $userId,
            (string) $this->request->input('first_name', ''),
            (string) $this->request->input('last_name', ''),
            (string) $this->request->input('email', ''),
            (string) $this->request->input('phone', '')
        );

        if ($result !== true) {
            Session::flash('profile_error', $result);
            $this->redirect(url('/profile/personal'));
        }

        Session::flash('profile_success', __('profile.updated'));
        AuthHelper::login($userId);
        $this->redirect(url('/profile/personal'));
    }

    public function security(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('security', [
            'title' => __('profile.security'),
            'pageTitle' => __('profile.security'),
            'user' => $user,
            'success' => flash('profile_success'),
            'error' => flash('profile_error'),
        ], $user, $userId);
    }

    public function updateSecurity(): never
    {
        [, $userId] = $this->requireUser();
        $currentPassword = (string) $this->request->input('current_password', '');
        $newPassword = (string) $this->request->input('new_password', '');
        $confirmPassword = (string) $this->request->input('new_password_confirmation', '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            Session::flash('profile_error', __('profile.error.required'));
            $this->redirect(url('/profile/security'));
        }

        if (strlen($newPassword) < 8) {
            Session::flash('profile_error', __('profile.error.password_short'));
            $this->redirect(url('/profile/security'));
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('profile_error', __('profile.error.password_mismatch'));
            $this->redirect(url('/profile/security'));
        }

        $userModel = new User();
        $authUser = $userModel->findAuthById($userId);
        if ($authUser === null || !$userModel->verifyPassword($currentPassword, (string) $authUser['password'])) {
            Session::flash('profile_error', __('profile.error.password_current'));
            $this->redirect(url('/profile/security'));
        }

        $userModel->updatePassword($userId, $newPassword);
        Session::flash('profile_success', __('profile.password_updated'));
        $this->redirect(url('/profile/security'));
    }

    public function travelers(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('travelers', [
            'title' => __('profile.other_travelers'),
            'pageTitle' => __('profile.other_travelers'),
            'travelers' => (new Booking())->travelersForUser($userId),
        ], $user, $userId);
    }

    public function reviews(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('reviews', [
            'title' => __('profile.my_reviews'),
            'pageTitle' => __('profile.my_reviews'),
            'reviews' => (new Review())->forUser($userId),
        ], $user, $userId);
    }

    public function preferences(): string
    {
        [$user, $userId] = $this->requireUser();
        $section = (string) $this->request->input('section', 'device');
        if (!in_array($section, ['device', 'accessibility', 'communication'], true)) {
            $section = 'device';
        }

        return $this->subpage('preferences', [
            'title' => __('profile.preferences'),
            'pageTitle' => match ($section) {
                'accessibility' => __('profile.accessibility'),
                'communication' => __('profile.communication'),
                default => __('profile.device_settings'),
            },
            'section' => $section,
            'preferences' => user_preferences($userId),
            'success' => flash('profile_success'),
        ], $user, $userId);
    }

    public function updatePreferences(): never
    {
        [, $userId] = $this->requireUser();
        $section = (string) $this->request->input('section', 'device');

        $payload = match ($section) {
            'accessibility' => [
                'large_text' => $this->request->input('large_text') !== null,
                'high_contrast' => $this->request->input('high_contrast') !== null,
            ],
            'communication' => [
                'marketing_emails' => $this->request->input('marketing_emails') !== null,
                'booking_updates' => $this->request->input('booking_updates') !== null,
                'promotional_offers' => $this->request->input('promotional_offers') !== null,
            ],
            default => [
                'push_notifications' => $this->request->input('push_notifications') !== null,
                'email_notifications' => $this->request->input('email_notifications') !== null,
                'sms_notifications' => $this->request->input('sms_notifications') !== null,
            ],
        };

        save_user_preferences($userId, $payload);
        Session::flash('profile_success', __('profile.preferences_updated'));
        $this->redirect(url('/profile/preferences?section=' . urlencode($section)));
    }

    public function notifications(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('notifications', [
            'title' => __('nav.notifications'),
            'pageTitle' => __('nav.notifications'),
            'notifications' => (new Notification())->forUser($userId),
        ], $user, $userId);
    }

    public function help(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('help', [
            'title' => __('profile.contact_support'),
            'pageTitle' => __('profile.contact_support'),
        ], $user, $userId, ['isHelpPage' => true]);
    }

    public function safety(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('safety', [
            'title' => __('profile.travel_safety'),
            'pageTitle' => __('profile.travel_safety'),
        ], $user, $userId);
    }

    public function privacy(): string
    {
        [$user, $userId] = $this->requireUser();

        return $this->subpage('privacy', [
            'title' => __('profile.privacy'),
            'pageTitle' => __('profile.privacy'),
        ], $user, $userId);
    }

    /**
     * @return array{0: array<string, mixed>, 1: int}
     */
    private function requireUser(): array
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/profile'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/profile'));
        }

        return [$user, (int) $user['id']];
    }

    /**
     * @param array<string, mixed> $contentData
     * @param array<string, mixed> $user
     * @param array<string, mixed> $extra
     * @return string
     */
    private function subpage(string $contentView, array $contentData, array $user, int $userId, array $extra = []): string
    {
        return $this->view('profile/_subpage', array_merge($this->profileShellData($user, $userId), $extra, [
            'profileContentView' => $contentView,
            'profileContentData' => $contentData,
            'pageTitle' => (string) ($contentData['pageTitle'] ?? ''),
            'title' => (string) ($contentData['title'] ?? __('profile.title')),
        ]));
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function profileShellData(array $user, int $userId): array
    {
        return [
            'isProfile' => true,
            'isAccountPage' => true,
            'user' => $user,
            'stats' => $this->profileService->statsForUser($userId),
            'tier' => $this->profileService->tierForUser($userId),
        ];
    }
}
