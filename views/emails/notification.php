<?php
/** @var string $appName */
/** @var string $firstName */
/** @var string $body */
/** @var string $url */
/** @var string $buttonLabel */
$preheader = $body;
include base_path('views/emails/_layout-start.php');
?>
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.65;color:#111111;">
                            Bonjour <?= e($firstName !== '' ? $firstName : 'à vous') ?>,<br><br>
                            <?= nl2br(e($body)) ?>
                        </p>
                        <p style="margin:0;text-align:center;">
                            <a href="<?= e($url) ?>"
                               style="display:inline-block;padding:12px 20px;border-radius:10px;background:#1b4d24;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                                <?= e($buttonLabel) ?>
                            </a>
                        </p>
<?php include base_path('views/emails/_layout-end.php'); ?>
