var SCHED_API = `${BASE_URL}ajax/get_maintenance_schedule.php`;

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
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading schedule…</td></tr>';

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
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4" style="color:var(--text-light); font-style:italic;">No scheduled maintenance found.</td></tr>';
            document.getElementById('schedRecordCount').textContent = '';
            document.getElementById('schedPagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = '';
        var rowStart = (j.pagination.page - 1) * j.pagination.limit;
        j.data.forEach((a, idx) => {
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
            var actionBtn = `<div class="d-flex gap-1">`
                + `<button class="mnt-btn-view" onclick="viewScheduleDetail(${a.scheduleId})" title="View Details"><i class="fas fa-eye"></i></button>`
                + (a.status !== 'scheduled'
                    ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform</button>`
                    : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform</button>`)
                + `</div>`;

            tbody.innerHTML += `
                <tr>
                    <td class="row-counter">${rowStart + idx + 1}</td>
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
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load schedule.</td></tr>';
    }
}

function renderPagination(p) {
    var wrap = document.getElementById('schedPagination');
    if (!wrap) return;
    if (p.totalPages <= 1) { wrap.innerHTML = ''; return; }
    renderPaginationControls('schedPagination', p.page, p.totalPages, 'loadDetailedSchedule');
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
            var actionBtn = `<div class="d-flex gap-1">`
                + `<button class="mnt-btn-view" onclick="viewScheduleDetail(${a.scheduleId})" title="View Details"><i class="fas fa-eye"></i></button>`
                + (a.status !== 'scheduled'
                    ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform</button>`
                    : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform</button>`)
                + `</div>`;

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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4" style="color:var(--text-light); font-style:italic;">No equipment matches the selected filter.</td></tr>';
        return;
    }

    // Sort by date ascending
    var sorted = [...assets].sort((a, b) => a.nextDueDate.localeCompare(b.nextDueDate));

    tbody.innerHTML = '';
    sorted.forEach((a, idx) => {
        var icon    = ICON_MAP[a.type_name] || 'fa-desktop';
        var typeCls = TYPE_CLASS[a.type_name] || '';
        var dateCls = a.status === 'overdue' ? 'overdue' : a.status === 'due_soon' ? 'due-soon' : 'scheduled';
        var badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due_soon' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
        var badgeLbl = a.status === 'overdue' ? '<i class="fas fa-circle"></i> Overdue' :
                         a.status === 'due_soon' ? '<i class="fas fa-clock"></i> Due Soon' :
                         '<i class="fas fa-check-circle"></i> Scheduled';
        schedAssetMap[a.scheduleId] = a;
        var actionBtn = `<div class="d-flex gap-1">`
            + `<button class="mnt-btn-view" onclick="viewScheduleDetail(${a.scheduleId})" title="View Details"><i class="fas fa-eye"></i></button>`
            + (a.status !== 'scheduled'
                ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-tools"></i> Perform</button>`
                : `<button class="mnt-btn-perform" onclick="performMaintenance(${a.scheduleId})"><i class="fas fa-clipboard-check"></i> Perform</button>`)
            + `</div>`;

        tbody.innerHTML += `
            <tr>
                <td class="row-counter">${idx + 1}</td>
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
    if (!dateStr) return '—';
    var d = new Date(dateStr);
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

/* =========================================================
   VIEW SCHEDULE DETAIL — opens detail modal
   ========================================================= */
async function viewScheduleDetail(scheduleId) {
    var modalEl = document.getElementById('maintenanceDetailModal');
    if (!modalEl) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    document.getElementById('detailModalTitleText').textContent = 'Schedule Details';
    document.getElementById('detail-modal-loader').style.display = 'block';
    document.getElementById('detail-modal-content').innerHTML = '';
    document.getElementById('detailModalActionBtn').style.display = 'none';

    try {
        var r = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : ''}ajax/get_maintenance_detail.php?type=schedule&scheduleId=${scheduleId}`);
        var j = await r.json();
        document.getElementById('detail-modal-loader').style.display = 'none';

        if (!j.success) {
            document.getElementById('detail-modal-content').innerHTML =
                `<div class="detail-empty"><i class="fas fa-exclamation-triangle"></i> ${j.message}</div>`;
            return;
        }

        var d = j.data;
        var statusClass = d.status === 'overdue' ? 'badge-overdue' : d.status === 'due_soon' ? 'badge-due-soon' : 'badge-scheduled';
        var statusLabel = d.status === 'overdue' ? 'Overdue' : d.status === 'due_soon' ? 'Due Soon' : 'Scheduled';
        var statusIcon  = d.status === 'overdue' ? 'fa-exclamation-circle' : d.status === 'due_soon' ? 'fa-clock' : 'fa-check-circle';

        var html = `
            <div class="detail-section">
                <div class="detail-section-title"><i class="fas fa-desktop"></i> Equipment Information</div>
                <div class="detail-info-grid cols-3">
                    <div class="detail-field">
                        <span class="detail-field-label">Equipment</span>
                        <span class="detail-field-value">${escHtml(d.brand || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Type</span>
                        <span class="detail-field-value">${escHtml(d.type_name || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Serial Number</span>
                        <span class="detail-field-value" style="font-family:monospace;">${escHtml(d.serial || 'N/A')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Owner</span>
                        <span class="detail-field-value">${escHtml(d.owner_name || 'Unassigned')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Location</span>
                        <span class="detail-field-value">${escHtml(d.location_name || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Active</span>
                        <span class="detail-field-value">${d.isActive == 1 ? '<i class="fas fa-check-circle text-success"></i> Yes' : '<i class="fas fa-times-circle text-danger"></i> No'}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-section-title"><i class="fas fa-calendar-alt"></i> Schedule Information</div>
                <div class="detail-info-grid">
                    <div class="detail-field">
                        <span class="detail-field-label">Next Due Date</span>
                        <span class="detail-field-value">${formatDate(d.nextDueDate)}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Status</span>
                        <span class="detail-status-badge ${statusClass}"><i class="fas ${statusIcon}"></i> ${statusLabel} — ${d.daysLabel}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Frequency</span>
                        <span class="detail-field-value">${escHtml(d.maintenanceFrequency)}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Last Maintenance</span>
                        <span class="detail-field-value">${d.lastMaintenanceDate ? formatDate(d.lastMaintenanceDate) : '<span style="color:var(--text-light);font-style:italic;">Never</span>'}</span>
                    </div>
                </div>
            </div>`;

        // Recent maintenance records
        var records = d.recentRecords || [];
        html += `<div class="detail-section">
            <div class="detail-section-title"><i class="fas fa-history"></i> Recent Maintenance History</div>`;

        if (records.length === 0) {
            html += `<div class="detail-empty">No maintenance records yet for this equipment.</div>`;
        } else {
            html += `<table class="detail-recent-table">
                <thead><tr><th>Date</th><th>Technician</th><th>Status</th><th>Condition</th><th>Action</th></tr></thead>
                <tbody>`;
            records.forEach(r => {
                var condCls = 'cond-' + (r.conditionRating || 'good').toLowerCase();
                var statusCls = r.overallStatus === 'Operational' ? 'badge-operational' : r.overallStatus === 'For Replacement' ? 'badge-replacement' : 'badge-disposed';
                html += `<tr>
                    <td>${formatDate(r.maintenanceDate)}</td>
                    <td>${escHtml(r.technician || '—')}</td>
                    <td><span class="detail-status-badge ${statusCls}">${escHtml(r.overallStatus)}</span></td>
                    <td><span class="detail-cond-badge ${condCls}">${escHtml(r.conditionRating)}</span></td>
                    <td><button class="mnt-btn-report" onclick="viewRecordDetail(${r.recordId})" title="View Record"><i class="fas fa-eye"></i></button></td>
                </tr>`;
            });
            html += `</tbody></table>`;
        }
        html += `</div>`;

        document.getElementById('detail-modal-content').innerHTML = html;

    } catch (e) {
        document.getElementById('detail-modal-loader').style.display = 'none';
        document.getElementById('detail-modal-content').innerHTML =
            `<div class="detail-empty"><i class="fas fa-exclamation-triangle"></i> Failed to load: ${escHtml(e.message)}</div>`;
    }
}

/* =========================================================
   VIEW RECORD DETAIL — opens detail modal with checklist
   (duplicated from maintenance-history.js for standalone use)
   ========================================================= */
async function viewRecordDetail(recordId) {
    var modalEl = document.getElementById('maintenanceDetailModal');
    if (!modalEl) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    document.getElementById('detailModalTitleText').textContent = 'Maintenance Record Details';
    document.getElementById('detail-modal-loader').style.display = 'block';
    document.getElementById('detail-modal-content').innerHTML = '';

    var actionBtn = document.getElementById('detailModalActionBtn');
    actionBtn.style.display = 'inline-flex';
    actionBtn.onclick = function() {
        var params = new URLSearchParams({ recordId: recordId });
        window.open((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'includes/generative/generate_checklist_report.php?' + params.toString(), '_blank');
    };

    try {
        var r = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : ''}ajax/get_maintenance_detail.php?type=record&recordId=${recordId}`);
        var j = await r.json();
        document.getElementById('detail-modal-loader').style.display = 'none';

        if (!j.success) {
            document.getElementById('detail-modal-content').innerHTML =
                `<div class="detail-empty"><i class="fas fa-exclamation-triangle"></i> ${escHtml(j.message)}</div>`;
            return;
        }

        var d = j.data;
        var statusCls = d.overallStatus === 'Operational' ? 'badge-operational' : d.overallStatus === 'For Replacement' ? 'badge-replacement' : 'badge-disposed';
        var condCls   = 'cond-' + (d.conditionRating || 'good').toLowerCase();
        var dateLabel = formatDate(d.maintenanceDate);

        var html = `
            <div class="detail-section">
                <div class="detail-section-title"><i class="fas fa-desktop"></i> Equipment Information</div>
                <div class="detail-info-grid cols-3">
                    <div class="detail-field">
                        <span class="detail-field-label">Equipment</span>
                        <span class="detail-field-value">${escHtml(d.brand || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Type</span>
                        <span class="detail-field-value">${escHtml(d.type_name || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Serial Number</span>
                        <span class="detail-field-value" style="font-family:monospace;">${escHtml(d.serial || 'N/A')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Owner</span>
                        <span class="detail-field-value">${escHtml(d.owner_name || 'Unassigned')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Location</span>
                        <span class="detail-field-value">${escHtml(d.location_name || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Template</span>
                        <span class="detail-field-value">${escHtml(d.templateName || 'N/A')}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-section-title"><i class="fas fa-calendar-check"></i> Maintenance Summary</div>
                <div class="detail-info-grid">
                    <div class="detail-field">
                        <span class="detail-field-label">Date</span>
                        <span class="detail-field-value">${escHtml(dateLabel)}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Technician</span>
                        <span class="detail-field-value">${escHtml(d.preparedBy || '—')}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Overall Status</span>
                        <span class="detail-status-badge ${statusCls}">${escHtml(d.overallStatus)}</span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-field-label">Condition Rating</span>
                        <span class="detail-cond-badge ${condCls}"><i class="fas fa-circle"></i> ${escHtml(d.conditionRating)}</span>
                    </div>
                </div>
            </div>`;

        // Checklist responses
        var responses = d.responses || [];
        if (responses.length > 0) {
            html += `<div class="detail-section">
                <div class="detail-section-title"><i class="fas fa-tasks"></i> Checklist Responses</div>
                <div style="overflow-x:auto;">
                <table class="detail-checklist-table">
                    <thead><tr><th style="width:50px;">#</th><th>Task</th><th style="width:100px;">Response</th></tr></thead>
                    <tbody>`;

            var lastCat = '';
            var num = 0;
            responses.forEach(resp => {
                if (resp.categoryName && resp.categoryName !== lastCat) {
                    lastCat = resp.categoryName;
                    html += `<tr class="cat-row"><td colspan="3"><i class="fas fa-folder-open"></i> ${escHtml(lastCat)}</td></tr>`;
                }
                num++;
                var respVal  = (resp.response || 'N/A').trim();
                var respLower = respVal.toLowerCase();
                var respCls = 'resp-na';
                if (['yes', 'ok', 'done', 'pass'].includes(respLower))       respCls = 'resp-yes';
                else if (['no', 'fail', 'failed'].includes(respLower))        respCls = 'resp-no';
                else if (['n/a', 'na'].includes(respLower))                   respCls = 'resp-na';
                else if (['warning'].includes(respLower))                      respCls = 'resp-warning';
                else if (respLower.includes('minor'))                          respCls = 'resp-minor';

                html += `<tr>
                    <td style="color:var(--text-light);">${num}</td>
                    <td>${escHtml(resp.taskDescription)}</td>
                    <td><span class="detail-response ${respCls}">${escHtml(respVal)}</span></td>
                </tr>`;
            });

            html += `</tbody></table></div></div>`;
        }

        // Remarks
        html += `<div class="detail-section">
            <div class="detail-section-title"><i class="fas fa-comment-alt"></i> Remarks</div>
            <div class="detail-remarks ${d.remarks ? '' : 'empty'}">${d.remarks ? escHtml(d.remarks) : 'No remarks provided.'}</div>
        </div>`;

        // Signatories
        html += `<div class="detail-section">
            <div class="detail-section-title"><i class="fas fa-signature"></i> Signatories</div>
            <div class="detail-signatories">
                <div class="detail-signatory">
                    <div class="detail-signatory-label">Prepared / Conducted by</div>
                    <div class="detail-signatory-name">${escHtml(d.preparedBy || '—')}</div>
                </div>
                <div class="detail-signatory">
                    <div class="detail-signatory-label">Checked by</div>
                    <div class="detail-signatory-name">${escHtml(d.checkedBy || '—')}</div>
                </div>
                <div class="detail-signatory">
                    <div class="detail-signatory-label">Noted by</div>
                    <div class="detail-signatory-name">${escHtml(d.notedBy || '—')}</div>
                </div>
            </div>
        </div>`;

        document.getElementById('detail-modal-content').innerHTML = html;

    } catch (e) {
        document.getElementById('detail-modal-loader').style.display = 'none';
        document.getElementById('detail-modal-content').innerHTML =
            `<div class="detail-empty"><i class="fas fa-exclamation-triangle"></i> Failed to load: ${escHtml(e.message)}</div>`;
    }
}