<?php
/** @var string $redirect */
/** @var string $accountType client|agency */
$redirect = $redirect ?? '';
$accountType = $accountType === 'agency' ? 'agency' : 'client';
$isAgencyAccount = $accountType === 'agency';
$chooseUrl = url('/register' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
$loginUrl = url('/login' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
$authBackUrl = $chooseUrl;
$authBackLabel = __('auth.back_choose_profile');
$authHeroTitle = $isAgencyAccount ? __('auth.create_agency_hero_title') : __('auth.create_client_hero_title');
$authHeroLead = $isAgencyAccount ? __('auth.agency_lead') : __('auth.register_hero_lead');
$authHeroShowLogo = true;
?>
<div class="auth-screen auth-screen-register" data-auth-panel-root data-initial-mode="register">
    <div class="auth-screen-backdrop" aria-hidden="true"></div>

    <?php include base_path('views/partials/auth-screen-hero.php'); ?>

    <div class="auth-screen-card">
        <?php if (!empty($authError)): ?>
            <div class="auth-alert auth-alert-error" role="alert"><?= e($authError) ?></div>
        <?php endif; ?>

        <p class="auth-register-badge auth-register-badge-<?= e($accountType) ?>">
            <?= e($isAgencyAccount ? __('auth.profile_agency_tag') : __('auth.profile_client_tag')) ?>
        </p>

        <form method="post" action="<?= url('/register') ?>" class="auth-form" id="authRegisterForm">
            <?= csrf_field() ?>
            <input type="hidden" name="account_type" value="<?= e($accountType) ?>">
            <?php if ($redirect !== ''): ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <?php endif; ?>

            <div class="auth-field-stack">
                <label class="auth-label" for="authRegisterName"><?= e(__('auth.full_name')) ?></label>
                <label class="auth-field">
                    <input type="text"
                           id="authRegisterName"
                           name="full_name"
                           placeholder="<?= e(__('auth.name_placeholder')) ?>"
                           required
                           autocomplete="name"
                           value="<?= e(old('full_name')) ?>">
                </label>
            </div>

            <div class="auth-field-stack">
                <label class="auth-label" for="authRegisterEmail"><?= e(__('auth.email')) ?></label>
                <label class="auth-field">
                    <input type="email"
                           id="authRegisterEmail"
                           name="email"
                           placeholder="<?= e(__('auth.email_placeholder')) ?>"
                           required
                           autocomplete="email"
                           value="<?= e(old('email')) ?>">
                </label>
            </div>

            <div class="auth-field-stack">
                <label class="auth-label" for="authRegisterPassword"><?= e(__('auth.password')) ?></label>
                <label class="auth-field">
                    <input type="password"
                           id="authRegisterPassword"
                           name="password"
                           placeholder="••••••••"
                           required
                           minlength="8"
                           autocomplete="new-password"
                           data-auth-password>
                    <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </label>
            </div>

            <?php if ($isAgencyAccount): ?>
                <div class="auth-agency-fields" data-auth-agency-fields>
                    <div class="auth-field-stack">
                        <label class="auth-label" for="authAgencyName"><?= e(__('auth.agency_name')) ?></label>
                        <label class="auth-field">
                            <input type="text"
                                   id="authAgencyName"
                                   name="agency_name"
                                   placeholder="<?= e(__('auth.agency_name')) ?>"
                                   autocomplete="organization"
                                   value="<?= e(old('agency_name')) ?>"
                                   data-auth-agency-required
                                   required>
                        </label>
                    </div>

                    <div class="auth-field-stack">
                        <label class="auth-label" for="auth-agency-city"><?= e(__('auth.agency_city')) ?></label>
                        <label class="auth-field auth-field-select">
                            <?php
                            $selectedCity = (string) old('agency_city', config('app', 'default_city', 'Ziguinchor'));
                            $fieldName = 'agency_city';
                            $fieldId = 'auth-agency-city';
                            $required = true;
                            $selectAttributes = 'data-auth-agency-required autocomplete="address-level2" aria-label="' . e(__('auth.agency_city')) . '" required';
                            include base_path('views/host/partials/city-select.php');
                            ?>
                        </label>
                    </div>

                    <div class="auth-field-stack">
                        <label class="auth-label" for="authAgencyPhone"><?= e(__('auth.agency_phone')) ?></label>
                        <label class="auth-field">
                            <input type="tel"
                                   id="authAgencyPhone"
                                   name="agency_phone"
                                   placeholder="<?= e(__('auth.agency_phone')) ?>"
                                   autocomplete="tel"
                                   value="<?= e(old('agency_phone')) ?>">
                        </label>
                    </div>

                    <div class="auth-geolocate"
                         data-auth-agency-geolocate
                         data-geocode-url="<?= e(url('/api/geocode/reverse')) ?>"
                         data-msg-permission-prompt="<?= e(__('host.geolocate.permission_prompt')) ?>"
                         data-msg-loading-gps="<?= e(__('host.geolocate.loading_gps')) ?>"
                         data-msg-loading-address="<?= e(__('host.geolocate.loading_address')) ?>"
                         data-msg-success="<?= e(__('host.geolocate.success')) ?>"
                         data-msg-denied="<?= e(__('host.geolocate.denied')) ?>"
                         data-msg-unavailable="<?= e(__('host.geolocate.unavailable')) ?>"
                         data-msg-timeout="<?= e(__('host.geolocate.timeout')) ?>"
                         data-msg-failed="<?= e(__('host.geolocate.reverse_failed')) ?>"
                         data-msg-insecure="<?= e(__('host.geolocate.insecure')) ?>"
                         data-msg-pending="<?= e(__('auth.agency_location_pending')) ?>"
                         data-msg-auto="<?= e(__('auth.agency_location_auto')) ?>">
                        <p class="auth-geolocate-label"><?= e(__('auth.agency_location')) ?></p>
                        <p class="auth-geolocate-hint"><?= e(__('auth.agency_location_hint')) ?></p>

                        <p class="auth-geolocate-status"
                           data-auth-locate-status
                           role="alert"
                           aria-live="polite"
                           hidden></p>

                        <div class="auth-geolocate-summary"
                             data-auth-locate-summary
                             <?= old('agency_latitude') !== '' && old('agency_longitude') !== '' ? '' : 'hidden' ?>>
                            <dl class="auth-geolocate-details">
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_full_address')) ?></dt>
                                    <dd data-summary-address><?= e(old('agency_address')) ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_district')) ?></dt>
                                    <dd data-summary-district><?= e(old('agency_district') ?: '—') ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_city')) ?></dt>
                                    <dd data-summary-city><?= e(old('agency_city') ?: '—') ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_country')) ?></dt>
                                    <dd data-summary-country><?= e(old('agency_country') ?: '—') ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_latitude')) ?></dt>
                                    <dd data-summary-lat><?= e(old('agency_latitude')) ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_longitude')) ?></dt>
                                    <dd data-summary-lng><?= e(old('agency_longitude')) ?></dd>
                                </div>
                                <div class="auth-geolocate-detail">
                                    <dt><?= e(__('auth.agency_location_accuracy')) ?></dt>
                                    <dd data-summary-accuracy><?= e(old('agency_gps_accuracy') !== '' ? old('agency_gps_accuracy') . ' m' : '—') ?></dd>
                                </div>
                            </dl>
                        </div>

                        <input type="hidden" name="agency_address" value="<?= e(old('agency_address')) ?>" data-auth-locate-address>
                        <input type="hidden" name="agency_district" value="<?= e(old('agency_district')) ?>" data-auth-locate-district>
                        <input type="hidden" name="agency_country" value="<?= e(old('agency_country')) ?>" data-auth-locate-country>
                        <input type="hidden" name="agency_gps_accuracy" value="<?= e(old('agency_gps_accuracy')) ?>" data-auth-locate-accuracy>
                        <input type="hidden" name="agency_latitude" id="authAgencyLatitude" value="<?= e(old('agency_latitude')) ?>" data-auth-locate-lat>
                        <input type="hidden" name="agency_longitude" id="authAgencyLongitude" value="<?= e(old('agency_longitude')) ?>" data-auth-locate-lng>
                    </div>
                </div>
            <?php endif; ?>

            <label class="auth-terms">
                <input type="checkbox" name="terms" value="1" required>
                <span>
                    <?= e(__('auth.agree_prefix')) ?>
                    <a href="<?= url('/terms') ?>"><?= e(__('auth.terms_link')) ?></a>
                    <?= e(__('auth.and')) ?>
                    <a href="<?= url('/privacy') ?>"><?= e(__('auth.privacy_link')) ?></a>
                </span>
            </label>

            <button type="submit" class="auth-submit auth-submit-<?= e($accountType) ?>">
                <?= e($isAgencyAccount ? __('auth.open_agency_space') : __('auth.sign_up_tab')) ?>
            </button>
        </form>

        <p class="auth-switch-link-wrap">
            <?= e(__('auth.have_account')) ?>
            <a href="<?= e($loginUrl) ?>" class="auth-switch-link"><?= e(__('auth.sign_in_tab')) ?></a>
        </p>
    </div>
</div>
