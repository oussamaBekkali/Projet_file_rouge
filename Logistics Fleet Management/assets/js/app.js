/**
 * FleetTrack — Modern Logistics Fleet Management JS
 * Client-side interactions, Modal handling, and Real-time Search
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Mobile Sidebar Drawer Toggle
    // ----------------------------------------------------
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appSidebar = document.getElementById('appSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggle && appSidebar && sidebarBackdrop) {
        sidebarToggle.addEventListener('click', () => {
            appSidebar.classList.toggle('open');
            sidebarBackdrop.classList.toggle('active');
        });

        sidebarBackdrop.addEventListener('click', () => {
            appSidebar.classList.remove('open');
            sidebarBackdrop.classList.remove('active');
        });
    }

    // ----------------------------------------------------
    // 2. Real-time Search Filter for Tables
    // ----------------------------------------------------
    const searchInputs = document.querySelectorAll('.table-search-input');
    searchInputs.forEach(input => {
        const targetTableSelector = input.getAttribute('data-table');
        const targetTable = document.querySelector(targetTableSelector);

        if (targetTable) {
            input.addEventListener('input', () => {
                const filter = input.value.toLowerCase().trim();
                const rows = targetTable.querySelectorAll('tbody tr:not(.no-filter)');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });

    // ----------------------------------------------------
    // 3. Reusable Confirmation Modal for Delete Actions
    // ----------------------------------------------------
    const confirmModal = document.getElementById('confirmModal');
    const confirmModalTitle = document.getElementById('confirmModalTitle');
    const confirmModalText = document.getElementById('confirmModalText');
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');

    let pendingActionUrl = null;

    document.querySelectorAll('[data-confirm]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const message = trigger.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            const title = trigger.getAttribute('data-title') || 'Confirm Delete';
            const actionText = trigger.getAttribute('data-btn-text') || 'Delete';
            pendingActionUrl = trigger.getAttribute('href');

            if (confirmModal) {
                confirmModalTitle.textContent = title;
                confirmModalText.textContent = message;
                confirmActionBtn.textContent = actionText;
                confirmActionBtn.setAttribute('href', pendingActionUrl);
                confirmModal.classList.add('active');
            } else {
                // Fallback to native confirm if modal not present
                if (window.confirm(message)) {
                    window.location.href = pendingActionUrl;
                }
            }
        });
    });

    if (confirmCancelBtn && confirmModal) {
        confirmCancelBtn.addEventListener('click', () => {
            confirmModal.classList.remove('active');
            pendingActionUrl = null;
        });

        // Close on backdrop click
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                confirmModal.classList.remove('active');
                pendingActionUrl = null;
            }
        });
    }

    // ----------------------------------------------------
    // 4. Auto-dismiss Flash Alerts after 5 seconds
    // ----------------------------------------------------
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // ----------------------------------------------------
    // 5. System Clock Updates
    // ----------------------------------------------------
    const clockEl = document.getElementById('systemClock');
    if (clockEl) {
        const updateClock = () => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            clockEl.textContent = `${hours}:${minutes}`;
        };
        setInterval(updateClock, 30000);
    }
});
