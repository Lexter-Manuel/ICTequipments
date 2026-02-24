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
    
    init() {
        this.setupNavigation();
        this.setupMobileMenu();
        this.loadInitialPage();
        this.setupAnimations();
    }
    
    /**
     * Setup navigation click handlers
     */
    setupNavigation() {
        var navItems = document.querySelectorAll('.nav-item[data-page]');
        var breadcrumbLinks = document.querySelectorAll('.breadcrumb a[data-page]');
        
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
    }
    
    /**
     * Load a page with lazy loading
     * @param {string} pageName - The page to load
     * @param {boolean} useCache - Whether to use cached version
     */
    async loadPage(pageName, useCache = true) {
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
                'computer': '../modules/inventory/computer.php',
                'printer': '../modules/inventory/printer.php',
                'software': '../modules/inventory/software.php',
                'otherequipment': '../modules/inventory/other_equipment.php',
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
            'computer': 'Computers',
            'printer': 'Printers',
            'allinone': 'All-in-One PCs',
            'software': 'Software Licenses',
            'otherequipment': 'Other ICT Equipment',
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
     * Load initial page (dashboard home)
     */
    loadInitialPage() {
        this.loadPage('home');
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
        // Send to analytics endpoint
        if (navigator.sendBeacon) {
            var data = new FormData();
            data.append('page', pageName);
            data.append('timestamp', new Date().toISOString());
            navigator.sendBeacon('../ajax/log_activity.php', data);
        }
    }
    
    /**
     * Reload current page (refresh)
     */
    reloadPage() {
        this.loadPage(this.currentPage, false);
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