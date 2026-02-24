var SCHED_API = '../ajax/get_maintenance_schedule.php';

/* ── state ── */
var schedCurrentPage = 1;
var schedPageLimit   = 10;
var activeDivisionId   = null;
var activeDivisionName = '';
var activeSchedSubtab  = 'grouped';
var dvData = null;   // cached division drill-down response
var schedAssetMap = {}; // scheduleId → asset data for modal

/* ── icon / class maps ── */
var ICON_MAP = {
    'System Unit': 'fa-desktop',
    'All-in-One':  'fa-desktop',
    'Monitor':     'fa-tv',
    'Printer':     'fa-print',
    'Laptop':      'fa-laptop',
    'Mouse':       'fa-mouse',
    'Keyboard':    'fa-keyboard',
    'CCTV':        'fa-video',
    'NAS':         'fa-hdd',
};
var TYPE_CLASS = {
    'Monitor': 'type-monitor',
    'Printer': 'type-printer',
};

/* =========================================================
   INIT — SPA-safe
   ========================================================= */
function initSchedulePage() {
    loadStats();
    loadDetailedSchedule(1);
    loadSectionUnitFilter();
    loadSummaryView();

    // Enter key on search
    var searchEl = document.getElementById('schedSearchInput');
    if (searchEl) {
        searchEl.addEventListener('keydown', e => { if (e.key === 'Enter') applySchedFilters(); });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSchedulePage);
} else {
    initSchedulePage();
}

/* =========================================================
   STATS
   ========================================================= */
async function loadStats() {
    try {
        var r = await fetch(`${SCHED_API}?view=stats`);
        var j = await r.json();
        if (!j.success) return;
        document.getElementById('statOverdue').textContent   = j.data.overdue;
        document.getElementById('statDueSoon').textContent   = j.data.dueSoon;
        document.getElementById('statScheduled').textContent = j.data.scheduled;
        document.getElementById('statTotal').textContent     = j.data.total;
    } catch (e) { console.error('loadStats', e); }
}

/* =========================================================
   DIVISION FILTER DROPDOWN
   ========================================================= */
async function loadSectionUnitFilter() {
    try {
        var r = await fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'ajax/get_section_units.php');
        var j = await r.json();
        if (!j.success) return;
        var sel = document.getElementById('schedSectionUnitFilter');
        j.data.forEach(function(loc) {
            var opt = document.createElement('option');
            opt.value = loc.location_name;
            var badge = loc.location_type_id == 2 ? '[Section]' : '[Unit]';
            opt.textContent = loc.location_name + ' ' + badge;
            sel.appendChild(opt);
        });
    } catch (e) { console.error('loadSectionUnitFilter', e); }
}

/* =========================================================
   DETAILED VIEW — paginated table
   ========================================================= */
async function loadDetailedSchedule(page) {
    schedCurrentPage = page || 1;
    var tbody = document.getElementById('schedDetailedBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading schedule…</td></tr>';

    var search      = (document.getElementById('schedSearchInput')?.value || '').trim();
    var sectionUnit  = document.getElementById('schedSectionUnitFilter')?.value || '';
    var status       = document.getElementById('schedStatusFilter')?.value || '';

    var params = new URLSearchParams({
        view: 'detailed',
        page: schedCurrentPage,
        limit: schedPageLimit,
    });
    if (search)      params.set('search', search);
    if (sectionUnit) params.set('sectionUnit', sectionUnit);
    if (status)      params.set('status', status);

    try {
        var r = await fetch(`${SCHED_API}?${params}`);
        var j = await r.json();
        if (!j.success || !j.data.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--text-light); font-style:italic;">No scheduled maintenance found.</td></tr>';
            document.getElementById('schedRecordCount').textContent = '';
            document.getElementById('schedPagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = '';
        j.data.forEach(a => {
            var icon     = ICON_MAP[a.type_name] || 'fa-desktop';
            var typeCls  = TYPE_CLASS[a.type_name] || '';
            var dateCls  = a.status === 'overdue' ? 'overdue' : a.status === 'due_soon' ? 'due-soon' : 'scheduled';
            var badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due_soon' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
            var badgeIcon = a.status === 'overdue' ? 'fa-circle' : a.status === 'due_soon' ? 'fa-clock' : 'fa-check-circle';
            var badgeLabel = a.status === 'overdue' ? 'Overdue' : a.status === 'due_soon' ? 'Due Soon' : 'Scheduled';
            var dateLabel = formatDate(a.nextDueDate);
            var daysIcon = a.status === 'overdue' ? 'fa-exclamation-circle' : a.status === 'due_soon' ? 'fa-clock' : 'fa-calendar';
            var daysSub = a.status === 'overdue' ? 'overdue-label' : '';

            schedAssetMap[a.scheduleId] = a;
            var actionBtn = a.status !== 'scheduled'
                ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform Now</button>`
                : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform Now</button>`;

            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="mnt-date-primary">${dateLabel}</div>
                        <div class="mnt-date-sub"><i class="fas ${daysIcon}"></i> ${a.daysLabel}</div>
                    </td>
                    <td>
                        <div class="mnt-equip-cell">
                            <div class="mnt-equip-icon ${typeCls}"><i class="fas ${icon}"></i></div>
                            <div>
                                <div class="mnt-equip-name">${escHtml(a.brand)}</div>
                                <div class="mnt-equip-serial">SN: ${escHtml(a.serial || 'N/A')}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mnt-location-primary">${escHtml(a.location_name || '')}</div>
                        <div class="mnt-location-sub">${escHtml(a.owner_name || '')}</div>
                    </td>
                    <td><span class="mnt-badge mnt-badge-frequency">${a.maintenanceFrequency}</span></td>
                    <td><span class="mnt-badge ${badgeCls}"><i class="fas ${badgeIcon}"></i> ${badgeLabel}</span></td>
                    <td>${actionBtn}</td>
                </tr>`;
        });

        // Footer
        var p = j.pagination;
        document.getElementById('schedRecordCount').textContent =
            `Showing ${(p.page - 1) * p.limit + 1}–${Math.min(p.page * p.limit, p.total)} of ${p.total} records`;
        renderPagination(p);
    } catch (e) {
        console.error('loadDetailedSchedule', e);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load schedule.</td></tr>';
    }
}

function renderPagination(p) {
    var wrap = document.getElementById('schedPagination');
    if (!wrap) return;
    if (p.totalPages <= 1) { wrap.innerHTML = ''; return; }

    var html = `<button class="page-btn" onclick="loadDetailedSchedule(${p.page - 1})" ${p.page === 1 ? 'disabled' : ''}>
        <i class="fas fa-chevron-left"></i></button>`;

    getSchedPaginationRange(p.page, p.totalPages).forEach(function(n) {
        if (n === '...') {
            html += `<span class="page-ellipsis">…</span>`;
        } else {
            html += `<button class="page-btn ${n === p.page ? 'active' : ''}" onclick="loadDetailedSchedule(${n})">${n}</button>`;
        }
    });

    html += `<button class="page-btn" onclick="loadDetailedSchedule(${p.page + 1})" ${p.page === p.totalPages ? 'disabled' : ''}>
        <i class="fas fa-chevron-right"></i></button>`;

    wrap.innerHTML = html;
}

function getSchedPaginationRange(current, total) {
    if (total <= 7) return Array.from({length: total}, function(_, i) { return i + 1; });
    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3) return [1, '...', total-4, total-3, total-2, total-1, total];
    return [1, '...', current-1, current, current+1, '...', total];
}

function changeSchedPerPage() {
    var sel = document.getElementById('schedPerPageSelect');
    if (!sel) return;
    schedPageLimit = parseInt(sel.value);
    loadDetailedSchedule(1);
}

/* =========================================================
   FILTERS
   ========================================================= */
function applySchedFilters() {
    loadDetailedSchedule(1);
}

/* =========================================================
   VIEW SWITCHER
   ========================================================= */
function switchView(view) {
    document.getElementById('view-detailed').className = view === 'detailed' ? 'd-block' : 'd-none';
    document.getElementById('view-summary').className  = view === 'summary'  ? 'd-block' : 'd-none';
    document.getElementById('btnDetailed').classList.toggle('active', view === 'detailed');
    document.getElementById('btnSummary').classList.toggle('active',  view === 'summary');
    document.getElementById('filterBar').style.display = view === 'summary' ? 'none' : '';
    if (view === 'summary') backToDivisionsSchedule();
}

/* =========================================================
   SUMMARY VIEW — Division Cards
   ========================================================= */
async function loadSummaryView() {
    var grid = document.getElementById('divisionCardsGrid');
    try {
        var r = await fetch(`${SCHED_API}?view=summary`);
        var j = await r.json();
        if (!j.success || !j.data.length) {
            grid.innerHTML = '<div class="text-center py-5" style="grid-column:1/-1; color:var(--text-light);">No divisions with scheduled maintenance.</div>';
            return;
        }

        grid.innerHTML = '';
        j.data.forEach(div => {
            var total = div.overdue + div.dueSoon + div.scheduled;

            // Build section groups
            var sectionsHtml = '';
            div.sections.forEach(sec => {
                var unitRows = '';
                sec.units.forEach(u => {
                    var statusHtml;
                    if (u.overdue > 0) {
                        statusHtml = `<span class="mnt-section-status danger"><i class="fas fa-exclamation-circle"></i> ${u.overdue} Overdue</span>`;
                    } else if (u.dueSoon > 0) {
                        statusHtml = `<span class="mnt-section-status warn"><i class="fas fa-clock"></i> ${u.dueSoon} Due Soon</span>`;
                    } else {
                        statusHtml = `<span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>`;
                    }
                    unitRows += `
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">${escHtml(u.unitName)}</span>
                            ${statusHtml}
                        </div>`;
                });

                sectionsHtml += `
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">${escHtml(sec.sectionName)}</div>
                        ${unitRows}
                    </div>`;
            });

            grid.innerHTML += `
                <div class="mnt-summary-card">
                    <div class="mnt-summary-card-header">
                        <h3 class="mnt-summary-card-title">${escHtml(div.divisionName)}</h3>
                    </div>
                    <div class="mnt-summary-stats">
                        <div class="mnt-summary-stat">
                            <div class="mnt-summary-stat-value ${div.overdue > 0 ? 'overdue' : 'total'}">${div.overdue}</div>
                            <div class="mnt-summary-stat-label">Overdue</div>
                        </div>
                        <div class="mnt-summary-stat">
                            <div class="mnt-summary-stat-value due-soon">${div.dueSoon}</div>
                            <div class="mnt-summary-stat-label">Due Soon</div>
                        </div>
                        <div class="mnt-summary-stat">
                            <div class="mnt-summary-stat-value scheduled">${div.scheduled}</div>
                            <div class="mnt-summary-stat-label">Scheduled</div>
                        </div>
                    </div>
                    <div class="mnt-summary-body">${sectionsHtml}</div>
                    <div class="mnt-summary-card-footer">
                        <button class="btn btn-outline-primary w-100 btn-sm" onclick="viewDivisionSchedule('${div.divisionId}','${escAttr(div.divisionName)}')">
                            <i class="fas fa-arrow-right"></i> View Division Assets
                        </button>
                    </div>
                </div>`;
        });
    } catch (e) {
        console.error('loadSummaryView', e);
        grid.innerHTML = '<div class="text-center py-5 text-danger" style="grid-column:1/-1;">Failed to load summary.</div>';
    }
}

/* =========================================================
   DIVISION DRILL-DOWN
   ========================================================= */
async function viewDivisionSchedule(divId, divName) {
    activeDivisionId   = divId;
    activeDivisionName = divName;

    // Hide grid, show detail
    document.getElementById('divisionCardsGrid').style.display = 'none';
    document.getElementById('dvSchedName').textContent = divName;
    document.getElementById('dvSchedBreadcrumb').textContent = divName;

    // Reset subtab
    activeSchedSubtab = 'grouped';
    ['dvSchedTabGrouped', 'dvSchedTabAll', 'dvSchedTabEmp'].forEach(id => {
        document.getElementById(id).classList.toggle('active', id === 'dvSchedTabGrouped');
    });
    ['dvSchedPanelGrouped', 'dvSchedPanelAll', 'dvSchedPanelEmployees'].forEach(id => {
        document.getElementById(id).classList.toggle('dv-panel-active', id === 'dvSchedPanelGrouped');
    });

    document.getElementById('divisionViewSchedule').classList.add('dv-active');

    // Show loading
    document.getElementById('dvSchedPanelGrouped').innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading…</div>';
    document.getElementById('dvSchedBadges').innerHTML = '';

    try {
        var r = await fetch(`${SCHED_API}?view=division&division=${divId}`);
        var j = await r.json();
        if (!j.success) return;

        dvData = j;

        // Stat badges
        var s = j.stats;
        document.getElementById('dvSchedBadges').innerHTML = `
            <span class="dv-stat-pill total"><i class="fas fa-layer-group"></i> ${s.total} Assets</span>
            ${s.overdue > 0 ? `<span class="dv-stat-pill overdue"><i class="fas fa-exclamation-circle"></i> ${s.overdue} Overdue</span>` : ''}
            ${s.dueSoon > 0 ? `<span class="dv-stat-pill due-soon"><i class="fas fa-clock"></i> ${s.dueSoon} Due Soon</span>` : ''}
            <span class="dv-stat-pill scheduled"><i class="fas fa-check-circle"></i> ${s.scheduled} Scheduled</span>
        `;

        // Populate unit filter
        var unitSel = document.getElementById('dvSchedUnitFilter');
        unitSel.innerHTML = '<option value="">All Units</option>';
        (j.units || []).forEach(u => {
            var opt = document.createElement('option');
            opt.value = u.unit_id;
            opt.textContent = u.unit_name;
            unitSel.appendChild(opt);
        });

        renderDvContent();
    } catch (e) {
        console.error('viewDivisionSchedule', e);
        document.getElementById('dvSchedPanelGrouped').innerHTML = '<div class="text-center py-4 text-danger">Failed to load division data.</div>';
    }
}

function backToDivisionsSchedule() {
    document.getElementById('divisionCardsGrid').style.display = '';
    document.getElementById('divisionViewSchedule').classList.remove('dv-active');
    activeDivisionId = null;
    dvData = null;
}

/* =========================================================
   DIVISION: UNIT FILTER CHANGE — refetch with unit param
   ========================================================= */
async function onDvUnitFilterChange() {
    if (!activeDivisionId) return;
    var uid = document.getElementById('dvSchedUnitFilter').value;

    // Show loading in active panel
    var panelId = activeSchedSubtab === 'grouped' ? 'dvSchedPanelGrouped' :
                    activeSchedSubtab === 'all' ? 'dvSchedAllBody' : 'dvSchedEmpGrid';
    var el = document.getElementById(panelId);
    if (el.tagName === 'TBODY') el.innerHTML = '<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>';
    else el.innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></div>';

    try {
        var params = new URLSearchParams({ view: 'division', division: activeDivisionId });
        if (uid) params.set('unit', uid);
        var r = await fetch(`${SCHED_API}?${params}`);
        var j = await r.json();
        if (!j.success) return;
        dvData = j;

        // Update count badge in header
        var s = j.stats;
        document.getElementById('dvSchedBadges').innerHTML = `
            <span class="dv-stat-pill total"><i class="fas fa-layer-group"></i> ${s.total} Assets</span>
            ${s.overdue > 0 ? `<span class="dv-stat-pill overdue"><i class="fas fa-exclamation-circle"></i> ${s.overdue} Overdue</span>` : ''}
            ${s.dueSoon > 0 ? `<span class="dv-stat-pill due-soon"><i class="fas fa-clock"></i> ${s.dueSoon} Due Soon</span>` : ''}
            <span class="dv-stat-pill scheduled"><i class="fas fa-check-circle"></i> ${s.scheduled} Scheduled</span>
        `;

        renderDvContent();
    } catch (e) { console.error('onDvUnitFilterChange', e); }
}

/* =========================================================
   SUBTAB SWITCHER
   ========================================================= */
function switchSchedSubtab(tab) {
    activeSchedSubtab = tab;
    document.getElementById('dvSchedTabGrouped').classList.toggle('active', tab === 'grouped');
    document.getElementById('dvSchedTabAll').classList.toggle('active',     tab === 'all');
    document.getElementById('dvSchedTabEmp').classList.toggle('active',     tab === 'employees');
    document.getElementById('dvSchedPanelGrouped').classList.toggle('dv-panel-active', tab === 'grouped');
    document.getElementById('dvSchedPanelAll').classList.toggle('dv-panel-active',     tab === 'all');
    document.getElementById('dvSchedPanelEmployees').classList.toggle('dv-panel-active', tab === 'employees');
    renderDvContent();
}

/* =========================================================
   RENDER DV CONTENT  — dispatches to grouped / all / emp
   ========================================================= */
function renderDvContent() {
    if (!dvData) return;
    var assets = dvData.data || [];
    document.getElementById('dvSchedCount').textContent = assets.length + ' asset' + (assets.length !== 1 ? 's' : '');

    if (activeSchedSubtab === 'grouped')   renderSchedGrouped(assets);
    else if (activeSchedSubtab === 'all')  renderSchedAll(assets);
    else                                   renderSchedEmployees();
}

/* ---- Render: Grouped by Same Due Date ---- */
function renderSchedGrouped(assets) {
    var panel = document.getElementById('dvSchedPanelGrouped');

    if (!assets.length) {
        panel.innerHTML = emptyState('No equipment matches the selected filter.');
        return;
    }

    // Group by date
    var groups = {};
    assets.forEach(a => {
        var d = a.nextDueDate;
        if (!groups[d]) groups[d] = [];
        groups[d].push(a);
    });

    var sortedDates = Object.keys(groups).sort();
    var multiGroups = sortedDates.filter(d => groups[d].length >= 2);

    if (!multiGroups.length) {
        panel.innerHTML = `
            <div class="dv-empty">
                <i class="fas fa-calendar-check"></i>
                <h4>No Shared Due Dates</h4>
                <p>No equipment in this selection shares the same due date. Switch to <strong>All Equipment</strong> to see individual records.</p>
            </div>`;
        return;
    }

    var html = '';
    multiGroups.forEach(date => {
        var group = groups[date];
        var rep = group[0];
        var markerClass = rep.status === 'overdue' ? 'overdue' : rep.status === 'due_soon' ? 'due-soon' : 'scheduled';

        html += `
        <div class="dv-date-group">
            <div class="dv-date-group-header">
                <div class="dv-date-group-marker ${markerClass}"></div>
                <div class="dv-date-group-label">${formatDate(date)}</div>
                <div class="dv-date-group-sublabel">${rep.daysLabel}</div>
                <div class="dv-date-group-count"><i class="fas fa-layer-group"></i> ${group.length} Equipment</div>
            </div>
            <div class="dv-group-table-wrap">
                <table class="dv-group-table">
                    <thead><tr><th>Equipment</th><th>Unit / Section</th><th>Owner</th><th>Frequency</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>`;

        group.forEach(a => {
            var icon    = ICON_MAP[a.type_name] || 'fa-desktop';
            var typeCls = TYPE_CLASS[a.type_name] || '';
            var badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due_soon' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
            var badgeLbl = a.status === 'overdue' ? '<i class="fas fa-circle"></i> Overdue' :
                             a.status === 'due_soon' ? '<i class="fas fa-clock"></i> Due Soon' :
                             '<i class="fas fa-check-circle"></i> Scheduled';
            schedAssetMap[a.scheduleId] = a;
            var actionBtn = a.status !== 'scheduled'
                ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform Now</button>`
                : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform Now</button>`;

            html += `
                <tr>
                    <td>
                        <div class="mnt-equip-cell">
                            <div class="mnt-equip-icon ${typeCls}"><i class="fas ${icon}"></i></div>
                            <div>
                                <div class="mnt-equip-name">${escHtml(a.brand)}</div>
                                <div class="mnt-equip-serial">SN: ${escHtml(a.serial || 'N/A')}</div>
                            </div>
                        </div>
                    </td>
                    <td><div class="mnt-location-primary">${escHtml(a.unit_name || a.location_name || '')}</div></td>
                    <td><div class="mnt-tech-name">${escHtml(a.owner_name || '')}</div></td>
                    <td><span class="mnt-badge mnt-badge-frequency">${a.maintenanceFrequency}</span></td>
                    <td><span class="mnt-badge ${badgeCls}">${badgeLbl}</span></td>
                    <td>${actionBtn}</td>
                </tr>`;
        });

        html += `</tbody></table></div></div>`;
    });

    panel.innerHTML = html;
}

/* ---- Render: All Equipment (flat list) ---- */
function renderSchedAll(assets) {
    var tbody = document.getElementById('dvSchedAllBody');

    if (!assets.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4" style="color:var(--text-light); font-style:italic;">No equipment matches the selected filter.</td></tr>';
        return;
    }

    // Sort by date ascending
    var sorted = [...assets].sort((a, b) => a.nextDueDate.localeCompare(b.nextDueDate));

    tbody.innerHTML = '';
    sorted.forEach(a => {
        var icon    = ICON_MAP[a.type_name] || 'fa-desktop';
        var typeCls = TYPE_CLASS[a.type_name] || '';
        var dateCls = a.status === 'overdue' ? 'overdue' : a.status === 'due_soon' ? 'due-soon' : 'scheduled';
        var badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due_soon' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
        var badgeLbl = a.status === 'overdue' ? '<i class="fas fa-circle"></i> Overdue' :
                         a.status === 'due_soon' ? '<i class="fas fa-clock"></i> Due Soon' :
                         '<i class="fas fa-check-circle"></i> Scheduled';
        schedAssetMap[a.scheduleId] = a;
        var actionBtn = a.status !== 'scheduled'
            ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform Now</button>`
            : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform Now</button>`;

        tbody.innerHTML += `
            <tr>
                <td>
                    <div class="mnt-equip-cell">
                        <div class="mnt-equip-icon ${typeCls}"><i class="fas ${icon}"></i></div>
                        <div>
                            <div class="mnt-equip-name">${escHtml(a.brand)}</div>
                            <div class="mnt-equip-serial">SN: ${escHtml(a.serial || 'N/A')}</div>
                        </div>
                    </div>
                </td>
                <td><div class="mnt-location-primary">${escHtml(a.unit_name || a.location_name || '')}</div></td>
                <td><div class="mnt-tech-name">${escHtml(a.owner_name || '')}</div></td>
                <td>
                    <div class="mnt-date-primary ${dateCls}">${formatDate(a.nextDueDate)}</div>
                    <div class="mnt-date-sub"><i class="fas fa-clock"></i> ${a.daysLabel}</div>
                </td>
                <td><span class="mnt-badge mnt-badge-frequency">${a.maintenanceFrequency}</span></td>
                <td><span class="mnt-badge ${badgeCls}">${badgeLbl}</span></td>
                <td>${actionBtn}</td>
            </tr>`;
    });
}

/* ---- Render: By Employee ---- */
function renderSchedEmployees() {
    var grid = document.getElementById('dvSchedEmpGrid');
    if (!dvData) return;

    var employees = dvData.employees || [];

    if (!employees.length) {
        grid.innerHTML = `<div class="dv-empty" style="grid-column:1/-1">
            <i class="fas fa-users"></i><h4>No Employees Found</h4>
            <p>No employees match the selected unit filter.</p>
        </div>`;
        return;
    }

    var html = '';
    employees.forEach(emp => {
        var assets = emp.assets || [];
        var overdue = emp.overdue || 0;
        var due     = emp.dueSoon || 0;
        var sched   = emp.scheduled || 0;

        var cardClass = overdue > 0 ? 'has-overdue' : due > 0 ? 'has-due-soon' : 'all-scheduled';
        var overdueClass = overdue > 0 ? 'is-overdue' : 'zero';
        var dueClass     = due > 0     ? 'is-due'     : 'zero';
        var schedClass   = sched > 0   ? 'is-sched'   : 'zero';

        // Equipment rows (max 4)
        var shown = assets.slice(0, 4);
        var extra = assets.length - shown.length;

        var equipRows = '';
        shown.forEach(a => {
            var icon = ICON_MAP[a.typeName] || 'fa-desktop';
            var typeCls = TYPE_CLASS[a.typeName] || '';
            var chipClass = a.status === 'overdue' ? 'overdue' : a.status === 'due_soon' ? 'due-soon' : 'scheduled';
            var chipLbl =
                a.status === 'overdue'  ? `<i class="fas fa-exclamation-circle"></i> ${formatDate(a.nextDueDate)}` :
                a.status === 'due_soon' ? `<i class="fas fa-clock"></i> ${formatDate(a.nextDueDate)}` :
                                          `<i class="fas fa-check-circle"></i> ${formatDate(a.nextDueDate)}`;

            equipRows += `
                <div class="dv-emp-equip-row">
                    <div class="dv-emp-equip-icon ${typeCls}"><i class="fas ${icon}"></i></div>
                    <div class="dv-emp-equip-info">
                        <div class="dv-emp-equip-name">${escHtml(a.brand)}</div>
                        <div class="dv-emp-equip-serial">${escHtml(a.serial || 'N/A')}</div>
                    </div>
                    <span class="dv-emp-due-chip ${chipClass}">${chipLbl}</span>
                </div>`;
        });
        if (extra > 0) {
            equipRows += `<div class="dv-emp-no-equip">+${extra} more equipment</div>`;
        }

        // Footer status
        var footerStatus = overdue > 0
            ? `<span style="color:var(--color-danger);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-exclamation-circle"></i> ${overdue} overdue item${overdue > 1 ? 's' : ''} need attention</span>`
            : due > 0
            ? `<span style="color:var(--color-warning);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-clock"></i> ${due} item${due > 1 ? 's' : ''} due soon</span>`
            : `<span style="color:var(--color-success);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-check-circle"></i> All equipment on schedule</span>`;

        var empClickAttr = emp.employeeId ? `onclick="goToEmployeeProfile(${emp.employeeId})" style="cursor:pointer;"` : '';

        html += `
        <div class="dv-emp-card ${cardClass}" ${empClickAttr}>
            <div class="dv-emp-card-header">
                <div class="dv-emp-avatar"><i class="fas fa-user"></i></div>
                <div class="dv-emp-identity">
                    <div class="dv-emp-name">${escHtml(emp.name)}</div>
                    <div class="dv-emp-meta">
                        <span class="dv-emp-position">${escHtml(emp.position)}</span>
                        <span class="dv-emp-unit-tag"><i class="fas fa-map-marker-alt"></i> ${escHtml(emp.unit)}</span>
                    </div>
                </div>
            </div>
            <div class="dv-emp-sched-stats">
                <div class="dv-emp-sched-stat">
                    <div class="dv-emp-sched-stat-num ${overdueClass}">${overdue}</div>
                    <div class="dv-emp-sched-stat-lbl">Overdue</div>
                </div>
                <div class="dv-emp-sched-stat">
                    <div class="dv-emp-sched-stat-num ${dueClass}">${due}</div>
                    <div class="dv-emp-sched-stat-lbl">Due Soon</div>
                </div>
                <div class="dv-emp-sched-stat">
                    <div class="dv-emp-sched-stat-num ${schedClass}">${sched}</div>
                    <div class="dv-emp-sched-stat-lbl">Scheduled</div>
                </div>
            </div>
            <div class="dv-emp-equip-list">${equipRows}</div>
            <div class="dv-emp-card-footer">
                <div class="dv-emp-total-label"><strong>${assets.length}</strong> assigned asset${assets.length !== 1 ? 's' : ''}</div>
                ${footerStatus}
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

/* =========================================================
   NAVIGATE: Employee Profile (same pattern as history page)
   ========================================================= */
function goToEmployeeProfile(employeeId) {
    if (!employeeId) return;
    if (typeof navigateToPage === 'function') {
        navigateToPage('roster');
    } else if (window.dashboardApp?.loadPage) {
        window.dashboardApp.loadPage('roster');
    }
    waitForFunction('viewEmployee', 3000).then(fn => {
        fn(employeeId);
    }).catch(() => {
        console.warn('viewEmployee not available after timeout');
    });
}

function waitForFunction(fnName, timeout) {
    return new Promise((resolve, reject) => {
        var start = Date.now();
        var check = () => {
            if (typeof window[fnName] === 'function') return resolve(window[fnName]);
            if (Date.now() - start > timeout) return reject();
            setTimeout(check, 50);
        };
        check();
    });
}

/* =========================================================
   PERFORM MAINTENANCE — opens reusable modal
   ========================================================= */
function performMaintenance(scheduleId) {
    var a = schedAssetMap[scheduleId];
    if (!a) {
        console.error('No asset data cached for scheduleId', scheduleId);
        return;
    }
    openMaintenanceModal({
        scheduleId:    a.scheduleId,
        equipmentType: a.equipmentType,
        typeName:      a.type_name,
        brand:         a.brand,
        serial:        a.serial,
        owner:         a.owner_name || 'Unassigned',
        location:      a.location_name || 'N/A'
    });
}

/* =========================================================
   HELPERS
   ========================================================= */
function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function escAttr(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function emptyState(msg) {
    return `<div class="dv-empty"><i class="fas fa-search"></i><h4>No Results</h4><p>${msg}</p></div>`;
}