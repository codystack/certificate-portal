        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="assets/js/vendor.bundle.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
        <script src="assets/js/theme.bundle.js"></script>
        <script>
            // Shared Notyf instance + CSRF token for fetch calls.
            const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
            <?php if ($f = take_flash()): ?>
            notyf.<?php echo $f["type"] === "error" ? "error" : "success"; ?>(<?php echo json_encode($f["message"]); ?>);
            <?php endif; ?>
        </script>
    </body>
</html>
