<?php
/** @var string $gtranslateClass */
$gtranslateClass = $gtranslateClass ?? '';

$gtLanguages = [
    'fr' => ['label' => 'Français', 'code' => 'FR', 'flag' => 'fr'],
    'en' => ['label' => 'English', 'code' => 'EN', 'flag' => 'en'],
    'es' => ['label' => 'Español', 'code' => 'ES', 'flag' => 'es'],
    'pt' => ['label' => 'Português', 'code' => 'PT', 'flag' => 'pt'],
    'zh-TW' => ['label' => '繁體中文', 'code' => 'ZH', 'flag' => 'zh-CN'],
    'ar' => ['label' => 'العربية', 'code' => 'AR', 'flag' => 'ar'],
];
?>
<div class="gt-lang<?= $gtranslateClass !== '' ? ' ' . e($gtranslateClass) : '' ?>" data-gt-lang-switcher>
    <button type="button"
            class="gt-lang-trigger"
            data-gt-trigger
            aria-expanded="false"
            aria-haspopup="listbox"
            aria-label="Choisir la langue">
        <img class="gt-lang-flag"
             data-gt-flag
             src="https://cdn.gtranslate.net/flags/svg/fr.svg"
             width="22"
             height="22"
             alt=""
             decoding="async">
        <span class="gt-lang-code" data-gt-code>FR</span>
        <i class="bi bi-chevron-down gt-lang-caret" aria-hidden="true"></i>
    </button>
    <ul class="gt-lang-menu" data-gt-menu role="listbox" hidden>
        <?php foreach ($gtLanguages as $lang => $meta): ?>
            <li role="none">
                <button type="button"
                        class="gt-lang-option<?= $lang === 'fr' ? ' is-active' : '' ?>"
                        role="option"
                        data-gt-lang="<?= e($lang) ?>"
                        data-gt-label="<?= e($meta['label']) ?>"
                        data-gt-code="<?= e($meta['code']) ?>"
                        data-gt-flag-code="<?= e($meta['flag']) ?>"
                        aria-selected="<?= $lang === 'fr' ? 'true' : 'false' ?>">
                    <img src="https://cdn.gtranslate.net/flags/svg/<?= e($meta['flag']) ?>.svg"
                         width="22"
                         height="22"
                         alt=""
                         decoding="async">
                    <span><?= e($meta['label']) ?></span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
