<?php
/** @var array<string, mixed> $booking */
/** @var array<int, array<string, mixed>> $propertyImages */

$statusLabels = [
    'pending' => __('reservations.status.pending'),
    'confirmed' => __('reservations.status.confirmed'),
    'cancelled' => __('reservations.status.cancelled'),
    'completed' => __('reservations.status.completed'),
    'rejected' => __('reservations.status.rejected'),
];

$statusClasses = [
    'pending' => 'reservation-status-pending',
    'confirmed' => 'reservation-status-confirmed',
    'cancelled' => 'reservation-status-cancelled',
    'completed' => 'reservation-status-completed',
    'rejected' => 'reservation-status-rejected',
];

$propertyId = (int) $booking['property_id'];
$bookingId = (int) $booking['id'];
$propertyUrl = url('/properties/' . $propertyId . '?city=' . urlencode((string) ($booking['city'] ?? '')));
$title = translated((string) ($booking['title'] ?? ''));
$establishmentName = trim((string) ($booking['establishment_name'] ?? ''));
$displayTitle = $establishmentName !== '' ? $establishmentName . ', ' . $title : $title;
$propertyType = (string) ($booking['type'] ?? 'autre');
$gallery = [];

foreach ($propertyImages ?? [] as $image) {
    $path = trim((string) ($image['path'] ?? ''));
    if ($path !== '') {
        $gallery[] = property_image($path, $propertyType);
    }
}

if ($gallery === []) {
    $gallery[] = property_image($booking['primary_image'] ?? null, $propertyType);
}

$addressLine = trim((string) ($booking['establishment_address'] ?? ''));
if ($addressLine === '') {
    $addressLine = trim((string) ($booking['address'] ?? ''));
}
$district = trim((string) ($booking['establishment_district'] ?? ''));
if ($district === '') {
    $district = trim((string) ($booking['district'] ?? ''));
}
$city = trim((string) ($booking['establishment_city'] ?? ''));
if ($city === '') {
    $city = trim((string) ($booking['city'] ?? ''));
}
$country = trim((string) ($booking['country'] ?? 'Sénégal'));

$addressParts = array_filter([$addressLine, $district, $city, $country]);
$fullAddress = implode(', ', $addressParts);

$latitude = $booking['establishment_latitude'] ?? $booking['latitude'] ?? null;
$longitude = $booking['establishment_longitude'] ?? $booking['longitude'] ?? null;
$coordsLabel = is_numeric($latitude) && is_numeric($longitude)
    ? number_format((float) $latitude, 8, '.', '') . ', ' . number_format((float) $longitude, 8, '.', '')
    : '';

$mapsUrl = maps_directions_url($booking);

$checkInHint = trim(strip_tags((string) ($booking['description'] ?? '')));
if ($checkInHint === '') {
    $checkInHint = trim(strip_tags((string) ($booking['rules'] ?? '')));
}
if ($checkInHint === '') {
    $checkInHint = __('reservations.detail.checkin_default');
}

$rulesText = trim((string) ($booking['rules'] ?? ''));
$hasRules = $rulesText !== '';
$status = (string) ($booking['status'] ?? 'pending');
$isCancelled = in_array($status, ['cancelled', 'rejected'], true);
$detailOptions = booking_detail_options($booking);
$safetyOptions = booking_detail_safety_options($booking);
$reservationTab = booking_reservation_tab($booking);

$phone = trim((string) ($booking['establishment_phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/\s+/', '', $phone) : '';
$bookingReference = booking_reference($bookingId);
$nights = (int) ($booking['nights'] ?? 0);
$guests = (int) ($booking['guests'] ?? 0);
$totalAmount = money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'));
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
?>
<section class="account-page reservation-detail-page" data-reservation-detail-id="<?= (int) ($booking['id'] ?? 0) ?>">
    <div class="reservation-detail-shell">
        <header class="reservation-detail-nav">
            <a href="<?= url('/reservations') ?>" class="reservation-detail-back">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                <?= e(__('reservations.detail.back')) ?>
            </a>
            <span class="reservation-detail-brand"><?= e(config('app', 'name')) ?></span>
            <a href="<?= url('/help') ?>" class="reservation-detail-help" aria-label="<?= e(__('reservations.detail.help')) ?>">
                <i class="bi bi-question-circle" aria-hidden="true"></i>
            </a>
        </header>

        <?php include base_path('views/partials/reservation-detail-gallery.php'); ?>

        <?php if ($reservationSuccess = flash('reservation_success')): ?>
            <div class="reservation-detail-alert reservation-detail-alert-success" role="status">
                <?= e((string) $reservationSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($reservationError = flash('reservation_error')): ?>
            <div class="reservation-detail-alert reservation-detail-alert-error" role="alert">
                <?= e((string) $reservationError) ?>
            </div>
        <?php endif; ?>

        <?php if ($isCancelled): ?>
            <div class="reservation-detail-alert reservation-detail-alert-cancelled" role="status">
                <?= e(__('reservations.cancelled_banner')) ?>
            </div>
        <?php endif; ?>

        <button type="button"
                class="reservation-detail-ticket-btn"
                id="reservation-ticket-toggle"
                aria-expanded="false"
                aria-controls="reservation-ticket-panel">
            <?= e(__('reservations.detail.view_ticket')) ?>
        </button>

        <div class="reservation-detail-ticket-panel"
             id="reservation-ticket-panel"
             hidden>
            <?php include base_path('views/partials/reservation-ticket.php'); ?>
        </div>

        <a href="<?= e($propertyUrl) ?>" class="reservation-detail-title"><?= e($displayTitle) ?></a>

        <div class="reservation-detail-rows">
            <article class="reservation-detail-row">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="reservation-detail-content">
                    <p class="reservation-detail-row-title">
                        <?= e(booking_stay_range_label((string) $booking['check_in'], (string) $booking['check_out'])) ?>
                    </p>
                    <p class="reservation-detail-row-text">
                        <?= e(__('reservations.detail.checkin_time', ['time' => '14:00'])) ?>
                    </p>
                    <p class="reservation-detail-row-text">
                        <?= e(__('reservations.detail.checkout_time', ['time' => '11:00'])) ?>
                    </p>
                </div>
            </article>

            <article class="reservation-detail-row">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-key"></i>
                </div>
                <div class="reservation-detail-content">
                    <h2 class="reservation-detail-row-heading"><?= e(__('reservations.detail.checkin_title')) ?></h2>
                    <p class="reservation-detail-row-text reservation-detail-rules-full"><?= nl2br(e($checkInHint)) ?></p>
                    <a href="<?= e($propertyUrl) ?>" class="reservation-detail-link">
                        <?= e(__('reservations.detail.checkin_more')) ?>
                    </a>
                </div>
            </article>

            <article class="reservation-detail-row">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="reservation-detail-content">
                    <h2 class="reservation-detail-row-heading"><?= e(__('reservations.detail.address_title')) ?></h2>
                    <?php if ($establishmentName !== ''): ?>
                        <p class="reservation-detail-row-text reservation-detail-row-strong"><?= e($establishmentName) ?></p>
                    <?php endif; ?>
                    <p class="reservation-detail-row-text"><?= e($fullAddress) ?></p>
                    <?php if ($coordsLabel !== ''): ?>
                        <p class="reservation-detail-row-text reservation-detail-coords">
                            <?= e(__('reservations.detail.coordinates')) ?> : <?= e($coordsLabel) ?>
                        </p>
                    <?php endif; ?>
                    <a href="<?= e($mapsUrl) ?>"
                       class="reservation-detail-link"
                       target="_blank"
                       rel="noopener noreferrer">
                        <?= e(__('reservations.detail.directions')) ?>
                    </a>
                </div>
            </article>

            <article class="reservation-detail-row">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="reservation-detail-content">
                    <h2 class="reservation-detail-row-heading"><?= e(__('reservations.detail.rules_title')) ?></h2>
                    <?php if ($hasRules): ?>
                        <p class="reservation-detail-row-text reservation-detail-rules-full"><?= nl2br(e($rulesText)) ?></p>
                    <?php else: ?>
                        <p class="reservation-detail-row-text"><?= e(__('reservations.detail.rules_empty')) ?></p>
                    <?php endif; ?>
                    <a href="<?= e($propertyUrl) ?>" class="reservation-detail-link">
                        <?= e(__('reservations.detail.rules_all')) ?>
                    </a>
                </div>
            </article>

            <article class="reservation-detail-row">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="reservation-detail-content">
                    <h2 class="reservation-detail-row-heading"><?= e(__('reservations.detail.booking_info_title')) ?></h2>
                    <dl class="reservation-detail-info-list">
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.reference')) ?></dt>
                            <dd><?= e($bookingReference) ?></dd>
                        </div>
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.status')) ?></dt>
                            <dd><?= e($statusLabel) ?></dd>
                        </div>
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.guests')) ?></dt>
                            <dd><?= $guests ?></dd>
                        </div>
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.nights')) ?></dt>
                            <dd><?= $nights ?></dd>
                        </div>
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.total')) ?></dt>
                            <dd><?= e($totalAmount) ?></dd>
                        </div>
                        <div class="reservation-detail-info-item">
                            <dt><?= e(__('reservations.detail.booked_at')) ?></dt>
                            <dd><?= e(format_date((string) ($booking['created_at'] ?? ''), 'datetime')) ?></dd>
                        </div>
                    </dl>
                </div>
            </article>
        </div>

        <hr class="reservation-detail-divider">

        <section class="reservation-detail-contact">
            <h2 class="reservation-detail-section-title"><?= e(__('reservations.detail.contact_title')) ?></h2>
            <p class="reservation-detail-section-lead"><?= e(__('reservations.detail.contact_lead')) ?></p>

            <article class="reservation-detail-row reservation-detail-row-compact">
                <div class="reservation-detail-icon" aria-hidden="true">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="reservation-detail-content">
                    <a href="<?= e(messages_start_url($propertyId)) ?>" class="reservation-detail-link">
                        <?= e(__('messages.contact')) ?>
                    </a>
                </div>
            </article>

            <?php if ($phone !== ''): ?>
                <h3 class="reservation-detail-subtitle"><?= e(__('reservations.detail.other_methods')) ?></h3>
                <article class="reservation-detail-row reservation-detail-row-compact">
                    <div class="reservation-detail-icon" aria-hidden="true">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div class="reservation-detail-content">
                        <a href="<?= e($phoneHref) ?>" class="reservation-detail-link">
                            <?= e(__('reservations.detail.call', ['phone' => $phone])) ?>
                        </a>
                    </div>
                </article>
            <?php endif; ?>
        </section>

        <hr class="reservation-detail-divider">

        <section class="reservation-detail-options"
                 data-reservation-tab="<?= e($reservationTab) ?>"
                 data-reservation-status="<?= e($status) ?>">
            <h2 class="reservation-detail-section-title"><?= e(__('reservations.detail.options_title')) ?></h2>
            <?php
            $options = $detailOptions;
            include base_path('views/partials/reservation-detail-option-list.php');
            ?>
        </section>

        <section class="reservation-detail-safety"
                 data-reservation-tab="<?= e($reservationTab) ?>">
            <h2 class="reservation-detail-section-title"><?= e(__('reservations.detail.safety_title')) ?></h2>
            <?php
            $options = $safetyOptions;
            include base_path('views/partials/reservation-detail-option-list.php');
            ?>
        </section>

        <?php if (!empty($canReview)): ?>
            <a href="<?= url('/reservations/' . $bookingId . '/review') ?>" class="reservation-detail-cta reservation-detail-cta-review">
                <?= e(__('reviews.cta.rate')) ?>
            </a>
        <?php elseif (!empty($hasReview)): ?>
            <p class="reservation-detail-review-done" role="status">
                <?= e(__('reviews.thanks')) ?>
            </p>
        <?php endif; ?>

        <?php if (!$isCancelled): ?>
            <a href="<?= e(messages_start_url($propertyId)) ?>" class="reservation-detail-cta">
                <?= e(__('reservations.detail.contact_cta')) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    var toggle = document.getElementById('reservation-ticket-toggle');
    var panel = document.getElementById('reservation-ticket-panel');

    function openTicketPanel() {
        if (!panel || !toggle) {
            return;
        }
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (toggle && panel) {
        toggle.addEventListener('click', function () {
            var open = panel.hidden;
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

    document.querySelectorAll('[data-action="ticket"]').forEach(function (button) {
        button.addEventListener('click', openTicketPanel);
    });

    var galleryMainImage = document.getElementById('reservationGalleryMainImage');
    var galleryCount = document.querySelector('.reservation-detail-gallery-count');
    var galleryThumbs = document.querySelectorAll('.reservation-detail-gallery-thumb');

    galleryThumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var src = thumb.getAttribute('data-src') || '';
            var index = thumb.getAttribute('data-index') || '0';
            if (!galleryMainImage || src === '') {
                return;
            }
            galleryMainImage.src = src;
            galleryThumbs.forEach(function (item) {
                item.classList.toggle('is-active', item === thumb);
            });
            if (galleryCount) {
                galleryCount.textContent = (parseInt(index, 10) + 1) + ' / ' + galleryThumbs.length;
            }
        });
    });

    document.querySelectorAll('.reservation-detail-option[data-share]').forEach(function (button) {
        button.addEventListener('click', function () {
            var title = button.getAttribute('data-share') || '';
            var shareUrl = button.getAttribute('data-share-url') || window.location.href;
            if (navigator.share) {
                navigator.share({ title: title, url: shareUrl }).catch(function () {});
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl).catch(function () {});
            }
        });
    });

    document.querySelectorAll('.reservation-detail-cancel-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || '';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.reservation-detail-option-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var panelId = button.getAttribute('aria-controls') || '';
            var panel = panelId ? document.getElementById(panelId) : null;
            if (!panel) {
                return;
            }
            var open = panel.hidden;
            panel.hidden = !open;
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.classList.toggle('is-open', open);
            if (open) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
})();
</script>
