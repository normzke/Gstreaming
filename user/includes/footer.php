            </div>
        </main>
    </div>

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
                mainContent.classList.toggle('sidebar-collapsed');
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
    </script>
</body>
</html>
