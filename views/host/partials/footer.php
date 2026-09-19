        </div>

        <?php if (($activeNav ?? 'none') !== 'none'): ?>
            <?php include base_path('views/host/partials/bottom-nav-host.php'); ?>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('.host-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || '';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
})();
</script>
