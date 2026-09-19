<?php
$appName = (string) ($appName ?? config('app', 'name'));
$updated = __('legal.updated_on', ['date' => '17/09/2026']);
?>
<section class="help-page legal-page">
    <div class="container app-container help-page-inner legal-page-inner">
        <header class="help-page-hero">
            <a href="<?= url('/') ?>" class="help-page-brand brand-link" aria-label="<?= e($appName) ?>">
                <?php
                $brandVariant = 'logo';
                $brandClass = 'brand-mark-logo';
                include base_path('views/partials/brand-mark.php');
                ?>
            </a>
            <p class="help-page-kicker"><?= e(__('legal.kicker')) ?></p>
            <h1 class="help-page-title"><?= e(__('legal.terms_title')) ?></h1>
            <p class="legal-updated"><?= e($updated) ?></p>
        </header>

        <article class="legal-content">
            <section>
                <h2><?= e(__('legal.terms.s1_title')) ?></h2>
                <p><?= e(__('legal.terms.s1_p1', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s1_p2', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s1_p3')) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s2_title')) ?></h2>
                <p><?= e(__('legal.terms.s2_p1')) ?></p>
                <ul>
                    <li><?= e(__('legal.terms.s2_li1')) ?></li>
                    <li><?= e(__('legal.terms.s2_li2')) ?></li>
                    <li><?= e(__('legal.terms.s2_li3')) ?></li>
                </ul>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s3_title')) ?></h2>
                <p><?= e(__('legal.terms.s3_p1', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s3_p2', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s3_p3', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s4_title')) ?></h2>
                <p><?= e(__('legal.terms.s4_p1')) ?></p>
                <p><?= e(__('legal.terms.s4_p2', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s4_p3', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s5_title')) ?></h2>
                <p><?= e(__('legal.terms.s5_p1')) ?></p>
                <p><?= e(__('legal.terms.s5_p2', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s5_p3')) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s6_title')) ?></h2>
                <p><?= e(__('legal.terms.s6_p1')) ?></p>
                <p><?= e(__('legal.terms.s6_p2', ['name' => $appName])) ?></p>
                <p><?= e(__('legal.terms.s6_p3', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s7_title')) ?></h2>
                <p><?= e(__('legal.terms.s7_p1')) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s8_title')) ?></h2>
                <p><?= e(__('legal.terms.s8_p1', ['name' => $appName])) ?></p>
                <ul>
                    <li><?= e(__('legal.terms.s8_li1')) ?></li>
                    <li><?= e(__('legal.terms.s8_li2')) ?></li>
                    <li><?= e(__('legal.terms.s8_li3')) ?></li>
                    <li><?= e(__('legal.terms.s8_li4')) ?></li>
                    <li><?= e(__('legal.terms.s8_li5')) ?></li>
                </ul>
                <p><?= e(__('legal.terms.s8_p2', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s9_title')) ?></h2>
                <p><?= e(__('legal.terms.s9_p1', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s10_title')) ?></h2>
                <p><?= e(__('legal.terms.s10_p1', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s11_title')) ?></h2>
                <p><?= e(__('legal.terms.s11_p1', ['name' => $appName])) ?></p>
            </section>

            <section>
                <h2><?= e(__('legal.terms.s12_title')) ?></h2>
                <p><?= e(__('legal.terms.s12_p1')) ?></p>
                <p>
                    <?= e(__('legal.terms.s12_p2')) ?>
                    <a href="mailto:support@casa-blog-immo.com">support@casa-blog-immo.com</a>
                </p>
            </section>
        </article>

        <p class="legal-links">
            <a href="<?= url('/privacy') ?>"><?= e(__('legal.privacy_title')) ?></a>
            <a href="<?= url('/help') ?>"><?= e(__('nav.help')) ?></a>
        </p>
    </div>
</section>
