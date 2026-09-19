<?php
/** @var array<string, mixed> $invoice */

$currency = (string) ($invoice['currency'] ?? 'XOF');
$company = is_array($invoice['company'] ?? null) ? $invoice['company'] : [];
$statusLabels = [
    'pending' => __('reservations.status.pending'),
    'confirmed' => __('reservations.status.confirmed'),
    'completed' => __('reservations.status.completed'),
];
$statusLabel = $statusLabels[(string) ($invoice['status'] ?? '')] ?? '';
$companyName = (string) ($company['name'] ?? config('app', 'name', 'Zig Imobilier'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.45;
        }
        .header {
            background: #1b4d24;
            color: #ffffff;
            padding: 22px 24px 18px 58px;
            position: relative;
        }
        .ribbon {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 42px;
            background: #c9a227;
            color: #111111;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            letter-spacing: 1px;
        }
        .ribbon-inner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-90deg);
            white-space: nowrap;
        }
        .header-top {
            width: 100%;
            margin-bottom: 14px;
        }
        .header-top td {
            vertical-align: top;
        }
        .brand-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 4px;
        }
        .brand-tagline {
            margin: 0;
            font-size: 10px;
            opacity: 0.9;
        }
        .contact {
            text-align: right;
            font-size: 9px;
            line-height: 1.5;
        }
        .meta {
            width: 100%;
            background: rgba(255, 255, 255, 0.12);
            border-collapse: collapse;
        }
        .meta td {
            padding: 10px 12px;
            vertical-align: top;
            font-size: 10px;
        }
        .total-box {
            background: #c9a227;
            color: #111111;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .total-box-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .total-box-value {
            font-size: 16px;
            font-weight: bold;
        }
        .bill-to-label {
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .content {
            padding: 22px 24px 26px;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items th {
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 10px;
        }
        .items th.right,
        .items td.right {
            text-align: right;
        }
        .items td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .items tr.alt td {
            background: #f8fafc;
        }
        .item-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .item-sub {
            font-size: 9px;
            color: #64748b;
        }
        .bottom {
            width: 100%;
        }
        .bottom td {
            vertical-align: top;
        }
        .note h3,
        .footer h3 {
            margin: 0 0 6px;
            font-size: 12px;
        }
        .note p,
        .footer p {
            margin: 0;
            font-size: 9px;
            color: #64748b;
        }
        .totals {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-collapse: collapse;
        }
        .totals td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals tr.grand td {
            background: #1b4d24;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            border-bottom: none;
        }
        .footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .signature {
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 140px;
            margin: 24px 0 6px auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="ribbon"><div class="ribbon-inner"><?= e(__('invoice.ribbon')) ?></div></div>
        <table class="header-top">
            <tr>
                <td width="55%">
                    <div class="brand-name"><?= e($companyName) ?></div>
                    <div class="brand-tagline"><?= e(__('invoice.tagline')) ?></div>
                </td>
                <td width="45%" class="contact">
                    <?php if (!empty($company['address'])): ?><?= e((string) $company['address']) ?><br><?php endif; ?>
                    <?= e(trim(((string) ($company['city'] ?? '')) . ', ' . ((string) ($company['region'] ?? '')))) ?><br>
                    <?= e((string) ($company['country'] ?? '')) ?><br>
                    <?php if (!empty($company['phone'])): ?><?= e((string) $company['phone']) ?><br><?php endif; ?>
                    <?php if (!empty($company['email'])): ?><?= e((string) $company['email']) ?><br><?php endif; ?>
                    <?php if (!empty($company['website'])): ?><?= e((string) $company['website']) ?><?php endif; ?>
                </td>
            </tr>
        </table>
        <table class="meta">
            <tr>
                <td width="58%">
                    <strong><?= e(__('invoice.project')) ?></strong> <?= e((string) ($invoice['project_label'] ?? '')) ?><br>
                    <strong><?= e(__('invoice.number')) ?></strong> <?= e((string) ($invoice['invoice_number'] ?? '')) ?><br>
                    <strong><?= e(__('invoice.reference')) ?></strong> <?= e((string) ($invoice['reference'] ?? '')) ?><br>
                    <strong><?= e(__('invoice.date')) ?></strong> <?= e(format_date((string) ($invoice['issued_at'] ?? ''), 'datetime')) ?>
                </td>
                <td width="42%">
                    <div class="total-box">
                        <div class="total-box-label"><?= e(__('invoice.total_due')) ?></div>
                        <div class="total-box-value"><?= e(money($invoice['total'] ?? 0, $currency)) ?></div>
                    </div>
                    <div class="bill-to-label"><?= e(__('invoice.bill_to')) ?></div>
                    <?= e((string) ($invoice['guest_name'] ?? '')) ?><br>
                    <?php if (!empty($invoice['guest_email'])): ?><?= e((string) $invoice['guest_email']) ?><br><?php endif; ?>
                    <?php if (!empty($invoice['guest_phone'])): ?><?= e((string) $invoice['guest_phone']) ?><?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <table class="items">
            <thead>
                <tr>
                    <th><?= e(__('invoice.col.description')) ?></th>
                    <th class="right"><?= e(__('invoice.col.price')) ?></th>
                    <th class="right"><?= e(__('invoice.col.qty')) ?></th>
                    <th class="right"><?= e(__('invoice.col.total')) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="alt">
                    <td>
                        <div class="item-title"><?= e((string) ($invoice['line_label'] ?? '')) ?></div>
                        <div class="item-sub"><?= e((string) ($invoice['line_description'] ?? '')) ?></div>
                        <?php if (!empty($invoice['property_address'])): ?>
                            <div class="item-sub"><?= e((string) $invoice['property_address']) ?></div>
                        <?php endif; ?>
                        <?php if ((int) ($invoice['guests'] ?? 0) > 0): ?>
                            <div class="item-sub"><?= e(__('invoice.guests_count', ['count' => (int) $invoice['guests']])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="right"><?= e(money($invoice['unit_price'] ?? 0, $currency)) ?></td>
                    <td class="right"><?= (int) ($invoice['quantity'] ?? 0) ?></td>
                    <td class="right"><?= e(money($invoice['subtotal'] ?? 0, $currency)) ?></td>
                </tr>
            </tbody>
        </table>

        <table class="bottom">
            <tr>
                <td width="58%" class="note">
                    <h3><?= e(__('invoice.payment_info')) ?></h3>
                    <p><?= e(__('invoice.payment_lead')) ?></p>
                    <?php if ($statusLabel !== ''): ?>
                        <p><strong><?= e(__('invoice.status_note', ['status' => $statusLabel])) ?></strong></p>
                    <?php endif; ?>
                </td>
                <td width="42%">
                    <table class="totals">
                        <tr>
                            <td><?= e(__('invoice.subtotal')) ?></td>
                            <td class="right"><?= e(money($invoice['subtotal'] ?? 0, $currency)) ?></td>
                        </tr>
                        <tr>
                            <td><?= e(__('invoice.tax', ['rate' => (float) ($invoice['tax_rate'] ?? 0)])) ?></td>
                            <td class="right"><?= e(money($invoice['tax_amount'] ?? 0, $currency)) ?></td>
                        </tr>
                        <tr class="grand">
                            <td><?= e(__('invoice.grand_total')) ?></td>
                            <td class="right"><?= e(money($invoice['total'] ?? 0, $currency)) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td width="60%">
                    <h3><?= e(__('invoice.thanks')) ?></h3>
                    <p><?= e(__('invoice.terms')) ?></p>
                </td>
                <td width="40%" class="signature">
                    <div><?= e(__('invoice.manager')) ?></div>
                    <div class="signature-line"></div>
                    <div><?= e($companyName) ?></div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
