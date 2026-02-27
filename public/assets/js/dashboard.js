/**
 * NIA UPRIIS Dashboard - Main JavaScript
 * Handles navigation, lazy loading, and UI interactions
 */

class DashboardApp {
    constructor() {
        this.contentArea = document.getElementById('contentArea');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.breadcrumb = document.getElementById('breadcrumb');
        this.sidebar = document.getElementById('sidebar');
        this.mobileToggle = document.getElementById('mobileToggle');
        
        this.currentPage = 'home';
        this.pageCache = {}; // Cache loaded pages
        
        this.init();
    }
    
    // Map data categories → page names whose cache should be invalidated
    static CATEGORY_PAGE_MAP = {
        'equipment':    ['home', 'equipment', 'computer', 'printer', 'otherequipment', 'roster',
                         'equipment-summary', 'equipment-assignment', 'maintenance-schedule'],
        'employees':    ['home', 'employees', 'roster', 'equipment-assignment'],
        'maintenance':  ['home', 'maintenance-schedule', 'maintenance-history', 'perform-maintenance',
                         'pending-approvals', 'history', 'schedule', 'notifications', 'maintenance-summary'],
        'software':     ['home', 'software'],
        'organization': ['home', 'organization', 'divisions', 'sections', 'units'],
        'accounts':     ['accounts', 'profile'],
        'settings':     ['settings', 'home'],
    };

    init() {
        this.setupNavigation();
        this.setupMobileMenu();
        this.setupProfileDropdown();
        this.loadInitialPage();
        this.setupAnimations();
        this.setupRealtime();
    }

    /**
     * Setup smart real-time polling
     * Polls a lightweight endpoint every 5s; only refreshes when data changes
     */
    setupRealtime() {
        if (typeof RealtimeManager === 'undefined') {
            console.warn('[Dashboard] RealtimeManager not loaded — real-time disabled');
            return;
        }

        this.realtime = new RealtimeManager({
            interval: 5000,           // 5-second check interval
            pauseOnHidden: true,      // save resources when tab hidden
        });

        // Listen to every category via wildcard
        this.realtime.on('*', (category) => {
            this._handleDataChange(category);
        });
    }

    /**
     * Handle a data-change event from the RealtimeManager
     * Invalidates relevant page caches and reloads the current page if affected
     */
    _handleDataChange(category) {
        const affectedPages = DashboardApp.CATEGORY_PAGE_MAP[category] || [];

        // Invalidate cached HTML for every affected page
        affectedPages.forEach(page => {
            delete this.pageCache[page];
        });

        // If the user is currently viewing an affected page, silently refresh it
        if (affectedPages.includes(this.currentPage)) {
            console.log(`[Realtime] "${category}" changed → refreshing "${this.currentPage}"`);
            this.reloadPage();
        }
    }
    
    /**
     * Setup navigation click handlers
     */
    setupNavigation() {
        var navItems = document.querySelectorAll('.nav-item[data-page]');
        var breadcrumbLinks = document.querySelectorAll('.breadcrumb a[data-page]');
        var dropdownLinks = document.querySelectorAll('.dropdown-menu-item[data-page]');
        
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                var page = item.dataset.page;
                this.loadPage(page);
                
                // Update active state
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
                
                // Close mobile menu if open
                if (window.innerWidth <= 1024) {
                    this.closeMobileMenu();
                }
            });
        });
        
        breadcrumbLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                var page = link.dataset.page;
                this.loadPage(page);
            });
        });

        // Dropdown menu item navigation
        dropdownLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                var page = link.dataset.page;
                this.loadPage(page);

                // Update sidebar active state
                navItems.forEach(nav => nav.classList.remove('active'));
                var matchingSidebarItem = document.querySelector('.nav-item[data-page="' + page + '"]');
                if (matchingSidebarItem) matchingSidebarItem.classList.add('active');

                // Close dropdown
                this.closeProfileDropdown();
            });
        });
    }
    
    /**
     * Load a page with lazy loading
     * @param {string} pageName - The page to load
     * @param {boolean} useCache - Whether to use cached version
     */
    async loadPage(pageName, useCache = true) {
        // Save sub-tab state before navigating away
        this.saveSubTabState();

        // Check cache first
        if (useCache && this.pageCache[pageName]) {
            this.renderPage(this.pageCache[pageName], pageName);
            return;
        }
        
        // Show loading spinner
        this.showLoading();
        
        try {
            // Map page names to actual file paths
            var pageMap = {
                'home': '../modules/dashboard/home.php',
                'roster': '../modules/dashboard/roster.php',
                'employees': '../modules/inventory/employees.php',
                'equipment': '../modules/inventory/equipment.php',
                'computer': '../modules/inventory/equipment.php',
                'printer': '../modules/inventory/equipment.php',
                'software': '../modules/inventory/software.php',
                'otherequipment': '../modules/inventory/equipment.php',
                'organization': '../modules/organization/organization.php',
                'divisions': '../modules/organization/organization.php',
                'sections': '../modules/organization/organization.php',
                'units': '../modules/organization/organization.php',
                'maintenance-schedule': '../modules/maintenance/maintenance-schedule.php',
                'maintenance-templates': '../modules/maintenance/maintenance-templates.php',
                'equipment-assignment': '../modules/maintenance/equipment-assignment.php',
                'perform-maintenance': '../modules/maintenance/perform-maintenance.php',
                'pending-approvals': '../modules/maintenance/pending-approvals.php',
                'maintenance-history': '../modules/maintenance/maintenance-history.php',
                'schedule': '../modules/maintenance/schedule.php',
                'history': '../modules/maintenance/history.php',
                'notifications': '../modules/maintenance/notifications.php',
                'equipment-summary': '../modules/reports/equipment-summary.php',
                'maintenance-summary': '../modules/reports/maintenance-summary.php',
                'audit-trail': '../modules/reports/audit-trail.php',
                'accounts': '../modules/users/accounts.php',
                'profile': '../modules/users/profile.php',
                'settings': '../modules/settings/settings.php'
            };
            
            var pageUrl = pageMap[pageName] || pageMap['home'];
            
            // Fetch the page content
            var response = await fetch(pageUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            var html = await response.text();
            
            // Cache the page
            this.pageCache[pageName] = html;
            
            // Render the page
            this.renderPage(html, pageName);
            
        } catch (error) {
            console.error('Error loading page:', error);
            this.showError('Failed to load page. Please try again.');
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Update sidebar active state to match the current page
     * @param {string} pageName - The page name to activate
     */
    updateSidebarActive(pageName) {
        var navItems = document.querySelectorAll('.nav-item[data-page]');
        navItems.forEach(nav => nav.classList.remove('active'));
        var match = document.querySelector(`.nav-item[data-page="${pageName}"]`);
        if (match) {
            match.classList.add('active');
        }
    }

    /**
     * Render the loaded page content
     * @param {string} html - The HTML content to render
     * @param {string} pageName - The page name for tracking
     */
    renderPage(html, pageName) {
        this.contentArea.innerHTML = html;
        this.currentPage = pageName;

        // Keep URL clean — persist page via sessionStorage only
        history.replaceState({ page: pageName }, '');
        sessionStorage.setItem('nia-active-page', pageName);
        
        // Update sidebar active state
        this.updateSidebarActive(pageName);

        // Update breadcrumb
        this.updateBreadcrumb(pageName);
        
        // Execute any scripts in the loaded content
        this.executeScripts();
        
        // Trigger page-specific initialization if exists
        if (window.initPage && typeof window.initPage === 'function') {
            window.initPage();
        }

        // Restore sub-tab state after page renders
        this.restoreSubTabState(pageName);
        
        // Add fade-in animation
        this.contentArea.classList.add('page-content');
        
        // Log page view (for analytics)
        this.logPageView(pageName);
    }
    
    /**
     * Update breadcrumb navigation
     * @param {string} pageName - Current page name
     */
    updateBreadcrumb(pageName) {
        var pageNames = {
            'home': 'Dashboard',
            'roster': 'Roster',
            'employees': 'Employees',
            'equipment': 'Equipment Inventory',
            'computer': 'Equipment Inventory',
            'printer': 'Equipment Inventory',
            'allinone': 'All-in-One PCs',
            'software': 'Software Licenses',
            'otherequipment': 'Equipment Inventory',
            'organization': 'Organization',
            'divisions': 'Organization',
            'sections': 'Organization',
            'units': 'Organization',
            'schedule-templates': 'Schedule Templates',
            'equipment-assignment': 'Equipment Assignment',
            'perform-maintenance': 'Perform Maintenance',
            'pending-approvals': 'Pending Approvals',
            'schedule': 'Maintenance Schedule',
            'history': 'Maintenance History',
            'notifications': 'Notifications',
            'equipment-summary': 'Equipment Summary',
            'maintenance-summary': 'Maintenance Summary',
            'audit-trail': 'Audit Trail',
            'accounts': 'Accounts',
            'profile': 'My Profile',
            'settings': 'System Settings'
        };
        
        var displayName = pageNames[pageName] || 'Dashboard';
        
        this.breadcrumb.innerHTML = `
            <a href="#" data-page="home"><i class="fas fa-home"></i></a>
            <span class="current">${displayName}</span>
        `;
        
        // Re-attach event listeners to new breadcrumb links
        var breadcrumbLinks = this.breadcrumb.querySelectorAll('a[data-page]');
        breadcrumbLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                var page = link.dataset.page;
                this.loadPage(page);
            });
        });
    }

    executeScripts() {
        var scripts = this.contentArea.querySelectorAll('script');
        scripts.forEach(oldScript => {
            var newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }
    
    /**
     * Show loading spinner
     */
    showLoading() {
        this.loadingSpinner.style.display = 'flex';
        this.contentArea.style.opacity = '0.5';
    }
    
    /**
     * Hide loading spinner
     */
    hideLoading() {
        this.loadingSpinner.style.display = 'none';
        this.contentArea.style.opacity = '1';
    }
    
    /**
     * Show error message
     * @param {string} message - Error message to display
     */
    showError(message) {
        this.contentArea.innerHTML = `
            <div class="error-container" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-exclamation-circle" style="font-size: 64px; color: #dc2626; margin-bottom: 20px;"></i>
                <h2 style="font-size: 24px; color: var(--text-dark); margin-bottom: 12px;">Oops! Something went wrong</h2>
                <p style="color: var(--text-medium); margin-bottom: 24px;">${message}</p>
                <button onclick="location.reload()" style="padding: 12px 24px; background: var(--primary-green); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-redo"></i> Refresh Page
                </button>
            </div>
        `;
    }
    

    setupMobileMenu() {
        var toggleIcon = this.mobileToggle.querySelector('i');
        
        this.mobileToggle.addEventListener('click', () => {
            this.sidebar.classList.toggle('active');
            toggleIcon.classList.toggle('fa-bars');
            toggleIcon.classList.toggle('fa-times');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!this.sidebar.contains(e.target) && !this.mobileToggle.contains(e.target)) {
                    this.closeMobileMenu();
                }
            }
        });
    }

    closeMobileMenu() {
        this.sidebar.classList.remove('active');
        var toggleIcon = this.mobileToggle.querySelector('i');
        toggleIcon.classList.add('fa-bars');
        toggleIcon.classList.remove('fa-times');
    }

    /**
     * Setup profile dropdown toggle & outside-click dismiss
     */
    setupProfileDropdown() {
        var dropdown = document.getElementById('userProfileDropdown');
        var toggle = document.getElementById('profileToggle');
        if (!dropdown || !toggle) return;

        // Toggle on click
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            var isOpen = dropdown.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                this.closeProfileDropdown();
            }
        });
    }

    closeProfileDropdown() {
        var dropdown = document.getElementById('userProfileDropdown');
        var toggle = document.getElementById('profileToggle');
        if (dropdown) dropdown.classList.remove('open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }
    
    /**
     * Perform global search
     * @param {string} query - Search query
     */
    async performSearch(query) {
        try {
            var response = await fetch(`../ajax/global_search.php?q=${encodeURIComponent(query)}`);
            var results = await response.json();
            
            // Display search results (you can customize this)
            console.log('Search results:', results);
            
        } catch (error) {
            console.error('Search error:', error);
        }
    }
    
    /**
     * Load initial page – restores last active page from URL hash or sessionStorage
     */
    loadInitialPage() {
        var page = 'home';

        // Restore last active page from sessionStorage
        var saved = sessionStorage.getItem('nia-active-page');
        if (saved && this.getPageMap()[saved]) {
            page = saved;
        }

        // Strip any leftover hash from the URL
        if (window.location.hash) {
            history.replaceState(null, '', window.location.pathname);
        }

        this.loadPage(page);
    }

    /**
     * Returns the page map for validation
     */
    getPageMap() {
        return {
            'home': true, 'roster': true, 'employees': true, 'equipment': true,
            'computer': true, 'printer': true, 'software': true, 'otherequipment': true,
            'organization': true, 'divisions': true, 'sections': true, 'units': true,
            'maintenance-schedule': true, 'maintenance-templates': true,
            'equipment-assignment': true, 'perform-maintenance': true,
            'pending-approvals': true, 'maintenance-history': true,
            'schedule': true, 'history': true, 'notifications': true,
            'equipment-summary': true, 'maintenance-summary': true,
            'audit-trail': true, 'accounts': true, 'profile': true, 'settings': true
        };
    }
    
    /**
     * Setup page animations
     */
    setupAnimations() {
        // Smooth animations on page load
        window.addEventListener('load', () => {
            var cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    }
    
    /**
     * Log page view for analytics
     * @param {string} pageName - Page name
     */
    logPageView(pageName) {
        // Page navigation is no longer logged to activity_log
        // Only meaningful user actions (create, update, delete, login, etc.) are logged server-side
    }
    
    /**
     * Reload current page (refresh) while preserving sub-tab state
     */
    reloadPage() {
        this.saveSubTabState();
        this.loadPage(this.currentPage, false);
    }

    /**
     * Save active sub-tab state to sessionStorage
     */
    saveSubTabState() {
        var state = {};

        // Equipment category tabs (e.g. category-computers, category-printers)
        var activeCategory = this.contentArea.querySelector('.category-content.active');
        if (activeCategory && activeCategory.id) {
            state.category = activeCategory.id.replace('category-', '');
        }

        // Equipment sub-tabs (e.g. subtab-systemunits, subtab-monitors)
        var activeSubTab = this.contentArea.querySelector('.sub-tab-content.active');
        if (activeSubTab && activeSubTab.id) {
            state.subtab = activeSubTab.id.replace('subtab-', '');
        }

        // Organization tabs (data-tab attribute)
        var activeOrgTab = this.contentArea.querySelector('.org-tab.active');
        if (activeOrgTab && activeOrgTab.dataset.tab) {
            state.orgTab = activeOrgTab.dataset.tab;
        }

        // Maintenance division-view sub-tabs
        var activeDvPanel = this.contentArea.querySelector('.dv-panel.dv-panel-active');
        if (activeDvPanel && activeDvPanel.id) {
            state.dvPanel = activeDvPanel.id;
        }

        if (Object.keys(state).length > 0) {
            sessionStorage.setItem('nia-subtab-state', JSON.stringify(state));
        }
    }

    /**
     * Restore sub-tab state from sessionStorage after page renders
     */
    restoreSubTabState(pageName) {
        var raw = sessionStorage.getItem('nia-subtab-state');
        if (!raw) return;

        try {
            var state = JSON.parse(raw);
        } catch (e) { return; }

        // Clear after reading so it only applies once
        sessionStorage.removeItem('nia-subtab-state');

        // Use requestAnimationFrame to ensure DOM is ready
        requestAnimationFrame(() => {
            // Equipment category tabs
            if (state.category) {
                var catBtn = this.contentArea.querySelector('#categoryTabs .toggle-btn');
                var catBtns = this.contentArea.querySelectorAll('#categoryTabs .toggle-btn');
                var targetCat = document.getElementById('category-' + state.category);
                if (targetCat) {
                    this.contentArea.querySelectorAll('.category-content').forEach(c => c.classList.remove('active'));
                    catBtns.forEach(b => b.classList.remove('active'));
                    targetCat.classList.add('active');
                    // Find and activate the matching button
                    catBtns.forEach(b => {
                        if (b.getAttribute('onclick') && b.getAttribute('onclick').includes("'" + state.category + "'")) {
                            b.classList.add('active');
                        }
                    });
                }
            }

            // Equipment sub-tabs
            if (state.subtab) {
                var subBtns = this.contentArea.querySelectorAll('.subtoggle-btn');
                var targetSub = document.getElementById('subtab-' + state.subtab);
                if (targetSub) {
                    this.contentArea.querySelectorAll('.sub-tab-content').forEach(c => c.classList.remove('active'));
                    subBtns.forEach(b => b.classList.remove('active'));
                    targetSub.classList.add('active');
                    subBtns.forEach(b => {
                        if (b.getAttribute('onclick') && b.getAttribute('onclick').includes("'" + state.subtab + "'")) {
                            b.classList.add('active');
                        }
                    });
                }
            }

            // Organization tabs
            if (state.orgTab) {
                var orgTabs = this.contentArea.querySelectorAll('#orgTabs .org-tab');
                var orgPanels = this.contentArea.querySelectorAll('.org-tab-panel');
                var targetOrgPanel = document.getElementById('panel-' + state.orgTab);
                if (targetOrgPanel) {
                    orgTabs.forEach(t => t.classList.remove('active'));
                    orgPanels.forEach(p => p.classList.remove('active'));
                    targetOrgPanel.classList.add('active');
                    orgTabs.forEach(t => {
                        if (t.dataset.tab === state.orgTab) t.classList.add('active');
                    });
                }
            }

            // Maintenance division-view sub-tabs
            if (state.dvPanel) {
                // Determine which type based on panel id prefix
                if (state.dvPanel.includes('Sched') && typeof switchSchedSubtab === 'function') {
                    var tab = state.dvPanel.replace('dvSchedPanel', '').toLowerCase();
                    switchSchedSubtab(tab);
                } else if (state.dvPanel.includes('Hist') && typeof switchHistSubtab === 'function') {
                    var tab = state.dvPanel.replace('dvHistPanel', '').toLowerCase();
                    switchHistSubtab(tab);
                }
            }
        });
    }
    
    /**
     * Clear page cache
     */
    clearCache() {
        this.pageCache = {};
    }
}

// Initialize the dashboard app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardApp = new DashboardApp();
});

// Utility function to reload page from anywhere
function reloadCurrentPage() {
    if (window.dashboardApp) {
        window.dashboardApp.reloadPage();
    }
}

// Utility function to load specific page from anywhere
function navigateToPage(pageName) {
    if (window.dashboardApp) {
        window.dashboardApp.loadPage(pageName);
    }
}

// Keyboard shortcut: Escape closes dropdown
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var dropdown = document.getElementById('userProfileDropdown');
        if (dropdown && dropdown.classList.contains('open')) {
            dropdown.classList.remove('open');
            var toggle = document.getElementById('profileToggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    }
});

        // Update date and time
        function updateDateTime() {
            var now = new Date();
            
            // Format date: February 09, 2026
            var dateOptions = { 
                year: 'numeric', 
                month: 'long', 
                day: '2-digit',
                weekday: 'long'
            };
            var dateString = now.toLocaleDateString('en-US', dateOptions);
            
            // Format time: 10:30:45 AM
            var timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            var timeString = now.toLocaleTimeString('en-US', timeOptions);
            
            // Update the display
            document.getElementById('currentDate').textContent = dateString;
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Update immediately
        updateDateTime();
        
        // Update every second
        setInterval(updateDateTime, 1000);