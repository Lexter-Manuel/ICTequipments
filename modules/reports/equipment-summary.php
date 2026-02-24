<?php
// modules/reports/equipment-summary.php
require_once '../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // ---------- Filters ----------
    $filterDivision = isset($_GET['division']) ? trim($_GET['division']) : '';
    $filterEqType   = isset($_GET['eq_type'])  ? trim($_GET['eq_type'])  : '';
    $filterYear     = isset($_GET['year'])      ? trim($_GET['year'])     : '';

    // Load all queries from shared include
    require_once __DIR__ . '/../../includes/queries/equipment_summary_queries.php';

    // Build query string for print URL
    $printParams = [];
    if ($filterDivision !== '') $printParams['division'] = $filterDivision;
    if ($filterEqType   !== '') $printParams['eq_type']  = $filterEqType;
    if ($filterYear     !== '') $printParams['year']     = $filterYear;
    $printQuery = !empty($printParams) ? '?' . http_build_query($printParams) : '';

} catch (PDOException $e) {
    error_log("Equipment summary error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="assets/css/equipment-summary.css?v=<?php echo time(); ?>">

<div class="report-container">
    <div class="report-header">
        <h2><i class="fas fa-boxes"></i> Equipment Summary Report</h2>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button class="print-btn" id="toggleEqFilters" onclick="document.getElementById('eqFilterPanel').classList.toggle('open')"><i class="fas fa-filter"></i> Filters</button>
            <button class="print-btn" onclick="window.open('../includes/generative/generate_equipment_summary.php<?= $printQuery ?>', '_blank')"><i class="fas fa-print"></i> Print Report</button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="summary-filter-panel" id="eqFilterPanel">
        <form id="eqFilterForm" class="summary-filter-form" method="GET">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-building"></i> Division</label>
                    <select name="division" id="eqDivision">
                        <option value="">All Divisions</option>
                        <?php foreach ($divisions as $div): ?>
                        <option value="<?= $div['location_id'] ?>" <?= $filterDivision == $div['location_id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['location_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-desktop"></i> Equipment Type</label>
                    <select name="eq_type" id="eqEqType">
                        <option value="">All Types</option>
                        <?php foreach ($equipmentTypes as $et): ?>
                        <option value="<?= $et['typeId'] ?>" <?= $filterEqType == $et['typeId'] ? 'selected' : '' ?>><?= htmlspecialchars($et['typeName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Year Acquired</label>
                    <select name="year" id="eqYear">
                        <option value="">All Years</option>
                        <?php foreach ($yearOptions as $yr): ?>
                        <option value="<?= $yr ?>" <?= $filterYear == $yr ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="filter-apply-btn" onclick="applyEqFilters()"><i class="fas fa-search"></i> Apply</button>
                    <button type="button" class="filter-reset-btn" onclick="resetEqFilters()"><i class="fas fa-times"></i> Reset</button>
                </div>
            </div>
            <?php if ($filterDivision !== '' || $filterEqType !== '' || $filterYear !== ''): ?>
            <div class="active-filters">
                <span class="active-filters-label"><i class="fas fa-filter"></i> Active:</span>
                <?php if ($filterDivision !== ''): 
                    $divName = '';
                    foreach ($divisions as $d) { if ($d['location_id'] == $filterDivision) { $divName = $d['location_name']; break; } }
                ?>
                <span class="filter-tag"><?= htmlspecialchars($divName) ?> <button onclick="clearEqFilter('division')">&times;</button></span>
                <?php endif; ?>
                <?php if ($filterEqType !== ''):
                    $etName = '';
                    foreach ($equipmentTypes as $et) { if ($et['typeId'] == $filterEqType) { $etName = $et['typeName']; break; } }
                ?>
                <span class="filter-tag"><?= htmlspecialchars($etName) ?> <button onclick="clearEqFilter('eq_type')">&times;</button></span>
                <?php endif; ?>
                <?php if ($filterYear !== ''): ?>
                <span class="filter-tag">Year: <?= htmlspecialchars($filterYear) ?> <button onclick="clearEqFilter('year')">&times;</button></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Totals -->
    <div class="report-grid cols-4">
        <div class="rpt-stat">
            <div class="rpt-icon green"><i class="fas fa-cubes"></i></div>
            <div><div class="rpt-val"><?= number_format($total) ?></div><div class="rpt-lbl">Total Equipment</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon blue"><i class="fas fa-user-check"></i></div>
            <div><div class="rpt-val"><?= number_format($assigned) ?></div><div class="rpt-lbl">Assigned</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon orange"><i class="fas fa-box-open"></i></div>
            <div><div class="rpt-val"><?= number_format($unassigned) ?></div><div class="rpt-lbl">Unassigned</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon purple"><i class="fas fa-key"></i></div>
            <div><div class="rpt-val"><?= number_format($softTotal) ?></div><div class="rpt-lbl">Software Licenses</div></div>
        </div>
    </div>

    <!-- Type Breakdown -->
    <div class="report-grid cols-4" style="margin-bottom: 1.5rem;">
        <div class="rpt-stat"><div class="rpt-icon green"><i class="fas fa-server"></i></div><div><div class="rpt-val"><?= $suCount ?></div><div class="rpt-lbl">System Units</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon blue"><i class="fas fa-tv"></i></div><div><div class="rpt-val"><?= $moCount ?></div><div class="rpt-lbl">Monitors</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon purple"><i class="fas fa-print"></i></div><div><div class="rpt-val"><?= $prCount ?></div><div class="rpt-lbl">Printers</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon teal"><i class="fas fa-laptop"></i></div><div><div class="rpt-val"><?= $aioCount + $othCount ?></div><div class="rpt-lbl">AIO & Other</div></div></div>
    </div>

    <div class="report-grid cols-2">
        <!-- By Division -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-building"></i> Equipment by Division</h3></div>
            <div class="rpt-panel-body" style="padding: 0;">
                <table class="rpt-table">
                    <thead><tr><th>Division</th><th>SU</th><th>Monitors</th><th>Printers</th><th>AIO</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php $gTotal = 0; foreach ($byDivision as $div):
                            $dTotal = $div['system_units'] + $div['monitors'] + $div['printers'] + $div['allinones'];
                            $gTotal += $dTotal;
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($div['division']) ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['system_units'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['monitors'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['printers'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['allinones'] ?></td>
                            <td style="font-family: var(--font-mono); font-weight: 700;"><?= $dTotal ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Grand Total</td><td colspan="4"></td><td style="font-family: var(--font-mono);"><?= $gTotal ?></td></tr></tfoot>
                </table>
            </div>
        </div>

        <!-- Condition & Status -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-heartbeat"></i> Equipment Health</h3></div>
            <div class="rpt-panel-body">
                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin-bottom: 0.75rem; text-transform: uppercase;">Condition Rating</h4>
                <?php foreach ($conditionSummary as $cond):
                    $bgClass = 'bg-' . strtolower($cond['conditionRating']);
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 70px; text-align: center;"><?= $cond['conditionRating'] ?></span>
                    <div style="flex: 1; height: 20px; background: #f3f4f6; border-radius: 10px; overflow: hidden;">
                        <?php $condTot = array_sum(array_column($conditionSummary, 'cnt')); $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100) : 0; ?>
                        <div style="width: <?= max($pct, 3) ?>%; height: 100%; background: var(--primary-green); border-radius: 10px;"></div>
                    </div>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem; min-width: 45px; text-align: right;"><?= $cond['cnt'] ?> (<?= $pct ?>%)</span>
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

    <div class="report-grid cols-2" style="margin-top: 1.5rem;">
        <!-- Year Acquired -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-calendar"></i> Acquisition Timeline</h3></div>
            <div class="rpt-panel-body" style="padding: 0;">
                <table class="rpt-table">
                    <thead><tr><th>Year</th><th>Count</th><th>% of Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($byYear as $yr): $pct = $total > 0 ? round(($yr['total'] / $total) * 100, 1) : 0; ?>
                        <tr>
                            <td style="font-weight: 600; font-family: var(--font-mono);"><?= $yr['year_acquired'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $yr['total'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $pct ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Software Licenses -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-key"></i> Software License Summary</h3></div>
            <div class="rpt-panel-body">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                    <div style="background: #f0fdf4; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #16a34a;"><?= $softTotal ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #16a34a; text-transform: uppercase;">Total</div>
                    </div>
                    <div style="background: #f0f9ff; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #2563eb;"><?= $softPerpetual ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #2563eb; text-transform: uppercase;">Perpetual</div>
                    </div>
                    <div style="background: #fffbeb; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #d97706;"><?= $softExpiring ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #d97706; text-transform: uppercase;">Expiring (90d)</div>
                    </div>
                    <div style="background: #fef2f2; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #dc2626;"><?= $softExpired ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #dc2626; text-transform: uppercase;">Expired</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function applyEqFilters() {
    var params = new URLSearchParams();
    var div = document.getElementById('eqDivision').value;
    var eq  = document.getElementById('eqEqType').value;
    var yr  = document.getElementById('eqYear').value;
    if (div) params.set('division', div);
    if (eq)  params.set('eq_type', eq);
    if (yr)  params.set('year', yr);
    var qs = params.toString();
    var url = '../modules/reports/equipment-summary.php' + (qs ? '?' + qs : '');
    document.getElementById('contentArea').innerHTML = '<div class="loading-spinner" style="display:flex;"><div class="spinner"></div><p>Loading...</p></div>';
    fetch(url).then(r => r.text()).then(html => { document.getElementById('contentArea').innerHTML = html; });
}
function resetEqFilters() {
    document.getElementById('eqDivision').value = '';
    document.getElementById('eqEqType').value = '';
    document.getElementById('eqYear').value = '';
    applyEqFilters();
}
function clearEqFilter(key) {
    if (key === 'division') document.getElementById('eqDivision').value = '';
    if (key === 'eq_type')  document.getElementById('eqEqType').value = '';
    if (key === 'year')     document.getElementById('eqYear').value = '';
    applyEqFilters();
}
<?php if ($filterDivision !== '' || $filterEqType !== '' || $filterYear !== ''): ?>
document.addEventListener('DOMContentLoaded', function() {
    var panel = document.getElementById('eqFilterPanel');
    if (panel) panel.classList.add('open');
});
if (document.getElementById('eqFilterPanel')) document.getElementById('eqFilterPanel').classList.add('open');
<?php endif; ?>
</script>