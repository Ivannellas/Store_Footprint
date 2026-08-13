document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Sidebar Toggle Logic
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        let toggleBtn = sidebar.querySelector('.sidebar-toggle');
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.className = 'sidebar-toggle';
            toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
            toggleBtn.innerHTML = '<span class="toggle-arrow">&#10094;</span>';
            sidebar.insertBefore(toggleBtn, sidebar.firstChild);
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // 2. Pure JS Parent-Child Page Mapper
    const pageRelations = {
        'user_accounts.php': [
            'add_user_account.php', 
            'edit_user_account.php', 
            'view_user_account.php',
            'user_permission_account.php' // <-- Gidungag diri ang subpage
        ],
        'add_personnel.php': ['edit_personnel.php'],
        'my_account.php': ['edit_profile.php']
    };

    // 3. Extract Clean Filename (Tangtangon ang / ug ang ?id= parameters)
    const currentPath = window.location.pathname; // Example: /Store_Footprint/views/user_permission_account.php
    
    // Ang window.location.search kay "?" ug ang padayon. 
    const rawFilename = currentPath.split('/').pop(); 
    const currentPage = rawFilename.split('?')[0].toLowerCase() || 'index.php';

    const menuItems = document.querySelectorAll('.menu-list li');

    function setActiveItem(activeLi) {
        menuItems.forEach(item => item.classList.remove('current_page_item'));
        if (activeLi) {
            activeLi.classList.add('current_page_item');
        }
    }

    let isMatched = false;

    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (!link) return;

        const href = link.getAttribute('href') || '';
        const hrefFilename = href.split('/').pop();
        const hrefPage = hrefFilename.split('?')[0].toLowerCase();

        // Condition A: Exact Match
        const isExactMatch = (hrefPage === currentPage);

        // Condition B: Sub-page Match
        const subPages = pageRelations[hrefPage] || [];
        const isSubpageMatch = subPages.map(p => p.toLowerCase()).includes(currentPage);

        // Condition C: Root/Index Match
        const isIndexMatch = (currentPage === 'index.php' || currentPage === '') && hrefPage === 'index.php';

        if (isExactMatch || isSubpageMatch || isIndexMatch) {
            setActiveItem(item);
            isMatched = true;
        }
    });

    // Fallback: Kung walay na-match ug naa sa index
    if (!isMatched && (currentPage === 'index.php' || currentPage === '')) {
        const dashboardLink = document.querySelector('.menu-list a[href*="index.php"]');
        if (dashboardLink) {
            setActiveItem(dashboardLink.closest('li'));
        }
    }
});