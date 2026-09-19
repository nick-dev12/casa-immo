<?php
/** @var array<string, mixed> $establishment */
/** @var array<string, mixed>|null $property */
/** @var array<string, mixed>|null $land */
/** @var string $listingKind logement|terrain */
/** @var string $rentalPeriod nightly|monthly */
/** @var array<string, string> $propertyTypes */
/** @var array<string, string> $landTypes */
/** @var array<string, string> $landPaperTypes */
/** @var array<string, list<array<string, mixed>>> $amenitiesByCategory */
/** @var list<int> $selectedAmenityIds */

$isPropertyEdit = $property !== null;
$isLandEdit = $land !== null;
$isEdit = $isPropertyEdit || $isLandEdit;
$listingKind = $isLandEdit ? 'terrain' : ($listingKind ?? 'logement');
$activeNav = $isEdit ? 'properties' : 'new';
$showBack = true;
$pageTitle = $isEdit
    ? ($isLandEdit ? __('host.land_edit') : __('host.property_edit'))
    : __('host.listing_new');
include base_path('views/host/partials/header.php');

$formAction = $isLandEdit
    ? url('/host/lands/' . (int) $land['id'])
    : ($isPropertyEdit
        ? url('/host/properties/' . (int) $property['id'])
        : url('/host/properties'));

$currentType = (string) ($property['type'] ?? 'appartement');
$currentLandType = (string) ($land['land_type'] ?? 'residentiel');
$currentPaperType = (string) ($land['paper_type'] ?? '');
$landPaperTypes = $landPaperTypes ?? localized_types(\App\Models\Land::paperTypes(), 'land_paper_type');
$selectedCity = (string) ($property['city'] ?? $land['city'] ?? $establishment['city'] ?? config('app', 'default_city', 'Ziguinchor'));
$defaultAddress = (string) ($property['address'] ?? $land['address'] ?? '');
$defaultLatitude = (string) ($property['latitude'] ?? $land['latitude'] ?? '');
$defaultLongitude = (string) ($property['longitude'] ?? $land['longitude'] ?? '');
$defaultDistrict = (string) ($property['district'] ?? $land['district'] ?? $establishment['district'] ?? '');
$defaultTitle = (string) ($property['title'] ?? $land['title'] ?? '');
$defaultDescription = (string) ($property['description'] ?? $land['description'] ?? '');

$rentalPeriod = $rentalPeriod ?? 'nightly';
if ($isPropertyEdit && (float) ($property['price_per_month'] ?? 0) > 0 && (float) ($property['price_per_night'] ?? 0) <= 0) {
    $rentalPeriod = 'monthly';
}

$listingPrice = '';
if ($isLandEdit) {
    $listingPrice = (string) ($land['price'] ?? '');
} elseif ($isPropertyEdit) {
    $listingPrice = $rentalPeriod === 'monthly'
        ? (string) ($property['price_per_month'] ?? '')
        : (string) ($property['price_per_night'] ?? '');
}

$selectedAmenityIds = $selectedAmenityIds ?? [];
$existingImages = $existingImages ?? [];
$publishUrl = $isLandEdit
    ? url('/host/lands/' . (int) $land['id'] . '/publish')
    : ($isPropertyEdit ? url('/host/properties/' . (int) $property['id'] . '/publish') : '');
$canPublish = !$isEdit || in_array((string) ($property['status'] ?? $land['status'] ?? 'draft'), ['draft', 'rejected', 'approved'], true);
$publicListingUrl = $isLandEdit
    ? url('/lands/' . (int) $land['id'] . '?city=' . urlencode($selectedCity))
    : ($isPropertyEdit ? url('/properties/' . (int) $property['id'] . '?city=' . urlencode($selectedCity)) : '');
$imageUploadUrl = $isPropertyEdit
    ? url('/host/properties/' . (int) $property['id'] . '/images')
    : ($isLandEdit ? url('/host/lands/' . (int) $land['id'] . '/images') : '');
$videoUploadUrl = $isPropertyEdit
    ? url('/host/properties/' . (int) $property['id'] . '/video')
    : '';
$existingVideoPath = $isPropertyEdit ? ((string) ($property['video_path'] ?? '') ?: null) : null;
?>
<form method="post"
      action="<?= e($formAction) ?>"
      class="host-form host-form-simple"
      data-listing-kind="<?= e($listingKind) ?>"
      data-host-managed="1"
      data-uploads-base="<?= e(rtrim(url(''), '/')) ?>"
      <?= $imageUploadUrl !== '' ? 'data-image-upload-url="' . e($imageUploadUrl) . '"' : '' ?>
      <?= $videoUploadUrl !== '' ? 'data-video-upload-url="' . e($videoUploadUrl) . '"' : '' ?>
      <?= $publishUrl !== '' ? 'data-publish-url="' . e($publishUrl) . '"' : '' ?>
      <?= $publicListingUrl !== '' ? 'data-public-listing-url="' . e($publicListingUrl) . '"' : '' ?>>
    <?= csrf_field() ?>

    <?php if ($isEdit && !empty($existingImages)): ?>
        <?php include base_path('views/host/partials/listing-gallery.php'); ?>
    <?php endif; ?>

    <?php if (!$isEdit): ?>
    <section class="host-panel host-form-section">
        <h2 class="host-section-title"><?= e(__('host.section.listing_kind')) ?></h2>
        <fieldset class="host-chip-field">
            <legend class="visually-hidden"><?= e(__('host.section.listing_kind')) ?></legend>
            <div class="host-chip-grid">
                <label class="host-chip">
                    <input type="radio" name="listing_kind" value="logement"<?= $listingKind === 'logement' ? ' checked' : '' ?>>
                    <span><i class="bi bi-house-door"></i> <?= e(__('host.listing_kind.logement')) ?></span>
                </label>
                <label class="host-chip">
                    <input type="radio" name="listing_kind" value="terrain"<?= $listingKind === 'terrain' ? ' checked' : '' ?>>
                    <span><i class="bi bi-map"></i> <?= e(__('host.listing_kind.terrain')) ?></span>
                </label>
            </div>
        </fieldset>
    </section>
    <?php else: ?>
        <input type="hidden" name="listing_kind" value="<?= e($listingKind) ?>">
    <?php endif; ?>

    <section class="host-panel host-form-section host-basics-section">
        <div class="host-basics-head">
            <h2 class="host-section-title"><?= e(__('host.section.basics')) ?></h2>
            <p class="host-section-hint"><?= e(__('host.section.basics_hint')) ?></p>
        </div>

        <div class="host-basics-stack">
            <label class="host-field host-field-title">
                <span><?= e(__('host.field.title')) ?></span>
                <input type="text"
                       name="title"
                       required
                       autocomplete="off"
                       placeholder="<?= e(__('host.field.title_placeholder')) ?>"
                       value="<?= e($defaultTitle) ?>">
            </label>

            <div class="host-basics-block" data-listing-section="logement"<?= $listingKind === 'terrain' ? ' hidden' : '' ?>>
                <fieldset class="host-chip-field">
                    <legend><?= e(__('plist.property_type')) ?></legend>
                    <div class="host-chip-grid host-type-grid">
                        <?php foreach ($propertyTypes as $value => $label): ?>
                            <label class="host-chip host-chip-tile">
                                <input type="radio" name="type" value="<?= e($value) ?>"<?= $currentType === $value ? ' checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>

            <div class="host-basics-block" data-listing-section="terrain"<?= $listingKind !== 'terrain' ? ' hidden' : '' ?>>
                <fieldset class="host-chip-field">
                    <legend><?= e(__('host.field.land_type')) ?></legend>
                    <div class="host-chip-grid host-type-grid">
                        <?php foreach ($landTypes as $value => $label): ?>
                            <label class="host-chip host-chip-tile">
                                <input type="radio" name="land_type" value="<?= e($value) ?>"<?= $currentLandType === $value ? ' checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="host-chip-field host-land-paper-field">
                    <legend><?= e(__('host.field.land_paper_type')) ?></legend>
                    <p class="host-land-paper-lead"><?= e(__('host.field.land_paper_type_lead')) ?></p>
                    <div class="host-chip-grid host-type-grid host-paper-grid">
                        <?php foreach ($landPaperTypes as $value => $label): ?>
                            <label class="host-chip host-chip-tile host-chip-paper">
                                <input type="radio"
                                       name="paper_type"
                                       value="<?= e($value) ?>"
                                       data-required-when-visible="1"
                                       <?= $currentPaperType === $value ? ' checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="host-land-metrics">
                    <div class="host-land-metrics-head">
                        <span class="host-land-metrics-icon" aria-hidden="true">
                            <i class="bi bi-bounding-box"></i>
                        </span>
                        <div>
                            <h3 class="host-land-metrics-title"><?= e(__('host.field.land_dimensions')) ?></h3>
                            <p class="host-land-metrics-lead"><?= e(__('host.field.land_metrics_lead')) ?></p>
                        </div>
                    </div>

                    <div class="host-land-metrics-grid">
                        <label class="host-land-metric">
                            <span class="host-land-metric-label"><?= e(__('host.field.land_length')) ?></span>
                            <div class="host-input-suffix host-input-suffix-compact">
                                <input type="number"
                                       name="land_length"
                                       id="hostLandLength"
                                       min="0.01"
                                       step="0.01"
                                       data-required-when-visible="1"
                                       placeholder="25"
                                       inputmode="decimal"
                                       value="<?= e((string) ($land['length_m'] ?? '')) ?>"
                                       <?= $listingKind === 'terrain' ? ' required' : '' ?>>
                                <span class="host-input-suffix-unit">m</span>
                            </div>
                        </label>

                        <label class="host-land-metric">
                            <span class="host-land-metric-label"><?= e(__('host.field.land_width')) ?></span>
                            <div class="host-input-suffix host-input-suffix-compact">
                                <input type="number"
                                       name="land_width"
                                       id="hostLandWidth"
                                       min="0.01"
                                       step="0.01"
                                       data-required-when-visible="1"
                                       placeholder="20"
                                       inputmode="decimal"
                                       value="<?= e((string) ($land['width_m'] ?? '')) ?>"
                                       <?= $listingKind === 'terrain' ? ' required' : '' ?>>
                                <span class="host-input-suffix-unit">m</span>
                            </div>
                        </label>

                        <label class="host-land-metric host-land-metric-area">
                            <span class="host-land-metric-label"><?= e(__('host.field.land_area')) ?></span>
                            <div class="host-input-suffix host-input-suffix-compact host-input-suffix-area">
                                <input type="number"
                                       name="land_area"
                                       id="hostLandArea"
                                       min="0.01"
                                       step="0.01"
                                       data-required-when-visible="1"
                                       placeholder="500"
                                       inputmode="decimal"
                                       value="<?= e((string) ($land['area'] ?? '')) ?>"
                                       <?= $listingKind === 'terrain' ? ' required' : '' ?>>
                                <select name="land_area_unit"
                                        id="hostLandAreaUnit"
                                        class="host-input-suffix-unit-select"
                                        aria-label="<?= e(__('host.field.land_area_unit')) ?>">
                                    <?php
                                    $areaUnit = (string) ($land['area_unit'] ?? 'm2');
                                    foreach (['m2' => 'm²', 'ha' => 'ha', 'are' => 'are'] as $unit => $unitLabel):
                                    ?>
                                        <option value="<?= e($unit) ?>"<?= $areaUnit === $unit ? ' selected' : '' ?>><?= e($unitLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </label>
                    </div>

                    <p class="host-land-area-hint"
                       id="hostLandAreaHint"
                       data-template="<?= e(__('host.field.land_area_auto_hint', ['area' => ':area'])) ?>"
                       hidden></p>

                    <?php
                    $planMode = 'live';
                    $planLength = (float) ($land['length_m'] ?? 0);
                    $planWidth = (float) ($land['width_m'] ?? 0);
                    $planArea = (float) ($land['area'] ?? 0);
                    $planUnit = (string) ($land['area_unit'] ?? 'm2');
                    include base_path('views/partials/land-plan-preview.php');
                    ?>
                </div>
            </div>

            <div class="host-price-composer" id="hostPriceComposer">
                <div class="host-price-composer-head" data-listing-section="logement"<?= $listingKind === 'terrain' ? ' hidden' : '' ?>>
                    <span class="host-price-composer-title"><?= e(__('host.field.rental_period')) ?></span>
                    <fieldset class="host-period-segment">
                        <legend class="host-sr-only"><?= e(__('host.field.rental_period')) ?></legend>
                        <label class="host-period-option">
                            <input type="radio" name="rental_period" value="nightly"<?= $rentalPeriod === 'nightly' ? ' checked' : '' ?>>
                            <span><?= e(__('host.rental_period.nightly')) ?></span>
                        </label>
                        <label class="host-period-option">
                            <input type="radio" name="rental_period" value="monthly"<?= $rentalPeriod === 'monthly' ? ' checked' : '' ?>>
                            <span><?= e(__('host.rental_period.monthly')) ?></span>
                        </label>
                    </fieldset>
                </div>

                <div class="host-price-composer-body">
                    <span class="host-price-composer-kicker"
                          id="hostPriceLabel"
                          data-label-night="<?= e(__('host.field.price_night_short')) ?>"
                          data-label-month="<?= e(__('host.field.price_month_short')) ?>"
                          data-label-land="<?= e(__('host.field.land_price_short')) ?>">
                        <?= e($listingKind === 'terrain' ? __('host.field.land_price_short') : ($rentalPeriod === 'monthly' ? __('host.field.price_month_short') : __('host.field.price_night_short'))) ?>
                    </span>
                    <div class="host-price-composer-field">
                        <input type="number"
                               id="hostPrice"
                               name="<?= $listingKind === 'terrain' ? 'land_price' : 'listing_price' ?>"
                               min="0"
                               step="1000"
                               required
                               inputmode="numeric"
                               placeholder="<?= e($listingKind === 'terrain' ? '5000000' : ($rentalPeriod === 'monthly' ? '350000' : '25000')) ?>"
                               value="<?= e($listingPrice) ?>">
                        <span class="host-price-composer-currency">FCFA</span>
                    </div>
                </div>

                <p class="host-price-composer-hint"
                   id="hostPriceHint"
                   data-hint-night="<?= e(__('host.price_hint.nightly')) ?>"
                   data-hint-month="<?= e(__('host.price_hint.monthly')) ?>"
                   data-hint-land="<?= e(__('host.price_hint.land')) ?>">
                    <?= e($listingKind === 'terrain' ? __('host.price_hint.land') : ($rentalPeriod === 'monthly' ? __('host.price_hint.monthly') : __('host.price_hint.nightly'))) ?>
                </p>
            </div>
        </div>
    </section>

    <section class="host-panel host-form-section">
        <h2 class="host-section-title"><?= e(__('host.section.location')) ?></h2>

        <div class="host-form-grid">
            <label class="host-field">
                <span><?= e(__('host.field.city')) ?></span>
                <?php include base_path('views/host/partials/city-select.php'); ?>
            </label>
            <label class="host-field host-field-autocomplete">
                <span><?= e(__('host.field.district')) ?></span>
                <div class="host-autocomplete">
                    <input type="text"
                           name="district"
                           id="hostDistrict"
                           autocomplete="off"
                           spellcheck="false"
                           data-suggest-url="<?= url('/api/search/suggest') ?>"
                           placeholder="<?= e(__('host.field.district_placeholder')) ?>"
                           value="<?= e($defaultDistrict) ?>">
                    <div class="host-suggestions search-suggestions" id="hostDistrictSuggestions" hidden></div>
                </div>
            </label>
        </div>

        <label class="host-field">
            <span><?= e(__('host.field.address')) ?></span>
            <div class="host-address-row">
                <input type="text"
                       name="address"
                       id="hostAddress"
                       placeholder="<?= e(__('host.field.address_placeholder')) ?>"
                       value="<?= e($defaultAddress) ?>">
                <button type="button"
                        class="host-btn host-btn-locate"
                        id="hostLocateBtn"
                        aria-describedby="hostLocateStatus"
                        data-geocode-url="<?= url('/api/geocode/reverse') ?>"
                        data-msg-permission-prompt="<?= e(__('host.geolocate.permission_prompt')) ?>"
                        data-msg-loading-gps="<?= e(__('host.geolocate.loading_gps')) ?>"
                        data-msg-loading-address="<?= e(__('host.geolocate.loading_address')) ?>"
                        data-msg-success="<?= e(__('host.geolocate.success')) ?>"
                        data-msg-denied="<?= e(__('host.geolocate.denied')) ?>"
                        data-msg-unavailable="<?= e(__('host.geolocate.unavailable')) ?>"
                        data-msg-timeout="<?= e(__('host.geolocate.timeout')) ?>"
                        data-msg-failed="<?= e(__('host.geolocate.reverse_failed')) ?>"
                        data-msg-insecure="<?= e(__('host.geolocate.insecure')) ?>"
                        data-msg-auto="<?= e(__('host.geolocate.auto')) ?>">
                    <i class="bi bi-crosshair" aria-hidden="true"></i>
                    <span><?= e(__('host.geolocate.btn')) ?></span>
                </button>
            </div>
            <input type="hidden" name="latitude" id="hostLatitude" value="<?= e($defaultLatitude) ?>">
            <input type="hidden" name="longitude" id="hostLongitude" value="<?= e($defaultLongitude) ?>">
            <p class="host-locate-status" id="hostLocateStatus" hidden aria-live="polite"></p>
        </label>
    </section>

    <section class="host-panel host-form-section" data-listing-section="logement"<?= $listingKind === 'terrain' ? ' hidden' : '' ?>>
        <h2 class="host-section-title"><?= e(__('host.section.capacity')) ?></h2>

        <div class="host-stepper-row">
            <div class="host-stepper">
                <span><?= e(__('host.field.capacity')) ?></span>
                <div class="host-stepper-control">
                    <button type="button" class="host-stepper-btn" data-stepper="capacity" data-dir="-1" aria-label="-">−</button>
                    <input type="number" name="capacity" id="capacity" min="1" max="20" value="<?= (int) ($property['capacity'] ?? 2) ?>" readonly>
                    <button type="button" class="host-stepper-btn" data-stepper="capacity" data-dir="1" aria-label="+">+</button>
                </div>
            </div>
            <div class="host-stepper">
                <span><?= e(__('host.field.bedrooms')) ?></span>
                <div class="host-stepper-control">
                    <button type="button" class="host-stepper-btn" data-stepper="bedrooms" data-dir="-1" aria-label="-">−</button>
                    <input type="number" name="bedrooms" id="bedrooms" min="1" max="10" value="<?= (int) ($property['bedrooms'] ?? 1) ?>" readonly>
                    <button type="button" class="host-stepper-btn" data-stepper="bedrooms" data-dir="1" aria-label="+">+</button>
                </div>
            </div>
            <div class="host-stepper">
                <span><?= e(__('host.field.bathrooms')) ?></span>
                <div class="host-stepper-control">
                    <button type="button" class="host-stepper-btn" data-stepper="bathrooms" data-dir="-1" aria-label="-">−</button>
                    <input type="number" name="bathrooms" id="bathrooms" min="1" max="10" value="<?= (int) ($property['bathrooms'] ?? 1) ?>" readonly>
                    <button type="button" class="host-stepper-btn" data-stepper="bathrooms" data-dir="1" aria-label="+">+</button>
                </div>
            </div>
        </div>
    </section>

    <?php if ($amenitiesByCategory !== []): ?>
    <section class="host-panel host-form-section host-amenities-section" data-listing-section="logement"<?= $listingKind === 'terrain' ? ' hidden' : '' ?>>
        <div class="host-amenities-head">
            <h2 class="host-section-title"><?= e(__('host.section.amenities')) ?></h2>
            <p class="host-section-hint"><?= e(__('host.amenities_hint')) ?></p>
        </div>

        <div class="host-amenities-body">
            <?php foreach ($amenitiesByCategory as $category => $items): ?>
                <div class="host-amenity-group">
                    <h3 class="host-amenity-category"><?= e(amenity_category($category)) ?></h3>
                    <div class="host-amenity-grid" role="group" aria-label="<?= e(amenity_category($category)) ?>">
                        <?php foreach ($items as $amenity): ?>
                            <?php $amenityId = (int) $amenity['id']; ?>
                            <label class="host-amenity-check">
                                <input type="checkbox"
                                       name="amenities[]"
                                       value="<?= $amenityId ?>"
                                       <?= in_array($amenityId, $selectedAmenityIds, true) ? ' checked' : '' ?>>
                                <span class="host-amenity-check-box">
                                    <i class="bi <?= e(amenity_icon((string) $amenity['icon'])) ?>" aria-hidden="true"></i>
                                    <span class="host-amenity-label"><?= e((string) $amenity['name']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php include base_path('views/host/partials/image-upload.php'); ?>

    <?php include base_path('views/host/partials/video-upload.php'); ?>

    <details class="host-panel host-form-section host-details">
        <summary><?= e(__('host.section.optional')) ?></summary>
        <div class="host-details-body">
            <label class="host-field">
                <span><?= e(__('host.field.description')) ?></span>
                <textarea name="description" rows="3" placeholder="<?= e(__('host.field.description_placeholder')) ?>"><?= e($defaultDescription) ?></textarea>
            </label>
        </div>
    </details>

    <div class="host-form-actions host-form-actions-sticky">
        <?php if ($canPublish): ?>
            <?php if ($isEdit && $publishUrl !== ''): ?>
                <button type="submit"
                        formaction="<?= e($publishUrl) ?>"
                        formmethod="post"
                        class="host-btn host-btn-primary host-btn-block"
                        id="hostPublishBtn"
                        data-label-default="<?= e(__('host.publish')) ?>"
                        data-label-loading="<?= e(__('host.publishing')) ?>">
                    <?= e(__('host.publish')) ?>
                </button>
            <?php else: ?>
                <button type="submit"
                        name="intent"
                        value="publish"
                        class="host-btn host-btn-primary host-btn-block"
                        id="hostPublishBtn"
                        data-label-default="<?= e(__('host.publish')) ?>"
                        data-label-loading="<?= e(__('host.publishing')) ?>">
                    <?= e(__('host.publish')) ?>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</form>

<script>
(function () {
    var form = document.querySelector('.host-form-simple[data-host-managed="1"]');
    if (!form) {
        return;
    }

    var fileInput = document.getElementById('hostImagesInput');
    if (fileInput) {
        fileInput.removeAttribute('name');
    }

    form.addEventListener('submit', function (event) {
        if (!window.__hostImagesReady) {
            event.preventDefault();
            event.stopPropagation();
            window.__hostFormPendingSubmit = { submitter: event.submitter };
        }
    }, true);
})();
</script>
<script src="<?= asset('js/host-image-compress.js') ?>" defer></script>
<script src="<?= asset('js/host-images.js') ?>" defer></script>
<script src="<?= asset('js/host-video.js') ?>" defer></script>
<script>
document.querySelectorAll('.host-stepper-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-stepper');
        var input = document.getElementById(id);
        if (!input) return;
        var min = parseInt(input.min || '1', 10);
        var max = parseInt(input.max || '99', 10);
        var next = parseInt(input.value, 10) + parseInt(btn.getAttribute('data-dir'), 10);
        input.value = String(Math.min(max, Math.max(min, next)));
    });
});
</script>
<?php include base_path('views/host/partials/footer.php'); ?>
