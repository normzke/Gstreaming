            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('mainContent').classList.add('sidebar-collapsed');
        }

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
