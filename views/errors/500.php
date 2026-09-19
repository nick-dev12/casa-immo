<section class="container py-5 text-center">
    <h1 class="display-1 text-danger fw-bold">500</h1>
    <h2 class="mb-3"><?= e($title ?? 'Erreur serveur') ?></h2>
    <p class="text-muted mb-4"><?= e($message ?? 'Une erreur interne est survenue.') ?></p>
    <a href="<?= url('/') ?>" class="btn btn-primary">Retour à l'accueil</a>

    <?php if (!empty($debug) && isset($exception)): ?>
        <div class="alert alert-danger text-start mt-5">
            <strong><?= e($exception::class) ?>:</strong>
            <?= e($exception->getMessage()) ?>
            <br>
            <small class="text-muted"><?= e($exception->getFile()) ?>:<?= (int) $exception->getLine() ?></small>
        </div>
    <?php endif; ?>
</section>
