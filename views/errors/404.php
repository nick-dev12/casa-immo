<section class="container py-5 text-center">
    <h1 class="display-1 text-muted fw-bold">404</h1>
    <h2 class="mb-3"><?= e($title ?? 'Page introuvable') ?></h2>
    <p class="text-muted mb-4"><?= e($message ?? 'La page demandée n\'existe pas.') ?></p>
    <a href="<?= url('/') ?>" class="btn btn-primary">Retour à l'accueil</a>
</section>
