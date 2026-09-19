<?php
/** @var array<string, mixed> $payload */
/** @var string $appName */
/** @var string $preheader */
$appName = (string) ($payload['app_name'] ?? $appName ?? config('app', 'name'));
$firstName = (string) ($payload['first_name'] ?? '');
$guestName = $firstName !== '' ? $firstName : __('booking.email.guest_fallback');
include base_path('views/emails/_layout-start.php');
?>
                        <p style="margin:0 0 8px;font-size:13px;color:#64748b;line-height:1.5;">
                            <?= e(__('booking.email.confirmation_line', ['number' => (string) $payload['confirmation_number']])) ?><br>
                            <strong style="color:#111111;"><?= e(__('booking.email.pin_line', ['pin' => (string) $payload['confirmation_pin']])) ?></strong>
                        </p>

                        <p style="margin:20px 0 12px;font-size:17px;line-height:1.5;color:#111111;font-weight:700;">
                            <?= e(__('booking.email.thanks', ['name' => $guestName, 'city' => (string) $payload['city']])) ?>
                        </p>

                        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#111111;">
                            <?= e(__('booking.email.host_waiting', [
                                'property' => (string) $payload['property_title'],
                                'date' => format_date((string) $payload['check_in'], 'stay'),
                            ])) ?>
                        </p>

                        <p style="margin:0 0 24px;font-size:14px;line-height:1.55;color:#475569;">
                            <?= e(__('booking.email.manage_hint')) ?>
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                               style="margin:0 0 20px;background:#f8faf9;border:1px solid #e8efe9;border-radius:10px;">
                            <tr>
                                <td style="padding:16px 18px;font-size:13px;line-height:1.55;color:#64748b;">
                                    <?= e(__('booking.email.pin_warning')) ?>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 6px;font-size:16px;font-weight:700;color:#111111;">
                            <?= e((string) $payload['property_title']) ?>
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                               style="margin:0 0 24px;border-top:1px solid #eeeeee;">
                            <tr>
                                <td style="padding:14px 0 8px;width:42%;vertical-align:top;font-size:13px;font-weight:700;color:#111111;">
                                    <?= e(__('booking.email.arrival')) ?>
                                </td>
                                <td style="padding:14px 0 8px;vertical-align:top;font-size:13px;color:#111111;line-height:1.5;">
                                    <?= e((string) $payload['check_in_long']) ?><br>
                                    <span style="color:#64748b;"><?= e((string) $payload['check_in_window']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;width:42%;vertical-align:top;font-size:13px;font-weight:700;color:#111111;">
                                    <?= e(__('booking.email.departure')) ?>
                                </td>
                                <td style="padding:8px 0;vertical-align:top;font-size:13px;color:#111111;line-height:1.5;">
                                    <?= e((string) $payload['check_out_long']) ?><br>
                                    <span style="color:#64748b;"><?= e((string) $payload['check_out_window']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;width:42%;vertical-align:top;font-size:13px;font-weight:700;color:#111111;">
                                    <?= e(__('booking.email.booking_line')) ?>
                                </td>
                                <td style="padding:8px 0;vertical-align:top;font-size:13px;color:#111111;line-height:1.5;">
                                    <?= e((string) $payload['nights_label']) ?>, <?= e((string) $payload['room_label']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0 4px;width:42%;vertical-align:top;font-size:13px;font-weight:700;color:#111111;">
                                    <?= e(__('booking.email.guests_line')) ?>
                                </td>
                                <td style="padding:8px 0 4px;vertical-align:top;font-size:13px;color:#111111;">
                                    <?= e((string) $payload['guests_label']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;width:42%;vertical-align:top;font-size:13px;font-weight:700;color:#111111;">
                                    <?= e(__('booking.email.total_line')) ?>
                                </td>
                                <td style="padding:8px 0;vertical-align:top;font-size:13px;color:#111111;font-weight:700;">
                                    <?= e((string) $payload['total_amount']) ?>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                               style="margin:0 0 24px;background:#f0f7f1;border-radius:10px;">
                            <tr>
                                <td style="padding:16px 18px;font-size:13px;line-height:1.55;color:#1b4d24;">
                                    <strong><?= e(__('booking.email.payment_title')) ?></strong><br>
                                    <?= e(__('booking.email.payment_body', ['app' => $appName])) ?>
                                </td>
                            </tr>
                        </table>

                        <?php
                        $emergencyServices = $payload['emergency_services'] ?? [];
                        $emergencyCity = (string) ($payload['emergency_city'] ?? '');
                        if ($emergencyServices !== []):
                        ?>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                               style="margin:0 0 20px;border:1px solid #fde68a;border-radius:10px;background:#fffbeb;">
                            <tr>
                                <td style="padding:16px 18px;">
                                    <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#92400e;">
                                        <?= e(__('booking.email.emergency_title')) ?>
                                    </p>
                                    <p style="margin:0 0 12px;font-size:12px;line-height:1.5;color:#78350f;">
                                        <?= e(__('booking.email.emergency_lead', ['city' => $emergencyCity])) ?>
                                    </p>
                                    <?php foreach ($emergencyServices as $service): ?>
                                        <?php if (!is_array($service)) {
                                            continue;
                                        } ?>
                                        <p style="margin:0 0 6px;font-size:13px;line-height:1.45;color:#111111;">
                                            <strong><?= e((string) ($service['label'] ?? '')) ?></strong>
                                            — <?= e((string) ($service['number'] ?? '')) ?>
                                            <span style="color:#64748b;"> · <?= e((string) ($service['description'] ?? '')) ?></span>
                                        </p>
                                    <?php endforeach; ?>
                                    <p style="margin:8px 0 0;font-size:11px;line-height:1.45;color:#78350f;">
                                        <?= e(__('reservations.emergency.note')) ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <?php endif; ?>

                        <p style="margin:0 0 20px;font-size:12px;line-height:1.55;color:#64748b;">
                            <?= e(__('booking.email.security_notice', ['app' => $appName])) ?>
                        </p>

                        <p style="margin:0;text-align:center;">
                            <a href="<?= e((string) $payload['manage_url']) ?>"
                               style="display:inline-block;padding:12px 22px;border-radius:10px;background:#1b4d24;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                                <?= e(__('booking.email.cta')) ?>
                            </a>
                        </p>
<?php include base_path('views/emails/_layout-end.php'); ?>
