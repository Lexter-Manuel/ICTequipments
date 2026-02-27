<?php
// modules/maintenance/maintenance-schedule.php
?>
<link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-schedule.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-checklist.css?v=<?php echo time(); ?>">

<div class="page-content">

    <!-- ── PAGE HEADER ── -->
    <div class="mnt-page-header">
        <div class="mnt-header-left">
            <div class="mnt-header-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <h1 class="mnt-page-title">Maintenance Schedule</h1>
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
            <!-- <button class="mnt-btn-export"><i class="fas fa-file-export"></i> Export</button> -->
        </div>
    </div>

    <!-- ── TOP-LEVEL STAT CARDS ── -->
    <div class="mnt-sched-stats" id="statsBar">
        <div class="mnt-sched-stat overdue">
            <div class="mnt-sched-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="mnt-sched-stat-value" id="statOverdue"><span class="spinner-border spinner-border-sm"></span></div>
            <div class="mnt-sched-stat-label">Overdue</div>
        </div>
        <div class="mnt-sched-stat due-soon">
            <div class="mnt-sched-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="mnt-sched-stat-value" id="statDueSoon"><span class="spinner-border spinner-border-sm"></span></div>
            <div class="mnt-sched-stat-label">Due Soon</div>
        </div>
        <div class="mnt-sched-stat scheduled">
            <div class="mnt-sched-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="mnt-sched-stat-value" id="statScheduled"><span class="spinner-border spinner-border-sm"></span></div>
            <div class="mnt-sched-stat-label">Scheduled</div>
        </div>
        <div class="mnt-sched-stat total">
            <div class="mnt-sched-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="mnt-sched-stat-value" id="statTotal"><span class="spinner-border spinner-border-sm"></span></div>
            <div class="mnt-sched-stat-label">Total Assets</div>
        </div>
    </div>

    <!-- ── FILTER BAR ── -->
    <div class="mnt-filter-bar" id="filterBar">
        <div class="mnt-filter-group grow-2">
            <span class="mnt-filter-label">Search</span>
            <div class="mnt-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="schedSearchInput" class="mnt-filter-input" placeholder="Search serial, brand, or owner…">
            </div>
        </div>
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Section/Unit</span>
            <select class="mnt-filter-select" id="schedSectionUnitFilter">
                <option value="">All Sections/Units</option>
                <!-- populated by JS -->
            </select>
        </div>
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Status</span>
            <select class="mnt-filter-select" id="schedStatusFilter">
                <option value="">All Statuses</option>
                <option value="overdue">Overdue</option>
                <option value="due_soon">Due Soon</option>
                <option value="scheduled">Scheduled</option>
            </select>
        </div>
        <div class="mnt-filter-actions">
            <button class="btn btn-primary" onclick="applySchedFilters()"><i class="fas fa-filter"></i> Apply</button>
            <!-- <button class="mnt-btn-export"><i class="fas fa-file-export"></i> Export</button> -->
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
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Equipment</th>
                            <th>Location / Owner</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="schedDetailedBody">
                        <tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading schedule…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mnt-table-footer">
                <span class="mnt-record-count" id="schedRecordCount"></span>
                <div class="mnt-pagination" id="schedPagination"></div>
                <div class="per-page-control">
                    <label>Rows:
                        <select id="schedPerPageSelect" onchange="changeSchedPerPage()">
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
    <div id="view-summary" class="d-none">
        <div class="mnt-summary-grid" id="divisionCardsGrid">
            <div class="text-center py-5" style="grid-column:1/-1">
                <span class="spinner-border spinner-border-sm me-2"></span> Loading divisions…
            </div>
        </div>

        <!-- ══════════════════════════════════════════════
             DIVISION DETAIL VIEW (inline, replaces modal)
        ══════════════════════════════════════════════ -->
        <div id="divisionViewSchedule">

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

            <div class="dv-card">
                <div class="dv-header">
                    <div class="dv-header-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="dv-header-info">
                        <div class="dv-division-name" id="dvSchedName"></div>
                        <div class="dv-stat-badges" id="dvSchedBadges"></div>
                    </div>
                </div>

                <div class="dv-toolbar">
                    <div class="dv-section-filter">
                        <label for="dvSchedUnitFilter"><i class="fas fa-filter" style="color:var(--primary-green);"></i> Unit / Section</label>
                        <select class="dv-filter-select" id="dvSchedUnitFilter" onchange="onDvUnitFilterChange()">
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

                <div class="dv-content">
                    <div class="dv-panel dv-panel-active" id="dvSchedPanelGrouped"></div>
                    <div class="dv-panel" id="dvSchedPanelAll">
                        <div class="dv-all-table-card">
                            <div style="overflow-x:auto;">
                                <table class="dv-group-table" id="dvSchedAllTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
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
                    <div class="dv-panel" id="dvSchedPanelEmployees">
                        <div class="dv-emp-grid" id="dvSchedEmpGrid"></div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /#view-summary -->

</div><!-- /.page-content -->

<?php include __DIR__ . '/../../includes/components/maintenance_modal.php'; ?>
<?php include __DIR__ . '/../../includes/components/detail_view_modal.php'; ?>
<script src="assets/js/maintenance-schedule.js?v=<?php echo time(); ?>"></script>