/**
 * utils.js — Shared utility functions for the NIA ICT Inventory System
 * Loaded globally via dashboard.php — available to all pages
 */

// ============================================================
// HTML ESCAPING
// ============================================================

/**
 * Safely escape HTML entities to prevent XSS
 * @param {*} text - The text to escape
 * @returns {string} Escaped HTML string
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================================
// PAGINATION HELPERS
// ============================================================

/**
 * Calculate a smart pagination range with ellipsis
 * @param {number} current - Current page number
 * @param {number} total - Total number of pages
 * @returns {Array} Array of page numbers and '...' strings
 */
function getPaginationRange(current, total) {
    if (total <= 7) return Array.from({ length: total }, function(_, i) { return i + 1; });
    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
    return [1, '...', current - 1, current, current + 1, '...', total];
}

/**
 * Render pagination controls into a container element
 * @param {string} containerId - ID of the pagination container element
 * @param {number} currentPage - Current active page
 * @param {number} totalPages - Total number of pages
 * @param {string} goToFnName - Name of the global function to call on page click
 */
function renderPaginationControls(containerId, currentPage, totalPages, goToFnName) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var html = '<button class="page-btn" onclick="' + goToFnName + '(' + (currentPage - 1) + ')" ' +
        (currentPage === 1 ? 'disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';

    getPaginationRange(currentPage, totalPages).forEach(function(p) {
        if (p === '...') {
            html += '<span class="page-ellipsis">&hellip;</span>';
        } else {
            html += '<button class="page-btn ' + (p === currentPage ? 'active' : '') + '" onclick="' +
                goToFnName + '(' + p + ')">' + p + '</button>';
        }
    });

    html += '<button class="page-btn" onclick="' + goToFnName + '(' + (currentPage + 1) + ')" ' +
        (currentPage === totalPages ? 'disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';

    container.innerHTML = html;
}

/**
 * Update the record count display element
 * @param {string} elementId - ID of the count display element
 * @param {number} start - Start index (1-based)
 * @param {number} end - End index
 * @param {number} total - Total records
 * @param {string} [label='record'] - Label for the items (e.g. 'printer', 'record')
 */
function updateRecordCount(elementId, start, end, total, label) {
    var el = document.getElementById(elementId);
    if (!el) return;
    label = label || 'record';
    var plural = total !== 1 ? '(s)' : '';
    el.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start) + '&ndash;' + end +
        '</strong> of <strong>' + total + '</strong> ' + label + plural;
}

// ============================================================
// TABLE FILTER & PAGINATION FACTORY
// ============================================================

/**
 * Create a reusable table filter/pagination controller
 * @param {Object} config
 * @param {string} config.tableBodyId - ID of the tbody element
 * @param {string} config.rowSelector - CSS selector for data rows (e.g. 'tr[data-printer-id]')
 * @param {string} config.searchInputId - ID of the search input
 * @param {string} config.statusFilterId - ID of the status filter select (optional)
 * @param {string} config.perPageSelectId - ID of the per-page select element
 * @param {string} config.recordCountId - ID of the record count display element
 * @param {string} config.paginationId - ID of the pagination container
 * @param {string} config.recordLabel - Label for records (e.g. 'printer')
 * @param {Function} config.filterFn - Custom filter function(row, searchTerm, statusFilter) => boolean
 * @returns {Object} Controller with filter(), changePage(), changePerPage() methods
 */
function createTableController(config) {
    var state = {
        currentPage: 1,
        perPage: 25,
        filteredRows: []
    };
    var goToFnName = 'tableCtrl_' + config.tableBodyId + '_goTo';

    // Register global goTo function
    window[goToFnName] = function(page) {
        var totalPages = Math.max(1, Math.ceil(state.filteredRows.length / state.perPage));
        if (page < 1 || page > totalPages) return;
        state.currentPage = page;
        controller.applyState();
    };

    var controller = {
        applyState: function() {
            var searchEl = document.getElementById(config.searchInputId);
            var statusEl = config.statusFilterId ? document.getElementById(config.statusFilterId) : null;
            var searchTerm = searchEl ? searchEl.value.toLowerCase() : '';
            var statusFilter = statusEl ? statusEl.value : '';
            var allRows = Array.from(document.querySelectorAll('#' + config.tableBodyId + ' ' + config.rowSelector));

            state.filteredRows = allRows.filter(function(row) {
                return config.filterFn(row, searchTerm, statusFilter);
            });

            var total = state.filteredRows.length;
            var totalPages = Math.max(1, Math.ceil(total / state.perPage));
            if (state.currentPage > totalPages) state.currentPage = totalPages;

            var start = (state.currentPage - 1) * state.perPage;
            var end = Math.min(start + state.perPage, total);

            allRows.forEach(function(r) { r.style.display = 'none'; });
            state.filteredRows.forEach(function(row, idx) {
                row.style.display = (idx >= start && idx < end) ? '' : 'none';
            });

            updateRecordCount(config.recordCountId, total === 0 ? 0 : start + 1, end, total, config.recordLabel);
            renderPaginationControls(config.paginationId, state.currentPage, totalPages, goToFnName);
        },

        filter: function() {
            state.currentPage = 1;
            controller.applyState();
        },

        changePerPage: function() {
            var selectEl = document.getElementById(config.perPageSelectId);
            state.perPage = parseInt(selectEl.value);
            state.currentPage = 1;
            controller.applyState();
        },

        getState: function() {
            return state;
        }
    };

    return controller;
}

// ============================================================
// GENERIC TAB/TOGGLE SWITCHING
// ============================================================

/**
 * Switch active state among sibling toggle buttons and their content panels
 * @param {string} activeId - The ID suffix of the content panel to show
 * @param {HTMLElement} clickedBtn - The button that was clicked
 * @param {string} btnContainerSelector - CSS selector for the button container
 * @param {string} contentPrefix - ID prefix for content panels
 * @param {string} [btnClass='toggle-btn'] - Class name for the buttons
 */
function switchToggle(activeId, clickedBtn, btnContainerSelector, contentPrefix) {
    // Deactivate all buttons in container
    var container = clickedBtn.closest(btnContainerSelector);
    if (container) {
        container.querySelectorAll('button').forEach(function(btn) {
            btn.classList.remove('active');
        });
    }
    clickedBtn.classList.add('active');

    // Hide all related panels, show target
    var parent = document.getElementById(contentPrefix + activeId);
    if (parent) {
        var siblings = parent.parentElement.querySelectorAll('[id^="' + contentPrefix + '"]');
        siblings.forEach(function(el) { el.classList.remove('active'); });
        parent.classList.add('active');
    }
}

// ============================================================
// RELOAD HELPERS
// ============================================================

/**
 * Reload the current page via the dashboard SPA router, or fall back to hard reload
 * @param {string} [pageName] - Optional page name to load
 */
function reloadCurrentPage(pageName) {
    if (window.dashboard) {
        window.dashboard.pageCache = {};
        if (pageName) {
            window.dashboard.loadPage(pageName);
        } else if (window.dashboard.currentPage) {
            window.dashboard.loadPage(window.dashboard.currentPage);
        } else {
            location.reload();
        }
    } else {
        location.reload();
    }
}
