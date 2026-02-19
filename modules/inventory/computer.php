<?php

require_once '../../config/database.php';

$db = getDB();

// Fetch System Units
$stmtSU = $db->query("
    SELECT 
        s.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName, m.lastMaintenanceDate
    FROM tbl_systemunit s
    LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
    LEFT JOIN tbl_maintenance_schedule m ON (
        s.systemunitId = m.equipmentId 
        AND (
            LOWER(TRIM(m.equipmentType)) = 'System Unit'     
            OR m.equipmentType = '1'
        )
    )
    ORDER BY s.systemunitId DESC
");
$systemUnits = $stmtSU->fetchAll();

// Fetch Monitors
$stmtMon = $db->query("
    SELECT 
        m.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName, m2.lastMaintenanceDate
    FROM tbl_monitor m
    LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
    LEFT JOIN tbl_maintenance_schedule m2 ON (
        m.monitorId = m2.equipmentId 
        AND (
            LOWER(TRIM(m2.equipmentType)) = 'monitor' 
            OR m2.equipmentType = '3'
        )
    )
    ORDER BY m.monitorId DESC
");
$monitors = $stmtMon->fetchAll();

// Fetch All-in-Ones
$stmtAIO = $db->query("
    SELECT 
        a.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName, m.lastMaintenanceDate
    FROM tbl_allinone a
    LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
    LEFT JOIN tbl_maintenance_schedule m ON (
        a.allinoneId = m.equipmentId 
        AND (
            LOWER(TRIM(m.equipmentType)) = 'All-in-one' 
            OR m.equipmentType = '2'
        )
    )
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

<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/computer.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-desktop"></i>
        </div>
        <div>
            <h1 class="page-title">Computer Equipment</h1>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tab-navigation">
    <button class="tab-btn active" onclick="switchTab('systemunits')">
        <i class="fas fa-tower-broadcast"></i> System Units
    </button>
    <button class="tab-btn" onclick="switchTab('monitors')">
        <i class="fas fa-tv"></i> Monitors
    </button>
    <button class="tab-btn" onclick="switchTab('allinone')">
        <i class="fas fa-computer"></i> All-in-One PCs
    </button>
</div>

<!-- ══ SYSTEM UNITS TAB ══ -->
<div class="tab-content active" id="systemunits-tab">
    <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom: var(--space-5);">
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

    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="systemunitSearch" placeholder="Serial, brand, processor..." oninput="filterSystemUnits()">
        </div>
        <button class="btn btn-primary" onclick="openAddSystemUnit()">
            <i class="fas fa-plus"></i> Add System Unit
        </button>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand &amp; Category</th>
                    <th>Specifications</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Last Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="systemunitTableBody">
                <?php foreach ($systemUnits as $s): ?>
                <?php $status = $s['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td><span class="serial-number"><?php echo htmlspecialchars($s['systemUnitSerial']); ?></span></td>
                    <td>
                        <div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($s['systemUnitBrand']); ?></div>
                        <div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($s['systemUnitCategory'] ?? 'Pre-Built'); ?></div>
                    </td>
                    <td>
                        <div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value"><?php echo htmlspecialchars($s['specificationProcessor']); ?></span></div>
                        <div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value"><?php echo htmlspecialchars($s['specificationMemory']); ?></span></div>
                        <div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value"><?php echo htmlspecialchars($s['specificationStorage']); ?></span></div>
                    </td>
                    <td><?php echo htmlspecialchars($s['yearAcquired'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($s['employeeName']): ?>
                            <div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($s['employeeName']); ?></div>
                            <div style="font-size:12px;color:var(--text-light)">ID: <?php echo htmlspecialchars($s['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color:var(--text-light);font-style:italic">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($s['lastMaintenanceDate'])): ?>
                            <div class="maintenance-info"><i class="fas fa-tools"></i><?php echo date('M d, Y', strtotime($s['lastMaintenanceDate'])); ?></div>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-clock"></i> No record</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editSystemUnit(<?php echo $s['systemunitId']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-danger" title="Delete" onclick="deleteSystemUnit(<?php echo $s['systemunitId']; ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="footer-info"><span id="recordCount"></span></div>
        <div class="pagination-controls" id="paginationControls"></div>
        <div class="per-page-control">
            <label>Rows:
                <select id="perPageSelect" onchange="changePerPage()">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </label>
        </div>
    </div>
</div>

<!-- ══ MONITORS TAB ══ -->
<div class="tab-content" id="monitors-tab">
    <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom: var(--space-5);">
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

    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="monitorSearch" placeholder="Serial, brand, size..." oninput="filterMonitors()">
        </div>
        <button class="btn btn-primary" onclick="openAddMonitor()">
            <i class="fas fa-plus"></i> Add Monitor
        </button>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand &amp; Model</th>
                    <th>Size</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Last Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="monitorTableBody">
                <?php foreach ($monitors as $m): ?>
                <?php $status = $m['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td><span class="serial-number"><?php echo htmlspecialchars($m['monitorSerial']); ?></span></td>
                    <td><div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($m['monitorBrand']); ?></div></td>
                    <td><?php echo htmlspecialchars($m['monitorSize'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($m['yearAcquired'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($m['employeeName']): ?>
                            <div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($m['employeeName']); ?></div>
                            <div style="font-size:12px;color:var(--text-light)">ID: <?php echo htmlspecialchars($m['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color:var(--text-light);font-style:italic">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($m['lastMaintenanceDate'])): ?>
                            <div class="maintenance-info"><i class="fas fa-tools"></i><?php echo date('M d, Y', strtotime($m['lastMaintenanceDate'])); ?></div>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-clock"></i> No record</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editMonitor(<?php echo $m['monitorId']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-danger" title="Delete" onclick="deleteMonitor(<?php echo $m['monitorId']; ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="footer-info"><span id="recordCount"></span></div>
        <div class="pagination-controls" id="paginationControls"></div>
        <div class="per-page-control">
            <label>Rows:
                <select id="perPageSelect" onchange="changePerPage()">
                    <option value="10">10</option><option value="25" selected>25</option>
                    <option value="50">50</option><option value="100">100</option>
                </select>
            </label>
        </div>
    </div>
</div>

<!-- ══ ALL-IN-ONE TAB ══ -->
<div class="tab-content" id="allinone-tab">
    <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom: var(--space-5);">
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

    <div class="filters-bar">
        <div class="filter-group" style="flex:1">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" id="allinoneSearch" placeholder="Brand, processor..." oninput="filterAllInOnes()">
        </div>
        <button class="btn btn-primary" onclick="openAddAllInOne()">
            <i class="fas fa-plus"></i> Add All-in-One
        </button>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Brand &amp; Model</th>
                    <th>Specifications</th>
                    <th>Assigned To</th>
                    <th>Last Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="allinoneTableBody">
                <?php foreach ($allInOnes as $a): ?>
                <?php $status = $a['employeeId'] ? 'Active' : 'Available'; ?>
                <tr>
                    <td><div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($a['allinoneBrand']); ?></div></td>
                    <td>
                        <div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationProcessor']); ?></span></div>
                        <div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationMemory']); ?></span></div>
                        <div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationStorage']); ?></span></div>
                    </td>
                    <td>
                        <?php if ($a['employeeName']): ?>
                            <div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($a['employeeName']); ?></div>
                            <div style="font-size:12px;color:var(--text-light)">ID: <?php echo htmlspecialchars($a['employeeId']); ?></div>
                        <?php else: ?>
                            <span style="color:var(--text-light);font-style:italic">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($a['lastMaintenanceDate'])): ?>
                            <div class="maintenance-info"><i class="fas fa-tools"></i><?php echo date('M d, Y', strtotime($a['lastMaintenanceDate'])); ?></div>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-clock"></i> No record</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="Edit" onclick="editAllInOne(<?php echo $a['allinoneId']; ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-danger" title="Delete" onclick="deleteAllInOne(<?php echo $a['allinoneId']; ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="footer-info"><span id="recordCount"></span></div>
        <div class="pagination-controls" id="paginationControls"></div>
        <div class="per-page-control">
            <label>Rows:
                <select id="perPageSelect" onchange="changePerPage()">
                    <option value="10">10</option><option value="25" selected>25</option>
                    <option value="50">50</option><option value="100">100</option>
                </select>
            </label>
        </div>
    </div>
</div>

<!-- System Unit Modal -->
<div class="modal fade" id="systemunitModal" tabindex="-1" aria-labelledby="systemunitModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="systemunitModalTitle"><i class="fas fa-desktop"></i> Add New System Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="systemunitForm">
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i> Unit Information</h6>
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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="suSerial" class="form-label">Serial Number *</label>
                                <input type="text" class="form-control" id="suSerial" required>
                            </div>
                            <div class="col-md-6">
                                <label for="suYear" class="form-label">Year Acquired *</label>
                                <input type="number" class="form-control" id="suYear" required min="2000" max="2030">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-user"></i> Assignment</h6>
                        <div class="row mb-0">
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
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSystemUnit()"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Monitor Modal -->
<div class="modal fade" id="monitorModal" tabindex="-1" aria-labelledby="monitorModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monitorModalTitle"><i class="fas fa-tv"></i> Add New Monitor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="monitorForm">
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i> Monitor Information</h6>
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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="monSerial" class="form-label">Serial Number *</label>
                                <input type="text" class="form-control" id="monSerial" required>
                            </div>
                            <div class="col-md-6">
                                <label for="monYear" class="form-label">Year Acquired *</label>
                                <input type="number" class="form-control" id="monYear" required min="2000" max="2030">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-user"></i> Assignment</h6>
                        <label for="monEmployee" class="form-label">Assigned Employee</label>
                        <select class="form-select" id="monEmployee">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMonitor()"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- All-in-One Modal -->
<div class="modal fade" id="allinoneModal" tabindex="-1" aria-labelledby="allinoneModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allinoneModalTitle"><i class="fas fa-computer"></i> Add New All-in-One</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="allinoneForm">
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i> Device Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="aioBrand" class="form-label">Brand &amp; Model *</label>
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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="aioGPU" class="form-label">GPU *</label>
                                <input type="text" class="form-control" id="aioGPU" required placeholder="e.g., Intel Iris Xe Graphics">
                            </div>
                            <div class="col-md-6">
                                <label for="aioStorage" class="form-label">Storage *</label>
                                <input type="text" class="form-control" id="aioStorage" required placeholder="e.g., 512GB SSD">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-user"></i> Assignment</h6>
                        <label for="aioEmployee" class="form-label">Assigned Employee</label>
                        <select class="form-select" id="aioEmployee">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAllInOne()"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/computer_management.js?v=<?php echo time()?>"></script>