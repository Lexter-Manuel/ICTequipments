/* =========================================================
   MOCK DATA — Maintenance Schedule
   ========================================================= */
const SCHED_DIVISIONS = {
    EOD: {
        name: 'Engineering & Operation Division',
        stats: { overdue: 1, dueSoon: 2, scheduled: 31 },
        units: ['All Units', 'Construction Unit', 'Planning Unit', 'O&M Unit', 'Field Operations', 'Dispatch Unit'],
        assets: [
            { id: 101, name: 'Dell Optiplex 7080',      serial: 'SU-2024-009', type: 'system_unit', unit: 'Construction Unit', owner: 'Engr. Juan Dela Cruz', date: '2026-02-10', dateLabel: 'Feb 10, 2026', status: 'overdue',   statusLabel: 'Overdue',   daysLabel: '9 Days Overdue', frequency: 'Semi-Annual' },
            { id: 303, name: 'Canon PIXMA G3010',       serial: 'PR-2023-145', type: 'printer',     unit: 'Construction Unit', owner: 'Construction Unit',    date: '2026-03-20', dateLabel: 'Mar 20, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '29 Days Away', frequency: 'Semi-Annual' },
            { id: 401, name: 'Samsung S24C450',         serial: 'MO-2024-078', type: 'monitor',     unit: 'Planning Unit',     owner: 'Planning Staff',       date: '2026-02-20', dateLabel: 'Feb 20, 2026', status: 'due',       statusLabel: 'Due Soon',  daysLabel: '1 Day Away',   frequency: 'Annual' },
            { id: 501, name: 'HP ProDesk 405 G8',       serial: 'SU-2024-112', type: 'system_unit', unit: 'Planning Unit',     owner: 'Santos, G.',           date: '2026-03-15', dateLabel: 'Mar 15, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '24 Days Away', frequency: 'Annual' },
            { id: 402, name: 'HP Monitor V24e G5',      serial: 'MO-2024-091', type: 'monitor',     unit: 'O&M Unit',          owner: 'Pedro Reyes',          date: '2026-02-20', dateLabel: 'Feb 20, 2026', status: 'due',       statusLabel: 'Due Soon',  daysLabel: '1 Day Away',   frequency: 'Annual' },
            { id: 103, name: 'Asus Vivo Mini PC',       serial: 'SU-2025-011', type: 'system_unit', unit: 'O&M Unit',          owner: 'Pedro Reyes',          date: '2026-03-12', dateLabel: 'Mar 12, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '21 Days Away', frequency: 'Annual' },
            { id: 502, name: 'Acer Veriton S2690',      serial: 'SU-2025-033', type: 'system_unit', unit: 'Field Operations',  owner: 'Field Team',           date: '2026-04-05', dateLabel: 'Apr 5, 2026',  status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '45 Days Away', frequency: 'Annual' },
            { id: 503, name: 'Epson L5290 Printer',     serial: 'PR-2024-077', type: 'printer',     unit: 'Dispatch Unit',     owner: 'Dispatch Unit',        date: '2026-04-05', dateLabel: 'Apr 5, 2026',  status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '45 Days Away', frequency: 'Semi-Annual' },
        ]
    },
    ADFIN: {
        name: 'Administrative & Finance Division',
        stats: { overdue: 2, dueSoon: 1, scheduled: 35 },
        units: ['All Units', 'Property Unit', 'Records Unit', 'HR Unit', 'Accounting Unit', 'Cashier Unit'],
        assets: [
            { id: 201, name: 'Lenovo ThinkPad T14',     serial: 'LP-2024-032', type: 'laptop',      unit: 'Property Unit',  owner: 'Ana Garcia',    date: '2026-02-12', dateLabel: 'Feb 12, 2026', status: 'overdue',   statusLabel: 'Overdue',  daysLabel: '7 Days Overdue', frequency: 'Annual' },
            { id: 601, name: 'Dell Monitor P2423D',     serial: 'MO-2023-067', type: 'monitor',     unit: 'Property Unit',  owner: 'Property Unit', date: '2026-02-12', dateLabel: 'Feb 12, 2026', status: 'overdue',   statusLabel: 'Overdue',  daysLabel: '7 Days Overdue', frequency: 'Annual' },
            { id: 301, name: 'Epson L3110',             serial: 'PR-2023-112', type: 'printer',     unit: 'Records Unit',   owner: 'Records Unit',  date: '2026-02-18', dateLabel: 'Feb 18, 2026', status: 'due',       statusLabel: 'Due Soon', daysLabel: '3 Days Away',    frequency: 'Semi-Annual' },
            { id: 602, name: 'HP ProDesk 400 G7',       serial: 'SU-2024-089', type: 'system_unit', unit: 'Records Unit',   owner: 'Records Staff', date: '2026-02-18', dateLabel: 'Feb 18, 2026', status: 'due',       statusLabel: 'Due Soon', daysLabel: '3 Days Away',    frequency: 'Annual' },
            { id: 102, name: 'HP EliteDesk 800 G5',     serial: 'SU-2023-156', type: 'system_unit', unit: 'Accounting Unit',owner: 'Maria Santos',  date: '2026-02-14', dateLabel: 'Feb 14, 2026', status: 'overdue',   statusLabel: 'Overdue',  daysLabel: '5 Days Overdue', frequency: 'Semi-Annual' },
            { id: 603, name: 'Lenovo IdeaCentre 3',     serial: 'SU-2024-201', type: 'system_unit', unit: 'Accounting Unit',owner: 'Reyes, F.',     date: '2026-02-14', dateLabel: 'Feb 14, 2026', status: 'overdue',   statusLabel: 'Overdue',  daysLabel: '5 Days Overdue', frequency: 'Annual' },
            { id: 203, name: 'Dell Latitude 3410',      serial: 'LP-2024-067', type: 'laptop',      unit: 'HR Unit',        owner: 'Carmen Lopez',  date: '2026-04-08', dateLabel: 'Apr 8, 2026',  status: 'scheduled', statusLabel: 'Scheduled',daysLabel: '48 Days Away',   frequency: 'Annual' },
            { id: 202, name: 'Acer TravelMate P2',      serial: 'LP-2024-055', type: 'laptop',      unit: 'Cashier Unit',   owner: 'Jose Ramirez',  date: '2026-03-05', dateLabel: 'Mar 5, 2026',  status: 'scheduled', statusLabel: 'Scheduled',daysLabel: '14 Days Away',   frequency: 'Annual' },
        ]
    },
    ODM: {
        name: 'Office of the Department Manager',
        stats: { overdue: 0, dueSoon: 2, scheduled: 18 },
        units: ['All Units', 'ICT Unit', 'Legal Services', 'Public Relations', 'BAC Unit'],
        assets: [
            { id: 302, name: 'HP LaserJet Pro M404n',   serial: 'PR-2024-089', type: 'printer',     unit: 'ICT Unit',         owner: 'ICT Unit',    date: '2026-02-22', dateLabel: 'Feb 22, 2026', status: 'due',       statusLabel: 'Due Soon',  daysLabel: '3 Days Away',    frequency: 'Annual' },
            { id: 701, name: 'Lenovo ThinkPad L14',     serial: 'LP-2024-145', type: 'laptop',      unit: 'ICT Unit',         owner: 'ICT Staff',   date: '2026-02-22', dateLabel: 'Feb 22, 2026', status: 'due',       statusLabel: 'Due Soon',  daysLabel: '3 Days Away',    frequency: 'Semi-Annual' },
            { id: 204, name: 'Lenovo ThinkPad E14',     serial: 'LP-2024-112', type: 'laptop',      unit: 'Legal Services',   owner: 'Legal Staff', date: '2026-05-10', dateLabel: 'May 10, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '80 Days Away',   frequency: 'Annual' },
            { id: 205, name: 'Dell Vostro 3400',        serial: 'LP-2025-023', type: 'laptop',      unit: 'Public Relations', owner: 'PR Staff',    date: '2026-06-15', dateLabel: 'Jun 15, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '116 Days Away',  frequency: 'Annual' },
            { id: 702, name: 'HP EliteBook 840 G8',     serial: 'LP-2025-044', type: 'laptop',      unit: 'BAC Unit',         owner: 'BAC Staff',   date: '2026-06-15', dateLabel: 'Jun 15, 2026', status: 'scheduled', statusLabel: 'Scheduled', daysLabel: '116 Days Away',  frequency: 'Annual' },
        ]
    }
};

/* =========================================================
   STATE
   ========================================================= */
let activeDivisionKey = null;
let activeSchedSubtab = 'grouped';

/* =========================================================
   MAIN VIEW SWITCHER
   ========================================================= */
function switchView(view) {
    document.getElementById('view-detailed').className = view === 'detailed' ? 'd-block' : 'd-none';
    document.getElementById('view-summary').className  = view === 'summary'  ? 'd-block' : 'd-none';
    document.getElementById('btnDetailed').classList.toggle('active', view === 'detailed');
    document.getElementById('btnSummary').classList.toggle('active',  view === 'summary');

    // Hide filter bar when in summary/division view context
    document.getElementById('filterBar').style.display = view === 'summary' ? 'none' : '';

    // If switching back to summary, close division view
    if (view === 'summary') backToDivisionsSchedule();
}

/* =========================================================
   OPEN DIVISION VIEW (replaces modal)
   ========================================================= */
function viewDivisionSchedule(divKey) {
    activeDivisionKey = divKey;
    const div = SCHED_DIVISIONS[divKey];

    // Hide cards grid
    document.getElementById('divisionCardsGrid').style.display = 'none';

    // Set header content
    document.getElementById('dvSchedName').textContent = div.name;
    document.getElementById('dvSchedBreadcrumb').textContent = div.name;

    // Stat badges
    const badges = document.getElementById('dvSchedBadges');
    badges.innerHTML = `
        <span class="dv-stat-pill total"><i class="fas fa-layer-group"></i> ${div.assets.length} Assets</span>
        ${div.stats.overdue  > 0 ? `<span class="dv-stat-pill overdue"><i class="fas fa-exclamation-circle"></i> ${div.stats.overdue} Overdue</span>` : ''}
        ${div.stats.dueSoon  > 0 ? `<span class="dv-stat-pill due-soon"><i class="fas fa-clock"></i> ${div.stats.dueSoon} Due Soon</span>` : ''}
        <span class="dv-stat-pill scheduled"><i class="fas fa-check-circle"></i> ${div.stats.scheduled} Scheduled</span>
    `;

    // Populate unit filter
    const unitSel = document.getElementById('dvSchedUnitFilter');
    unitSel.innerHTML = '';
    div.units.forEach(u => {
        const opt = document.createElement('option');
        opt.value = u === 'All Units' ? '' : u;
        opt.textContent = u;
        unitSel.appendChild(opt);
    });

    // Reset to default subtab
    activeSchedSubtab = 'grouped';
    document.getElementById('dvSchedTabGrouped').classList.add('active');
    document.getElementById('dvSchedTabAll').classList.remove('active');
    document.getElementById('dvSchedTabEmp').classList.remove('active');
    document.getElementById('dvSchedPanelGrouped').classList.add('dv-panel-active');
    document.getElementById('dvSchedPanelAll').classList.remove('dv-panel-active');
    document.getElementById('dvSchedPanelEmployees').classList.remove('dv-panel-active');

    // Show division view
    const dvEl = document.getElementById('divisionViewSchedule');
    dvEl.classList.add('dv-active');

    renderScheduleDivision();
}

/* =========================================================
   BACK TO DIVISION CARDS GRID
   ========================================================= */
function backToDivisionsSchedule() {
    document.getElementById('divisionCardsGrid').style.display = '';
    document.getElementById('divisionViewSchedule').classList.remove('dv-active');
    activeDivisionKey = null;
}

/* =========================================================
   SUBTAB SWITCHER
   ========================================================= */
function switchSchedSubtab(tab) {
    activeSchedSubtab = tab;
    document.getElementById('dvSchedTabGrouped').classList.toggle('active',   tab === 'grouped');
    document.getElementById('dvSchedTabAll').classList.toggle('active',        tab === 'all');
    document.getElementById('dvSchedTabEmp').classList.toggle('active',        tab === 'employees');
    document.getElementById('dvSchedPanelGrouped').classList.toggle('dv-panel-active',   tab === 'grouped');
    document.getElementById('dvSchedPanelAll').classList.toggle('dv-panel-active',        tab === 'all');
    document.getElementById('dvSchedPanelEmployees').classList.toggle('dv-panel-active',  tab === 'employees');
    renderScheduleDivision();
}

/* =========================================================
   RENDER FUNCTION (responds to both filter + subtab changes)
   ========================================================= */
function renderScheduleDivision() {
    if (!activeDivisionKey) return;
    const div = SCHED_DIVISIONS[activeDivisionKey];

    const unitFilter = document.getElementById('dvSchedUnitFilter').value;
    let assets = div.assets.filter(a => !unitFilter || a.unit === unitFilter);

    document.getElementById('dvSchedCount').textContent =
        assets.length + ' asset' + (assets.length !== 1 ? 's' : '');

    if (activeSchedSubtab === 'grouped')   renderSchedGrouped(assets);
    else if (activeSchedSubtab === 'all') renderSchedAll(assets);
    else                                  renderSchedEmployees(assets);
}

/* ---- Render: Grouped by Same Due Date ---- */
function renderSchedGrouped(assets) {
    const panel = document.getElementById('dvSchedPanelGrouped');

    if (!assets.length) { panel.innerHTML = emptyState('No equipment matches the selected filter.'); return; }

    // Group by date
    const groups = {};
    assets.forEach(a => {
        if (!groups[a.date]) groups[a.date] = [];
        groups[a.date].push(a);
    });

    // Sort dates ascending
    const sortedDates = Object.keys(groups).sort();

    // Keep only groups with 2+ assets (same date requirement)
    const multiGroups = sortedDates.filter(d => groups[d].length >= 2);

    if (!multiGroups.length) {
        panel.innerHTML = `
            <div class="dv-empty">
                <i class="fas fa-calendar-check"></i>
                <h4>No Shared Due Dates</h4>
                <p>No equipment in this selection shares the same due date. Switch to <strong>All Equipment</strong> to see individual records.</p>
            </div>`;
        return;
    }

    const iconMap = { system_unit: 'fa-desktop', laptop: 'fa-laptop', printer: 'fa-print', monitor: 'fa-tv' };
    const typeClass = { system_unit: '', laptop: '', printer: 'type-printer', monitor: 'type-monitor' };

    let html = '';
    multiGroups.forEach(date => {
        const group = groups[date];
        const rep = group[0];
        const markerClass = rep.status === 'overdue' ? 'overdue' : rep.status === 'due' ? 'due-soon' : 'scheduled';
        const sublabel = rep.status === 'overdue' ? rep.daysLabel :
                         rep.status === 'due'     ? rep.daysLabel : rep.daysLabel;

        html += `
        <div class="dv-date-group">
            <div class="dv-date-group-header">
                <div class="dv-date-group-marker ${markerClass}"></div>
                <div class="dv-date-group-label">${rep.dateLabel}</div>
                <div class="dv-date-group-sublabel">${sublabel}</div>
                <div class="dv-date-group-count"><i class="fas fa-layer-group"></i> ${group.length} Equipment</div>
            </div>
            <div class="dv-group-table-wrap">
                <table class="dv-group-table">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Unit / Section</th>
                            <th>Owner</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>`;

        group.forEach(a => {
            const badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
            const badgeLbl = a.status === 'overdue' ? '<i class="fas fa-circle"></i> Overdue' : a.status === 'due' ? '<i class="fas fa-clock"></i> Due Soon' : '<i class="fas fa-check-circle"></i> Scheduled';
            const actionBtn = a.status !== 'scheduled'
                ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.id},'${a.type}')"><i class="fas fa-tools"></i> Perform Now</button>`
                : `<button class="mnt-btn-wait" disabled><i class="fas fa-hourglass-half"></i> Wait</button>`;

            html += `
                        <tr>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon ${typeClass[a.type] || ''}"><i class="fas ${iconMap[a.type] || 'fa-desktop'}"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">${a.name}</div>
                                        <div class="mnt-equip-serial">SN: ${a.serial}</div>
                                    </div>
                                </div>
                            </td>
                            <td><div class="mnt-location-primary">${a.unit}</div></td>
                            <td><div class="mnt-tech-name">${a.owner}</div></td>
                            <td><span class="mnt-badge mnt-badge-frequency">${a.frequency}</span></td>
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
    const tbody = document.getElementById('dvSchedAllBody');
    const iconMap = { system_unit: 'fa-desktop', laptop: 'fa-laptop', printer: 'fa-print', monitor: 'fa-tv' };
    const typeClass = { system_unit: '', laptop: '', printer: 'type-printer', monitor: 'type-monitor' };

    if (!assets.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-light); font-style:italic;">No equipment matches the selected filter.</td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    // Sort by date ascending
    assets.sort((a, b) => a.date.localeCompare(b.date));
    assets.forEach(a => {
        const badgeCls = a.status === 'overdue' ? 'mnt-badge-overdue' : a.status === 'due' ? 'mnt-badge-due' : 'mnt-badge-scheduled';
        const badgeLbl = a.status === 'overdue' ? '<i class="fas fa-circle"></i> Overdue' : a.status === 'due' ? '<i class="fas fa-clock"></i> Due Soon' : '<i class="fas fa-check-circle"></i> Scheduled';
        const actionBtn = a.status !== 'scheduled'
            ? `<button class="mnt-btn-perform" onclick="performMaintenance(${a.id},'${a.type}')"><i class="fas fa-tools"></i> Perform Now</button>`
            : `<button class="mnt-btn-wait" disabled><i class="fas fa-hourglass-half"></i> Wait</button>`;
        const daysCls = a.status === 'overdue' ? 'overdue' : a.status === 'due' ? 'due-soon' : 'scheduled';

        tbody.innerHTML += `
            <tr>
                <td>
                    <div class="mnt-equip-cell">
                        <div class="mnt-equip-icon ${typeClass[a.type] || ''}"><i class="fas ${iconMap[a.type] || 'fa-desktop'}"></i></div>
                        <div>
                            <div class="mnt-equip-name">${a.name}</div>
                            <div class="mnt-equip-serial">SN: ${a.serial}</div>
                        </div>
                    </div>
                </td>
                <td><div class="mnt-location-primary">${a.unit}</div></td>
                <td><div class="mnt-tech-name">${a.owner}</div></td>
                <td>
                    <div class="mnt-date-primary ${a.status === 'overdue' ? 'overdue' : a.status === 'due' ? 'due-soon' : 'scheduled'}">${a.dateLabel}</div>
                    <div class="mnt-date-sub"><i class="fas fa-clock"></i> ${a.daysLabel}</div>
                </td>
                <td><span class="mnt-badge mnt-badge-frequency">${a.frequency}</span></td>
                <td><span class="mnt-badge ${badgeCls}">${badgeLbl}</span></td>
                <td>${actionBtn}</td>
            </tr>`;
    });
}

/* =========================================================
   EMPLOYEE MOCK DATA — Schedule
   Each employee has assigned assets cross-referenced by
   assetId against SCHED_DIVISIONS data.
   ========================================================= */
const SCHED_EMPLOYEES = {
    EOD: [
        {
            id: 'E001', name: 'Engr. Juan Dela Cruz', position: 'Project Engineer', unit: 'Construction Unit',
            assets: [101, 303]
        },
        {
            id: 'E002', name: 'Planning Staff', position: 'Planner', unit: 'Planning Unit',
            assets: [401, 501]
        },
        {
            id: 'E003', name: 'Pedro Reyes', position: 'O&M Technician', unit: 'O&M Unit',
            assets: [402, 103]
        },
        {
            id: 'E004', name: 'Field Team Lead', position: 'Field Engineer', unit: 'Field Operations',
            assets: [502]
        },
        {
            id: 'E005', name: 'Dispatch Officer', position: 'Dispatcher', unit: 'Dispatch Unit',
            assets: [503]
        },
    ],
    ADFIN: [
        {
            id: 'E006', name: 'Ana Garcia', position: 'Property Custodian', unit: 'Property Unit',
            assets: [201, 601]
        },
        {
            id: 'E007', name: 'Records Unit Staff', position: 'Records Officer', unit: 'Records Unit',
            assets: [301, 602]
        },
        {
            id: 'E008', name: 'Maria Santos', position: 'Accountant', unit: 'Accounting Unit',
            assets: [102, 603]
        },
        {
            id: 'E009', name: 'Carmen Lopez', position: 'HR Officer', unit: 'HR Unit',
            assets: [203]
        },
        {
            id: 'E010', name: 'Jose Ramirez', position: 'Cashier', unit: 'Cashier Unit',
            assets: [202]
        },
    ],
    ODM: [
        {
            id: 'E011', name: 'ICT Staff', position: 'ICT Coordinator', unit: 'ICT Unit',
            assets: [302, 701]
        },
        {
            id: 'E012', name: 'Legal Staff', position: 'Legal Officer', unit: 'Legal Services',
            assets: [204]
        },
        {
            id: 'E013', name: 'PR Staff', position: 'Public Relations Officer', unit: 'Public Relations',
            assets: [205]
        },
        {
            id: 'E014', name: 'BAC Unit Staff', position: 'BAC Secretary', unit: 'BAC Unit',
            assets: [702]
        },
    ]
};

/* ---- Render: By Employee ---- */
function renderSchedEmployees(filteredAssets) {
    const grid = document.getElementById('dvSchedEmpGrid');
    if (!activeDivisionKey) return;

    const unitFilter    = document.getElementById('dvSchedUnitFilter').value;
    const allEmployees  = SCHED_EMPLOYEES[activeDivisionKey] || [];
    const filteredIds   = new Set(filteredAssets.map(a => a.id));

    // Build a lookup from asset id -> asset object for this division
    const assetLookup = {};
    (SCHED_DIVISIONS[activeDivisionKey].assets || []).forEach(a => assetLookup[a.id] = a);

    const iconMap  = { system_unit: 'fa-desktop', laptop: 'fa-laptop', printer: 'fa-print', monitor: 'fa-tv' };
    const typeClass = { system_unit: '', laptop: '', printer: 'type-printer', monitor: 'type-monitor' };

    // Filter employees by unit
    let employees = allEmployees.filter(e => !unitFilter || e.unit === unitFilter);

    // Only include employees who have at least 1 asset in the current filtered set
    employees = employees.filter(e => e.assets.some(id => filteredIds.has(id)));

    if (!employees.length) {
        grid.innerHTML = `<div class="dv-empty" style="grid-column:1/-1">
            <i class="fas fa-users"></i>
            <h4>No Employees Found</h4>
            <p>No employees match the selected unit filter.</p>
        </div>`;
        return;
    }

    let html = '';
    employees.forEach(emp => {
        // Get employee's assets that are in the current filtered set
        const empAssets = emp.assets
            .filter(id => filteredIds.has(id))
            .map(id => assetLookup[id])
            .filter(Boolean);

        const overdue  = empAssets.filter(a => a.status === 'overdue').length;
        const due      = empAssets.filter(a => a.status === 'due').length;
        const sched    = empAssets.filter(a => a.status === 'scheduled').length;

        const cardClass = overdue > 0 ? 'has-overdue' : due > 0 ? 'has-due-soon' : 'all-scheduled';
        const overdueClass  = overdue  > 0 ? 'is-overdue' : 'zero';
        const dueClass      = due      > 0 ? 'is-due'     : 'zero';
        const schedClass    = sched    > 0 ? 'is-sched'   : 'zero';

        // Equipment rows (max 4 shown, rest hidden)
        const shown = empAssets.slice(0, 4);
        const extra = empAssets.length - shown.length;

        let equipRows = '';
        shown.forEach(a => {
            const chipClass = a.status === 'overdue' ? 'overdue' : a.status === 'due' ? 'due-soon' : 'scheduled';
            const chipLbl   = a.status === 'overdue' ? `<i class="fas fa-exclamation-circle"></i> ${a.dateLabel}` :
                              a.status === 'due'     ? `<i class="fas fa-clock"></i> ${a.dateLabel}` :
                                                       `<i class="fas fa-check-circle"></i> ${a.dateLabel}`;
            equipRows += `
                <div class="dv-emp-equip-row">
                    <div class="dv-emp-equip-icon ${typeClass[a.type] || ''}">
                        <i class="fas ${iconMap[a.type] || 'fa-desktop'}"></i>
                    </div>
                    <div class="dv-emp-equip-info">
                        <div class="dv-emp-equip-name">${a.name}</div>
                        <div class="dv-emp-equip-serial">${a.serial}</div>
                    </div>
                    <span class="dv-emp-due-chip ${chipClass}">${chipLbl}</span>
                </div>`;
        });

        if (extra > 0) {
            equipRows += `<div class="dv-emp-no-equip">+${extra} more equipment</div>`;
        }

        // Worst-status summary label for footer
        const footerStatus = overdue > 0
            ? `<span style="color:var(--color-danger);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-exclamation-circle"></i> ${overdue} overdue item${overdue > 1 ? 's' : ''} need attention</span>`
            : due > 0
            ? `<span style="color:var(--color-warning);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-clock"></i> ${due} item${due > 1 ? 's' : ''} due soon</span>`
            : `<span style="color:var(--color-success);font-weight:700;font-size:var(--text-xs);"><i class="fas fa-check-circle"></i> All equipment on schedule</span>`;

        html += `
        <div class="dv-emp-card ${cardClass}">
            <div class="dv-emp-card-header">
                <div class="dv-emp-avatar"><i class="fas fa-user"></i></div>
                <div class="dv-emp-identity">
                    <div class="dv-emp-name">${emp.name}</div>
                    <div class="dv-emp-meta">
                        <span class="dv-emp-position">${emp.position}</span>
                        <span class="dv-emp-unit-tag"><i class="fas fa-map-marker-alt"></i> ${emp.unit}</span>
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
                <div class="dv-emp-total-label"><strong>${empAssets.length}</strong> assigned asset${empAssets.length !== 1 ? 's' : ''}</div>
                ${footerStatus}
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

function emptyState(msg) {
    return `<div class="dv-empty"><i class="fas fa-search"></i><h4>No Results</h4><p>${msg}</p></div>`;
}

function performMaintenance(id, type) {
    sessionStorage.setItem('selectedEquipmentId', id);
    sessionStorage.setItem('selectedEquipmentType', type);
    window.location.href = '?page=perform-maintenance&id=' + id + '&type=' + type;
}