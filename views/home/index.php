<?php
$adultCount = max(1, (int) ($filters['adults'] ?? $filters['guests'] ?? 2));
$childCount = max(0, (int) ($filters['children'] ?? 0));
$destinationValue = ($filters['q'] ?? '') !== ''
    ? ($filters['q'] ?? '')
    : (
        ($filters['district'] ?? '') !== '' && ($filters['city'] ?? '') !== ''
            ? ($filters['district'] . ', ' . $filters['city'])
            : ($filters['city'] ?? '')
    );

$checkIn = (string) ($filters['check_in'] ?? '');
$checkOut = (string) ($filters['check_out'] ?? '');
$dateFlex = max(0, (int) ($filters['date_flex'] ?? 0));
$heroImage = asset('images/brand/hero-home.jpg');
?>

<header class="booking-site-header booking-site-header--home" id="bookingSiteHeader">
    <div class="booking-site-header-inner container app-container">
        <a href="<?= url('/') ?>" class="booking-logo brand-link" aria-label="<?= e($appName ?? config('app', 'name')) ?>">
            <?php
            $brandVariant = 'logo';
            $brandClass = 'brand-mark-logo';
            include base_path('views/partials/brand-mark.php');
            ?>
        </a>
        <div class="booking-header-end">
            <?php
            $gtranslateClass = 'gt-lang--header gt-lang--compact';
            include base_path('views/partials/gtranslate.php');
            ?>
            <div class="booking-topbar-actions d-none d-lg-flex">
                <a href="<?= url(\App\Helpers\AuthHelper::check() ? '/host' : '/login?redirect=/host') ?>" class="booking-topbar-publish"><?= e(__('nav.add_listing')) ?></a>
                <?php
                $variant = 'desktop';
                include base_path('views/partials/auth-menu.php');
                ?>
            </div>
            <div class="booking-topbar-mobile d-lg-none">
                <button type="button"
                        class="booking-mobile-menu"
                        id="bookingMobileMenuBtn"
                        aria-label="<?= e(__('nav.open_menu')) ?>"
                        aria-expanded="false"
                        aria-controls="bookingMobileMenu">
                    <span class="booking-menu-icon-open booking-menu-grid" aria-hidden="true">
                        <span></span><span></span><span></span>
                        <span></span><span></span><span></span>
                        <span></span><span></span><span></span>
                    </span>
                    <i class="bi bi-x-lg booking-menu-icon-close" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<?php if (!empty($authSuccess)): ?>
<div class="booking-flash booking-flash-success" role="status">
    <div class="container app-container">
        <p><?= e((string) $authSuccess) ?></p>
    </div>
</div>
<?php endif; ?>

<section class="booking-hero" data-is-searching="<?= !empty($isSearching) ? '1' : '0' ?>">
    <div class="booking-hero-media" style="--hero-image: url('<?= e($heroImage) ?>')" aria-hidden="true"></div>
    <div class="booking-hero-inner container app-container">
        <div class="booking-hero-panel">
        <div class="booking-search-stack" data-home-search-stack>
            <div class="booking-search-toggle" role="group" aria-label="<?= e(__('nav.search')) ?>" data-home-search-toggle>
                <button type="button" class="booking-search-toggle-btn search-tab active" data-target="logements">
                    <?= e(__('nav.properties')) ?>
                </button>
                <button type="button" class="booking-search-toggle-btn search-tab" data-target="terrains">
                    <?= e(__('nav.lands')) ?>
                </button>
            </div>

        <!-- Recherche logements -->
        <form action="<?= url('/') ?>" method="get" class="search-form-logements booking-search-form is-active" id="searchFormProperties">
            <input type="hidden" name="city" id="searchCityProperties" value="<?= e($filters['city'] ?? '') ?>">
            <input type="hidden" name="district" id="searchDistrictProperties" value="<?= e($filters['district'] ?? '') ?>">
            <input type="hidden" name="adults" id="adultsInputProperties" value="<?= $adultCount ?>">
            <input type="hidden" name="children" id="childrenInputProperties" value="<?= $childCount ?>">
            <input type="hidden" name="guests" id="guestsInputProperties" value="<?= $adultCount + $childCount ?>">
            <input type="hidden" name="check_in" id="checkInInputProperties" value="<?= e($checkIn) ?>">
            <input type="hidden" name="check_out" id="checkOutInputProperties" value="<?= e($checkOut) ?>">
            <input type="hidden" name="date_flex" id="dateFlexInputProperties" value="<?= $dateFlex ?>">

            <div class="booking-search-box">
                <?php
                $selectedType = (string) ($filters['type'] ?? '');
                $selectedTypeLabel = $selectedType !== '' && isset($propertyTypes[$selectedType])
                    ? $propertyTypes[$selectedType]
                    : __('search.category.all');
                ?>
                <div class="booking-field booking-field-dropdown booking-cat-dropdown" data-cat-dropdown>
                    <input type="hidden" name="type" id="searchTypeProperties" value="<?= e($selectedType) ?>">
                    <button type="button"
                            class="booking-cat-trigger"
                            id="propertyTypeTrigger"
                            aria-expanded="false"
                            aria-haspopup="listbox"
                            aria-controls="propertyTypeMenu"
                            aria-label="<?= e(__('search.category.aria')) ?>">
                        <span class="booking-cat-label" data-cat-label><?= e($selectedTypeLabel) ?></span>
                        <i class="bi bi-caret-down-fill booking-field-caret" aria-hidden="true"></i>
                    </button>
                    <ul class="booking-cat-menu" id="propertyTypeMenu" role="listbox" hidden>
                        <li role="none">
                            <button type="button"
                                    class="booking-cat-option<?= $selectedType === '' ? ' is-selected' : '' ?>"
                                    role="option"
                                    data-value=""
                                    aria-selected="<?= $selectedType === '' ? 'true' : 'false' ?>">
                                <?= e(__('search.category.all')) ?>
                            </button>
                        </li>
                        <?php foreach ($propertyTypes as $key => $label): ?>
                            <li role="none">
                                <button type="button"
                                        class="booking-cat-option<?= $selectedType === $key ? ' is-selected' : '' ?>"
                                        role="option"
                                        data-value="<?= e($key) ?>"
                                        aria-selected="<?= $selectedType === $key ? 'true' : 'false' ?>">
                                    <?= e($label) ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="booking-field booking-field-dest search-bar-dynamic">
                    <i class="bi bi-search booking-field-icon" aria-hidden="true"></i>
                    <input type="search"
                           name="q"
                           id="searchInputProperties"
                           class="search-input-dynamic booking-field-input"
                           value="<?= e($destinationValue) ?>"
                           placeholder="<?= e(__('search.destination.placeholder')) ?>"
                           aria-label="<?= e(__('search.destination.label')) ?>"
                           autocomplete="off"
                           data-suggest-url="<?= url('/api/search/suggest') ?>"
                           data-suggest-type="properties">
                    <div class="search-suggestions" id="suggestionsProperties" hidden></div>
                </div>

                <button type="submit" class="booking-search-btn"><?= e(__('search.submit')) ?></button>
            </div>
        </form>

        <!-- Recherche terrains -->
        <form action="<?= url('/lands') ?>" method="get" class="search-form-terrains booking-search-form" id="searchFormLands" hidden>
            <input type="hidden" name="city" id="searchCityLands" value="">
            <input type="hidden" name="district" id="searchDistrictLands" value="">
            <div class="booking-search-box">
                <label class="booking-field booking-field-dropdown" for="landTypeSelect">
                    <select name="type" id="landTypeSelect" class="booking-field-select booking-field-select--bare" aria-label="<?= e(__('search.lands.type')) ?>">
                        <option value=""><?= e(__('search.lands.type_all')) ?></option>
                        <?php foreach ($landTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-caret-down-fill booking-field-caret" aria-hidden="true"></i>
                </label>

                <div class="booking-field booking-field-dest search-bar-dynamic">
                    <i class="bi bi-search booking-field-icon" aria-hidden="true"></i>
                    <input type="search"
                           name="q"
                           id="searchInputLands"
                           class="search-input-dynamic booking-field-input"
                           placeholder="<?= e(__('search.destination.placeholder')) ?>"
                           aria-label="<?= e(__('search.lands.where')) ?>"
                           autocomplete="off"
                           data-suggest-url="<?= url('/api/search/suggest') ?>"
                           data-suggest-type="lands">
                    <div class="search-suggestions" id="suggestionsLands" hidden></div>
                </div>

                <button type="submit" class="booking-search-btn"><?= e(__('search.submit')) ?></button>
            </div>
        </form>
        </div>
        </div>

        <?php if (empty($isSearching)): ?>
        <ul class="booking-hero-stats" aria-label="<?= e(__('home.stats.aria')) ?>">
            <li>
                <span class="booking-hero-stat-value"><?= (int) ($propertyCount ?? 0) ?></span>
                <span class="booking-hero-stat-label"><?= e(__('nav.properties')) ?></span>
            </li>
            <li>
                <span class="booking-hero-stat-value"><?= (int) ($landCount ?? 0) ?></span>
                <span class="booking-hero-stat-label"><?= e(__('nav.lands')) ?></span>
            </li>
            <li>
                <span class="booking-hero-stat-value"><?= count($cities ?? []) ?></span>
                <span class="booking-hero-stat-label"><?= e(__('nav.explore')) ?></span>
            </li>
        </ul>
        <?php endif; ?>

        <div class="booking-search-backdrop" id="bookingSearchBackdrop" hidden aria-hidden="true"></div>
    </div>
</section>

<?php if (empty($isSearching) && !empty($cityGrid)): ?>
<section class="casamance-cities-section" id="homeCasamanceCities" data-home-cities>
    <div class="container app-container">
        <header class="casamance-cities-head">
            <p class="casamance-cities-eyebrow"><?= e(__('home.hero.kicker')) ?></p>
            <h2 class="casamance-cities-title"><?= e(__('section.cities.title')) ?></h2>
        </header>
        <div class="casamance-cities-grid">
            <?php foreach ($cityGrid as $dest): ?>
                <?php
                $cityName = (string) ($dest['city'] ?? '');
                $hrefLogements = url('/properties?' . http_build_query(['city' => $cityName]));
                $hrefTerrains = url('/lands?' . http_build_query(['city' => $cityName]));
                ?>
                <a href="<?= e($hrefLogements) ?>"
                   class="casamance-city-card"
                   data-href-logements="<?= e($hrefLogements) ?>"
                   data-href-terrains="<?= e($hrefTerrains) ?>">
                    <img src="<?= e($dest['image']) ?>"
                         alt="<?= e($cityName) ?>"
                         loading="lazy"
                         width="400"
                         height="400">
                    <div class="casamance-city-overlay"></div>
                    <div class="casamance-city-label">
                        <span class="casamance-city-name"><?= e($cityName) ?></span>
                        <span class="casamance-city-tagline"><?= e($dest['tagline']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (empty($isSearching)): ?>
<section class="home-vitrine">
    <div class="container app-container">
        <ul class="home-vitrine-trust">
            <li class="home-vitrine-trust-item">
                <span class="home-vitrine-trust-icon" aria-hidden="true"><i class="bi bi-patch-check"></i></span>
                <div>
                    <strong><?= e(__('home.trust.verified')) ?></strong>
                    <p><?= e(__('home.trust.verified_lead')) ?></p>
                </div>
            </li>
            <li class="home-vitrine-trust-item">
                <span class="home-vitrine-trust-icon" aria-hidden="true"><i class="bi bi-geo-alt"></i></span>
                <div>
                    <strong><?= e(__('home.trust.local')) ?></strong>
                    <p><?= e(__('home.trust.local_lead')) ?></p>
                </div>
            </li>
            <li class="home-vitrine-trust-item">
                <span class="home-vitrine-trust-icon" aria-hidden="true"><i class="bi bi-chat-heart"></i></span>
                <div>
                    <strong><?= e(__('home.trust.support')) ?></strong>
                    <p><?= e(__('home.trust.support_lead')) ?></p>
                </div>
            </li>
        </ul>
    </div>
</section>
<?php endif; ?>

<div class="booking-body">
    <div data-search-panel="logements" class="is-active">
    <?php if (!empty($isSearching)): ?>
    <section class="listing-section">
        <div class="container app-container">
            <div class="section-head">
                <h2><?= e(__('section.search_results')) ?></h2>
                <a href="<?= url('/') ?>"><?= e(__('section.clear')) ?></a>
            </div>
            <?php if (empty($recommended)): ?>
                <p class="text-muted">
                    <?php if (($filters['check_in'] ?? '') !== '' && ($filters['check_out'] ?? '') !== ''): ?>
                        <?= e(__('section.no_properties_dates', [
                            'from' => format_date($filters['check_in'], 'd/m/Y'),
                            'to' => format_date($filters['check_out'], 'd/m/Y'),
                            'query' => ($filters['q'] ?? '') !== ''
                                ? __('section.no_properties_query', ['query' => $filters['q']])
                                : '',
                        ])) ?>
                    <?php else: ?>
                        <?= e(__('section.no_properties', [
                            'query' => ($filters['q'] ?? '') !== ''
                                ? __('section.no_properties_query', ['query' => $filters['q']])
                                : '',
                        ])) ?>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <div class="recommended-scroll listing-grid">
                    <?php foreach ($recommended as $item): ?>
                        <?php include base_path('views/partials/property-card.php'); ?>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= url('/properties?' . http_build_query(array_filter($filters))) ?>" class="btn btn-primary btn-sm">
                        <?= e(__('section.view_all_results')) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($isSearching) && !empty($otherProperties)): ?>
    <section class="listing-section">
        <div class="container app-container">
            <div class="section-head">
                <h2><?= e(__('section.other_properties')) ?></h2>
                <a href="<?= url('/properties') ?>"><?= e(__('section.view_all')) ?></a>
            </div>
            <div class="recommended-scroll">
                <?php foreach ($otherProperties as $item): ?>
                    <?php include base_path('views/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php elseif (empty($isSearching)): ?>
    <?php if (!empty($recommended)): ?>
    <section class="listing-section listing-section-featured">
        <div class="container app-container">
            <div class="section-head section-head--featured">
                <h2>
                    <span class="section-head-icon" aria-hidden="true"><i class="bi bi-stars"></i></span>
                    <?= e(__('section.recommended')) ?>
                </h2>
                <a href="<?= url('/properties') ?>"><?= e(__('section.view_all')) ?></a>
            </div>
            <div class="listing-scroll-wrap">
            <div class="recommended-scroll listing-row">
                <?php foreach ($recommended as $item): ?>
                    <?php $cardCompact = true; $cardShowCity = true; include base_path('views/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    $cityIndex = 0;
    foreach ($cityPropertyRows as $cityRow):
        if (empty($cityRow['properties'])) {
            continue;
        }
    ?>
    <section class="listing-section listing-section-city<?= $cityIndex % 2 === 1 ? ' listing-section-city--alt' : '' ?>">
        <div class="container app-container">
            <div class="section-head section-head--city">
                <h2>
                    <span class="section-head-marker" aria-hidden="true"></span>
                    <?= e(__('section.properties_in_city', ['city' => $cityRow['city']])) ?>
                </h2>
                <a href="<?= url('/properties?' . http_build_query(['city' => $cityRow['city']])) ?>"><?= e(__('section.view_all')) ?></a>
            </div>
            <div class="listing-scroll-wrap">
            <div class="recommended-scroll listing-row">
                <?php foreach ($cityRow['properties'] as $item): ?>
                    <?php $cardCompact = true; $cardShowCity = false; include base_path('views/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </section>
    <?php
        $cityIndex++;
    endforeach;
    ?>
    <?php endif; ?>
    </div>

    <?php if (!empty($lands)): ?>
    <div data-search-panel="terrains" hidden>
        <section class="listing-section listing-section-featured pb-5 pb-lg-4">
            <div class="container app-container">
                <div class="section-head">
                    <h2><?= e(__('section.lands_available')) ?></h2>
                    <a href="<?= url('/lands') ?>"><?= e(__('section.view_all')) ?></a>
                </div>
                <div class="recommended-scroll listing-row">
                    <?php foreach ($lands as $item): ?>
                        <?php $cardCompact = true; include base_path('views/partials/land-card.php'); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
    <?php endif; ?>

    <?php if (empty($isSearching)): ?>
    <section class="home-vitrine-cta" aria-labelledby="homeCtaTitle">
        <div class="container app-container">
            <div class="home-vitrine-cta-card">
                <div class="home-vitrine-cta-copy">
                    <h2 id="homeCtaTitle"><?= e(__('home.cta.title')) ?></h2>
                    <p><?= e(__('home.cta.lead')) ?></p>
                </div>
                <div class="home-vitrine-cta-actions">
                    <a href="<?= url('/properties') ?>" class="home-vitrine-btn home-vitrine-btn-primary"><?= e(__('home.cta.explore')) ?></a>
                    <a href="<?= url(\App\Helpers\AuthHelper::check() ? '/host' : '/login?redirect=/host') ?>" class="home-vitrine-btn home-vitrine-btn-outline"><?= e(__('home.cta.list')) ?></a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<div class="booking-mobile-overlay" id="bookingMobileOverlay" hidden></div>
<aside class="booking-mobile-discovery d-lg-none" id="bookingMobileMenu" aria-hidden="true" hidden>
    <header class="booking-mobile-discovery-head">
        <span class="booking-mobile-discovery-spacer" aria-hidden="true"></span>
        <h2 class="booking-mobile-discovery-title"><?= e(__('nav.discovery')) ?></h2>
        <button type="button" class="booking-mobile-discovery-close" id="bookingMobileMenuClose" aria-label="<?= e(__('nav.close_menu')) ?>">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </header>

    <div class="booking-mobile-discovery-body">
        <nav class="booking-mobile-discovery-grid" aria-label="<?= e(__('layout.main_nav')) ?>">
            <button type="button" class="booking-discovery-card search-tab active" data-target="logements">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-house-door"></i></span>
                <span class="booking-discovery-label"><?= e(__('nav.properties')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('nav.listings_count', ['count' => (int) ($propertyCount ?? 0)])) ?></span>
            </button>
            <button type="button" class="booking-discovery-card search-tab" data-target="terrains">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-map"></i></span>
                <span class="booking-discovery-label"><?= e(__('nav.lands')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('nav.listings_count', ['count' => (int) ($landCount ?? 0)])) ?></span>
            </button>
            <a href="<?= url('/properties') ?>" class="booking-discovery-card">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-compass"></i></span>
                <span class="booking-discovery-label"><?= e(__('nav.explore')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('nav.cities_count', ['count' => count($cities ?? [])])) ?></span>
            </a>
            <a href="<?= url(\App\Helpers\AuthHelper::check() ? '/host' : '/login?redirect=/host') ?>" class="booking-discovery-card">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-building-add"></i></span>
                <span class="booking-discovery-label"><?= e(__('nav.add_listing')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('nav.listings_count', ['count' => (int) ($propertyCount ?? 0)])) ?></span>
            </a>
            <a href="<?= url('/help') ?>" class="booking-discovery-card">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-question-circle"></i></span>
                <span class="booking-discovery-label"><?= e(__('nav.help')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('nav.support')) ?></span>
            </a>
            <a href="<?= url('/terms') ?>" class="booking-discovery-card">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-file-text"></i></span>
                <span class="booking-discovery-label"><?= e(__('legal.terms_short')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('legal.kicker')) ?></span>
            </a>
            <a href="<?= url('/privacy') ?>" class="booking-discovery-card">
                <span class="booking-discovery-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <span class="booking-discovery-label"><?= e(__('legal.privacy_short')) ?></span>
                <span class="booking-discovery-meta"><?= e(__('legal.kicker')) ?></span>
            </a>
        </nav>

        <?php if (\App\Helpers\AuthHelper::check()): ?>
            <form method="post" action="<?= url('/logout') ?>" class="booking-mobile-discovery-logout">
                <?= csrf_field() ?>
                <button type="submit" class="booking-mobile-discovery-logout-btn"><?= e(__('profile.logout')) ?></button>
            </form>
        <?php endif; ?>
    </div>
</aside>

<script src="<?= asset('js/booking-mobile.js') ?>" defer></script>
<div hidden
     data-home-search-scripts="<?= e(asset('js/search.js') . '|' . asset('js/booking-datepicker.js')) ?>"></div>
<script src="<?= asset('js/home-search-loader.js') ?>" defer></script>
