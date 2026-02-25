<?php
// modules/maintenance/maintenance-history.php
?>
<link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-history.css?v=<?php echo time(); ?>">

<style>
.mnt-date-mode-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
    flex-wrap: wrap;
}

.mnt-date-picker-group {
    display: none;
}

.mnt-date-picker-group.active {
    display: block;
}

.mnt-date-mode-wrap.mode-stack {
    flex-direction: column;
    align-items: stretch;
    gap: 4px;
}

.mnt-date-mode-wrap.mode-full > select {
    flex: 0 0 100%;
}
</style>

<div class="page-content">

    <!-- ── PAGE HEADER ── -->
    <div class="mnt-page-header">
        <div class="mnt-header-left">
            <div class="mnt-header-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h1 class="mnt-page-title">Maintenance History</h1>
            </div>
        </div>
        <div class="mnt-header-right">
            <div class="mnt-view-toggle" id="histMainViewToggle">
                <button class="mnt-toggle-btn active" id="btnHistoryDetailed" onclick="switchHistoryView('detailed')">
                    <i class="fas fa-list"></i> Specific Equipment
                </button>
                <button class="mnt-toggle-btn" id="btnHistorySummary" onclick="switchHistoryView('summary')">
                    <i class="fas fa-building"></i> Per Section
                </button>
            </div>
            <button id="exportHistoryBtn" class="mnt-btn-export" onclick="exportReport()">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </div>
    </div>

    <!-- ── TOP-LEVEL STAT CARDS ── -->
    <div class="mnt-hist-stats" id="histStatsBar">
        <div class="mnt-hist-stat total">
            <div class="mnt-hist-stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="mnt-hist-stat-value" id="statTotalRecords">&mdash;</div>
            <div class="mnt-hist-stat-label">Total Records</div>
        </div>
        <div class="mnt-hist-stat maintained">
            <div class="mnt-hist-stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="mnt-hist-stat-value" id="statMaintained">&mdash;</div>
            <div class="mnt-hist-stat-label">Maintained</div>
        </div>
        <div class="mnt-hist-stat excellent">
            <div class="mnt-hist-stat-icon"><i class="fas fa-star"></i></div>
            <div class="mnt-hist-stat-value" id="statExcellentGood">&mdash;</div>
            <div class="mnt-hist-stat-label">Excellent / Good</div>
        </div>
        <div class="mnt-hist-stat pending">
            <div class="mnt-hist-stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="mnt-hist-stat-value" id="statPending">&mdash;</div>
            <div class="mnt-hist-stat-label">Pending</div>
        </div>
    </div>

    <!-- ── FILTER BAR ── -->
    <div class="mnt-filter-bar" id="histFilterBar">

        <!-- Date Mode Selector -->
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Date Range</span>
            <div class="mnt-date-mode-wrap">
                <select class="mnt-filter-select" id="histDateMode" onchange="onHistDateModeChange()">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="custom">Custom Range</option>
                    <option value="alltime">All Time</option>
                </select>

                <!-- Daily picker -->
                <div class="mnt-date-picker-group" id="histPickerDaily">
                    <input type="date" id="histPickerDailyVal">
                </div>

                <!-- Weekly picker -->
                <div class="mnt-date-picker-group" id="histPickerWeekly">
                    <input type="week" id="histPickerWeeklyVal">
                </div>

                <!-- Monthly picker -->
                <div class="mnt-date-picker-group active" id="histPickerMonthly">
                    <input type="month" id="histPickerMonthlyVal">
                </div>

                <!-- Yearly picker -->
                <div class="mnt-date-picker-group" id="histPickerYearly">
                    <input type="number" id="histPickerYearlyVal" min="2000" max="2099" placeholder="Year">
                </div>
            
                <!-- Custom Range picker -->
                <div class="mnt-date-picker-group" id="histPickerCustom">
                    <input type="date" id="histPickerCustomFrom">
                    <span class="mnt-date-sep">to</span>
                    <input type="date" id="histPickerCustomTo">
                </div>

                <!-- All Time: no picker shown -->
            </div>
        </div>

        <!-- sectionUnit -->
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Section/Unit</span>
            <div class="mnt-sectionUnit-wrap">
                <select class="mnt-filter-select" id="histSectionUnitFilter">
                    <option value="">All Sections/Units</option>
                </select>
            </div>
        </div>

        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Search</span>
            <div class="mnt-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="mnt-filter-input" id="histSearchInput"
                       placeholder="Search serial, technician, or remarks…">
            </div>
        </div>

        <div class="mnt-filter-actions">
            <span class="mnt-filter-label" style="opacity: 0;">space</span>
            <button class="btn btn-primary" onclick="loadDetailedHistory()">
                <i class="fas fa-filter"></i> Apply
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         DETAILED VIEW
    ══════════════════════════════════════════════════ -->
    <div id="history-detailed" class="d-block">
        <div class="mnt-table-card">
            <div class="mnt-table-wrap">
                <table class="mnt-table">
                    <thead>
                        <tr>
                            <th>Date Completed</th>
                            <th>Equipment</th>
                            <th>Location</th>
                            <th>Technician</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="histDetailedBody">
                        <tr>
                            <td colspan="6" style="text-align:center; padding:60px; color:var(--text-light);">
                                <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
                                <div style="margin-top:10px;">Loading maintenance records…</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
           <div class="mnt-table-footer">
                <span class="mnt-record-count" id="histRecordCount">Loading…</span>
                <div class="mnt-pagination" id="histPagination"></div>
                <div class="per-page-control">
                    <label>Rows:
                        <select id="histPerPageSelect" onchange="changeHistPerPage()">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         PER-SECTION SUMMARY VIEW — Division Cards Grid
    ══════════════════════════════════════════════════ -->
    <div id="history-summary" class="d-none">
        <div class="mnt-summary-grid" id="histDivisionCardsGrid">
            <!-- Rendered dynamically by JS -->
            <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-light);">
                <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
                <div style="margin-top:10px;">Loading division summaries…</div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════
             DIVISION DETAIL VIEW (inline, replaces grid)
        ══════════════════════════════════════════════ -->
        <div id="divisionViewHistory">

            <!-- Top-bar: back button + breadcrumb -->
            <div class="dv-topbar">
                <button class="dv-back-btn" onclick="backToDivisionsHistory()">
                    <i class="fas fa-arrow-left"></i> Back to Divisions
                </button>
                <div class="dv-topbar-meta">
                    <i class="fas fa-history"></i>
                    <span>Maintenance History</span>
                    <span style="color:var(--border-color)">›</span>
                    <span id="dvHistBreadcrumb" style="color:var(--text-dark); font-weight:600;"></span>
                </div>
            </div>

            <!-- Division header -->
            <div class="dv-card">
                <div class="dv-header">
                    <div class="dv-header-icon"><i class="fas fa-building"></i></div>
                    <div class="dv-header-info">
                        <div class="dv-division-name" id="dvHistName"></div>
                        <div class="dv-stat-badges" id="dvHistBadges"></div>
                    </div>
                </div>

                <!-- Toolbar: section/unit filter + sub-tabs -->
                <div class="dv-toolbar">
                    <div class="dv-section-filter">
                        <label for="dvHistUnitFilter"><i class="fas fa-filter" style="color:var(--primary-green);"></i> Unit / Section</label>
                        <select class="dv-filter-select" id="dvHistUnitFilter" onchange="renderHistoryDivision()">
                            <option value="">All Units</option>
                        </select>
                    </div>

                    <div class="dv-toolbar-divider"></div>

                    <div class="dv-subtabs">
                        <button class="dv-subtab-btn active" id="dvHistTabGrouped" onclick="switchHistSubtab('grouped')">
                            <i class="fas fa-calendar-alt"></i> Same Maintenance Date
                        </button>
                        <button class="dv-subtab-btn" id="dvHistTabAll" onclick="switchHistSubtab('all')">
                            <i class="fas fa-list"></i> All Equipment
                        </button>
                        <button class="dv-subtab-btn" id="dvHistTabEmp" onclick="switchHistSubtab('employees')">
                            <i class="fas fa-users"></i> By Employee
                        </button>
                    </div>

                    <div class="dv-result-count" id="dvHistCount"></div>
                </div>

                <!-- Content area -->
                <div class="dv-content">

                    <!-- Panel A: Grouped by Same Maintenance Date -->
                    <div class="dv-panel dv-panel-active" id="dvHistPanelGrouped">
                        <!-- Rendered by JS -->
                    </div>

                    <!-- Panel B: All Equipment flat list -->
                    <div class="dv-panel" id="dvHistPanelAll">
                        <div class="dv-all-table-card">
                            <div style="overflow-x:auto;">
                                <table class="dv-group-table" id="dvHistAllTable">
                                    <thead>
                                        <tr>
                                            <th>Equipment</th>
                                            <th>Unit / Section</th>
                                            <th>Technician</th>
                                            <th>Maintenance Date</th>
                                            <th>Time</th>
                                            <th>Condition</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dvHistAllBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Panel C: By Employee -->
                    <div class="dv-panel" id="dvHistPanelEmployees">
                        <div class="dv-emp-grid" id="dvHistEmpGrid"></div>
                    </div>

                </div><!-- /.dv-content -->
            </div><!-- /.dv-card -->

        </div><!-- /#divisionViewHistory -->

    </div><!-- /#history-summary -->

</div><!-- /.page-content -->

<script>

function onHistDateModeChange() {
    const mode = document.getElementById('histDateMode').value;
    const wrap = document.querySelector('.mnt-date-mode-wrap');
    wrap.classList.remove('mode-full', 'mode-stack');

    // Hide all picker groups first
    document.querySelectorAll('.mnt-date-picker-group').forEach(el => el.classList.remove('active'));

    const today = new Date();
    const pad   = n => String(n).padStart(2, '0');
    const iso   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

    switch (mode) {
        case 'daily':
            document.getElementById('histPickerDaily').classList.add('active');
            if (!document.getElementById('histPickerDailyVal').value) {
                document.getElementById('histPickerDailyVal').value = iso(today);
            }
            break;

        case 'weekly':
            document.getElementById('histPickerWeekly').classList.add('active');
            if (!document.getElementById('histPickerWeeklyVal').value) {
                // Default to current ISO week
                const jan4 = new Date(today.getFullYear(), 0, 4);
                const week = Math.ceil(((today - jan4) / 86400000 + jan4.getDay() + 1) / 7);
                document.getElementById('histPickerWeeklyVal').value =
                    `${today.getFullYear()}-W${pad(week)}`;
            }
            break;

        case 'monthly':
            document.getElementById('histPickerMonthly').classList.add('active');
            if (!document.getElementById('histPickerMonthlyVal').value) {
                document.getElementById('histPickerMonthlyVal').value =
                    `${today.getFullYear()}-${pad(today.getMonth()+1)}`;
            }
            break;

        case 'yearly':
            document.getElementById('histPickerYearly').classList.add('active');
            if (!document.getElementById('histPickerYearlyVal').value) {
                document.getElementById('histPickerYearlyVal').value = today.getFullYear();
            }
            break;

        case 'custom':
            document.getElementById('histPickerCustom').classList.add('active');
            wrap.classList.add('mode-stack'); // 👈 stack 100% / 100%
            if (!document.getElementById('histPickerCustomFrom').value) {
                document.getElementById('histPickerCustomFrom').value =
                    `${today.getFullYear()}-${pad(today.getMonth()+1)}-01`;
                document.getElementById('histPickerCustomTo').value = iso(today);
            }
            break;

        case 'alltime':
            // No picker needed
            wrap.classList.add('mode-full');
            break;
    }
}

function getHistDateParams() {
    const mode = document.getElementById('histDateMode').value;
    const pad  = n => String(n).padStart(2, '0');
    let dateFrom = '', dateTo = '', rangeLabel = '';

    const today    = new Date();
    const isoToday = `${today.getFullYear()}-${pad(today.getMonth()+1)}-${pad(today.getDate())}`;

    switch (mode) {

        case 'daily': {
            const v = document.getElementById('histPickerDailyVal').value || isoToday;
            dateFrom = dateTo = v;
            rangeLabel = `Daily: ${v}`;
            break;
        }

        case 'weekly': {
            const v = document.getElementById('histPickerWeeklyVal').value; // "YYYY-Www"
            if (v) {
                // Parse ISO week → Monday (start) and Sunday (end)
                const [yearStr, weekStr] = v.split('-W');
                const year = parseInt(yearStr), week = parseInt(weekStr);
                // Jan 4 of the year is always in week 1
                const jan4   = new Date(year, 0, 4);
                const monday = new Date(jan4);
                monday.setDate(jan4.getDate() - ((jan4.getDay() + 6) % 7) + (week - 1) * 7);
                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                const fmtD   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
                dateFrom = fmtD(monday);
                dateTo   = fmtD(sunday);
                rangeLabel = `Week ${pad(week)}, ${year} (${dateFrom} – ${dateTo})`;
            }
            break;
        }

        case 'monthly': {
            const v = document.getElementById('histPickerMonthlyVal').value; // "YYYY-MM"
            if (v) {
                const [y, m] = v.split('-').map(Number);
                const lastDay = new Date(y, m, 0).getDate();
                dateFrom = `${y}-${pad(m)}-01`;
                dateTo   = `${y}-${pad(m)}-${pad(lastDay)}`;
                rangeLabel = `${new Date(y, m-1).toLocaleString('default',{month:'long'})} ${y}`;
            }
            break;
        }

        case 'yearly': {
            const y = parseInt(document.getElementById('histPickerYearlyVal').value) || today.getFullYear();
            dateFrom = `${y}-01-01`;
            dateTo   = `${y}-12-31`;
            rangeLabel = `Year ${y}`;
            break;
        }

        case 'custom': {
            dateFrom   = document.getElementById('histPickerCustomFrom').value;
            dateTo     = document.getElementById('histPickerCustomTo').value;
            rangeLabel = `Custom: ${dateFrom} – ${dateTo}`;
            break;
        }

        case 'alltime':
        default:
            dateFrom = '';
            dateTo   = '';
            rangeLabel = 'All Time';
            break;
    }

    return { dateFrom, dateTo, rangeLabel };
}

// Initialise pickers to sensible defaults on page load
(function initHistDatePicker() {
    const today = new Date();
    const pad   = n => String(n).padStart(2, '0');
    // Default mode is "monthly" — set current month
    document.getElementById('histPickerMonthlyVal').value =
        `${today.getFullYear()}-${pad(today.getMonth()+1)}`;
})();

// Load section/unit filter options
(async function loadSectionUnitFilter() {
    try {
        const resp = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : ''}ajax/get_section_units.php`);
        const json = await resp.json();
        if (json.success && json.data) {
            const sel = document.getElementById('histSectionUnitFilter');
            json.data.forEach(loc => {
                const opt   = document.createElement('option');
                opt.value   = loc.location_name;
                const badge = loc.location_type_id == 2 ? '[Section]' : '[Unit]';
                opt.textContent = `${loc.location_name} ${badge}`;
                sel.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Failed to load section/unit filter:', e);
    }
})();
</script>

<?php include __DIR__ . '/../../includes/components/detail_view_modal.php'; ?>
<script src="assets/js/maintenance-history.js?v=<?php echo time(); ?>"></script>