<?php
// modules/reports/audit-trail.php
require_once '../../config/database.php';
?>

<div class="audit-container">
    <div class="audit-header">
        <h2><i class="fas fa-shield-alt"></i> Audit Trail</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button class="filter-btn secondary" onclick="exportAuditTrail()">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="audit-filters">
        <div class="filter-row">
            <div class="filter-group wide">
                <label>Search description / user</label>
                <input type="text" id="auditSearch" placeholder="e.g. Juan Dela Cruz..."
                       onkeyup="if(event.key==='Enter') loadAuditTrail()">
            </div>
            <div class="filter-group">
                <label>Action</label>
                <select id="auditAction">
                    <option value="">All Actions</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Module</label>
                <select id="auditModule">
                    <option value="">All Modules</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="auditSuccess">
                    <option value="">All</option>
                    <option value="1">Success</option>
                    <option value="0">Failed</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="auditDateFrom">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="auditDateTo">
            </div>
            <button class="filter-btn primary" onclick="loadAuditTrail()">
                <i class="fas fa-search"></i> Filter
            </button>
            <button class="filter-btn secondary" onclick="resetAuditFilters()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="audit-table-wrap">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="auditTableBody">
                <tr>
                    <td colspan="7">
                        <div class="audit-empty">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading audit trail...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="audit-pagination" id="auditPagination" style="display: none;">
            <div class="page-info" id="pageInfo"></div>
            <div class="page-btns" id="pageBtns"></div>
        </div>
    </div>
</div>

<script>
var auditCurrentPage = 1;
var auditTotalPages  = 1;

// Human-readable labels for each ACTION_* constant
var ACTION_LABELS = {
    LOGIN:          'Login',
    LOGOUT:         'Logout',
    LOGIN_FAILED:   'Login Failed',
    PASSWORD_RESET: 'Password Reset',
    CREATE:         'Create',
    UPDATE:         'Update',
    DELETE:         'Delete',
    RESTORE:        'Restore',
    EXPORT:         'Export',
    IMPORT:         'Import',
    VIEW:           'View',
};

// Font Awesome icon per action
var ACTION_ICONS = {
    LOGIN:          'fa-right-to-bracket',
    LOGOUT:         'fa-right-from-bracket',
    LOGIN_FAILED:   'fa-lock',
    PASSWORD_RESET: 'fa-key',
    CREATE:         'fa-circle-plus',
    UPDATE:         'fa-pen-to-square',
    DELETE:         'fa-trash',
    RESTORE:        'fa-rotate-left',
    EXPORT:         'fa-file-export',
    IMPORT:         'fa-file-import',
    VIEW:           'fa-eye',
};

function loadAuditTrail(page) {
    page = page || 1;
    auditCurrentPage = page;

    var params = new URLSearchParams({
        page:      page,
        per_page:  25,
        search:    document.getElementById('auditSearch').value,
        action:    document.getElementById('auditAction').value,
        module:    document.getElementById('auditModule').value,
        success:   document.getElementById('auditSuccess').value,
        date_from: document.getElementById('auditDateFrom').value,
        date_to:   document.getElementById('auditDateTo').value,
    });

    var tbody = document.getElementById('auditTableBody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-light);"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

    fetch('../ajax/get_audit_trail.php?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (!resp.success) {
                tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-exclamation-circle"></i><p>Error loading data</p></div></td></tr>';
                return;
            }

            if (resp.filters) {
                populateActionDropdown('auditAction', resp.filters.actions);
                populateModuleDropdown('auditModule', resp.filters.modules);
            }

            var data = resp.data;
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-clipboard-check"></i><p>No activity logs found</p></div></td></tr>';
                document.getElementById('auditPagination').style.display = 'none';
                return;
            }

            var html = '';
            data.forEach(function(row) {
                var dt      = new Date(row.timestamp);
                var dateStr = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                var timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                var badgeClass  = 'badge-' + (ACTION_LABELS[row.action] ? row.action : 'default');
                var actionLabel = ACTION_LABELS[row.action] || row.action;
                var iconClass   = ACTION_ICONS[row.action] || 'fa-circle-dot';

                var isSuccess  = row.success == 1;
                var statusCls  = isSuccess ? 'success' : 'failed';
                var statusTxt  = isSuccess ? 'Success' : 'Failed';

                html += '<tr>';

                // Timestamp
                html += '<td style="white-space:nowrap;">'
                      +   '<div style="font-weight:600;font-size:0.8rem;">' + escapeHtml(dateStr) + '</div>'
                      +   '<div style="font-size:0.7rem;color:var(--text-light);font-family:monospace;">' + escapeHtml(timeStr) + '</div>'
                      + '</td>';

                // User
                html += '<td>'
                      +   '<div style="font-weight:600;font-size:0.8rem;">' + escapeHtml(row.user_name || 'System') + '</div>'
                      +   '<div style="font-size:0.7rem;color:var(--text-light);">' + escapeHtml(row.email || '') + '</div>'
                      + '</td>';

                // Action badge
                html += '<td>'
                      +   '<span class="action-badge ' + badgeClass + '">'
                      +     '<i class="fas ' + iconClass + '" style="font-size:0.6rem;"></i>'
                      +     escapeHtml(actionLabel)
                      +   '</span>'
                      + '</td>';

                // Module
                html += '<td>'
                      + (row.module
                            ? '<span class="module-badge">' + escapeHtml(row.module) + '</span>'
                            : '<span style="color:var(--text-light);font-size:0.75rem;">—</span>')
                      + '</td>';

                // Description
                html += '<td class="desc-cell" title="' + escapeHtml(row.description || '') + '">'
                      + escapeHtml(row.description || '—')
                      + '</td>';

                // IP
                html += '<td style="font-family:monospace;font-size:0.75rem;color:var(--text-medium);">'
                      + escapeHtml(row.ip_address || '—')
                      + '</td>';

                // Status
                html += '<td>'
                      +   '<span class="status-pill ' + statusCls + '">'
                      +     '<span class="dot"></span>' + statusTxt
                      +   '</span>'
                      + '</td>';

                html += '</tr>';
            });

            tbody.innerHTML = html;

            // Pagination
            var pag   = resp.pagination;
            auditTotalPages = pag.total_pages;
            var start = (pag.page - 1) * pag.per_page + 1;
            var end   = Math.min(pag.page * pag.per_page, pag.total);
            document.getElementById('pageInfo').textContent =
                'Showing ' + start + '–' + end + ' of ' + pag.total.toLocaleString() + ' entries';

            var btnHtml = '';
            btnHtml += '<button class="page-btn" onclick="loadAuditTrail(' + Math.max(1, pag.page - 1) + ')"'
                     + (pag.page <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';

            var startP = Math.max(1, pag.page - 2);
            var endP   = Math.min(pag.total_pages, pag.page + 2);
            for (var i = startP; i <= endP; i++) {
                btnHtml += '<button class="page-btn' + (i === pag.page ? ' active' : '')
                         + '" onclick="loadAuditTrail(' + i + ')">' + i + '</button>';
            }
            btnHtml += '<button class="page-btn" onclick="loadAuditTrail(' + Math.min(pag.total_pages, pag.page + 1) + ')"'
                     + (pag.page >= pag.total_pages ? ' disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
            document.getElementById('pageBtns').innerHTML = btnHtml;

            document.getElementById('auditPagination').style.display = 'flex';
        })
        .catch(function(e) {
            console.error('Audit trail error:', e);
            tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-exclamation-triangle"></i><p>Failed to load audit trail</p></div></td></tr>';
        });
}

// Populate Action dropdown with human-readable labels
function populateActionDropdown(id, rawValues) {
    var sel = document.getElementById(id);
    var cur = sel.value;
    while (sel.options.length > 1) sel.remove(1);
    rawValues.forEach(function(raw) {
        var o = document.createElement('option');
        o.value = raw;
        o.textContent = ACTION_LABELS[raw] || raw;
        sel.appendChild(o);
    });
    sel.value = cur;
}

function populateModuleDropdown(id, values) {
    var sel = document.getElementById(id);
    var cur = sel.value;
    while (sel.options.length > 1) sel.remove(1);
    values.forEach(function(v) {
        var o = document.createElement('option');
        o.value = v; o.textContent = v;
        sel.appendChild(o);
    });
    sel.value = cur;
}

function resetAuditFilters() {
    ['auditSearch','auditAction','auditModule','auditSuccess','auditDateFrom','auditDateTo']
        .forEach(function(id) { document.getElementById(id).value = ''; });
    loadAuditTrail(1);
}

function escapeHtml(text) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(text || ''));
    return d.innerHTML;
}

function exportAuditTrail() {
    var params = new URLSearchParams({
        page: 1, per_page: 10000,
        search:    document.getElementById('auditSearch').value,
        action:    document.getElementById('auditAction').value,
        module:    document.getElementById('auditModule').value,
        success:   document.getElementById('auditSuccess').value,
        date_from: document.getElementById('auditDateFrom').value,
        date_to:   document.getElementById('auditDateTo').value,
    });

    fetch('../ajax/get_audit_trail.php?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (!resp.success || !resp.data.length) { alert('No data to export'); return; }
            var csv = 'Timestamp,User,Email,Action,Module,Description,IP Address,Status\n';
            resp.data.forEach(function(row) {
                csv += [
                    '"' + row.timestamp + '"',
                    '"' + (row.user_name || 'System').replace(/"/g,'""') + '"',
                    '"' + (row.email || '').replace(/"/g,'""') + '"',
                    '"' + (ACTION_LABELS[row.action] || row.action) + '"',
                    '"' + (row.module || '').replace(/"/g,'""') + '"',
                    '"' + (row.description || '').replace(/"/g,'""') + '"',
                    '"' + (row.ip_address || '') + '"',
                    '"' + (row.success == 1 ? 'Success' : 'Failed') + '"',
                ].join(',') + '\n';
            });
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'audit_trail_' + new Date().toISOString().slice(0,10) + '.csv';
            a.click();
        });
}

loadAuditTrail();
</script>