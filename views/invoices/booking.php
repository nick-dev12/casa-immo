<?php
/** @var array<string, mixed> $invoice */
/** @var bool $embedded */

$embedded = !empty($embedded);
$currency = (string) ($invoice['currency'] ?? 'XOF');
$company = is_array($invoice['company'] ?? null) ? $invoice['company'] : [];
$companyLines = array_filter([
    trim((string) ($company['address'] ?? '')),
    trim(((string) ($company['city'] ?? '')) . ', ' . ((string) ($company['region'] ?? ''))),
    trim((string) ($company['country'] ?? '')),
]);
$guestLines = array_filter([
    (string) ($invoice['guest_name'] ?? ''),
    (string) ($invoice['guest_email'] ?? ''),
    (string) ($invoice['guest_phone'] ?? ''),
]);
$statusLabels = [
    'pending' => __('reservations.status.pending'),
    'confirmed' => __('reservations.status.confirmed'),
    'completed' => __('reservations.status.completed'),
];
$statusLabel = $statusLabels[(string) ($invoice['status'] ?? '')] ?? '';
?>
<article class="invoice-doc<?= $embedded ? ' invoice-doc--embedded' : '' ?>">
    <header class="invoice-header">
        <div class="invoice-ribbon" aria-hidden="true">
            <span><?= e(__('invoice.ribbon')) ?></span>
        </div>

        <div class="invoice-header-main">
            <div class="invoice-brand">
                <div class="invoice-logo" aria-hidden="true"><?= e(mb_substr((string) ($company['name'] ?? 'Z'), 0, 1)) ?></div>
                <div>
                    <p class="invoice-brand-name"><?= e((string) ($company['name'] ?? config('app', 'name'))) ?></p>
                    <p class="invoice-brand-tagline"><?= e(__('invoice.tagline')) ?></p>
                </div>
            </div>

            <ul class="invoice-company-contact">
                <?php foreach ($companyLines as $line): ?>
                    <li><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($line) ?></li>
                <?php endforeach; ?>
                <?php if (!empty($company['phone'])): ?>
                    <li><i class="bi bi-telephone" aria-hidden="true"></i> <?= e((string) $company['phone']) ?></li>
                <?php endif; ?>
                <?php if (!empty($company['email'])): ?>
                    <li><i class="bi bi-envelope" aria-hidden="true"></i> <?= e((string) $company['email']) ?></li>
                <?php endif; ?>
                <?php if (!empty($company['website'])): ?>
                    <li><i class="bi bi-globe2" aria-hidden="true"></i> <?= e((string) $company['website']) ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="invoice-meta-grid">
            <div class="invoice-meta-block">
                <p><strong><?= e(__('invoice.project')) ?></strong> <?= e((string) ($invoice['project_label'] ?? '')) ?></p>
                <p><strong><?= e(__('invoice.number')) ?></strong> <?= e((string) ($invoice['invoice_number'] ?? '')) ?></p>
                <p><strong><?= e(__('invoice.reference')) ?></strong> <?= e((string) ($invoice['reference'] ?? '')) ?></p>
                <p><strong><?= e(__('invoice.date')) ?></strong> <?= e(format_date((string) ($invoice['issued_at'] ?? ''), 'datetime')) ?></p>
            </div>
            <div class="invoice-meta-side">
                <div class="invoice-total-due">
                    <span><?= e(__('invoice.total_due')) ?></span>
                    <strong><?= e(money($invoice['total'] ?? 0, $currency)) ?></strong>
                </div>
                <div class="invoice-bill-to">
                    <p class="invoice-bill-to-label"><?= e(__('invoice.bill_to')) ?></p>
                    <?php foreach ($guestLines as $line): ?>
                        <p><?= e($line) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="invoice-body-content">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th><?= e(__('invoice.col.description')) ?></th>
                    <th><?= e(__('invoice.col.price')) ?></th>
                    <th><?= e(__('invoice.col.qty')) ?></th>
                    <th><?= e(__('invoice.col.total')) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= e((string) ($invoice['line_label'] ?? '')) ?></strong>
                        <span><?= e((string) ($invoice['line_description'] ?? '')) ?></span>
                        <?php if (!empty($invoice['property_address'])): ?>
                            <span><?= e((string) $invoice['property_address']) ?></span>
                        <?php endif; ?>
                        <?php if ((int) ($invoice['guests'] ?? 0) > 0): ?>
                            <span><?= e(__('invoice.guests_count', ['count' => (int) $invoice['guests']])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(money($invoice['unit_price'] ?? 0, $currency)) ?></td>
                    <td><?= (int) ($invoice['quantity'] ?? 0) ?></td>
                    <td><?= e(money($invoice['subtotal'] ?? 0, $currency)) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-summary-row">
            <div class="invoice-payment-note">
                <h3><?= e(__('invoice.payment_info')) ?></h3>
                <p><?= e(__('invoice.payment_lead')) ?></p>
                <?php if ($statusLabel !== ''): ?>
                    <p class="invoice-status-note"><?= e(__('invoice.status_note', ['status' => $statusLabel])) ?></p>
                <?php endif; ?>
            </div>

            <div class="invoice-totals">
                <div class="invoice-totals-row">
                    <span><?= e(__('invoice.subtotal')) ?></span>
                    <span><?= e(money($invoice['subtotal'] ?? 0, $currency)) ?></span>
                </div>
                <div class="invoice-totals-row">
                    <span><?= e(__('invoice.tax', ['rate' => (float) ($invoice['tax_rate'] ?? 0)])) ?></span>
                    <span><?= e(money($invoice['tax_amount'] ?? 0, $currency)) ?></span>
                </div>
                <div class="invoice-totals-row invoice-totals-row--grand">
                    <span><?= e(__('invoice.grand_total')) ?></span>
                    <span><?= e(money($invoice['total'] ?? 0, $currency)) ?></span>
                </div>
            </div>
        </div>

        <footer class="invoice-footer">
            <div>
                <h3><?= e(__('invoice.thanks')) ?></h3>
                <p><?= e(__('invoice.terms')) ?></p>
            </div>
            <div class="invoice-signature">
                <p><?= e(__('invoice.manager')) ?></p>
                <span class="invoice-signature-line"></span>
                <p><?= e((string) ($company['name'] ?? config('app', 'name'))) ?></p>
            </div>
        </footer>
    </div>
</article>
