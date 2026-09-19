<?php
/** @var string $appName */
/** @var string|null $preheader */
$preheader = $preheader ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111111;">
<?php if ($preheader !== ''): ?>
<div style="display:none;max-height:0;overflow:hidden;"><?= e($preheader) ?></div>
<?php endif; ?>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                   style="max-width:560px;background:#ffffff;border:1px solid #eeeeee;border-radius:12px;">
                <tr>
                    <td align="center" style="padding:28px 24px 16px;background:#ffffff;">
                        <img src="cid:casa-blog-logo" alt="<?= e($appName) ?>" width="64" height="64"
                             style="display:block;width:64px;height:64px;margin:0 auto 12px;border:0;border-radius:50%;">
                        <p style="margin:0;font-size:18px;font-weight:700;color:#1b4d24;letter-spacing:-0.02em;">
                            <?= e($appName) ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 32px 28px;background:#ffffff;">
