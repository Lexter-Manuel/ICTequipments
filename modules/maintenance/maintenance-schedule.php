<?php
// modules/inventory/maintenance-schedule.php
?>
<link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-schedule.css?v=<?php echo time(); ?>">

<div class="page-content">

    <!-- ── PAGE HEADER ── -->
    <div class="mnt-page-header">
        <div class="mnt-header-left">
            <div class="mnt-header-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <h1 class="mnt-page-title">Maintenance Schedule</h1>
                <p class="mnt-page-subtitle">Track upcoming and overdue maintenance tasks</p>
            </div>
        </div>
        <div class="mnt-header-right">
            <div class="mnt-view-toggle" id="mainViewToggle">
                <button class="mnt-toggle-btn active" id="btnDetailed" onclick="switchView('detailed')">
                    <i class="fas fa-list"></i> Specific Equipment
                </button>
                <button class="mnt-toggle-btn" id="btnSummary" onclick="switchView('summary')">
                    <i class="fas fa-th-large"></i> Per Section
                </button>
            </div>
            <button class="mnt-btn-export"><i class="fas fa-file-export"></i> Export</button>
        </div>
    </div>

    <!-- ── TOP-LEVEL STAT CARDS ── -->
    <div class="mnt-sched-stats" id="statsBar">
        <div class="mnt-sched-stat overdue">
            <div class="mnt-sched-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="mnt-sched-stat-value">3</div>
            <div class="mnt-sched-stat-label">Overdue</div>
        </div>
        <div class="mnt-sched-stat due-soon">
            <div class="mnt-sched-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="mnt-sched-stat-value">5</div>
            <div class="mnt-sched-stat-label">Due Soon</div>
        </div>
        <div class="mnt-sched-stat scheduled">
            <div class="mnt-sched-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="mnt-sched-stat-value">84</div>
            <div class="mnt-sched-stat-label">Scheduled</div>
        </div>
        <div class="mnt-sched-stat total">
            <div class="mnt-sched-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="mnt-sched-stat-value">92</div>
            <div class="mnt-sched-stat-label">Total Assets</div>
        </div>
    </div>

    <!-- ── FILTER BAR ── -->
    <div class="mnt-filter-bar" id="filterBar">
        <div class="mnt-filter-group grow-2">
            <span class="mnt-filter-label">Search</span>
            <div class="mnt-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="mnt-filter-input" placeholder="Search serial, brand, or owner…">
            </div>
        </div>
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Division / Section</span>
            <select class="mnt-filter-select">
                <option value="">All Sections</option>
                <option>Administrative and Finance Division</option>
                <option>Engineering and Operation Division</option>
                <option>Office of the Department Manager</option>
            </select>
        </div>
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Status</span>
            <select class="mnt-filter-select">
                <option value="">All Statuses</option>
                <option>Overdue</option>
                <option>Due Soon</option>
                <option>Scheduled</option>
            </select>
        </div>
        <div class="mnt-filter-actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <button class="mnt-btn-export"><i class="fas fa-file-export"></i> Export</button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         DETAILED VIEW
    ══════════════════════════════════════════════════ -->
    <div id="view-detailed" class="d-block">
        <div class="mnt-table-card">
            <div class="mnt-table-wrap">
                <table class="mnt-table">
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Equipment</th>
                            <th>Location / Owner</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- OVERDUE -->
                        <tr>
                            <td>
                                <div class="mnt-date-primary overdue">Feb 10, 2026</div>
                                <div class="mnt-date-sub overdue-label"><i class="fas fa-exclamation-circle"></i> 9 Days Overdue</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">Dell Optiplex 7080</div>
                                        <div class="mnt-equip-serial">SN: SU-2024-009</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Engineering Section</div>
                                <div class="mnt-location-sub">Construction Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Semi-Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-overdue"><i class="fas fa-circle"></i> Overdue</span></td>
                            <td><button class="mnt-btn-perform" onclick="performMaintenance(101,'system_unit')"><i class="fas fa-tools"></i> Perform Now</button></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="mnt-date-primary overdue">Feb 12, 2026</div>
                                <div class="mnt-date-sub overdue-label"><i class="fas fa-exclamation-circle"></i> 7 Days Overdue</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">Lenovo ThinkPad T14</div>
                                        <div class="mnt-equip-serial">SN: LP-2024-032</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Administrative Section</div>
                                <div class="mnt-location-sub">Property Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-overdue"><i class="fas fa-circle"></i> Overdue</span></td>
                            <td><button class="mnt-btn-perform" onclick="performMaintenance(201,'laptop')"><i class="fas fa-tools"></i> Perform Now</button></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="mnt-date-primary overdue">Feb 14, 2026</div>
                                <div class="mnt-date-sub overdue-label"><i class="fas fa-exclamation-circle"></i> 5 Days Overdue</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">HP EliteDesk 800 G5</div>
                                        <div class="mnt-equip-serial">SN: SU-2023-156</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Finance Section</div>
                                <div class="mnt-location-sub">Accounting Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Semi-Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-overdue"><i class="fas fa-circle"></i> Overdue</span></td>
                            <td><button class="mnt-btn-perform" onclick="performMaintenance(102,'system_unit')"><i class="fas fa-tools"></i> Perform Now</button></td>
                        </tr>
                        <!-- DUE SOON -->
                        <tr>
                            <td>
                                <div class="mnt-date-primary due-soon">Feb 20, 2026</div>
                                <div class="mnt-date-sub"><i class="fas fa-clock"></i> 1 Day Away</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon type-monitor"><i class="fas fa-tv"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">Samsung S24C450</div>
                                        <div class="mnt-equip-serial">SN: MO-2024-078</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Engineering Section</div>
                                <div class="mnt-location-sub">Planning Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-due"><i class="fas fa-clock"></i> Due Soon</span></td>
                            <td><button class="mnt-btn-perform" onclick="performMaintenance(401,'monitor')"><i class="fas fa-tools"></i> Perform Now</button></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="mnt-date-primary due-soon">Feb 20, 2026</div>
                                <div class="mnt-date-sub"><i class="fas fa-clock"></i> 1 Day Away</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon type-monitor"><i class="fas fa-tv"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">HP Monitor V24e G5</div>
                                        <div class="mnt-equip-serial">SN: MO-2024-091</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Engineering Section</div>
                                <div class="mnt-location-sub">O&amp;M Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-due"><i class="fas fa-clock"></i> Due Soon</span></td>
                            <td><button class="mnt-btn-perform" onclick="performMaintenance(402,'monitor')"><i class="fas fa-tools"></i> Perform Now</button></td>
                        </tr>
                        <!-- SCHEDULED -->
                        <tr>
                            <td>
                                <div class="mnt-date-primary scheduled">Mar 12, 2026</div>
                                <div class="mnt-date-sub"><i class="fas fa-calendar"></i> 21 Days Away</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">Asus Vivo Mini PC</div>
                                        <div class="mnt-equip-serial">SN: SU-2025-011</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Operation Section</div>
                                <div class="mnt-location-sub">O&amp;M Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-scheduled"><i class="fas fa-check-circle"></i> Scheduled</span></td>
                            <td><button class="mnt-btn-wait" disabled><i class="fas fa-hourglass-half"></i> Wait</button></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="mnt-date-primary scheduled">Mar 20, 2026</div>
                                <div class="mnt-date-sub"><i class="fas fa-calendar"></i> 29 Days Away</div>
                            </td>
                            <td>
                                <div class="mnt-equip-cell">
                                    <div class="mnt-equip-icon type-printer"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="mnt-equip-name">Canon PIXMA G3010</div>
                                        <div class="mnt-equip-serial">SN: PR-2023-145</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mnt-location-primary">Engineering Section</div>
                                <div class="mnt-location-sub">Construction Unit</div>
                            </td>
                            <td><span class="mnt-badge mnt-badge-frequency">Semi-Annual</span></td>
                            <td><span class="mnt-badge mnt-badge-scheduled"><i class="fas fa-check-circle"></i> Scheduled</span></td>
                            <td><button class="mnt-btn-wait" disabled><i class="fas fa-hourglass-half"></i> Wait</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mnt-table-footer">
                <span class="mnt-record-count">Showing 7 of 92 records</span>
                <div class="mnt-pagination">
                    <a class="mnt-page-btn active">1</a>
                    <a class="mnt-page-btn">2</a>
                    <a class="mnt-page-btn">3</a>
                    <span class="mnt-page-btn ellipsis">…</span>
                    <a class="mnt-page-btn">10</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         PER-SECTION SUMMARY VIEW — Division Cards Grid
    ══════════════════════════════════════════════════ -->
    <div id="view-summary" class="d-none">
        <div class="mnt-summary-grid" id="divisionCardsGrid">

            <!-- Engineering & Operation Division -->
            <div class="mnt-summary-card">
                <div class="mnt-summary-card-header">
                    <h3 class="mnt-summary-card-title">Engineering &amp; Operation Division</h3>
                </div>
                <div class="mnt-summary-stats">
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value overdue">1</div>
                        <div class="mnt-summary-stat-label">Overdue</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value due-soon">2</div>
                        <div class="mnt-summary-stat-label">Due Soon</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value scheduled">31</div>
                        <div class="mnt-summary-stat-label">Scheduled</div>
                    </div>
                </div>
                <div class="mnt-summary-body">
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">Engineering Section</div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Construction Unit</span>
                            <span class="mnt-section-status danger"><i class="fas fa-exclamation-circle"></i> 1 Overdue</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Planning Unit</span>
                            <span class="mnt-section-status warn"><i class="fas fa-clock"></i> 1 Due Soon</span>
                        </div>
                    </div>
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">Operation Section</div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">O&amp;M Unit</span>
                            <span class="mnt-section-status warn"><i class="fas fa-clock"></i> 1 Due Soon</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Field Operations</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Dispatch Unit</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                    </div>
                </div>
                <div class="mnt-summary-card-footer">
                    <button class="btn btn-outline-primary w-100 btn-sm" onclick="viewDivisionSchedule('EOD')">
                        <i class="fas fa-arrow-right"></i> View Division Assets
                    </button>
                </div>
            </div>

            <!-- Administrative & Finance Division -->
            <div class="mnt-summary-card">
                <div class="mnt-summary-card-header">
                    <h3 class="mnt-summary-card-title">Administrative &amp; Finance Division</h3>
                </div>
                <div class="mnt-summary-stats">
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value overdue">2</div>
                        <div class="mnt-summary-stat-label">Overdue</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value due-soon">1</div>
                        <div class="mnt-summary-stat-label">Due Soon</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value scheduled">35</div>
                        <div class="mnt-summary-stat-label">Scheduled</div>
                    </div>
                </div>
                <div class="mnt-summary-body">
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">Administrative Section</div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Property Unit</span>
                            <span class="mnt-section-status danger"><i class="fas fa-exclamation-circle"></i> 1 Overdue</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Records Unit</span>
                            <span class="mnt-section-status warn"><i class="fas fa-clock"></i> 1 Due Soon</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">HR Unit</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                    </div>
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">Finance Section</div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Accounting Unit</span>
                            <span class="mnt-section-status danger"><i class="fas fa-exclamation-circle"></i> 1 Overdue</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Cashier Unit</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                    </div>
                </div>
                <div class="mnt-summary-card-footer">
                    <button class="btn btn-outline-primary w-100 btn-sm" onclick="viewDivisionSchedule('ADFIN')">
                        <i class="fas fa-arrow-right"></i> View Division Assets
                    </button>
                </div>
            </div>

            <!-- Office of the Department Manager -->
            <div class="mnt-summary-card">
                <div class="mnt-summary-card-header">
                    <h3 class="mnt-summary-card-title">Office of the Department Manager</h3>
                </div>
                <div class="mnt-summary-stats">
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value total">0</div>
                        <div class="mnt-summary-stat-label">Overdue</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value due-soon">2</div>
                        <div class="mnt-summary-stat-label">Due Soon</div>
                    </div>
                    <div class="mnt-summary-stat">
                        <div class="mnt-summary-stat-value scheduled">18</div>
                        <div class="mnt-summary-stat-label">Scheduled</div>
                    </div>
                </div>
                <div class="mnt-summary-body">
                    <div class="mnt-section-group">
                        <div class="mnt-section-group-title">Department Units</div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">ICT Unit</span>
                            <span class="mnt-section-status warn"><i class="fas fa-clock"></i> 1 Due Soon</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Legal Services</span>
                            <span class="mnt-section-status warn"><i class="fas fa-clock"></i> 1 Due Soon</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">Public Relations</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                        <div class="mnt-section-row">
                            <span class="mnt-section-name">BAC Unit</span>
                            <span class="mnt-section-status ok"><i class="fas fa-check-circle"></i> All Good</span>
                        </div>
                    </div>
                </div>
                <div class="mnt-summary-card-footer">
                    <button class="btn btn-outline-primary w-100 btn-sm" onclick="viewDivisionSchedule('ODM')">
                        <i class="fas fa-arrow-right"></i> View Division Assets
                    </button>
                </div>
            </div>

        </div><!-- /.mnt-summary-grid -->

        <!-- ══════════════════════════════════════════════
             DIVISION DETAIL VIEW (inline, replaces modal)
        ══════════════════════════════════════════════ -->
        <div id="divisionViewSchedule">

            <!-- Top-bar: back button + breadcrumb -->
            <div class="dv-topbar">
                <button class="dv-back-btn" onclick="backToDivisionsSchedule()">
                    <i class="fas fa-arrow-left"></i> Back to Divisions
                </button>
                <div class="dv-topbar-meta">
                    <i class="fas fa-calendar-check"></i>
                    <span>Maintenance Schedule</span>
                    <span style="color:var(--border-color)">›</span>
                    <span id="dvSchedBreadcrumb" style="color:var(--text-dark); font-weight:600;"></span>
                </div>
            </div>

            <!-- Division header (mirrors profile-header-integrated) -->
            <div class="dv-card">
                <div class="dv-header">
                    <div class="dv-header-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="dv-header-info">
                        <div class="dv-division-name" id="dvSchedName"></div>
                        <div class="dv-stat-badges" id="dvSchedBadges"></div>
                    </div>
                </div>

                <!-- Toolbar: section/unit filter + sub-tabs -->
                <div class="dv-toolbar">
                    <div class="dv-section-filter">
                        <label for="dvSchedUnitFilter"><i class="fas fa-filter" style="color:var(--primary-green);"></i> Unit / Section</label>
                        <select class="dv-filter-select" id="dvSchedUnitFilter" onchange="renderScheduleDivision()">
                            <option value="">All Units</option>
                        </select>
                    </div>

                    <div class="dv-toolbar-divider"></div>

                    <div class="dv-subtabs">
                        <button class="dv-subtab-btn active" id="dvSchedTabGrouped" onclick="switchSchedSubtab('grouped')">
                            <i class="fas fa-calendar-alt"></i> Same Due Date
                        </button>
                        <button class="dv-subtab-btn" id="dvSchedTabAll" onclick="switchSchedSubtab('all')">
                            <i class="fas fa-list"></i> All Equipment
                        </button>
                        <button class="dv-subtab-btn" id="dvSchedTabEmp" onclick="switchSchedSubtab('employees')">
                            <i class="fas fa-users"></i> By Employee
                        </button>
                    </div>

                    <div class="dv-result-count" id="dvSchedCount"></div>
                </div>

                <!-- Content area -->
                <div class="dv-content">

                    <!-- Panel A: Grouped by Same Due Date -->
                    <div class="dv-panel dv-panel-active" id="dvSchedPanelGrouped">
                        <!-- Rendered by JS -->
                    </div>

                    <!-- Panel B: All Equipment flat list -->
                    <div class="dv-panel" id="dvSchedPanelAll">
                        <div class="dv-all-table-card">
                            <div style="overflow-x:auto;">
                                <table class="dv-group-table" id="dvSchedAllTable">
                                    <thead>
                                        <tr>
                                            <th>Equipment</th>
                                            <th>Unit / Section</th>
                                            <th>Owner</th>
                                            <th>Next Due Date</th>
                                            <th>Frequency</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dvSchedAllBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Panel C: By Employee -->
                    <div class="dv-panel" id="dvSchedPanelEmployees">
                        <div class="dv-emp-grid" id="dvSchedEmpGrid"></div>
                    </div>

                </div><!-- /.dv-content -->
            </div><!-- /.dv-card -->

        </div><!-- /#divisionViewSchedule -->

    </div><!-- /#view-summary -->

</div><!-- /.page-content -->

<script src="assets/js/maintenance-schedule.js?v=<?php echo time(); ?>"></script>