<?php
// modules/reports/maintenance-summary.php
require_once '../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // ---------- Filters ----------
    $filterDivision = isset($_GET['division']) ? trim($_GET['division']) : '';
    $filterEqType   = isset($_GET['eq_type'])  ? trim($_GET['eq_type'])  : '';
    $filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $filterDateTo   = isset($_GET['date_to'])   ? trim($_GET['date_to'])   : '';

    // Load all queries from shared include
    require_once __DIR__ . '/../../includes/queries/maintenance_summary_queries.php';

    // Build query string for print URL
    $printParams = [];
    if ($filterDivision !== '') $printParams['division'] = $filterDivision;
    if ($filterEqType   !== '') $printParams['eq_type']  = $filterEqType;
    if ($filterDateFrom !== '') $printParams['date_from'] = $filterDateFrom;
    if ($filterDateTo   !== '') $printParams['date_to']   = $filterDateTo;
    $printQuery = !empty($printParams) ? '?' . http_build_query($printParams) : '';

} catch (PDOException $e) {
    error_log("Maintenance summary error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="../public/assets/css/maintenance_summary.css">

<div class="report-container">
    <div class="report-header">
        <h2><i class="fas fa-tools"></i> Maintenance Summary Report</h2>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button class="print-btn" id="toggleMsFilters" onclick="document.getElementById('msFilterPanel').classList.toggle('open')"><i class="fas fa-filter"></i> Filters</button>
            <button class="print-btn" id="printMsBtn" onclick="window.open('../includes/generative/generate_maintenance_summary.php<?= $printQuery ?>', '_blank')"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="summary-filter-panel" id="msFilterPanel">
        <form id="msFilterForm" class="summary-filter-form" method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-building"></i> Division</label>
                    <select name="division" id="msDivision">
                        <option value="">All Divisions</option>
                        <?php foreach ($divisions as $div): ?>
                        <option value="<?= $div['location_id'] ?>" <?= $filterDivision == $div['location_id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['location_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-desktop"></i> Equipment Type</label>
                    <select name="eq_type" id="msEqType">
                        <option value="">All Types</option>
                        <?php foreach ($equipmentTypes as $et): ?>
                        <option value="<?= $et['typeId'] ?>" <?= $filterEqType == $et['typeId'] ? 'selected' : '' ?>><?= htmlspecialchars($et['typeName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Date From</label>
                    <input type="date" name="date_from" id="msDateFrom" value="<?= htmlspecialchars($filterDateFrom) ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Date To</label>
                    <input type="date" name="date_to" id="msDateTo" value="<?= htmlspecialchars($filterDateTo) ?>">
                </div>
                <div class="filter-actions">
                    <button type="button" class="filter-apply-btn" onclick="applyMsFilters()"><i class="fas fa-search"></i> Apply</button>
                    <button type="button" class="filter-reset-btn" onclick="resetMsFilters()"><i class="fas fa-times"></i> Reset</button>
                </div>
            </div>
            <?php if ($filterDivision !== '' || $filterEqType !== '' || $filterDateFrom !== '' || $filterDateTo !== ''): ?>
            <div class="active-filters">
                <span class="active-filters-label"><i class="fas fa-filter"></i> Active:</span>
                <?php if ($filterDivision !== ''): 
                    $divName = '';
                    foreach ($divisions as $d) { if ($d['location_id'] == $filterDivision) { $divName = $d['location_name']; break; } }
                ?>
                <span class="filter-tag"><?= htmlspecialchars($divName) ?> <button onclick="clearMsFilter('division')">&times;</button></span>
                <?php endif; ?>
                <?php if ($filterEqType !== ''):
                    $etName = '';
                    foreach ($equipmentTypes as $et) { if ($et['typeId'] == $filterEqType) { $etName = $et['typeName']; break; } }
                ?>
                <span class="filter-tag"><?= htmlspecialchars($etName) ?> <button onclick="clearMsFilter('eq_type')">&times;</button></span>
                <?php endif; ?>
                <?php if ($filterDateFrom !== ''): ?>
                <span class="filter-tag">From: <?= htmlspecialchars($filterDateFrom) ?> <button onclick="clearMsFilter('date_from')">&times;</button></span>
                <?php endif; ?>
                <?php if ($filterDateTo !== ''): ?>
                <span class="filter-tag">To: <?= htmlspecialchars($filterDateTo) ?> <button onclick="clearMsFilter('date_to')">&times;</button></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Key Metrics -->
    <div class="report-grid cols-4">
        <div class="ms-stat"><div class="ms-icon green"><i class="fas fa-calendar-check"></i></div><div><div class="ms-val"><?= $activeSchedules ?></div><div class="ms-lbl">Active Schedules</div></div></div>
        <div class="ms-stat"><div class="ms-icon red"><i class="fas fa-exclamation-circle"></i></div><div><div class="ms-val"><?= $overdue ?></div><div class="ms-lbl">Overdue</div></div></div>
        <div class="ms-stat"><div class="ms-icon orange"><i class="fas fa-clock"></i></div><div><div class="ms-val"><?= $dueSoon ?></div><div class="ms-lbl">Due Soon</div></div></div>
        <div class="ms-stat"><div class="ms-icon blue"><i class="fas fa-check-double"></i></div><div><div class="ms-val"><?= number_format($totalRecords) ?></div><div class="ms-lbl">Total Records</div></div></div>
    </div>

    <div class="report-grid cols-3">
        <!-- Compliance Ring -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-chart-pie"></i> Compliance Rate</h3></div>
            <div class="ms-panel-body" style="text-align: center;">
                <?php
                    $pct = $compliance;
                    $color = $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
                    $circ = 2 * M_PI * 42;
                    $dash = ($pct / 100) * $circ;
                ?>
                <div class="compliance-ring">
                    <svg viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="<?= $color ?>" stroke-width="8"
                                stroke-dasharray="<?= round($dash, 1) ?> <?= round($circ, 1) ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)"/>
                        <text x="50" y="50" text-anchor="middle" dominant-baseline="central"
                              font-size="18" font-weight="800" fill="<?= $color ?>" font-family="monospace">
                            <?= $pct ?>%
                        </text>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-medium);">
                    <strong><?= $completedThisMonth ?></strong> of <strong><?= $dueThisMonth ?></strong> completed this month
                </div>
                <div style="font-size: 0.7rem; color: var(--text-light); margin-top: 0.25rem;">
                    <?= $thisMonthRecords ?> total records this month
                </div>
            </div>
        </div>

        <!-- Monthly Activity Chart -->
        <div class="ms-panel" style="grid-column: span 2;">
            <div class="ms-panel-hdr"><h3><i class="fas fa-chart-bar"></i> Monthly Maintenance Activity (Last 12 Months)</h3></div>
            <div class="ms-panel-body">
                <?php
                    $maxCnt = max(array_column($monthly, 'cnt') ?: [1]);
                    foreach ($monthly as $m): $barPct = round(($m['cnt'] / $maxCnt) * 100);
                ?>
                <div class="chart-bar-row">
                    <div class="chart-bar-label"><?= $m['label'] ?></div>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill" style="width: <?= max($barPct, 5) ?>%;">
                            <span class="chart-bar-val"><?= $m['cnt'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($monthly)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 1rem;">No maintenance records in the last 12 months.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-grid cols-2">
        <!-- By Template / Type -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-clipboard-list"></i> Records by Maintenance Type</h3></div>
            <div class="ms-panel-body" style="padding: 0;">
                <table class="ms-table">
                    <thead><tr><th>Maintenance Type</th><th>Records</th></tr></thead>
                    <tbody>
                        <?php foreach ($byType as $t): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($t['templateName'] ?? 'Unspecified') ?></td>
                            <td style="font-family: var(--font-mono); font-weight: 700;"><?= $t['cnt'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($byType)): ?>
                        <tr><td colspan="2" style="text-align: center; color: var(--text-light); padding: 1rem;">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Condition Summary -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-heartbeat"></i> Latest Equipment Health</h3></div>
            <div class="ms-panel-body">
                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin-bottom: 0.75rem; text-transform: uppercase;">Condition Rating</h4>
                <?php foreach ($conditionSummary as $cond):
                    $bgClass = 'bg-' . strtolower($cond['conditionRating']);
                    $condTot = array_sum(array_column($conditionSummary, 'cnt'));
                    $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100) : 0;
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 70px; text-align: center;"><?= $cond['conditionRating'] ?></span>
                    <div style="flex: 1; height: 20px; background: #f3f4f6; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= max($pct, 3) ?>%; height: 100%; background: var(--primary-green); border-radius: 10px;"></div>
                    </div>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem; min-width: 55px; text-align: right;"><?= $cond['cnt'] ?> (<?= $pct ?>%)</span>
                </div>
                <?php endforeach; ?>

                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin: 1.25rem 0 0.75rem; text-transform: uppercase;">Overall Status</h4>
                <?php foreach ($statusSummary as $stat):
                    $bgClass = 'bg-operational';
                    if ($stat['overallStatus'] === 'For Replacement') $bgClass = 'bg-replacement';
                    elseif ($stat['overallStatus'] === 'Disposed') $bgClass = 'bg-disposed';
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 100px; text-align: center;"><?= $stat['overallStatus'] ?></span>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem;"><?= $stat['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Overdue List -->
    <?php if (!empty($overdueList)): ?>
    <div class="ms-panel" style="margin-top: 1.5rem;">
        <div class="ms-panel-hdr">
            <h3><i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i> Overdue Maintenance</h3>
            <span class="badge-sm bg-overdue"><?= $overdue ?> items</span>
        </div>
        <div class="ms-panel-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
            <div class="ms-table-scroll">
            <table class="ms-table">
                <thead><tr><th>Equipment</th><th>Type</th><th>Maintenance</th><th>Due Date</th><th>Days Overdue</th></tr></thead>
                <tbody>
                    <?php foreach ($overdueList as $od): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($od['equipment_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($od['equipment_type'] ?? '') ?></td>
                        <td><?= htmlspecialchars($od['maintenanceFrequency'] ?? '') ?></td>
                        <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?= date('M d, Y', strtotime($od['nextDueDate'])) ?></td>
                        <td>
                            <?php $days = $od['days_overdue'];
                                $cls = $days > 30 ? 'color:#dc2626;' : ($days > 14 ? 'color:#ea580c;' : 'color:#d97706;');
                            ?>
                            <span style="font-family: var(--font-mono); font-weight: 700; <?= $cls ?>"><?= $days ?> days</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Technicians Leaderboard -->
    <?php if (!empty($technicians)): ?>
    <!-- <div class="ms-panel" style="margin-top: 1.5rem;">
        <div class="ms-panel-hdr"><h3><i class="fas fa-user-cog"></i> Maintenance Technicians</h3></div>
        <div class="ms-panel-body" style="padding: 0;">
            <div class="ms-table-scroll">
            <table class="ms-table">
                <thead><tr><th>#</th><th>Technician</th><th>Records</th><th>Avg Rating</th></tr></thead>
                <tbody>
                    <?php foreach ($technicians as $i => $tech): ?>
                    <tr>
                        <td style="font-weight: 700; font-family: var(--font-mono);"><?= $i + 1 ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($tech['preparedBy']) ?></td>
                        <td style="font-family: var(--font-mono);"><?= $tech['cnt'] ?></td>
                        <td>
                            <?php
                                $r = $tech['avg_rating'];
                                $stars = '';
                                for ($s = 1; $s <= 4; $s++) {
                                    $stars .= $s <= round($r) ? '<i class="fas fa-star" style="color:#f59e0b; font-size:0.7rem;"></i>' : '<i class="far fa-star" style="color:#d1d5db; font-size:0.7rem;"></i>';
                                }
                            ?>
                            <?= $stars ?> <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-medium);">(<?= $r ?>)</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div> -->
    <?php endif; ?>
</div>
<script>
function applyMsFilters() {
    var params = new URLSearchParams();
    var div = document.getElementById('msDivision').value;
    var eq  = document.getElementById('msEqType').value;
    var df  = document.getElementById('msDateFrom').value;
    var dt  = document.getElementById('msDateTo').value;
    if (div) params.set('division', div);
    if (eq)  params.set('eq_type', eq);
    if (df)  params.set('date_from', df);
    if (dt)  params.set('date_to', dt);
    var qs = params.toString();
    var url = '../modules/reports/maintenance-summary.php' + (qs ? '?' + qs : '');
    document.getElementById('contentArea').innerHTML = '<div class="loading-spinner" style="display:flex;"><div class="spinner"></div><p>Loading...</p></div>';
    fetch(url).then(r => r.text()).then(html => { document.getElementById('contentArea').innerHTML = html; });
}
function resetMsFilters() {
    document.getElementById('msDivision').value = '';
    document.getElementById('msEqType').value = '';
    document.getElementById('msDateFrom').value = '';
    document.getElementById('msDateTo').value = '';
    applyMsFilters();
}
function clearMsFilter(key) {
    if (key === 'division') document.getElementById('msDivision').value = '';
    if (key === 'eq_type')  document.getElementById('msEqType').value = '';
    if (key === 'date_from') document.getElementById('msDateFrom').value = '';
    if (key === 'date_to')  document.getElementById('msDateTo').value = '';
    applyMsFilters();
}
<?php if ($filterDivision !== '' || $filterEqType !== '' || $filterDateFrom !== '' || $filterDateTo !== ''): ?>
document.addEventListener('DOMContentLoaded', function() {
    var panel = document.getElementById('msFilterPanel');
    if (panel) panel.classList.add('open');
});
if (document.getElementById('msFilterPanel')) document.getElementById('msFilterPanel').classList.add('open');
<?php endif; ?>
</script>