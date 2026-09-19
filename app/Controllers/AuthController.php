<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url('/'));
        }

        return $this->view('auth/login', [
            'title' => __('auth.sign_in_title'),
            'redirect' => (string) $this->request->input('redirect', ''),
            'authError' => Session::getFlash('auth_error'),
            'authSuccess' => Session::getFlash('auth_success'),
            'isAccountPage' => true,
            'isAuthPage' => true,
        ]);
    }

    public function showRegister(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        return $this->view('auth/choose-profile', [
            'title' => __('auth.choose_profile_title'),
            'redirect' => (string) $this->request->input('redirect', ''),
            'isAccountPage' => true,
            'isAuthPage' => true,
            'isAuthChoosePage' => true,
        ]);
    }

    public function registerForm(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $type = (string) $this->request->input('type', 'client');
        if (!in_array($type, ['client', 'agency'], true)) {
            $this->redirect(url('/register'));
        }

        return $this->view('auth/register-form', [
            'title' => $type === 'agency' ? __('auth.create_agency_account') : __('auth.create_client_account'),
            'redirect' => (string) $this->request->input('redirect', ''),
            'accountType' => $type,
            'authError' => Session::getFlash('auth_error'),
            'isAccountPage' => true,
            'isAuthPage' => true,
            'isAuthRegisterPage' => true,
        ]);
    }

    public function login(): never
    {
        $email = trim((string) $this->request->input('email', ''));
        $password = (string) $this->request->input('password', '');
        $redirect = (string) $this->request->input('redirect', '');

        if ($email === '' || $password === '') {
            $this->flashAuthError(__('auth.error_required'), 'login', ['email' => $email], $redirect);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !$userModel->verifyPassword($password, (string) $user['password'])) {
            $this->flashAuthError(__('auth.error_invalid'), 'login', ['email' => $email], $redirect);
        }

        AuthHelper::login((int) $user['id']);
        $userModel->touchLogin((int) $user['id']);

        $this->redirect(url($this->postLoginPath($redirect)));
    }

    public function register(): never
    {
        $accountType = (string) $this->request->input('account_type', 'client');
        $fullName = trim((string) $this->request->input('full_name', ''));
        $email = trim((string) $this->request->input('email', ''));
        $password = (string) $this->request->input('password', '');
        $redirect = (string) $this->request->input('redirect', '');
        $agencyName = trim((string) $this->request->input('agency_name', ''));
        $agencyCity = trim((string) $this->request->input('agency_city', ''));
        $agencyPhone = trim((string) $this->request->input('agency_phone', ''));
        $agencyAddress = trim((string) $this->request->input('agency_address', ''));
        $agencyDistrict = trim((string) $this->request->input('agency_district', ''));
        $agencyCountry = trim((string) $this->request->input('agency_country', ''));
        $agencyLatitude = trim((string) $this->request->input('agency_latitude', ''));
        $agencyLongitude = trim((string) $this->request->input('agency_longitude', ''));
        $agencyGpsAccuracy = trim((string) $this->request->input('agency_gps_accuracy', ''));

        $nameParts = preg_split('/\s+/u', $fullName, 2) ?: [];
        $firstName = trim((string) ($nameParts[0] ?? ''));
        $lastName = trim((string) ($nameParts[1] ?? ''));
        if ($lastName === '') {
            $lastName = '.';
        }

        $oldInput = [
            'account_type' => $accountType,
            'full_name' => $fullName,
            'email' => $email,
            'agency_name' => $agencyName,
            'agency_city' => $agencyCity,
            'agency_phone' => $agencyPhone,
            'agency_address' => $agencyAddress,
            'agency_district' => $agencyDistrict,
            'agency_country' => $agencyCountry,
            'agency_latitude' => $agencyLatitude,
            'agency_longitude' => $agencyLongitude,
            'agency_gps_accuracy' => $agencyGpsAccuracy,
        ];

        if ($firstName === '' || $email === '' || strlen($password) < 8) {
            $this->flashAuthError(__('auth.error_register'), 'register', $oldInput, $redirect);
        }

        $userModel = new User();

        if ($accountType === 'agency') {
            if ($agencyName === '' || $agencyCity === '') {
                $this->flashAuthError(__('auth.error_agency_register'), 'register', $oldInput, $redirect);
            }

            if ($agencyLatitude === '' || $agencyLongitude === '') {
                $this->flashAuthError(__('auth.error_agency_location'), 'register', $oldInput, $redirect);
            }

            $latitude = (float) $agencyLatitude;
            $longitude = (float) $agencyLongitude;
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                $this->flashAuthError(__('auth.error_agency_location'), 'register', $oldInput, $redirect);
            }

            $user = $userModel->createAgencyAccount(
                $firstName,
                $lastName,
                $email,
                $password,
                $agencyName,
                $agencyCity,
                $agencyPhone !== '' ? $agencyPhone : null,
                $agencyAddress !== '' ? $agencyAddress : null,
                $latitude,
                $longitude,
                $agencyDistrict !== '' ? $agencyDistrict : null
            );

            if ($user === null) {
                $this->flashAuthError(__('auth.error_email_taken'), 'register', $oldInput, $redirect);
            }

            AuthHelper::login((int) $user['id']);
            Session::flash('auth_success', __('auth.success_lead_agency'));
            $this->redirect(url('/'));
        }

        $user = $userModel->createClient($firstName, $lastName, $email, $password);

        if ($user === null) {
            $this->flashAuthError(__('auth.error_email_taken'), 'register', $oldInput, $redirect);
        }

        AuthHelper::login((int) $user['id']);
        Session::flash('auth_success', __('auth.success_lead_client'));
        $this->redirect(url('/'));
    }

    public function registerSuccess(): string
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login'));
        }

        $payload = Session::getFlash('register_success');
        if (!is_array($payload)) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $accountType = (($payload['account_type'] ?? '') === 'agency') ? 'agency' : 'client';
        $continueUrl = (string) ($payload['continue_url'] ?? '');
        if ($continueUrl === '') {
            $continueUrl = $accountType === 'agency' ? url('/host') : url('/');
        }

        return $this->view('auth/register-success', [
            'title' => __('auth.success_title'),
            'accountType' => $accountType,
            'continueUrl' => $continueUrl,
            'isAccountPage' => true,
            'isAuthPage' => true,
        ]);
    }

    public function logout(): never
    {
        AuthHelper::logout();
        $this->redirect(url('/'));
    }

    public function showForgotPassword(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $step = (string) Session::get('password_reset_step', 'email');
        if (!in_array($step, ['email', 'code', 'password', 'done'], true)) {
            $step = 'email';
        }

        // L'écran "réussi" ne s'affiche qu'une fois.
        if ($step === 'done') {
            Session::set('password_reset_step', 'email');
        }

        return $this->view('auth/forgot-password', [
            'title' => __('auth.forgot_title'),
            'authError' => Session::getFlash('auth_error'),
            'resetStep' => $step,
            'pendingEmail' => (string) Session::get('password_reset_pending_email', ''),
            'resetToken' => (string) Session::get('password_reset_token', ''),
            'isAccountPage' => true,
            'isAuthPage' => true,
        ]);
    }

    public function sendForgotPassword(): never
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $email = trim((string) $this->request->input('email', ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('auth_error', __('auth.forgot_error_email'));
            Session::flash('_old_input.email', $email);
            Session::set('password_reset_step', 'email');
            Session::remove('password_reset_pending_email');
            Session::remove('password_reset_token');
            $this->redirect(url('/forgot-password'));
        }

        try {
            $result = (new \App\Services\PasswordResetService())->requestReset($email);

            if (!$result['ok']) {
                Session::flash('auth_error', __('auth.forgot_error_send'));
                Session::flash('_old_input.email', $email);
                Session::set('password_reset_step', 'email');
                Session::remove('password_reset_pending_email');
                Session::remove('password_reset_token');
                $this->redirect(url('/forgot-password'));
            }

            Session::set('password_reset_pending_email', mb_strtolower($email));
            Session::set('password_reset_step', 'code');
            Session::remove('password_reset_token');
        } catch (\Throwable) {
            Session::flash('auth_error', __('auth.forgot_error_send'));
            Session::flash('_old_input.email', $email);
            Session::set('password_reset_step', 'email');
            Session::remove('password_reset_pending_email');
            Session::remove('password_reset_token');
        }

        $this->redirect(url('/forgot-password'));
    }

    public function verifyForgotPassword(): never
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $email = trim((string) $this->request->input('email', Session::get('password_reset_pending_email', '')));
        $code = trim((string) $this->request->input('code', ''));
        if ($code === '') {
            $digits = $this->request->input('code_digits', []);
            if (is_array($digits)) {
                $code = implode('', array_map(static fn ($d): string => (string) $d, $digits));
            }
        }
        $code = preg_replace('/\D+/', '', $code) ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($code) !== 6) {
            Session::flash('auth_error', __('auth.forgot_error_code'));
            Session::set('password_reset_pending_email', mb_strtolower($email));
            Session::set('password_reset_step', 'code');
            $this->redirect(url('/forgot-password'));
        }

        $service = new \App\Services\PasswordResetService();
        if (!$service->verifyCode($email, $code)) {
            Session::flash('auth_error', __('auth.forgot_error_code_invalid'));
            Session::set('password_reset_pending_email', mb_strtolower($email));
            Session::set('password_reset_step', 'code');
            $this->redirect(url('/forgot-password'));
        }

        Session::set('password_reset_pending_email', mb_strtolower($email));
        Session::set('password_reset_token', $code);
        Session::set('password_reset_step', 'password');
        $this->redirect(url('/forgot-password'));
    }

    public function resetForgotPassword(): never
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $email = trim((string) $this->request->input('email', Session::get('password_reset_pending_email', '')));
        $token = preg_replace('/\D+/', '', trim((string) $this->request->input('token', Session::get('password_reset_token', '')))) ?? '';
        $password = (string) $this->request->input('password', '');
        $passwordConfirm = (string) $this->request->input('password_confirmation', '');

        Session::set('password_reset_pending_email', mb_strtolower($email));
        Session::set('password_reset_token', $token);
        Session::set('password_reset_step', 'password');

        if ($password !== $passwordConfirm) {
            Session::flash('auth_error', __('auth.reset_error_mismatch'));
            $this->redirect(url('/forgot-password'));
        }

        $result = (new \App\Services\PasswordResetService())->resetPassword($email, $token, $password);

        if ($result !== true) {
            Session::flash('auth_error', is_string($result) ? $result : __('auth.reset_error_failed'));
            $this->redirect(url('/forgot-password'));
        }

        Session::remove('password_reset_pending_email');
        Session::remove('password_reset_token');
        Session::set('password_reset_step', 'done');
        $this->redirect(url('/forgot-password'));
    }

    public function showResetPassword(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url(AuthHelper::homePath()));
        }

        $email = trim((string) $this->request->input('email', ''));
        $token = preg_replace('/\D+/', '', trim((string) $this->request->input('token', ''))) ?? '';

        if ($email === '' || strlen($token) !== 6) {
            Session::flash('auth_error', __('auth.reset_error_invalid'));
            $this->redirect(url('/forgot-password'));
        }

        if (!(new \App\Models\PasswordResetToken())->isValid($email, $token)) {
            Session::flash('auth_error', __('auth.reset_error_expired'));
            $this->redirect(url('/forgot-password'));
        }

        Session::set('password_reset_pending_email', mb_strtolower($email));
        Session::set('password_reset_token', $token);
        Session::set('password_reset_step', 'password');
        $this->redirect(url('/forgot-password'));
    }

    public function resetPassword(): never
    {
        $this->resetForgotPassword();
    }

    private function redirectToRegisterSuccess(string $accountType, string $continueUrl): never
    {
        Session::flash('register_success', [
            'account_type' => $accountType,
            'continue_url' => $continueUrl,
        ]);

        if ($accountType === 'agency') {
            Session::flash('host_success', __('auth.register_agency_success'));
        }

        $this->redirect(url('/register/success'));
    }

    private function flashAuthError(string $message, string $tab, array $oldInput, string $redirect): never
    {
        Session::flash('auth_error', $message);
        Session::flash('auth_tab', $tab);

        foreach ($oldInput as $key => $value) {
            Session::flash('_old_input.' . $key, $value);
        }

        if ($tab === 'register') {
            $accountType = (string) ($oldInput['account_type'] ?? 'client');
            if (!in_array($accountType, ['client', 'agency'], true)) {
                $accountType = 'client';
            }

            $params = array_filter([
                'type' => $accountType,
                'redirect' => $redirect,
            ]);

            $this->redirect(url('/register/form?' . http_build_query($params)));
        }

        $params = array_filter([
            'redirect' => $redirect,
        ]);

        $this->redirect(url('/login?' . http_build_query($params)));
    }

    private function safeRedirect(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || !str_starts_with($path, '/')) {
            return null;
        }

        return url($path);
    }

    /**
     * Après connexion : accueil par défaut. On ignore les redirects vers le profil
     * (ex. lien « Profil » du menu invité) pour arriver sur la page d'accueil.
     */
    private function postLoginPath(string $redirect): string
    {
        $redirect = trim($redirect);
        if ($redirect === '' || !str_starts_with($redirect, '/')) {
            return '/';
        }

        if ($redirect === '/profile' || str_starts_with($redirect, '/profile/')) {
            return '/';
        }

        return $redirect;
    }
}
