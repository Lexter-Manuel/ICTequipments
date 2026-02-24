<?php
// modules/reports/audit-trail.php
require_once '../../config/database.php';
?>

<style>
.audit-container { animation: fadeInUp 0.4s ease-out; }
.audit-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
}
.audit-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.audit-header h2 i { color: var(--primary-green); }

/* Filters */
.audit-filters {
    background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md); padding: 1.25rem; margin-bottom: 1.5rem;
}
.filter-row { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-medium); }
.filter-group input, .filter-group select {
    padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md);
    font-size: 0.825rem; background: #fff; min-width: 140px;
    transition: border-color 0.2s;
}
.filter-group input:focus, .filter-group select:focus { border-color: var(--primary-green); outline: none; }
.filter-btn {
    padding: 0.5rem 1rem; border-radius: var(--radius-md); border: none; font-weight: 600;
    font-size: 0.825rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 0.35rem;
}
.filter-btn.primary { background: var(--primary-green); color: #fff; }
.filter-btn.primary:hover { background: var(--primary-dark); }
.filter-btn.secondary { background: #f3f4f6; color: var(--text-medium); }
.filter-btn.secondary:hover { background: #e5e7eb; }

/* Table */
.audit-table-wrap {
    background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md); overflow: hidden;
}
.audit-table { width: 100%; border-collapse: collapse; }
.audit-table thead th {
    padding: 0.75rem 1rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em;
    font-weight: 700; color: var(--text-medium); background: #f9fafb; border-bottom: 2px solid var(--border-color);
    text-align: left; white-space: nowrap;
}
.audit-table tbody td {
    padding: 0.65rem 1rem; font-size: 0.825rem; color: var(--text-dark); border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.audit-table tbody tr:hover { background: #f9fafb; }
.audit-table tbody tr:last-child td { border-bottom: none; }

/* Badges */
.action-badge {
    padding: 0.2rem 0.6rem; border-radius: 8px; font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap;
}
.action-badge.login { background: #dbeafe; color: #1e40af; }
.action-badge.create { background: #dcfce7; color: #166534; }
.action-badge.update { background: #fef3c7; color: #92400e; }
.action-badge.delete { background: #fef2f2; color: #991b1b; }
.action-badge.other { background: #f3e8ff; color: #6b21a8; }

.module-badge {
    padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.65rem; font-weight: 600;
    background: #f3f4f6; color: var(--text-medium);
}

.success-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.success-dot.yes { background: #22c55e; }
.success-dot.no { background: #ef4444; }

/* Pagination */
.audit-pagination {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-top: 1px solid var(--border-color);
}
.audit-pagination .page-info { font-size: 0.8rem; color: var(--text-medium); }
.audit-pagination .page-btns { display: flex; gap: 0.25rem; }
.page-btn {
    padding: 0.35rem 0.65rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm);
    background: #fff; color: var(--text-dark); font-size: 0.8rem; cursor: pointer; transition: all 0.2s;
}
.page-btn:hover { background: #f3f4f6; }
.page-btn.active { background: var(--primary-green); color: #fff; border-color: var(--primary-green); }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Empty state */
.audit-empty { text-align: center; padding: 3rem; color: var(--text-light); }
.audit-empty i { font-size: 3rem; color: var(--neutral-300); margin-bottom: 0.75rem; display: block; }

/* Responsive */
@media (max-width: 768px) {
    .filter-row { flex-direction: column; }
    .filter-group { width: 100%; }
    .filter-group input, .filter-group select { width: 100%; }
    .audit-table-wrap { overflow-x: auto; }
}
</style>

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
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="auditSearch" placeholder="Search logs..." onkeyup="if(event.key==='Enter') loadAuditTrail()">
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
var auditTotalPages = 1;

function loadAuditTrail(page) {
    page = page || 1;
    auditCurrentPage = page;

    var params = new URLSearchParams({
        page: page,
        per_page: 25,
        search: document.getElementById('auditSearch').value,
        action: document.getElementById('auditAction').value,
        module: document.getElementById('auditModule').value,
        date_from: document.getElementById('auditDateFrom').value,
        date_to: document.getElementById('auditDateTo').value,
    });

    var tbody = document.getElementById('auditTableBody');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

    fetch('../ajax/get_audit_trail.php?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (!resp.success) {
                tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-exclamation-circle"></i><p>Error loading data</p></div></td></tr>';
                return;
            }

            // Populate filter dropdowns (first load)
            if (resp.filters) {
                populateFilterDropdown('auditAction', resp.filters.actions);
                populateFilterDropdown('auditModule', resp.filters.modules);
            }

            var data = resp.data;
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-clipboard-check"></i><p>No activity logs found</p></div></td></tr>';
                document.getElementById('auditPagination').style.display = 'none';
                return;
            }

            var html = '';
            data.forEach(function(row) {
                var actionClass = getActionClass(row.action);
                var dt = new Date(row.timestamp);
                var dateStr = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                var timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                html += '<tr>';
                html += '<td style="white-space:nowrap;"><div style="font-weight:600;font-size:0.8rem;">' + dateStr + '</div><div style="font-size:0.7rem;color:var(--text-light);font-family:var(--font-mono);">' + timeStr + '</div></td>';
                html += '<td><div style="font-weight:600;font-size:0.8rem;">' + escapeHtml(row.user_name || 'System') + '</div><div style="font-size:0.7rem;color:var(--text-light);">' + escapeHtml(row.email) + '</div></td>';
                html += '<td><span class="action-badge ' + actionClass + '">' + escapeHtml(row.action) + '</span></td>';
                html += '<td>' + (row.module ? '<span class="module-badge">' + escapeHtml(row.module) + '</span>' : '<span style="color:var(--text-light);font-size:0.75rem;">—</span>') + '</td>';
                html += '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + escapeHtml(row.description || '') + '">' + escapeHtml(row.description || '—') + '</td>';
                html += '<td style="font-family:var(--font-mono);font-size:0.75rem;">' + escapeHtml(row.ip_address) + '</td>';
                html += '<td><span class="success-dot ' + (row.success == 1 ? 'yes' : 'no') + '"></span></td>';
                html += '</tr>';
            });

            tbody.innerHTML = html;

            // Pagination
            var pag = resp.pagination;
            auditTotalPages = pag.total_pages;
            var pageInfo = document.getElementById('pageInfo');
            var start = (pag.page - 1) * pag.per_page + 1;
            var end = Math.min(pag.page * pag.per_page, pag.total);
            pageInfo.textContent = 'Showing ' + start + '-' + end + ' of ' + pag.total + ' entries';

            var pagBtns = document.getElementById('pageBtns');
            var btnHtml = '';
            btnHtml += '<button class="page-btn" onclick="loadAuditTrail(' + Math.max(1, pag.page - 1) + ')" ' + (pag.page <= 1 ? 'disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';
            
            var startP = Math.max(1, pag.page - 2);
            var endP = Math.min(pag.total_pages, pag.page + 2);
            for (var i = startP; i <= endP; i++) {
                btnHtml += '<button class="page-btn ' + (i == pag.page ? 'active' : '') + '" onclick="loadAuditTrail(' + i + ')">' + i + '</button>';
            }
            
            btnHtml += '<button class="page-btn" onclick="loadAuditTrail(' + Math.min(pag.total_pages, pag.page + 1) + ')" ' + (pag.page >= pag.total_pages ? 'disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
            pagBtns.innerHTML = btnHtml;

            document.getElementById('auditPagination').style.display = 'flex';
        })
        .catch(function(e) {
            console.error('Audit trail error:', e);
            tbody.innerHTML = '<tr><td colspan="7"><div class="audit-empty"><i class="fas fa-exclamation-triangle"></i><p>Failed to load audit trail</p></div></td></tr>';
        });
}

function populateFilterDropdown(id, options) {
    var sel = document.getElementById(id);
    var current = sel.value;
    // Keep first option
    while (sel.options.length > 1) sel.remove(1);
    options.forEach(function(opt) {
        var o = document.createElement('option');
        o.value = opt;
        o.textContent = opt;
        sel.appendChild(o);
    });
    sel.value = current;
}

function resetAuditFilters() {
    document.getElementById('auditSearch').value = '';
    document.getElementById('auditAction').value = '';
    document.getElementById('auditModule').value = '';
    document.getElementById('auditDateFrom').value = '';
    document.getElementById('auditDateTo').value = '';
    loadAuditTrail(1);
}

function getActionClass(action) {
    var a = (action || '').toLowerCase();
    if (a.indexOf('login') !== -1 || a.indexOf('logout') !== -1) return 'login';
    if (a.indexOf('create') !== -1 || a.indexOf('add') !== -1 || a.indexOf('insert') !== -1) return 'create';
    if (a.indexOf('update') !== -1 || a.indexOf('edit') !== -1 || a.indexOf('modify') !== -1) return 'update';
    if (a.indexOf('delete') !== -1 || a.indexOf('remove') !== -1) return 'delete';
    return 'other';
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text || ''));
    return div.innerHTML;
}

function exportAuditTrail() {
    var params = new URLSearchParams({
        page: 1,
        per_page: 10000,
        search: document.getElementById('auditSearch').value,
        action: document.getElementById('auditAction').value,
        module: document.getElementById('auditModule').value,
        date_from: document.getElementById('auditDateFrom').value,
        date_to: document.getElementById('auditDateTo').value,
    });

    fetch('../ajax/get_audit_trail.php?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (!resp.success || !resp.data.length) {
                alert('No data to export');
                return;
            }

            var csv = 'Timestamp,User,Email,Action,Module,Description,IP Address,Status\n';
            resp.data.forEach(function(row) {
                csv += '"' + row.timestamp + '","' + (row.user_name || 'System') + '","' + row.email + '","' + row.action + '","' + (row.module || '') + '","' + (row.description || '').replace(/"/g, '""') + '","' + row.ip_address + '","' + (row.success == 1 ? 'Success' : 'Failed') + '"\n';
            });

            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'audit_trail_' + new Date().toISOString().slice(0, 10) + '.csv';
            link.click();
        });
}

// Load on init
loadAuditTrail();
</script>
