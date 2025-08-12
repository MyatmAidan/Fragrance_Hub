  </div> <!-- wrap -->
  <footer>
      <div class="container">
          <p>&copy; Owner 2025</p>
      </div>
  </footer>
  <script src="../node_modules/metismenujs/dist/metismenujs.min.js"></script>
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          // Initialize MetisMenu with no auto-expansion
          new MetisMenu('.metismenu', {
              toggle: true,
              preventDefault: true,
              activeClass: 'mm-active',
              collapseClass: 'mm-collapse',
              collapseInClass: 'mm-show',
              collapseOutClass: 'mm-hide'
          });

          // Sidebar toggle functionality
          const sidebarToggle = document.getElementById('sidebarToggle');
          const sidebar = document.querySelector('.sidebar');
          const mainContent = document.querySelector('.main-content');

          if (sidebarToggle && sidebar) {
              sidebarToggle.addEventListener('click', function() {
                  // Toggle sidebar visibility
                  if (sidebar.style.display === 'none' || sidebar.style.display === '') {
                      // Show sidebar
                      sidebar.style.display = 'block';
                      if (mainContent) {
                          mainContent.classList.add('sidebar-visible');
                      }
                  } else {
                      // Hide sidebar
                      sidebar.style.display = 'none';
                      if (mainContent) {
                          mainContent.classList.remove('sidebar-visible');
                      }
                  }
              });
          }
      });
  </script>