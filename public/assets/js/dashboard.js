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
        this.setupSearch();
        this.loadInitialPage();
        this.setupAnimations();
    }
    
    /**
     * Setup navigation click handlers
     */
    setupNavigation() {
        const navItems = document.querySelectorAll('.nav-item[data-page]');
        const breadcrumbLinks = document.querySelectorAll('.breadcrumb a[data-page]');
        
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = item.dataset.page;
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
                const page = link.dataset.page;
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
            const pageMap = {
                'home': '../modules/dashboard/home.php',
                'employees': '../modules/inventory/employees.php',
                'computer': '../modules/inventory/computer.php',
                'printer': '../modules/inventory/printer.php',
                'software': '../modules/inventory/software.php',
                'divisions': '../modules/organization/divisions.php',
                'sections': '../modules/organization/sections.php',
                'units': '../modules/organization/units.php',
                'schedule': '../modules/maintenance/schedule.php',
                'history': '../modules/maintenance/history.php',
                'notifications': '../modules/maintenance/notifications.php',
                'inventory-report': '../modules/reports/inventory_report.php',
                'analytics': '../modules/reports/analytics.php',
                'accounts': '../modules/users/accounts.php',
                'settings': '../modules/settings/settings.php'
            };
            
            const pageUrl = pageMap[pageName] || pageMap['home'];
            
            // Fetch the page content
            const response = await fetch(pageUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const html = await response.text();
            
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
     * Render the loaded page content
     * @param {string} html - The HTML content to render
     * @param {string} pageName - The page name for tracking
     */
    renderPage(html, pageName) {
        this.contentArea.innerHTML = html;
        this.currentPage = pageName;
        
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
        const pageNames = {
            'home': 'Dashboard',
            'employees': 'Employees',
            'systemunits': 'System Units',
            'monitors': 'Monitors',
            'printers': 'Printers',
            'allinone': 'All-in-One PCs',
            'software': 'Software Licenses',
            'divisions': 'Divisions',
            'sections': 'Sections',
            'schedule': 'Maintenance Schedule',
            'history': 'Maintenance History',
            'notifications': 'Notifications',
            'inventory-report': 'Inventory Report',
            'analytics': 'Analytics',
            'accounts': 'Accounts',
            'settings': 'System Settings'
        };
        
        const displayName = pageNames[pageName] || 'Dashboard';
        
        this.breadcrumb.innerHTML = `
            <a href="#" data-page="home"><i class="fas fa-home"></i></a>
            <span>/</span>
            <span class="current">${displayName}</span>
        `;
        
        // Re-attach event listeners to new breadcrumb links
        const breadcrumbLinks = this.breadcrumb.querySelectorAll('a[data-page]');
        breadcrumbLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                this.loadPage(page);
            });
        });
    }
    
    /**
     * Execute scripts in loaded content
     */
    executeScripts() {
        const scripts = this.contentArea.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
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
    
    /**
     * Setup mobile menu toggle
     */
    setupMobileMenu() {
        const toggleIcon = this.mobileToggle.querySelector('i');
        
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
    
    /**
     * Close mobile menu
     */
    closeMobileMenu() {
        this.sidebar.classList.remove('active');
        const toggleIcon = this.mobileToggle.querySelector('i');
        toggleIcon.classList.add('fa-bars');
        toggleIcon.classList.remove('fa-times');
    }
    
    /**
     * Setup global search functionality
     */
    setupSearch() {
        const searchInput = document.getElementById('globalSearch');
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    this.performSearch(query);
                }, 500); // Debounce search
            }
        });
    }
    
    /**
     * Perform global search
     * @param {string} query - Search query
     */
    async performSearch(query) {
        try {
            const response = await fetch(`../ajax/global_search.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            
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
            const cards = document.querySelectorAll('.stat-card');
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
            const data = new FormData();
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