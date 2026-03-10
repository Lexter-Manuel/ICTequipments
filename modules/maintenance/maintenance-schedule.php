<?php
// modules/maintenance/maintenance-schedule.php
require_once '../../config/session-guard.php';
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

<!-- ══════════════════════════════════════════════════════
     BATCH INITIALIZE MODAL
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="batchInitModal" tabindex="-1" aria-labelledby="batchInitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl); overflow: hidden;">
            <div class="modal-header" style="background: var(--primary-green); color: #fff; border: none; padding: var(--space-4) var(--space-5);">
                <h5 class="modal-title" id="batchInitModalLabel">
                    <i class="fas fa-magic me-2"></i> Batch Initialize Maintenance Schedules
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: var(--space-5);">
                <!-- Step 1: Select Location -->
                <div id="batchStep1">
                    <div style="margin-bottom: var(--space-4);">
                        <p style="color: var(--text-light); font-size: var(--text-sm); margin: 0;">
                            Select a unit or section to create maintenance schedules for all equipment under it.
                            Equipment that already has an active schedule will be skipped.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Search Locations</label>
                        <input type="text" class="form-control" id="batchLocSearch" placeholder="Type to filter units/sections..." oninput="filterBatchLocations()">
                    </div>

                    <div id="batchLocationList" style="max-height: 320px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
                        <div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading locations…</div>
                    </div>
                </div>

                <!-- Step 2: Configure -->
                <div id="batchStep2" style="display: none;">
                    <div class="batch-selected-info" id="batchSelectedInfo" style="background: var(--bg-light); border-radius: var(--radius-lg); padding: var(--space-4); margin-bottom: var(--space-4);">
                        <!-- Populated by JS -->
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date (Shared Due Date)</label>
                            <input type="date" class="form-control" id="batchStartDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Frequency</label>
                            <select class="form-select" id="batchFrequency">
                                <option value="Monthly">Monthly (30 days)</option>
                                <option value="Quarterly">Quarterly (90 days)</option>
                                <option value="Semi-Annual" selected>Semi-Annual (180 days)</option>
                                <option value="Annual">Annual (365 days)</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info" style="font-size: var(--text-sm);">
                        <i class="fas fa-info-circle me-1"></i>
                        All equipment under this location will share the same due date initially.
                        If any equipment is maintained off-cycle, it will automatically diverge to its own schedule.
                    </div>
                </div>

                <!-- Step 3: Result -->
                <div id="batchStep3" style="display: none;">
                    <div id="batchResultContent" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm"></span> Processing…
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: var(--space-3) var(--space-5);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="batchCancelBtn">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="batchBackBtn" style="display:none;" onclick="batchGoBack()">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button type="button" class="btn btn-success" id="batchNextBtn" style="display:none;" onclick="batchGoNext()">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-success" id="batchConfirmBtn" style="display:none;" onclick="executeBatchInit()">
                    <i class="fas fa-check"></i> Create Schedules
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/components/maintenance_modal.php'; ?>
<?php include __DIR__ . '/../../includes/components/detail_view_modal.php'; ?>
<script src="assets/js/maintenance-schedule.js?v=<?php echo time(); ?>"></script>