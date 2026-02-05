<?php
/**
 * Computer Equipment Management Module
 * Manages System Units, Monitors, and All-in-One PCs in a tabbed interface
 * Database integrated with full CRUD operations
 */

require_once '../../config/database.php';

$db = getDB();

// Fetch System Units
$stmtSU = $db->query("
    SELECT 
        s.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
    FROM tbl_systemunit s
    LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
    ORDER BY s.systemunitId DESC
");
$systemUnits = $stmtSU->fetchAll();

// Fetch Monitors
$stmtMon = $db->query("
    SELECT 
        m.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
    FROM tbl_monitor m
    LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
    ORDER BY m.monitorId DESC
");
$monitors = $stmtMon->fetchAll();

// Fetch All-in-Ones
$stmtAIO = $db->query("
    SELECT 
        a.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
    FROM tbl_allinone a
    LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
    ORDER BY a.allinoneId DESC
");
$allInOnes = $stmtAIO->fetchAll();

// Fetch employees for dropdown
$stmtEmployees = $db->query("
    SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName
    FROM tbl_employee
    ORDER BY firstName, lastName
");
$employees = $stmtEmployees->fetchAll();
?>

<style>
/* Tab Navigation */
.tab-navigation {
    background: white;
    border-radius: 12px 12px 0 0;
    border: 1px solid var(--border-color);
    border-bottom: none;
    padding: 0;
    display: flex;
    gap: 0;
    margin-bottom: 0;
}

.tab-btn {
    flex: 1;
    padding: 18px 24px;
    border: none;
    background: transparent;
    color: var(--text-medium);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.tab-btn:first-child {
    border-radius: 12px 0 0 0;
}

.tab-btn:last-child {
    border-radius: 0 12px 0 0;
}

.tab-btn.active {
    background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
    color: white;
}

.tab-btn:not(.active):hover {
    background: var(--bg-light);
}

.tab-content {
    display: none;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0 0 12px 12px;
    padding: 24px;
}

.tab-content.active {
    display: block;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-header h2 {
    font-family: 'Crimson Pro', serif;
    font-size: 22px;
    color: var(--text-dark);
    font-weight: 700;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-secondary {
    background: white;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.stat-item {
    background: var(--bg-light);
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid var(--primary-green);
}

.stat-label {
    font-size: 13px;
    color: var(--text-medium);
    margin-bottom: 8px;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
}

/* Filters */
.filters-bar {
    background: var(--bg-light);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 16px;
    display: flex;
    gap: 12px;
    align-items: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group select,
.filter-group input {
    padding: 8px 10px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: white;
}

/* Data Table */
.data-table {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
    color: white;
}

.data-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
}

.data-table tbody tr:hover {
    background: var(--bg-light);
}

/* Specifications Display */
.spec-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    font-size: 12px;
    color: var(--text-medium);
}

.spec-item i {
    width: 14px;
    color: var(--primary-green);
}

.spec-value {
    font-weight: 500;
    color: var(--text-dark);
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.status-active {
    background: rgba(34, 197, 94, 0.12);
    color: #16a34a;
}

.status-available {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
}

.status-inrepair {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}

.status-retired {
    background: rgba(220, 38, 38, 0.08);
    color: #dc2626;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.btn-icon:hover {
    background: var(--primary-green);
    color: white;
    border-color: var(--primary-green);
}

.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: white !important;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h2>
        <i class="fas fa-desktop"></i>
        Computer Equipment Management
    </h2>
    <div class="header-actions">
        <button class="btn btn-secondary">
            <i class="fas fa-download"></i>
            Export
        </button>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tab-navigation">
    <button class="tab-btn active" onclick="switchTab('systemunits')">
        <i class="fas fa-tower-broadcast"></i>
        System Units
    </button>
    <button class="tab-btn" onclick="switchTab('monitors')">
        <i class="fas fa-tv"></i>
        Monitors
    </button>
    <button class="tab-btn" onclick="switchTab('allinone')">
        <i class="fas fa-computer"></i>
        All-in-One PCs
    </button>
</div>

<!-- System Units Tab -->
<div class="tab-content active" id="systemunits-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total System Units</div>
            <div class="stat-value"><?php echo count($systemUnits); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($systemUnits, fn($s) => $s['employeeId'] != null)); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($systemUnits, fn($s) => $s['employeeId'] == null)); ?></div>
        </div>
    </div>

    <!-- Header Actions -->
    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="systemunitSearch" placeholder="Serial, brand, processor..." oninput="filterSystemUnits()">
        </div>
        <button class="btn btn-primary" onclick="openAddSystemUnit()">
            <i class="fas fa-plus"></i>
            Add System Unit
        </button>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand & Category</th>
                    <th>Specifications</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="systemunitTableBody">
                <?php foreach ($systemUnits as $s): ?>
                <?php $status = $s['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-green);"><?php echo htmlspecialchars($s['systemUnitSerial']); ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($s['systemUnitBrand']); ?></div>
                        <div style="font-size: 12px; color: var(--text-light);">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($s['systemUnitCategory'] ?? 'Pre-Built'); ?>
                        </div>
                    </td>
                    <td>
                        <div class="spec-item">
                            <i class="fas fa-microchip"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($s['specificationProcessor']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-memory"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($s['specificationMemory']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-hdd"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($s['specificationStorage']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($s['yearAcquired'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($s['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($s['employeeName']); ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo htmlspecialchars($s['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($status); ?>">
                            <?php echo $status; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editSystemUnit(<?php echo $s['systemunitId']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" style="color: black" title="Delete" onclick="deleteSystemUnit(<?php echo $s['systemunitId']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Monitors Tab -->
<div class="tab-content" id="monitors-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total Monitors</div>
            <div class="stat-value"><?php echo count($monitors); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($monitors, fn($m) => $m['employeeId'] != null)); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($monitors, fn($m) => $m['employeeId'] == null)); ?></div>
        </div>
    </div>

    <!-- Header Actions -->
    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="monitorSearch" placeholder="Serial, brand, size..." oninput="filterMonitors()">
        </div>
        <button class="btn btn-primary" onclick="openAddMonitor()">
            <i class="fas fa-plus"></i>
            Add Monitor
        </button>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand & Model</th>
                    <th>Size</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="monitorTableBody">
                <?php foreach ($monitors as $m): ?>
                <?php $status = $m['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-green);"><?php echo htmlspecialchars($m['monitorSerial']); ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($m['monitorBrand']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($m['monitorSize'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($m['yearAcquired'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($m['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($m['employeeName']); ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo htmlspecialchars($m['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($status); ?>">
                            <?php echo $status; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editMonitor(<?php echo $m['monitorId']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" style="color: black" title="Delete" onclick="deleteMonitor(<?php echo $m['monitorId']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All-in-One PCs Tab -->
<div class="tab-content" id="allinone-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total All-in-One</div>
            <div class="stat-value"><?php echo count($allInOnes); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($allInOnes, fn($a) => $a['employeeId'] != null)); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($allInOnes, fn($a) => $a['employeeId'] == null)); ?></div>
        </div>
    </div>

    <!-- Header Actions -->
    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="allinoneSearch" placeholder="Brand, processor..." oninput="filterAllInOnes()">
        </div>
        <button class="btn btn-primary" onclick="openAddAllInOne()">
            <i class="fas fa-plus"></i>
            Add All-in-One
        </button>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Brand & Model</th>
                    <th>Specifications</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="allinoneTableBody">
                <?php foreach ($allInOnes as $a): ?>
                <?php $status = $a['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($a['allinoneBrand']); ?></div>
                    </td>
                    <td>
                        <div class="spec-item">
                            <i class="fas fa-microchip"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($a['specificationProcessor']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-memory"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($a['specificationMemory']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-hdd"></i>
                            <span class="spec-value"><?php echo htmlspecialchars($a['specificationStorage']); ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($a['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($a['employeeName']); ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo htmlspecialchars($a['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($status); ?>">
                            <?php echo $status; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editAllInOne(<?php echo $a['allinoneId']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" style="color: black" title="Delete" onclick="deleteAllInOne(<?php echo $a['allinoneId']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- System Unit Modal -->
<div class="modal fade" id="systemunitModal" tabindex="-1" aria-labelledby="systemunitModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="systemunitModalTitle">Add New System Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="systemunitForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="suCategory" class="form-label">Category *</label>
                            <select class="form-select" id="suCategory" required>
                                <option value="">Select Category</option>
                                <option value="Pre-Built">Pre-Built</option>
                                <option value="Custom Built">Custom Built</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="suBrand" class="form-label">Brand *</label>
                            <input type="text" class="form-control" id="suBrand" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="suProcessor" class="form-label">Processor *</label>
                            <input type="text" class="form-control" id="suProcessor" required placeholder="e.g., Intel Core i7-11700">
                        </div>
                        <div class="col-md-6">
                            <label for="suMemory" class="form-label">Memory (RAM) *</label>
                            <input type="text" class="form-control" id="suMemory" required placeholder="e.g., 16GB DDR4">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="suGPU" class="form-label">GPU *</label>
                            <input type="text" class="form-control" id="suGPU" required placeholder="e.g., NVIDIA RTX 3060">
                        </div>
                        <div class="col-md-6">
                            <label for="suStorage" class="form-label">Storage *</label>
                            <input type="text" class="form-control" id="suStorage" required placeholder="e.g., 512GB NVMe SSD">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="suSerial" class="form-label">Serial Number *</label>
                            <input type="text" class="form-control" id="suSerial" required>
                        </div>
                        <div class="col-md-6">
                            <label for="suYear" class="form-label">Year Acquired *</label>
                            <input type="number" class="form-control" id="suYear" required min="2000" max="2030">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="suEmployee" class="form-label">Assigned Employee</label>
                            <select class="form-select" id="suEmployee">
                                <option value="">Unassigned</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSystemUnit()" style="background-color: var(--primary-green); border-color: var(--primary-green);">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Monitor Modal -->
<div class="modal fade" id="monitorModal" tabindex="-1" aria-labelledby="monitorModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="monitorModalTitle">Add New Monitor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="monitorForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="monBrand" class="form-label">Brand *</label>
                            <input type="text" class="form-control" id="monBrand" required>
                        </div>
                        <div class="col-md-6">
                            <label for="monSize" class="form-label">Size *</label>
                            <input type="text" class="form-control" id="monSize" required placeholder="e.g., 24 inches">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="monSerial" class="form-label">Serial Number *</label>
                            <input type="text" class="form-control" id="monSerial" required>
                        </div>
                        <div class="col-md-6">
                            <label for="monYear" class="form-label">Year Acquired *</label>
                            <input type="number" class="form-control" id="monYear" required min="2000" max="2030">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="monEmployee" class="form-label">Assigned Employee</label>
                            <select class="form-select" id="monEmployee">
                                <option value="">Unassigned</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMonitor()" style="background-color: var(--primary-green); border-color: var(--primary-green);">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- All-in-One Modal -->
<div class="modal fade" id="allinoneModal" tabindex="-1" aria-labelledby="allinoneModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="allinoneModalTitle">Add New All-in-One</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="allinoneForm">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="aioBrand" class="form-label">Brand & Model *</label>
                            <input type="text" class="form-control" id="aioBrand" required placeholder="e.g., HP All-in-One 24-df1033">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="aioProcessor" class="form-label">Processor *</label>
                            <input type="text" class="form-control" id="aioProcessor" required placeholder="e.g., Intel Core i5-1135G7">
                        </div>
                        <div class="col-md-6">
                            <label for="aioMemory" class="form-label">Memory (RAM) *</label>
                            <input type="text" class="form-control" id="aioMemory" required placeholder="e.g., 8GB DDR4">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="aioGPU" class="form-label">GPU *</label>
                            <input type="text" class="form-control" id="aioGPU" required placeholder="e.g., Intel Iris Xe Graphics">
                        </div>
                        <div class="col-md-6">
                            <label for="aioStorage" class="form-label">Storage *</label>
                            <input type="text" class="form-control" id="aioStorage" required placeholder="e.g., 512GB SSD">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="aioEmployee" class="form-label">Assigned Employee</label>
                            <select class="form-select" id="aioEmployee">
                                <option value="">Unassigned</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAllInOne()" style="background-color: var(--primary-green); border-color: var(--primary-green);">Save</button>
            </div>
        </div>
    </div>
</div>
