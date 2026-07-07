<?php
// includes/footer.php
?>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- SweetAlert Helper -->
    <script src="<?php echo $base_path ?? ''; ?>assets/js/sweetalert-helper.js"></script>
    <script src="<?php echo $base_path ?? ''; ?>assets/js/global-sweetalert.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo $base_path ?? '' . $extra_js; ?>"></script>
    <?php endif; ?>
</body>
</html>