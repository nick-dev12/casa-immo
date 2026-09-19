<?php
/**
 * @var string $brandVariant logo|icon
 * @var string $brandClass
 * @var string $brandAlt
 */

$brandVariant = $brandVariant ?? 'logo';
$brandClass = $brandClass ?? '';
$brandAlt = $brandAlt ?? (string) config('app', 'name', 'Casa-blog Immo');
$src = $brandVariant === 'icon'
    ? asset('images/brand/icon-casa-immo.png')
    : asset('images/brand/logo-casa-immo.png');
?>
<img src="<?= e($src) ?>"
     alt="<?= e($brandAlt) ?>"
     class="brand-mark<?= $brandClass !== '' ? ' ' . e($brandClass) : '' ?>"
     width="<?= $brandVariant === 'icon' ? '64' : '360' ?>"
     height="<?= $brandVariant === 'icon' ? '64' : '104' ?>"
     decoding="async">
