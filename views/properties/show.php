<?php
/** @var array<string, mixed> $property */
/** @var array<int, array<string, mixed>> $images */
/** @var array<string, array<int, array<string, mixed>>> $amenitiesByCategory */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<string, mixed>|null $owner */

$type = (string) $property['type'];
$gallery = [];

foreach ($images as $image) {
    $gallery[] = property_image($image['path'] ?? null, $type);
}

if ($gallery === []) {
    $gallery[] = property_image($property['primary_image'] ?? null, $type);
}

$rating = (float) ($property['avg_rating'] ?? 0);
$reviewCount = (int) ($property['review_count'] ?? 0);
$priceNight = (float) ($property['price_per_night'] ?? 0);
$currency = (string) ($property['price_currency'] ?? 'XOF');
$isNightlyFurnished = is_nightly_furnished_property($property);
$city = (string) $property['city'];
$backUrl = url('/properties?' . http_build_query(['city' => $city]));
$propertyUrl = url('/properties/' . (int) $property['id']);
$shareText = translated((string) $property['title']) . ' — ' . location_line($property) . ' · ' . money($priceNight, $currency) . __('common.per_night');
$displayTitle = translated((string) $property['title']);
$mapTitle = $displayTitle;
$displayDescription = translated((string) ($property['description'] ?? ''));
?>

<!-- Galerie hero -->
<section class="pd-hero" aria-label="Photos du logement">
    <div class="pd-hero-nav">
        <a href="<?= e($backUrl) ?>" class="pd-hero-btn" aria-label="Retour">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="pd-hero-nav-right">
            <?php
            $gtranslateClass = 'gt-lang--header gt-lang--on-dark';
            include base_path('views/partials/gtranslate.php');
            ?>
            <button type="button"
                    class="pd-hero-btn js-share-property"
                    aria-label="<?= e(__('common.share')) ?>"
                    data-share-title="<?= e($displayTitle) ?>"
                    data-share-text="<?= e($shareText) ?>"
                    data-share-url="<?= e($propertyUrl) ?>">
                <i class="bi bi-share"></i>
            </button>
            <button type="button"
                    class="pd-hero-btn pd-fav-btn fav-btn"
                    aria-label="<?= e(__('common.add_favorite')) ?>"
                    aria-pressed="false"
                    data-favorite-type="property"
                    data-favorite-id="<?= (int) $property['id'] ?>"
                    data-favorite-label-add="<?= e(__('common.add_favorite')) ?>"
                    data-favorite-label-remove="<?= e(__('common.remove_favorite')) ?>">
                <i class="bi bi-heart"></i>
            </button>
        </div>
    </div>

    <!-- Mobile : image principale + barre miniatures -->
    <?php
    $galleryTotal = count($gallery);
    $mobileThumbVisible = $galleryTotal > 6 ? 5 : $galleryTotal;
    $mobileMoreCount = $galleryTotal > 6 ? $galleryTotal - 5 : 0;
    ?>
    <div class="pd-mobile-gallery" id="propertyMobileGallery">
        <button type="button"
                class="pd-mobile-main"
                id="mobileMainPhoto"
                data-index="0"
                data-src="<?= e($gallery[0]) ?>">
            <img src="<?= e($gallery[0]) ?>"
                 id="mobileMainImage"
                 alt="<?= e($displayTitle) ?>"
                 fetchpriority="high">
        </button>

        <div class="pd-mobile-thumbs-bar">
            <div class="pd-mobile-thumbs" id="mobileThumbs">
                <?php for ($i = 0; $i < $mobileThumbVisible; $i++): ?>
                    <button type="button"
                            class="pd-mobile-thumb gallery-item<?= $i === 0 ? ' active' : '' ?>"
                            data-index="<?= $i ?>"
                            data-src="<?= e($gallery[$i]) ?>"
                            aria-label="Photo <?= $i + 1 ?>">
                        <img src="<?= e($gallery[$i]) ?>" alt=""<?= $i > 0 ? ' loading="lazy"' : '' ?>>
                    </button>
                <?php endfor; ?>
                <?php if ($mobileMoreCount > 0): ?>
                    <button type="button"
                            class="pd-mobile-thumb pd-mobile-thumb-more gallery-item active-more"
                            data-index="5"
                            data-src="<?= e($gallery[5]) ?>"
                            data-more="+<?= $mobileMoreCount ?>"
                            aria-label="Voir les <?= $galleryTotal ?> photos">
                        <img src="<?= e($gallery[5]) ?>" alt="" loading="lazy">
                        <span class="pd-mobile-thumb-overlay">+<?= $mobileMoreCount ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Desktop : grille -->
    <div class="pd-gallery property-gallery" id="propertyGallery">
        <?php foreach (array_slice($gallery, 0, 5) as $index => $src): ?>
            <button type="button"
                    class="gallery-item <?= $index === 0 ? 'gallery-main' : '' ?>"
                    data-index="<?= $index ?>"
                    data-src="<?= e($src) ?>">
                <img src="<?= e($src) ?>"
                     alt="<?= e(__('common.photo')) ?> <?= $index + 1 ?> — <?= e($displayTitle) ?>"
                     <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                <?php if ($index === 4 && count($gallery) > 5): ?>
                    <span class="gallery-more">+<?= count($gallery) - 5 ?> photos</span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
        <?php if (count($gallery) > 5): ?>
            <?php foreach (array_slice($gallery, 5) as $i => $src): ?>
                <button type="button" class="gallery-hidden" data-index="<?= $i + 5 ?>" data-src="<?= e($src) ?>" hidden></button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="pd-gallery-actions">
        <button type="button" class="pd-show-all" id="showAllPhotos">
            <i class="bi bi-grid-3x3-gap"></i>
            <span><?= count($gallery) ?> photos</span>
        </button>
    </div>
</section>

<div class="pd-wrap">
    <!-- En-tête -->
    <header class="pd-header">
        <div class="pd-header-main">
            <span class="pd-type"><?= e(property_type($type)) ?></span>
            <h1 class="pd-title"><?= e($displayTitle) ?></h1>
            <a href="#pd-map" class="pd-location">
                <i class="bi bi-geo-alt-fill"></i>
                <?= e($property['address']) ?> · <?= e(location_line($property)) ?>
            </a>
        </div>
        <?php if ($rating > 0): ?>
            <a href="#pd-reviews" class="pd-score">
                <span class="pd-score-badge"><?= e(rating_display($rating)) ?></span>
                <span class="pd-score-text">
                    <strong><?= e(rating_label($rating)) ?></strong>
                    <?= $reviewCount ?> avis
                </span>
            </a>
        <?php endif; ?>
    </header>

    <!-- Stats rapides -->
    <div class="pd-stats">
        <div class="pd-stat">
            <i class="bi bi-door-closed"></i>
            <span><strong><?= (int) $property['bedrooms'] ?></strong> chambre<?= (int) $property['bedrooms'] > 1 ? 's' : '' ?></span>
        </div>
        <div class="pd-stat">
            <i class="bi bi-droplet"></i>
            <span><strong><?= (int) $property['bathrooms'] ?></strong> sdb</span>
        </div>
        <div class="pd-stat">
            <i class="bi bi-people"></i>
            <span><strong><?= (int) $property['capacity'] ?></strong> voyageurs</span>
        </div>
        <?php if (!empty($property['area_sqm'])): ?>
            <div class="pd-stat">
                <i class="bi bi-aspect-ratio"></i>
                <span><strong><?= e(number_format((float) $property['area_sqm'], 0, ',', ' ')) ?></strong> m²</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="pd-layout">
        <div class="pd-content">

            <!-- Vidéo -->
            <?php if (!empty($property['video_path'])): ?>
                <section class="pd-block pd-video-block">
                    <h2><?= e(__('property.video.title')) ?></h2>
                    <div class="pd-video-wrap">
                        <video class="pd-video-player"
                               controls
                               playsinline
                               preload="metadata"
                               poster="<?= e($gallery[0] ?? property_image(null, $type)) ?>">
                            <source src="<?= e(upload_url((string) $property['video_path'])) ?>" type="video/mp4">
                        </video>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Description -->
            <section class="pd-block">
                <h2>Description</h2>
                <div class="pd-description" id="propertyDescription">
                    <?= nl2br(e($displayDescription)) ?>
                </div>
            </section>

            <!-- Équipements -->
            <?php if ($amenitiesByCategory !== []): ?>
                <?php $amenityTotal = count($amenities); ?>
                <section class="pd-block pd-amenities-wrap">
                    <h2>Ce que propose ce logement</h2>
                    <div class="pd-amenities-grid">
                        <?php
                        $visibleAmenities = array_slice($amenities, 0, 8);
                        foreach ($visibleAmenities as $amenity):
                        ?>
                            <div class="pd-amenity">
                                <i class="bi <?= e(amenity_icon((string) $amenity['icon'])) ?>"></i>
                                <?= e($amenity['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($amenityTotal > count($visibleAmenities)): ?>
                        <button type="button" class="pd-amenities-more" id="openAmenitiesPanel">
                            Voir les <?= $amenityTotal ?> équipements
                        </button>
                    <?php endif; ?>

                    <div class="pd-amenities-panel" id="amenitiesPanel" hidden aria-hidden="true">
                        <div class="pd-amenities-panel-backdrop" id="amenitiesPanelBackdrop"></div>
                        <div class="pd-amenities-panel-box" role="dialog" aria-labelledby="amenitiesPanelTitle">
                            <div class="pd-amenities-panel-head">
                                <h3 id="amenitiesPanelTitle"><?= $amenityTotal ?> équipements</h3>
                                <button type="button" class="pd-amenities-panel-close" id="closeAmenitiesPanel" aria-label="Fermer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="pd-amenities-panel-body">
                                <?php foreach ($amenitiesByCategory as $category => $items): ?>
                                    <div class="pd-amenity-group">
                                        <h4><?= e(amenity_category($category)) ?></h4>
                                        <ul>
                                            <?php foreach ($items as $amenity): ?>
                                                <li>
                                                    <i class="bi <?= e(amenity_icon((string) $amenity['icon'])) ?>"></i>
                                                    <?= e($amenity['name']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Règles -->
            <?php if (!empty($property['rules'])): ?>
                <section class="pd-block">
                    <h2>Règles de la maison</h2>
                    <ul class="pd-rules">
                        <?php foreach (preg_split('/\.\s+/', (string) $property['rules']) as $rule): ?>
                            <?php if (trim($rule) !== ''): ?>
                                <li><i class="bi bi-dot"></i><?= e(trim($rule, '. ')) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <!-- Contact site ou hôte -->
            <?php
            $siteContactName = (string) config('app', 'invoice.company_name', config('app', 'name', 'Casa Immo'));
            $siteContactEmail = trim((string) config('app', 'invoice.email', ''));
            $siteContactPhone = trim((string) config('app', 'invoice.phone', ''));
            ?>
            <?php if (!empty($canViewHostContact) && $owner !== null): ?>
                <section class="pd-block pd-host">
                    <h2><?= e(__('property.host.title')) ?></h2>
                    <div class="pd-host-card">
                        <div class="pd-host-avatar">
                            <?= e(mb_strtoupper(mb_substr((string) $owner['first_name'], 0, 1) . mb_substr((string) $owner['last_name'], 0, 1))) ?>
                        </div>
                        <div class="pd-host-info">
                            <strong><?= e($owner['first_name'] . ' ' . $owner['last_name']) ?></strong>
                            <span><?= e(__('property.host.verified')) ?></span>
                        </div>
                        <div class="pd-host-actions">
                            <?php
                            $ownerId = (int) ($owner['id'] ?? 0);
                            $isOwnListing = \App\Helpers\AuthHelper::id() !== null && \App\Helpers\AuthHelper::id() === $ownerId;
                            ?>
                            <?php if (!$isOwnListing): ?>
                                <a href="<?= e(messages_start_url((int) $property['id'])) ?>" class="pd-btn-outline">
                                    <i class="bi bi-chat-dots"></i> <?= e(__('messages.contact')) ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($owner['phone'])): ?>
                                <a href="tel:<?= e($owner['phone']) ?>" class="pd-btn-primary"><i class="bi bi-telephone"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php elseif ($siteContactEmail !== '' || $siteContactPhone !== ''): ?>
                <section class="pd-block pd-support">
                    <h2><?= e(__('property.support.title')) ?></h2>
                    <p class="pd-support-lead"><?= e(__('property.support.lead')) ?></p>
                    <div class="pd-host-card pd-support-card">
                        <div class="pd-host-avatar pd-support-avatar" aria-hidden="true">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="pd-host-info">
                            <strong><?= e($siteContactName) ?></strong>
                            <span><?= e(__('property.support.meta')) ?></span>
                        </div>
                        <div class="pd-host-actions">
                            <?php if ($siteContactEmail !== ''): ?>
                                <a href="mailto:<?= e($siteContactEmail) ?>" class="pd-btn-outline">
                                    <i class="bi bi-envelope"></i> <?= e(__('property.support.email')) ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($siteContactPhone !== ''): ?>
                                <a href="tel:<?= e(preg_replace('/\s+/', '', $siteContactPhone)) ?>" class="pd-btn-primary">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Avis -->
            <?php if ($reviews !== []): ?>
                <?php
                $visibleReviews = array_slice($reviews, 0, 3);
                $hiddenReviewCount = max(0, count($reviews) - 3);
                ?>
                <section class="pd-block pd-reviews-wrap" id="pd-reviews">
                    <div class="pd-block-head">
                        <h2>Avis · <?= e(rating_display($rating)) ?> <i class="bi bi-star-fill"></i></h2>
                        <span><?= $reviewCount ?> commentaire<?= $reviewCount > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="pd-reviews pd-reviews-visible">
                        <?php foreach ($visibleReviews as $review): ?>
                            <?php include base_path('views/partials/review-card.php'); ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($hiddenReviewCount > 0): ?>
                        <button type="button" class="pd-reviews-more" id="openReviewsPanel">
                            Voir plus (<?= $hiddenReviewCount ?> avis)
                        </button>
                    <?php endif; ?>

                    <div class="pd-reviews-panel" id="reviewsPanel" hidden aria-hidden="true">
                        <div class="pd-reviews-panel-backdrop" id="reviewsPanelBackdrop"></div>
                        <div class="pd-reviews-panel-box" role="dialog" aria-labelledby="reviewsPanelTitle">
                            <div class="pd-reviews-panel-head">
                                <h3 id="reviewsPanelTitle">
                                    <?= $reviewCount ?> commentaire<?= $reviewCount > 1 ? 's' : '' ?>
                                    · <?= e(rating_display($rating)) ?> <i class="bi bi-star-fill"></i>
                                </h3>
                                <button type="button" class="pd-reviews-panel-close" id="closeReviewsPanel" aria-label="Fermer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="pd-reviews-panel-list">
                                <?php foreach ($reviews as $review): ?>
                                    <?php include base_path('views/partials/review-card.php'); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Carte -->
            <?php
            $listing = $property;
            include base_path('views/partials/listing-map.php');
            ?>
        </div>

        <!-- Réservation desktop -->
        <?php if ($isNightlyFurnished): ?>
        <aside class="pd-booking" id="bookingWidget">
            <div class="pd-booking-card">
                <div class="pd-booking-price">
                    <strong><?= e(money($priceNight, $currency)) ?></strong>
                    <span>/ nuit</span>
                    <?php if ($rating > 0): ?>
                        <div class="pd-booking-rating">
                            <i class="bi bi-star-fill"></i> <?= e(rating_display($rating)) ?>
                            <span>(<?= $reviewCount ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php include base_path('views/partials/booking-form.php'); ?>

                <?php if (!empty($property['price_per_week']) || !empty($property['price_per_month'])): ?>
                    <div class="pd-booking-alt">
                        <?php if (!empty($property['price_per_week'])): ?>
                            <div class="pd-booking-alt-row">
                                <span>Semaine</span>
                                <strong><?= e(money($property['price_per_week'], $currency)) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property['price_per_month'])): ?>
                            <div class="pd-booking-alt-row">
                                <span>Mois</span>
                                <strong><?= e(money($property['price_per_month'], $currency)) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
        <?php else: ?>
        <aside class="pd-booking" id="bookingWidget">
            <div class="pd-booking-card pd-booking-card-info">
                <?php if (!empty($property['price_per_month'])): ?>
                    <div class="pd-booking-price">
                        <strong><?= e(money($property['price_per_month'], $currency)) ?></strong>
                        <span>/ mois</span>
                    </div>
                <?php elseif (!empty($property['price_per_week'])): ?>
                    <div class="pd-booking-price">
                        <strong><?= e(money($property['price_per_week'], $currency)) ?></strong>
                        <span>/ semaine</span>
                    </div>
                <?php endif; ?>
                <p class="pd-booking-info-text"><?= e(__('booking.not_nightly_furnished_lead')) ?></p>
            </div>
        </aside>
        <?php endif; ?>
    </div>

    <?php if (!empty($similarProperties)): ?>
    <section class="pd-similar">
        <header class="pd-similar-head">
            <h2>Logements similaires à <?= e($city) ?></h2>
            <a href="<?= url('/properties?' . http_build_query(['city' => $city])) ?>">Voir tout</a>
        </header>
        <div class="pd-similar-grid">
            <?php foreach ($similarProperties as $item): ?>
                <?php $cardCompact = true; $cardShowCity = false; include base_path('views/partials/property-card.php'); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Barre mobile -->
<?php if ($isNightlyFurnished): ?>
<div class="pd-mobile-bar d-lg-none">
    <div class="pd-mobile-bar-inner">
        <div class="pd-mobile-price">
            <strong><?= e(money($priceNight, $currency)) ?></strong>
            <span>/ nuit</span>
        </div>
        <button type="button" class="pd-btn-book" id="openBookingSheet">Réserver</button>
    </div>
</div>

<!-- Sheet réservation mobile -->
<div class="pd-sheet" id="bookingSheet" hidden aria-hidden="true">
    <div class="pd-sheet-backdrop" id="bookingSheetBackdrop"></div>
    <div class="pd-sheet-panel" role="dialog" aria-labelledby="bookingSheetTitle">
        <div class="pd-sheet-handle"></div>
        <div class="pd-sheet-head">
            <h3 id="bookingSheetTitle">Réserver</h3>
            <button type="button" class="pd-sheet-close" id="closeBookingSheet" aria-label="Fermer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="pd-sheet-body">
            <div class="pd-booking-price pd-booking-price-sheet">
                <strong><?= e(money($priceNight, $currency)) ?></strong>
                <span>/ nuit</span>
            </div>
            <?php include base_path('views/partials/booking-form.php'); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Partager le logement -->
<div class="pd-share-sheet" id="shareSheet" hidden aria-hidden="true">
    <div class="pd-share-backdrop" id="shareSheetBackdrop"></div>
    <div class="pd-share-panel" role="dialog" aria-labelledby="shareSheetTitle">
        <div class="pd-share-handle"></div>
        <div class="pd-share-head">
            <h3 id="shareSheetTitle">Partager ce logement</h3>
            <button type="button" class="pd-share-close" id="closeShareSheet" aria-label="Fermer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <p class="pd-share-preview"><?= e($displayTitle) ?></p>
        <div class="pd-share-options">
            <a href="https://wa.me/?text=<?= rawurlencode($shareText . ' ' . $propertyUrl) ?>"
               class="pd-share-option"
               target="_blank"
               rel="noopener noreferrer">
                <span class="pd-share-icon pd-share-icon-wa"><i class="bi bi-whatsapp"></i></span>
                WhatsApp
            </a>
            <a href="mailto:?subject=<?= rawurlencode($displayTitle) ?>&body=<?= rawurlencode($shareText . "\n\n" . $propertyUrl) ?>"
               class="pd-share-option">
                <span class="pd-share-icon pd-share-icon-mail"><i class="bi bi-envelope"></i></span>
                E-mail
            </a>
            <button type="button" class="pd-share-option" id="copyPropertyLink">
                <span class="pd-share-icon pd-share-icon-copy"><i class="bi bi-link-45deg"></i></span>
                Copier le lien
            </button>
            <button type="button" class="pd-share-option" id="nativeShareBtn">
                <span class="pd-share-icon pd-share-icon-more"><i class="bi bi-share"></i></span>
                Plus d'options
            </button>
        </div>
        <div class="pd-share-url">
            <input type="text" id="shareUrlInput" value="<?= e($propertyUrl) ?>" readonly aria-label="Lien du logement">
            <button type="button" class="pd-share-copy-sm" id="copyPropertyLinkSm" aria-label="Copier">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
        <p class="pd-share-feedback" id="shareFeedback" hidden>Lien copié !</p>
    </div>
</div>

<!-- Modal galerie -->
<div class="gallery-modal" id="galleryModal" hidden>
    <div class="gallery-modal-backdrop" data-close-modal></div>
    <div class="gallery-modal-content">
        <button type="button" class="gallery-modal-close" data-close-modal aria-label="Fermer">
            <i class="bi bi-x-lg"></i>
        </button>
        <img src="" alt="Galerie photo" id="galleryModalImage">
        <div class="gallery-modal-nav">
            <button type="button" id="galleryPrev" aria-label="Photo précédente"><i class="bi bi-chevron-left"></i></button>
            <span id="galleryCounter">1 / <?= count($gallery) ?></span>
            <button type="button" id="galleryNext" aria-label="Photo suivante"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
    window.propertyGallery = <?= json_encode(array_values($gallery), JSON_UNESCAPED_UNICODE) ?>;
    window.propertyShare = <?= json_encode([
        'title' => (string) $property['title'],
        'text' => $shareText,
        'url' => $propertyUrl,
    ], JSON_UNESCAPED_UNICODE) ?>;
</script>
