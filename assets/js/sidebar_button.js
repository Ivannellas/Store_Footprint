   document.addEventListener('DOMContentLoaded', function() {
    
    // Sidebar toggle button functionality
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    // Build the toggle button
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
    toggleBtn.innerHTML = '<span class="toggle-arrow">&#10094;</span>'; // ❯

    sidebar.insertBefore(toggleBtn, sidebar.firstChild);

    // Restore saved state on page load
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    // Toggle + persist state
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // Highlight the current page in the sidebar menu
    const menuItems = document.querySelectorAll('.menu-list li');
    const currentPath = window.location.pathname;
    
    // Get the current page filename
    const currentPage = currentPath.split('/').pop() || 'index.php';
    
    // Function to set active item
    function setActiveItem(activeLi) {
        menuItems.forEach(item => {
            item.classList.remove('current_page_item');
        });
        if (activeLi) {
            activeLi.classList.add('current_page_item');
        }
    }

        // Check each menu item against current URL
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            const href = link.getAttribute('href');
            if (href) {
                const hrefPage = href.split('/').pop() || 'index.php';
                // Also check if href matches current page or if it's the same page
                if (hrefPage === currentPage || 
                    (currentPage === 'index.php' && href.includes('index.php')) ||
                    (currentPage === '' && href.includes('index.php'))) {
                    setActiveItem(item);
                }
            }
        }
    });
    
    // Handle click events - using event delegation for all buttons
    const menuList = document.querySelector('.menu-list');
    if (menuList) {
        menuList.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link) {
                const li = link.closest('li');
                if (li) {
                    // Remove active from all
                    menuItems.forEach(item => {
                        item.classList.remove('current_page_item');
                    });
                    // Add active to clicked
                    li.classList.add('current_page_item');
                }
            }
        });
    }
    
});