            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar toggle functionality for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                // Toggle mobile-open class for mobile view
                sidebar.classList.toggle('mobile-open');
                
                // Toggle collapsed for desktop view
                sidebar.classList.toggle('collapsed');
                if (mainContent) {
                    mainContent.classList.toggle('sidebar-collapsed');
                }
                
                sidebarToggle.setAttribute('aria-expanded', 
                    sidebar.classList.contains('mobile-open') ? 'true' : 'false');
            });
        }

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                sidebar && 
                !sidebar.contains(e.target) && 
                sidebarToggle &&
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        });

        // Close sidebar when clicking nav links on mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
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
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });

        // Table row selection
        document.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && e.target.classList.contains('select-all')) {
                const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            }
        });

        // Form validation
        document.addEventListener('submit', function(e) {
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
