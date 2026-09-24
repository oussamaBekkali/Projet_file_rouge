<?php
/**
 * Global Footer Include
 * Fleet Management System
 */
?>
        </main>

        <footer class="app-footer">
            <div class="footer-left">
                <span>&copy; <?= date('Y') ?> <strong>FleetTrack</strong> Logistics Fleet Management System</span>
            </div>
            <div class="footer-right">
                <span>Built with PHP, MySQL PDO, HTML5 &amp; Modern Vanilla CSS</span>
            </div>
        </footer>
    </div>
</div>

<!-- Reusable Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-card">
        <div class="modal-icon danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-title" id="confirmModalTitle">Confirm Action</h3>
        <p class="modal-description" id="confirmModalText">Are you sure you want to proceed? This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="confirmCancelBtn">Cancel</button>
            <a href="#" class="btn btn-danger" id="confirmActionBtn">Confirm Delete</a>
        </div>
    </div>
</div>

<!-- Main Application JS -->
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
