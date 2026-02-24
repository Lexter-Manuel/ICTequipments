var HIST_API = `${BASE_URL}ajax/get_maintenance_history.php`;

var histCurrentPage = 1;
var histPerPage     = 10;

var activeHistDivisionId   = null;
var activeHistDivisionData = null;
var activeHistDivisionMeta = null;
var activeHistSubtab       = 'grouped';
var summaryTreeCache       = null;

var iconMap   = { 'System Unit': 'fa-desktop', 'Monitor': 'fa-tv', 'Printer': 'fa-print', 'Laptop': 'fa-laptop', 'All-in-One': 'fa-desktop' };
var typeClass = { 'Printer': 'type-printer', 'Monitor': 'type-monitor' };
var condMap   = { 'Excellent': 'mnt-badge-excellent', 'Good': 'mnt-badge-good', 'Fair': 'mnt-badge-fair', 'Poor': 'mnt-badge-poor' };
var condIcon  = { 'Excellent': 'fa-star', 'Good': 'fa-check-circle', 'Fair': 'fa-minus-circle', 'Poor': 'fa-times-circle' };

function getIcon(typeName)      { return iconMap[typeName] || 'fa-desktop'; }
function getTypeClass(typeName)  { return typeClass[typeName] || ''; }
function getCondClass(cond)      { return condMap[cond] || 'mnt-badge-good'; }
function escHtml(str)            { var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }

function formatDate(dateStr) {
    if (!dateStr) return '—';
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
function formatTime(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}
function formatDateISO(dateStr) {
    if (!dateStr) return '';
    return dateStr.substring(0, 10); // YYYY-MM-DD
}

async function apiFetch(params) {
    var qs = new URLSearchParams(params).toString();
    var resp = await fetch(`${HIST_API}?${qs}`);
    return resp.json();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        loadDetailedHistory();
    });
} else {
    // Already loaded (SPA / dynamic page injection)
    loadStats();
    loadDetailedHistory();
}

/* =========================================================
   STATS
   ========================================================= */
async function loadStats() {
    var dp = (typeof getHistDateParams === 'function') ? getHistDateParams() : { dateFrom: '', dateTo: '', rangeLabel: 'All Time' };
    var sectionUnit = document.getElementById('histSectionUnitFilter')?.value || '';
    try {
        var res = await apiFetch({ view: 'stats', dateFrom: dp.dateFrom, dateTo: dp.dateTo, sectionUnit: sectionUnit });
        if (res.success) {
            document.getElementById('statTotalRecords').textContent  = res.data.totalRecords;
            document.getElementById('statMaintained').textContent    = res.data.maintained;
            document.getElementById('statExcellentGood').textContent = res.data.excellentGood;
            document.getElementById('statPending').textContent       = res.data.pending;
        }
    } catch (e) {
        console.error('Failed to load stats:', e);
    }
}

/* =========================================================
   DETAILED VIEW — Paginated Table
   ========================================================= */
async function loadDetailedHistory(page = 1) {
    histCurrentPage = page;
    var limit  = histPerPage;
    var dp     = (typeof getHistDateParams === 'function') ? getHistDateParams() : { dateFrom: '', dateTo: '', rangeLabel: 'All Time' };
    var search = document.getElementById('histSearchInput')?.value || '';
    var sectionUnit = document.getElementById('histSectionUnitFilter')?.value || '';
    var tbody  = document.getElementById('histDetailedBody');

    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--text-light);">
        <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
        <div style="margin-top:10px;">Loading…</div></td></tr>`;

    // Also refresh stats when filters change
    loadStats();

    try {
        var res = await apiFetch({ view: 'detailed', dateFrom: dp.dateFrom, dateTo: dp.dateTo, search, page, limit, sectionUnit: sectionUnit });
        if (!res.success) throw new Error(res.message);

        var rows = res.data;
        var pag  = res.pagination;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--text-light);font-style:italic;">
                <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                No maintenance records found for the selected filters.</td></tr>`;
            document.getElementById('histRecordCount').textContent = 'No records found';
            document.getElementById('histPagination').innerHTML = '';
            return;
        }

        var html = '';
        rows.forEach(r => {
            var dateLabel = formatDate(r.maintenanceDate);
            var timeLabel = formatTime(r.maintenanceDate);
            var cond      = r.conditionRating || 'Good';
            var typeName  = r.type_name || 'System Unit';

            // Try to derive section/unit from location_name
            var locationPrimary = r.location_name || '—';

            html += `
            <tr>
                <td>
                    <div class="mnt-date-primary completed">${escHtml(dateLabel)}</div>
                    <div class="mnt-date-sub">${escHtml(timeLabel)}</div>
                </td>
                <td>
                    <div class="mnt-equip-cell">
                        <div class="mnt-equip-icon ${getTypeClass(typeName)}"><i class="fas ${getIcon(typeName)}"></i></div>
                        <div>
                            <div class="mnt-equip-name">${escHtml(r.brand)}</div>
                            <div class="mnt-equip-serial">SN: ${escHtml(r.serial)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="mnt-location-primary">${escHtml(locationPrimary)}</div>
                    ${r.owner_name ? `<div class="mnt-location-sub">${escHtml(r.owner_name)}</div>` : ''}
                </td>
                <td>
                    <div class="mnt-tech-name">${escHtml(r.technician || '—')}</div>
                </td>
                <td><span class="mnt-badge ${getCondClass(cond)}"><i class="fas fa-circle"></i> ${escHtml(cond)}</span></td>
                <td><button class="mnt-btn-report" onclick="viewReport(${r.recordId})"><i class="fas fa-file-pdf"></i> View</button></td>
            </tr>`;
        });

        tbody.innerHTML = html;

        // Footer
        var start = (pag.page - 1) * pag.limit + 1;
        var end   = Math.min(pag.page * pag.limit, pag.total);
        document.getElementById('histRecordCount').textContent = `Showing ${start}–${end} of ${pag.total} completed maintenance records`;

        // Pagination
        renderPagination(pag);

    } catch (e) {
        console.error('Failed to load detailed history:', e);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--color-danger);">
            <i class="fas fa-exclamation-triangle"></i> Failed to load data: ${escHtml(e.message)}</td></tr>`;
    }
}

function renderPagination(pag) {
    var container = document.getElementById('histPagination');
    if (!container) return;
    if (pag.totalPages <= 1) { container.innerHTML = ''; return; }
    renderPaginationControls('histPagination', pag.page, pag.totalPages, 'loadDetailedHistory');
}

function changeHistPerPage() {
    var sel = document.getElementById('histPerPageSelect');
    if (!sel) return;
    histPerPage = parseInt(sel.value);
    loadDetailedHistory(1);
}

/* =========================================================
   MAIN VIEW SWITCHER
   ========================================================= */
function switchHistoryView(view) {
    document.getElementById('history-detailed').className = view === 'detailed' ? 'd-block' : 'd-none';
    document.getElementById('history-summary').className  = view === 'summary'  ? 'd-block' : 'd-none';
    document.getElementById('btnHistoryDetailed').classList.toggle('active', view === 'detailed');
    document.getElementById('btnHistorySummary').classList.toggle('active',  view === 'summary');

    document.getElementById('histFilterBar').style.display = view === 'summary' ? 'none' : '';
    document.getElementById('histStatsBar').style.display  = view === 'summary' ? 'none' : '';

    if (view === 'summary') {
        backToDivisionsHistory();
        if (!summaryTreeCache) loadSummaryView();
    }
}

/* =========================================================
   SUMMARY VIEW — Division Cards
   ========================================================= */
async function loadSummaryView() {
    var grid = document.getElementById('histDivisionCardsGrid');
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-light);">
        <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
        <div style="margin-top:10px;">Loading division summaries…</div></div>`;

    try {
        var res = await apiFetch({ view: 'summary' });
        if (!res.success) throw new Error(res.message);

        summaryTreeCache = res.data;
        renderDivisionCards(res.data);
    } catch (e) {
        console.error('Failed to load summary:', e);
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--color-danger);">
            <i class="fas fa-exclamation-triangle"></i> Failed to load: ${escHtml(e.message)}</div>`;
    }
}

function renderDivisionCards(divisions) {
    var grid = document.getElementById('histDivisionCardsGrid');

    if (!divisions.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-light);">
            <i class="fas fa-building" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
            No divisions found.</div>`;
        return;
    }

    var html = '';
    divisions.forEach(div => {
        var s = div.stats;
        var compClass = s.compliance >= 80 ? 'mnt-compliance-high' : s.compliance >= 50 ? 'mnt-compliance-medium' : 'mnt-compliance-low';

        // Build section groups from children
        var bodyHtml = '';
        if (div.children && div.children.length) {
            div.children.forEach(section => {
                // A section can be type 2 (Section) with type 3 children (Units)
                if (section.children && section.children.length) {
                    bodyHtml += `<div class="mnt-section-group">
                        <div class="mnt-section-group-title">${escHtml(section.location_name)}</div>`;
                    section.children.forEach(unit => {
                        var us = unit.stats;
                        var statusClass = us.pending > 0 ? 'warn' : 'ok';
                        var statusIcon  = us.pending > 0 ? 'fa-clock' : 'fa-check-circle';
                        bodyHtml += `<div class="mnt-section-row">
                            <span class="mnt-section-name">${escHtml(unit.location_name)}</span>
                            <span class="mnt-section-status ${statusClass}"><i class="fas ${statusIcon}"></i> ${us.maintained}/${us.total}</span>
                        </div>`;
                    });
                    bodyHtml += `</div>`;
                } else {
                    // Section with no children (or it's a unit directly under division)
                    var ss = section.stats;
                    if (ss.total > 0) {
                        var statusClass = ss.pending > 0 ? 'warn' : 'ok';
                        var statusIcon  = ss.pending > 0 ? 'fa-clock' : 'fa-check-circle';
                        bodyHtml += `<div class="mnt-section-group">
                            <div class="mnt-section-row">
                                <span class="mnt-section-name">${escHtml(section.location_name)}</span>
                                <span class="mnt-section-status ${statusClass}"><i class="fas ${statusIcon}"></i> ${ss.maintained}/${ss.total}</span>
                            </div>
                        </div>`;
                    }
                }
            });
        }

        if (!bodyHtml) {
            bodyHtml = `<div style="text-align:center;padding:20px;color:var(--text-light);font-style:italic;">No equipment data</div>`;
        }

        html += `
        <div class="mnt-summary-card">
            <div class="mnt-summary-card-header">
                <h3 class="mnt-summary-card-title">${escHtml(div.location_name)}</h3>
                <span class="mnt-compliance-badge ${compClass}">${s.compliance}% Compliance</span>
            </div>
            <div class="mnt-summary-stats">
                <div class="mnt-summary-stat">
                    <div class="mnt-summary-stat-value total">${s.total}</div>
                    <div class="mnt-summary-stat-label">Total</div>
                </div>
                <div class="mnt-summary-stat">
                    <div class="mnt-summary-stat-value maintained">${s.maintained}</div>
                    <div class="mnt-summary-stat-label">Maintained</div>
                </div>
                <div class="mnt-summary-stat">
                    <div class="mnt-summary-stat-value pending">${s.pending}</div>
                    <div class="mnt-summary-stat-label">Pending</div>
                </div>
            </div>
            <div class="mnt-summary-body">${bodyHtml}</div>
            <div class="mnt-summary-card-footer">
                <button class="btn btn-outline-primary w-100 btn-sm" onclick="viewDivisionHistory(${div.location_id})">
                    <i class="fas fa-arrow-right"></i> View Division History
                </button>
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

/* =========================================================
   OPEN DIVISION DRILL-DOWN VIEW
   ========================================================= */
async function viewDivisionHistory(divisionId) {
    activeHistDivisionId = divisionId;

    // Find division meta from cache
    var divMeta = null;
    if (summaryTreeCache) {
        divMeta = summaryTreeCache.find(d => d.location_id == divisionId);
    }

    var divName = divMeta ? divMeta.location_name : 'Division';
    var divStats = divMeta ? divMeta.stats : { total: 0, maintained: 0, pending: 0, compliance: 0 };

    document.getElementById('histDivisionCardsGrid').style.display = 'none';
    document.getElementById('dvHistName').textContent       = divName;
    document.getElementById('dvHistBreadcrumb').textContent = divName;

    // Stat badges
    document.getElementById('dvHistBadges').innerHTML = `
        <span class="dv-stat-pill total"><i class="fas fa-layer-group"></i> ${divStats.total} Total</span>
        <span class="dv-stat-pill maintained"><i class="fas fa-check-circle"></i> ${divStats.maintained} Maintained</span>
        ${divStats.pending > 0 ? `<span class="dv-stat-pill pending"><i class="fas fa-hourglass-half"></i> ${divStats.pending} Pending</span>` : ''}
        <span class="dv-stat-pill compliance"><i class="fas fa-chart-line"></i> ${divStats.compliance}% Compliance</span>
    `;

    // Reset sub-tab
    activeHistSubtab = 'grouped';
    document.getElementById('dvHistTabGrouped').classList.add('active');
    document.getElementById('dvHistTabAll').classList.remove('active');
    document.getElementById('dvHistTabEmp').classList.remove('active');
    document.getElementById('dvHistPanelGrouped').classList.add('dv-panel-active');
    document.getElementById('dvHistPanelAll').classList.remove('dv-panel-active');
    document.getElementById('dvHistPanelEmployees').classList.remove('dv-panel-active');

    // Show loading in grouped panel
    document.getElementById('dvHistPanelGrouped').innerHTML = `
        <div style="text-align:center;padding:60px;color:var(--text-light);">
            <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
            <div style="margin-top:10px;">Loading division data…</div>
        </div>`;

    document.getElementById('divisionViewHistory').classList.add('dv-active');

    // Fetch division data
    try {
        var res = await apiFetch({ view: 'division', divisionId });
        if (!res.success) throw new Error(res.message);

        activeHistDivisionData = res.data;
        activeHistDivisionMeta = { name: divName, stats: divStats };

        // Populate unit filter
        var unitSel = document.getElementById('dvHistUnitFilter');
        unitSel.innerHTML = '<option value="">All Units</option>';
        (res.data.units || []).forEach(u => {
            var opt = document.createElement('option');
            opt.value = u;
            opt.textContent = u;
            unitSel.appendChild(opt);
        });

        renderHistoryDivision();

    } catch (e) {
        console.error('Failed to load division data:', e);
        document.getElementById('dvHistPanelGrouped').innerHTML = `
            <div class="dv-empty"><i class="fas fa-exclamation-triangle"></i>
            <h4>Error</h4><p>${escHtml(e.message)}</p></div>`;
    }
}

/* =========================================================
   BACK TO DIVISION CARDS
   ========================================================= */
function backToDivisionsHistory() {
    document.getElementById('histDivisionCardsGrid').style.display = '';
    document.getElementById('divisionViewHistory').classList.remove('dv-active');
    activeHistDivisionId   = null;
    activeHistDivisionData = null;
}

/* =========================================================
   SUBTAB SWITCHER
   ========================================================= */
function switchHistSubtab(tab) {
    activeHistSubtab = tab;
    document.getElementById('dvHistTabGrouped').classList.toggle('active',   tab === 'grouped');
    document.getElementById('dvHistTabAll').classList.toggle('active',        tab === 'all');
    document.getElementById('dvHistTabEmp').classList.toggle('active',        tab === 'employees');
    document.getElementById('dvHistPanelGrouped').classList.toggle('dv-panel-active',   tab === 'grouped');
    document.getElementById('dvHistPanelAll').classList.toggle('dv-panel-active',        tab === 'all');
    document.getElementById('dvHistPanelEmployees').classList.toggle('dv-panel-active',  tab === 'employees');
    renderHistoryDivision();
}

/* =========================================================
   RENDER DIVISION DATA (respects unit filter + subtab)
   ========================================================= */
function renderHistoryDivision() {
    if (!activeHistDivisionData) return;

    var unitFilter = document.getElementById('dvHistUnitFilter').value;
    var assets = (activeHistDivisionData.assets || []).filter(a => !unitFilter || a.location_name === unitFilter);

    document.getElementById('dvHistCount').textContent =
        assets.length + ' record' + (assets.length !== 1 ? 's' : '');

    if (activeHistSubtab === 'grouped')        renderHistGrouped(assets);
    else if (activeHistSubtab === 'all')       renderHistAll(assets);
    else                                       renderHistEmployees(assets);
}

function renderHistGrouped(assets) {
    var panel = document.getElementById('dvHistPanelGrouped');

    if (!assets.length) { panel.innerHTML = dvEmptyState('No records match the selected filter.'); return; }

    // Group by date
    var groups = {};
    assets.forEach(a => {
        var dateKey = formatDateISO(a.maintenanceDate);
        if (!groups[dateKey]) groups[dateKey] = [];
        groups[dateKey].push(a);
    });

    var sortedDates = Object.keys(groups).sort().reverse();
    var multiGroups = sortedDates.filter(d => groups[d].length >= 2);

    if (!multiGroups.length) {
        panel.innerHTML = `
            <div class="dv-empty">
                <i class="fas fa-calendar-check"></i>
                <h4>No Shared Maintenance Dates</h4>
                <p>No equipment in this selection shares the same maintenance date. Switch to <strong>All Equipment</strong> to see individual records.</p>
            </div>`;
        return;
    }

    var html = '';
    multiGroups.forEach(dateKey => {
        var group = groups[dateKey];
        var dateLabel = formatDate(group[0].maintenanceDate);

        html += `
        <div class="dv-date-group">
            <div class="dv-date-group-header">
                <div class="dv-date-group-marker"></div>
                <div class="dv-date-group-label">${escHtml(dateLabel)}</div>
                <div class="dv-date-group-count"><i class="fas fa-tools"></i> ${group.length} Equipment</div>
            </div>
            <div class="dv-group-table-wrap">
                <table class="dv-group-table">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Unit / Section</th>
                            <th>Time</th>
                            <th>Technician</th>
                            <th>Condition</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>`;

        group.forEach(a => {
            var cond = a.conditionRating || 'Good';
            var tn   = a.type_name || 'System Unit';
            html += `
                        <tr>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon ${getTypeClass(tn)}"><i class="fas ${getIcon(tn)}"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">${escHtml(a.brand)}</div>
                                        <div class="mnt-equip-serial">SN: ${escHtml(a.serial)}</div>
                                    </div>
                                </div>
                            </td>
                            <td><div class="mnt-location-primary">${escHtml(a.location_name)}</div></td>
                            <td><div class="mnt-date-sub" style="font-size:var(--text-sm); color:var(--text-dark);">${escHtml(formatTime(a.maintenanceDate))}</div></td>
                            <td>
                                <div class="mnt-tech-name">${escHtml(a.technician || '—')}</div>
                            </td>
                            <td><span class="mnt-badge ${getCondClass(cond)}"><i class="fas fa-circle"></i> ${escHtml(cond)}</span></td>
                            <td><button class="mnt-btn-report" onclick="viewReport(${a.recordId})"><i class="fas fa-file-pdf"></i> View</button></td>
                        </tr>`;
        });

        html += `</tbody></table></div></div>`;
    });

    panel.innerHTML = html;
}

/* ---- Render: All Equipment (flat list) ---- */
function renderHistAll(assets) {
    var tbody = document.getElementById('dvHistAllBody');

    if (!assets.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-light); font-style:italic;">No records match the selected filter.</td></tr>`;
        return;
    }

    // Sort most recent first
    assets.sort((a, b) => (b.maintenanceDate || '').localeCompare(a.maintenanceDate || ''));

    var html = '';
    assets.forEach(a => {
        var cond = a.conditionRating || 'Good';
        var tn   = a.type_name || 'System Unit';
        html += `
            <tr>
                <td>
                    <div class="mnt-equip-cell">
                        <div class="mnt-equip-icon ${getTypeClass(tn)}"><i class="fas ${getIcon(tn)}"></i></div>
                        <div>
                            <div class="mnt-equip-name">${escHtml(a.brand)}</div>
                            <div class="mnt-equip-serial">SN: ${escHtml(a.serial)}</div>
                        </div>
                    </div>
                </td>
                <td><div class="mnt-location-primary">${escHtml(a.location_name)}</div></td>
                <td>
                    <div class="mnt-tech-name">${escHtml(a.technician || '—')}</div>
                </td>
                <td><div class="mnt-date-primary completed">${escHtml(formatDate(a.maintenanceDate))}</div></td>
                <td><div class="mnt-date-sub" style="color:var(--text-dark); font-size:var(--text-sm);">${escHtml(formatTime(a.maintenanceDate))}</div></td>
                <td><span class="mnt-badge ${getCondClass(cond)}"><i class="fas fa-circle"></i> ${escHtml(cond)}</span></td>
                <td><button class="mnt-btn-report" onclick="viewReport(${a.recordId})"><i class="fas fa-file-pdf"></i> View</button></td>
            </tr>`;
    });

    tbody.innerHTML = html;
}

/* ---- Render: By Employee ---- */
function renderHistEmployees(filteredAssets) {
    var grid = document.getElementById('dvHistEmpGrid');
    if (!activeHistDivisionData) return;

    var unitFilter   = document.getElementById('dvHistUnitFilter').value;
    var allEmployees = activeHistDivisionData.employees || [];
    var assetLookup  = activeHistDivisionData.assetLookup || {};

    // Filter assets by location
    var filteredIds = new Set(filteredAssets.map(a => a.recordId));

    // Filter employees by unit, then only those with matching assets
    var employees = allEmployees.filter(e => !unitFilter || e.unit === unitFilter);
    employees = employees.filter(e => e.assets && e.assets.some(id => filteredIds.has(id)));

    if (!employees.length) {
        grid.innerHTML = `<div class="dv-empty" style="grid-column:1/-1">
            <i class="fas fa-users"></i>
            <h4>No Employees Found</h4>
            <p>No employees match the selected unit filter or have maintenance records.</p>
        </div>`;
        return;
    }

    var html = '';
    employees.forEach(emp => {
        var empAssets = (emp.assets || [])
            .filter(id => filteredIds.has(id))
            .map(id => assetLookup[id])
            .filter(Boolean);

        var maintained = empAssets.filter(a => (a.conditionRating || '').toLowerCase() !== 'poor').length;
        var pending    = empAssets.filter(a => (a.conditionRating || '').toLowerCase() === 'poor').length;
        var excellent  = empAssets.filter(a => (a.conditionRating || '').toLowerCase() === 'excellent').length;

        var hasPoor      = empAssets.some(a => (a.conditionRating || '').toLowerCase() === 'poor');
        var hasFair      = empAssets.some(a => (a.conditionRating || '').toLowerCase() === 'fair');
        var complianceClass = hasPoor ? 'compliance-low' : hasFair ? 'compliance-medium' : 'compliance-high';

        // Most recent maintenance date
        var sorted = [...empAssets].sort((a, b) => (b.maintenanceDate || '').localeCompare(a.maintenanceDate || ''));
        var lastDate = sorted.length ? formatDate(sorted[0].maintenanceDate) : '—';

        // Equipment rows (max 4)
        var shown = empAssets.slice(0, 4);
        var extra = empAssets.length - shown.length;

        var equipRows = '';
        shown.forEach(a => {
            var cond    = a.conditionRating || 'Good';
            var condLow = cond.toLowerCase();
            var chipCls = condLow;
            var chipIco = condIcon[cond] || 'fa-question-circle';
            var tn      = a.type_name || 'System Unit';

            equipRows += `
                <div class="dv-emp-equip-row">
                    <div class="dv-emp-equip-icon ${getTypeClass(tn)}">
                        <i class="fas ${getIcon(tn)}"></i>
                    </div>
                    <div class="dv-emp-equip-info">
                        <div class="dv-emp-equip-name">${escHtml(a.brand)}</div>
                        <div class="dv-emp-equip-serial">${escHtml(a.serial)} &nbsp;·&nbsp; ${escHtml(formatDate(a.maintenanceDate))}</div>
                    </div>
                    <span class="dv-emp-maint-chip ${chipCls}">
                        <i class="fas ${chipIco}"></i> ${escHtml(cond)}
                    </span>
                </div>`;
        });

        if (extra > 0) {
            equipRows += `<div class="dv-emp-no-equip">+${extra} more record${extra > 1 ? 's' : ''}</div>`;
        }

        var compRate  = empAssets.length > 0 ? Math.round((maintained / empAssets.length) * 100) : 0;
        var compColor = compRate >= 90 ? 'var(--color-success)' : compRate >= 70 ? 'var(--color-warning)' : 'var(--color-danger)';

        html += `
        <div class="dv-emp-card ${complianceClass}" data-employee-id="${emp.employeeId}" style="cursor:pointer;" onclick="goToEmployeeProfile(${emp.employeeId})" title="Click to view employee profile">
            <div class="dv-emp-card-header">
                <div class="dv-emp-avatar"><i class="fas fa-user"></i></div>
                <div class="dv-emp-identity">
                    <div class="dv-emp-name">${escHtml(emp.name)} <i class="fas fa-external-link-alt" style="font-size:0.65rem;color:var(--primary-green);margin-left:4px;opacity:0.7;"></i></div>
                    <div class="dv-emp-meta">
                        <span class="dv-emp-position">${escHtml(emp.position)}</span>
                        <span class="dv-emp-unit-tag"><i class="fas fa-map-marker-alt"></i> ${escHtml(emp.unit)}</span>
                    </div>
                </div>
            </div>
            <div class="dv-emp-hist-stats">
                <div class="dv-emp-hist-stat">
                    <div class="dv-emp-hist-stat-num">${empAssets.length}</div>
                    <div class="dv-emp-hist-stat-lbl">Total</div>
                </div>
                <div class="dv-emp-hist-stat">
                    <div class="dv-emp-hist-stat-num is-maintained">${maintained}</div>
                    <div class="dv-emp-hist-stat-lbl">Maintained</div>
                </div>
                <div class="dv-emp-hist-stat">
                    <div class="dv-emp-hist-stat-num is-excellent">${excellent}</div>
                    <div class="dv-emp-hist-stat-lbl">Excellent</div>
                </div>
                <div class="dv-emp-hist-stat">
                    <div class="dv-emp-hist-stat-num ${pending > 0 ? 'is-pending' : 'zero'}">${pending}</div>
                    <div class="dv-emp-hist-stat-lbl">Pending</div>
                </div>
            </div>
            <div class="dv-emp-equip-list">${equipRows}</div>
            <div class="dv-emp-card-footer">
                <div class="dv-emp-last-maint">
                    <i class="fas fa-calendar-check"></i> Last: ${escHtml(lastDate)}
                </div>
                <span style="font-size:var(--text-xs); font-weight:700; color:${compColor};">
                    <i class="fas fa-chart-line"></i> ${compRate}% Compliance
                </span>
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

/* =========================================================
   HELPERS
   ========================================================= */
function dvEmptyState(msg) {
    return `<div class="dv-empty"><i class="fas fa-search"></i><h4>No Results</h4><p>${msg}</p></div>`;
}

function exportReport() {
    var dp     = (typeof getHistDateParams === 'function') ? getHistDateParams() : { dateFrom: '', dateTo: '', rangeLabel: 'All Time' };
    var search = document.getElementById('histSearchInput')?.value || '';
    var sectionUnit = document.getElementById('histSectionUnitFilter')?.value || '';
    var params = new URLSearchParams({ dateFrom: dp.dateFrom, dateTo: dp.dateTo, rangeLabel: dp.rangeLabel });
    if (search) params.set('search', search);
    if (sectionUnit) params.set('sectionUnit', sectionUnit);
    window.open(BASE_URL + 'includes/generative/generate_maintenance_history.php?' + params.toString(), '_blank');
}

function viewReport(recordId) {
    var params = new URLSearchParams({ recordId: recordId });
    window.open(BASE_URL + 'includes/generative/generate_checklist_report.php?' + params.toString(), '_blank');
}

async function goToEmployeeProfile(employeeId) {
    if (!employeeId) return;
    if (!window.dashboardApp) {
        console.warn('Dashboard app not available');
        return;
    }
    try {
        // Force a fresh load so the roster scripts always re-execute
        await window.dashboardApp.loadPage('roster', false);

        // External <script src="roster.js"> loads async — poll until ready
        var ready = await waitForFunction('viewEmployee', 3000);
        if (ready) {
            viewEmployee(employeeId);
        } else {
            console.error('viewEmployee not available after timeout');
        }
    } catch (e) {
        console.error('Failed to navigate to employee profile:', e);
    }
}

/** Poll for a global function to exist (max `timeout` ms). */
function waitForFunction(fnName, timeout) {
    return new Promise(function (resolve) {
        if (typeof window[fnName] === 'function') { resolve(true); return; }
        var elapsed = 0;
        var interval = 50;
        var timer = setInterval(function () {
            elapsed += interval;
            if (typeof window[fnName] === 'function') { clearInterval(timer); resolve(true); }
            else if (elapsed >= timeout)               { clearInterval(timer); resolve(false); }
        }, interval);
    });
}