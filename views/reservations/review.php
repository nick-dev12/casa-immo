<?php
/** @var array<string, mixed> $booking */
/** @var array<int, array<string, mixed>> $propertyImages */

$bookingId = (int) ($booking['id'] ?? 0);
$propertyId = (int) ($booking['property_id'] ?? 0);
$title = translated((string) ($booking['title'] ?? ''));
$establishmentName = trim((string) ($booking['establishment_name'] ?? ''));
$displayTitle = $establishmentName !== '' ? $establishmentName . ', ' . $title : $title;
$propertyType = (string) ($booking['type'] ?? 'autre');
$cover = property_image($booking['primary_image'] ?? null, $propertyType);
?>
<section class="account-page reservation-detail-page reservation-review-page">
    <div class="reservation-detail-shell">
        <header class="reservation-detail-nav">
            <a href="<?= url('/reservations/' . $bookingId) ?>" class="reservation-detail-back">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                <?= e(__('reservations.detail.back')) ?>
            </a>
            <span class="reservation-detail-brand"><?= e(config('app', 'name')) ?></span>
            <span class="reservation-detail-help" aria-hidden="true"></span>
        </header>

        <?php if ($reservationError = flash('reservation_error')): ?>
            <div class="reservation-detail-alert reservation-detail-alert-error" role="alert">
                <?= e((string) $reservationError) ?>
            </div>
        <?php endif; ?>

        <div class="reservation-review-hero">
            <img src="<?= e($cover) ?>" alt="" class="reservation-review-cover" width="120" height="90" loading="lazy">
            <div>
                <h1 class="reservation-review-title"><?= e(__('reviews.form.title')) ?></h1>
                <p class="reservation-review-lead"><?= e(__('reviews.form.lead', ['property' => $displayTitle])) ?></p>
                <p class="reservation-review-dates">
                    <?= e(booking_stay_range_label((string) $booking['check_in'], (string) $booking['check_out'])) ?>
                </p>
            </div>
        </div>

        <form method="post"
              action="<?= url('/reservations/' . $bookingId . '/review') ?>"
              class="reservation-review-form">
            <?= csrf_field() ?>

            <fieldset class="reservation-review-rating">
                <legend><?= e(__('reviews.form.rating_label')) ?></legend>
                <div class="reservation-review-stars" role="radiogroup" aria-label="<?= e(__('reviews.form.rating_label')) ?>">
                    <?php for ($star = 1; $star <= 5; $star++): ?>
                        <label class="reservation-review-star">
                            <input type="radio" name="rating" value="<?= $star ?>" required<?= $star === 5 ? ' checked' : '' ?>>
                            <span class="reservation-review-star-icon" aria-hidden="true">
                                <i class="bi bi-star-fill"></i>
                            </span>
                            <span class="reservation-review-sr"><?= e(__('reviews.form.star', ['n' => $star])) ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </fieldset>

            <label class="reservation-review-comment-label" for="review-comment">
                <?= e(__('reviews.form.comment_label')) ?>
            </label>
            <textarea id="review-comment"
                      name="comment"
                      class="reservation-review-comment"
                      rows="5"
                      maxlength="2000"
                      placeholder="<?= e(__('reviews.form.comment_placeholder')) ?>"></textarea>

            <button type="submit" class="reservation-detail-cta reservation-review-submit">
                <?= e(__('reviews.form.submit')) ?>
            </button>
        </form>
    </div>
</section>

<script>
(function () {
    var form = document.querySelector('.reservation-review-form');
    if (!form) {
        return;
    }
    var inputs = form.querySelectorAll('.reservation-review-star input[type="radio"]');
    inputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var value = parseInt(input.value, 10);
            inputs.forEach(function (other) {
                var star = parseInt(other.value, 10);
                var icon = other.parentElement;
                if (icon) {
                    icon.classList.toggle('is-active', star <= value);
                }
            });
        });
    });
    var checked = form.querySelector('.reservation-review-star input[type="radio"]:checked');
    if (checked) {
        checked.dispatchEvent(new Event('change'));
    }
})();
</script>
