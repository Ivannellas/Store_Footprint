document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Sidebar toggle functionality
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
    toggleBtn.innerHTML = '<span class="toggle-arrow">&#10094;</span>';

    sidebar.insertBefore(toggleBtn, sidebar.firstChild);

    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // 2. Dynamic Menu Highlighting
    const menuItems = document.querySelectorAll('.menu-list li');
    const currentPath = window.location.pathname;
    
    // Get the current page file name (e.g. "add_user_account.php")
    const currentPage = currentPath.split('/').pop().toLowerCase() || 'index.php';

    function setActiveItem(activeLi) {
        menuItems.forEach(item => item.classList.remove('current_page_item'));
        if (activeLi) {
            activeLi.classList.add('current_page_item');
        }
    }

    let isMatchFound = false;

    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (!link) return;

        const href = link.getAttribute('href') || '';
        const hrefPage = href.split('/').pop().toLowerCase();

        // Check 1: Exact match sa link ug sa browser URL page
        const isExactMatch = (hrefPage === currentPage);

        // Check 2: Special check for index.php or root directory "/"
        const isIndexMatch = (currentPage === 'index.php' || currentPage === '') && (hrefPage === 'index.php');

        // Check 3: Check if current page is inside data-subpages attribute
        const subpagesAttr = link.getAttribute('data-subpages') || '';
        const subpagesList = subpagesAttr.split(',').map(page => page.trim().toLowerCase());
        const isSubpageMatch = subpagesList.includes(currentPage);

        // If any condition matches, set active item!
        if (isExactMatch || isIndexMatch || isSubpageMatch) {
            setActiveItem(item);
            isMatchFound = true;
        }
    });

    // 3. Optional fallback for root or index if no page matched
    if (!isMatchFound && (currentPage === 'index.php' || currentPage === '')) {
        const dashboardLink = document.querySelector('.menu-list a[href*="index.php"]');
        if (dashboardLink) {
            setActiveItem(dashboardLink.closest('li'));
        }
    }
});