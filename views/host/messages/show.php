<?php
/** @var array<string, mixed> $conversation */
/** @var array<string, mixed>|null $other */
/** @var array<string, mixed>|null $listing */
/** @var list<array<string, mixed>> $messages */

$activeNav = 'messages';
include base_path('views/host/partials/header.php');
?>
<?php include base_path('views/messages/partials/thread-body.php'); ?>
<?php include base_path('views/host/partials/footer.php'); ?>
