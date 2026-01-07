</div>
</main>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                const isMobile = window.innerWidth <= 1024;
                
                if (isMobile) {
                    sidebar.classList.toggle('active');
                    if (overlay) overlay.classList.toggle('active');
                } else {
                    sidebar.classList.toggle('collapsed');
                    if (mainContent) mainContent.classList.toggle('sidebar-collapsed');
                }
                
                const isOpen = isMobile ? sidebar.classList.contains('active') : !sidebar.classList.contains('collapsed');
                sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        // Close mobile sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
            });
        }

        // Close sidebar when clicking nav links on mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024 && sidebar) {
                    sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                    if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    });

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });

    // Confirm delete actions
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });

    // Table row selection
    document.addEventListener('change', function (e) {
        if (e.target.type === 'checkbox' && e.target.classList.contains('select-all')) {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        }
    });

    // Form validation
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.classList.contains('needs-validation')) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }
    });
</script>
</body>

</html>