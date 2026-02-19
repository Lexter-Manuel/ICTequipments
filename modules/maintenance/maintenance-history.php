<?php
// modules/maintenance/maintenance-history.php
?>
<link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-history.css?v=<?php echo time(); ?>">

<div class="page-content">

    <!-- ── PAGE HEADER ── -->
    <div class="mnt-page-header">
        <div class="mnt-header-left">
            <div class="mnt-header-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h1 class="mnt-page-title">Maintenance History</h1>
                <p class="mnt-page-subtitle">Archive of completed maintenance activities</p>
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
            <button class="mnt-btn-export" onclick="exportReport()">
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

    <div class="mnt-filter-bar" id="histFilterBar">
        <div class="mnt-filter-group">
            <span class="mnt-filter-label">Date Range</span>
            <select class="mnt-filter-select" id="histDateRange">
                <option value="Last 7 Days">Last 7 Days</option>
                <option value="This Month">This Month</option>
                <option value="Last 3 Months" selected>Last 3 Months</option>
                <option value="This Year">This Year</option>
                <option value="All Time">All Time</option>
            </select>
        </div>
        <div class="mnt-filter-group grow-2">
            <span class="mnt-filter-label">Search</span>
            <div class="mnt-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="mnt-filter-input" id="histSearchInput" placeholder="Search serial, technician, or remarks…">
            </div>
        </div>
        <div class="mnt-filter-actions">
            <button class="btn btn-primary" onclick="loadDetailedHistory()"><i class="fas fa-filter"></i> Apply</button>
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
                            <th>Report</th>
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
                                            <th>Report</th>
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

<script src="assets/js/maintenance-history.js?v=<?php echo time(); ?>"></script>
