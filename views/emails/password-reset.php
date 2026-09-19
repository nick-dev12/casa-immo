<?php
/** @var string $appName */
/** @var string $firstName */
/** @var string $resetCode */
/** @var int $expiresMinutes */
$preheader = 'Code : ' . $resetCode;
include base_path('views/emails/_layout-start.php');
?>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#111111;">
                            Bonjour <?= e($firstName) ?>,<br><br>
                            Utilisez le code ci-dessous pour réinitialiser votre mot de passe.
                        </p>
                        <p style="margin:0 0 20px;padding:16px 10px;text-align:center;letter-spacing:.28em;font-size:32px;font-weight:800;color:#1b4d24;background:#ffffff;">
                            <?= e($resetCode) ?>
                        </p>
                        <p style="margin:0;font-size:14px;line-height:1.6;color:#111111;">
                            Ce code expire dans <?= (int) $expiresMinutes ?> minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.
                        </p>
<?php include base_path('views/emails/_layout-end.php'); ?>
