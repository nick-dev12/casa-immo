<?php
/** @var list<array<string, mixed>> $conversations */
/** @var string|null $error */

$activeNav = 'messages';
include base_path('views/host/partials/header.php');
?>
<section class="host-messages-page messages-page" data-messages-enable-banner>
    <?php
    $inHostShell = true;
    include base_path('views/messages/partials/inbox-body.php');
    ?>
</section>
<?php include base_path('views/host/partials/footer.php'); ?>
