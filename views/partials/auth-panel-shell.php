<?php
/**
 * @var 'login'|'register' $authMode
 * @var string             $redirect
 * @var string|null        $authError
 */
$authMode = $authMode ?? 'login';
$redirect = $redirect ?? '';
$isLogin = $authMode !== 'register';
?>
<div class="auth-panel" data-auth-panel-root data-initial-mode="<?= e($authMode) ?>">
    <div class="auth-panel-top">
        <a href="<?= url('/') ?>" class="auth-panel-back" aria-label="<?= e(__('auth.back_home')) ?>">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a href="<?= url('/') ?>" class="auth-panel-brand brand-link" aria-label="<?= e(config('app', 'name')) ?>">
            <?php
            $brandVariant = 'logo';
            $brandClass = 'brand-mark-logo';
            include base_path('views/partials/brand-mark.php');
            ?>
        </a>
        <span class="auth-panel-top-spacer" aria-hidden="true"></span>
    </div>

    <div class="auth-panel-body">
        <nav class="auth-tabs" aria-label="<?= e(__('auth.tabs')) ?>">
            <button type="button"
                    class="auth-tab<?= !$isLogin ? ' is-active' : '' ?>"
                    data-auth-tab="register"
                    aria-selected="<?= !$isLogin ? 'true' : 'false' ?>">
                <?= e(__('auth.sign_up_tab')) ?>
            </button>
            <button type="button"
                    class="auth-tab<?= $isLogin ? ' is-active' : '' ?>"
                    data-auth-tab="login"
                    aria-selected="<?= $isLogin ? 'true' : 'false' ?>">
                <?= e(__('auth.sign_in_tab')) ?>
            </button>
        </nav>

        <?php if (!empty($authError)): ?>
            <div class="auth-alert" role="alert"><?= e($authError) ?></div>
        <?php endif; ?>

        <div class="auth-views">
            <section class="auth-view<?= !$isLogin ? ' is-active' : '' ?>"
                     data-auth-view="register"
                     <?= $isLogin ? 'hidden' : '' ?>
                     aria-labelledby="auth-register-title">
                <h1 class="auth-title" id="auth-register-title"><?= e(__('auth.create_account')) ?></h1>

                <form method="post" action="<?= url('/register') ?>" class="auth-form" id="authRegisterForm">
                    <?= csrf_field() ?>
                    <?php if ($redirect !== ''): ?>
                        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                    <?php endif; ?>

                    <?php
                    $selectedAccountType = (string) old('account_type', 'client');
                    $isAgencyAccount = $selectedAccountType === 'agency';
                    ?>
                    <fieldset class="auth-account-type" data-auth-account-type>
                        <legend class="auth-account-type-label"><?= e(__('auth.account_type')) ?></legend>
                        <div class="auth-account-type-options">
                            <label class="auth-account-type-option">
                                <input type="radio"
                                       name="account_type"
                                       value="client"
                                       <?= !$isAgencyAccount ? 'checked' : '' ?>>
                                <span><?= e(__('auth.account_client')) ?></span>
                            </label>
                            <label class="auth-account-type-option">
                                <input type="radio"
                                       name="account_type"
                                       value="agency"
                                       <?= $isAgencyAccount ? 'checked' : '' ?>>
                                <span><?= e(__('auth.account_agency')) ?></span>
                            </label>
                        </div>
                    </fieldset>

                    <label class="auth-field">
                        <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-person"></i></span>
                        <input type="text"
                               name="full_name"
                               placeholder="<?= e(__('auth.full_name')) ?>"
                               required
                               autocomplete="name"
                               value="<?= e(old('full_name')) ?>">
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                               name="email"
                               placeholder="<?= e(__('auth.email')) ?>"
                               required
                               autocomplete="email"
                               value="<?= e(old('email')) ?>">
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-lock"></i></span>
                        <input type="password"
                               name="password"
                               placeholder="<?= e(__('auth.password')) ?>"
                               required
                               minlength="8"
                               autocomplete="new-password"
                               data-auth-password>
                        <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                    </label>

                    <div class="auth-agency-fields" data-auth-agency-fields <?= !$isAgencyAccount ? 'hidden' : '' ?>>
                        <p class="auth-agency-lead"><?= e(__('auth.agency_lead')) ?></p>

                        <label class="auth-field">
                            <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                            <input type="text"
                                   name="agency_name"
                                   placeholder="<?= e(__('auth.agency_name')) ?>"
                                   autocomplete="organization"
                                   value="<?= e(old('agency_name')) ?>"
                                   data-auth-agency-required>
                        </label>

                        <label class="auth-field auth-field-select">
                            <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-geo-alt"></i></span>
                            <?php
                            $selectedCity = (string) old('agency_city', config('app', 'default_city', 'Ziguinchor'));
                            $fieldName = 'agency_city';
                            $fieldId = 'auth-agency-city';
                            $required = false;
                            $selectAttributes = 'data-auth-agency-required autocomplete="address-level2" aria-label="' . e(__('auth.agency_city')) . '"';
                            include base_path('views/host/partials/city-select.php');
                            ?>
                        </label>

                        <label class="auth-field">
                            <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-telephone"></i></span>
                            <input type="tel"
                                   name="agency_phone"
                                   placeholder="<?= e(__('auth.agency_phone')) ?>"
                                   autocomplete="tel"
                                   value="<?= e(old('agency_phone')) ?>">
                        </label>

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

                            <input type="hidden"
                                   name="agency_address"
                                   value="<?= e(old('agency_address')) ?>"
                                   data-auth-locate-address>
                            <input type="hidden"
                                   name="agency_district"
                                   value="<?= e(old('agency_district')) ?>"
                                   data-auth-locate-district>
                            <input type="hidden"
                                   name="agency_country"
                                   value="<?= e(old('agency_country')) ?>"
                                   data-auth-locate-country>
                            <input type="hidden"
                                   name="agency_gps_accuracy"
                                   value="<?= e(old('agency_gps_accuracy')) ?>"
                                   data-auth-locate-accuracy>
                            <input type="hidden"
                                   name="agency_latitude"
                                   id="authAgencyLatitude"
                                   value="<?= e(old('agency_latitude')) ?>"
                                   data-auth-locate-lat>
                            <input type="hidden"
                                   name="agency_longitude"
                                   id="authAgencyLongitude"
                                   value="<?= e(old('agency_longitude')) ?>"
                                   data-auth-locate-lng>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit"><?= e(__('auth.sign_up_tab')) ?></button>
                </form>

                <p class="auth-social-divider"><span><?= e(__('auth.or_sign_up_with')) ?></span></p>
            </section>

            <section class="auth-view<?= $isLogin ? ' is-active' : '' ?>"
                     data-auth-view="login"
                     <?= !$isLogin ? 'hidden' : '' ?>
                     aria-labelledby="auth-login-title">
                <h1 class="auth-title" id="auth-login-title"><?= e(__('auth.welcome_back')) ?></h1>

                <form method="post" action="<?= url('/login') ?>" class="auth-form" id="authLoginForm">
                    <?= csrf_field() ?>
                    <?php if ($redirect !== ''): ?>
                        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                    <?php endif; ?>

                    <label class="auth-field">
                        <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                               name="email"
                               placeholder="<?= e(__('auth.email')) ?>"
                               required
                               autocomplete="email"
                               value="<?= e(old('email')) ?>">
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-icon" aria-hidden="true"><i class="bi bi-lock"></i></span>
                        <input type="password"
                               name="password"
                               placeholder="<?= e(__('auth.password')) ?>"
                               required
                               autocomplete="current-password"
                               data-auth-password>
                        <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                    </label>

                    <div class="auth-form-row">
                        <label class="auth-remember">
                            <input type="checkbox" name="remember" value="1" id="authRemember">
                            <span><?= e(__('auth.remember_password')) ?></span>
                        </label>
                        <a href="#" class="auth-forgot"><?= e(__('auth.forget_password')) ?></a>
                    </div>

                    <button type="submit" class="auth-submit"><?= e(__('auth.sign_in_tab')) ?></button>
                </form>

                <p class="auth-social-divider"><span><?= e(__('auth.or_sign_in_with')) ?></span></p>
            </section>
        </div>

        <div class="auth-social">
            <button type="button" class="auth-social-btn auth-social-google" data-auth-social="google">
                <span class="auth-social-icon auth-social-icon-google" aria-hidden="true">G</span>
                <span>Google</span>
            </button>
            <button type="button" class="auth-social-btn auth-social-apple" data-auth-social="apple">
                <span class="auth-social-icon" aria-hidden="true"><i class="bi bi-apple"></i></span>
                <span>Apple</span>
            </button>
        </div>
    </div>

    <div class="auth-panel-footer" aria-hidden="true"></div>
</div>
