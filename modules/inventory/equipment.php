<?php
/**
 * Unified Equipment Inventory Module
 * Combines: Computer (System Units, Monitors, All-in-One), Printers, Other Equipment
 */
require_once '../../config/session-guard.php';

require_once '../../config/database.php';
$db = getDB();

// ── Fetch items_per_page from system settings ──
$stmtPP = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'items_per_page' LIMIT 1");
$stmtPP->execute();
$defaultPerPage = (int)($stmtPP->fetchColumn() ?: 25);

// ── Fetch ALL equipment from unified table ──
$stmtAll = $db->query("
    SELECT eq.equipment_id, eq.type_id, eq.employee_id, eq.location_id,
           eq.brand, eq.model, eq.serial_number, eq.property_number,
           eq.status, eq.year_acquired, eq.acquisition_date, eq.is_archived,
           CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName,
           r.typeName,
           ms.lastMaintenanceDate,
           l.location_name
    FROM tbl_equipment eq
    INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
    LEFT JOIN tbl_maintenance_schedule ms ON (eq.equipment_id = ms.equipmentId AND eq.type_id = ms.equipmentType AND ms.isActive = 1)
    LEFT JOIN location l ON eq.location_id = l.location_id
    WHERE eq.is_archived = 0
    ORDER BY eq.equipment_id DESC
");
$allEquipment = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Bulk load specs
$allIds = array_column($allEquipment, 'equipment_id');
$specsMap = [];
if (!empty($allIds)) {
    $ph = implode(',', array_fill(0, count($allIds), '?'));
    $specStmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($ph)");
    $specStmt->execute($allIds);
    while ($row = $specStmt->fetch(PDO::FETCH_ASSOC)) {
        $specsMap[$row['equipment_id']][$row['spec_key']] = $row['spec_value'];
    }
}

// Split into legacy arrays with backward-compatible column names for templates
$systemUnits = [];
$monitors = [];
$allInOnes = [];
$printers = [];
$otherEquipment = [];

foreach ($allEquipment as $eq) {
    $specs = $specsMap[$eq['equipment_id']] ?? [];
    $eqId = $eq['equipment_id'];
    
    switch ($eq['typeName']) {
        case 'System Unit':
            $systemUnits[] = [
                'systemunitId'           => $eqId,
                'systemUnitBrand'        => $eq['brand'],
                'systemUnitSerial'       => $eq['serial_number'],
                'systemUnitCategory'     => $specs['Category'] ?? 'Pre-Built',
                'specificationProcessor' => $specs['Processor'] ?? '',
                'specificationMemory'    => $specs['Memory'] ?? '',
                'specificationGPU'       => $specs['GPU'] ?? '',
                'specificationStorage'   => $specs['Storage'] ?? '',
                'yearAcquired'           => $eq['year_acquired'],
                'employeeId'             => $eq['employee_id'],
                'employeeName'           => $eq['employeeName'],
                'lastMaintenanceDate'    => $eq['lastMaintenanceDate'],
                'location_id'            => $eq['location_id'],
                'location_name'          => $eq['location_name'],
            ];
            break;
        case 'Monitor':
            $monitors[] = [
                'monitorId'           => $eqId,
                'monitorBrand'        => $eq['brand'],
                'monitorSerial'       => $eq['serial_number'],
                'monitorSize'         => $specs['Monitor Size'] ?? '',
                'yearAcquired'        => $eq['year_acquired'],
                'employeeId'          => $eq['employee_id'],
                'employeeName'        => $eq['employeeName'],
                'lastMaintenanceDate' => $eq['lastMaintenanceDate'],
                'location_id'         => $eq['location_id'],
                'location_name'       => $eq['location_name'],
            ];
            break;
        case 'All-in-One':
            $allInOnes[] = [
                'allinoneId'             => $eqId,
                'allinoneBrand'          => $eq['brand'],
                'allinoneSerial'         => $eq['serial_number'],
                'specificationProcessor' => $specs['Processor'] ?? '',
                'specificationMemory'    => $specs['Memory'] ?? '',
                'specificationGPU'       => $specs['GPU'] ?? '',
                'specificationStorage'   => $specs['Storage'] ?? '',
                'yearAcquired'           => $eq['year_acquired'],
                'employeeId'             => $eq['employee_id'],
                'employeeName'           => $eq['employeeName'],
                'lastMaintenanceDate'    => $eq['lastMaintenanceDate'],
                'location_id'            => $eq['location_id'],
                'location_name'          => $eq['location_name'],
            ];
            break;
        case 'Printer':
            $printers[] = [
                'printerId'           => $eqId,
                'printerBrand'        => $eq['brand'],
                'printerModel'        => $eq['model'],
                'printerSerial'       => $eq['serial_number'],
                'yearAcquired'        => $eq['year_acquired'],
                'employeeId'          => $eq['employee_id'],
                'employeeName'        => $eq['employeeName'],
                'lastMaintenanceDate' => $eq['lastMaintenanceDate'],
                'location_id'         => $eq['location_id'],
                'location_name'       => $eq['location_name'],
            ];
            break;
        default:
            $otherEquipment[] = [
                'otherEquipmentId' => $eqId,
                'equipmentType'    => $eq['typeName'],
                'brand'            => $eq['brand'],
                'model'            => $eq['model'],
                'serialNumber'     => $eq['serial_number'],
                'yearAcquired'     => $eq['year_acquired'],
                'status'           => $eq['status'],
                'employeeId'       => $eq['employee_id'],
                'employeeName'     => $eq['employeeName'],
                'location_id'      => $eq['location_id'],
                'location_name'    => $eq['location_name'],
            ];
            break;
    }
}

// ── Fetch Employees ──
$stmtEmployees = $db->query("SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName FROM tbl_employee WHERE is_archive = '0' ORDER BY firstName, lastName");
$employees = $stmtEmployees->fetchAll();

// ── Fetch Locations ──
$stmtLoc = $db->query("SELECT location_id, location_name FROM location WHERE is_deleted = '0' ORDER BY location_name ASC");
$locations = $stmtLoc->fetchAll();

// ── Stats ──
$totalPrinters   = count($printers);
$printerInUse    = count(array_filter($printers, fn($p) => $p['employeeId'] != null));
$printerAvail    = count(array_filter($printers, fn($p) => $p['employeeId'] == null));

$totalOther      = count($otherEquipment);
$otherInUse      = count(array_filter($otherEquipment, fn($o) => $o['employeeId'] != null || $o['status'] == 'In Use'));
$otherAvail      = count(array_filter($otherEquipment, fn($o) => $o['employeeId'] == null && $o['status'] == 'Available'));
$otherMaint      = count(array_filter($otherEquipment, fn($o) => $o['status'] == 'Under Maintenance'));
?>

<?php include '../../includes/components/location_loader.php'; ?>

<link rel="stylesheet" href="assets/css/tabs.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/equipment.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/autocomplete.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/other_equipment.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div>
            <h1 class="page-title">Equipment Inventory</h1>
        </div>
    </div>

    <div class="header-actions">
        <!-- ══════════════════════════════════════════
        TOP-LEVEL CATEGORY TABS
        ══════════════════════════════════════════ -->
        <div class="toggle-nav equip-toggle-nav" id="categoryTabs">
            <button class="toggle-btn active" onclick="switchCategory('computers', this)">
                <i class="fas fa-desktop"></i> Computers
            </button>
            <button class="toggle-btn" onclick="switchCategory('printers', this)">
                <i class="fas fa-print"></i> Printers
            </button>
            <button class="toggle-btn" onclick="switchCategory('other', this)">
                <i class="fas fa-server"></i> Other Equipment
            </button>
        </div>
        <button class="btn btn-secondary exportEquipment"><i class="fas fa-download"></i> Export</button>
    </div>
</div>

<!-- COMPUTERS CATEGORY -->
<div class="category-content active" id="category-computers">

    <!-- Sub-tabs for computer types -->
    <div class="subtoggle-nav equip-subtoggle-nav">
        <button class="subtoggle-btn active" onclick="switchSubTab('systemunits', this)">
            <i class="fas fa-tower-broadcast"></i> System Units
        </button>
        <button class="subtoggle-btn" onclick="switchSubTab('monitors', this)">
            <i class="fas fa-tv"></i> Monitors
        </button>
        <button class="subtoggle-btn" onclick="switchSubTab('allinone', this)">
            <i class="fas fa-computer"></i> All-in-One PCs
        </button>
    </div>

    <!-- ── SYSTEM UNITS TAB ── -->
    <div class="sub-tab-content active" id="subtab-systemunits">
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

        <div class="data-table-container">
            <div class="table-header">
                <h2 class="table-title"><i class="fas fa-list"></i> System Unit Inventory</h2>
                <div class="table-controls">
                    <div class="filter-group">
                        <select id="suAssignFilter" onchange="filterSystemUnits()">
                            <option value="">All Assignments</option>
                            <option value="employee">Assigned to Employee</option>
                            <option value="location">Assigned to Location</option>
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="systemunitSearch" placeholder="Search serial, brand, processor..." oninput="filterSystemUnits()">
                    </div>
                    <button class="btn btn-primary" onclick="openAddSystemUnit()">
                        <i class="fas fa-plus"></i> Add System Unit
                    </button>
                </div>
            </div>

        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Brand &amp; Category</th>
                        <th>Specifications</th>
                        <th>Year</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="systemunitTableBody">
                    <?php foreach ($systemUnits as $s): ?>
                    <?php $status = $s['employeeId'] ? 'Active' : 'Available'; ?>
                    <?php
                        $suAssignType = $s['employeeId'] ? 'employee' : ($s['location_id'] ? 'location' : 'unassigned');
                    ?>
                    <tr data-su-id="<?php echo $s['systemunitId']; ?>"
                            data-assign-type="<?php echo $suAssignType; ?>"
                            data-serial="<?php echo strtolower(htmlspecialchars($s['systemUnitSerial'] ?? '')); ?>"
                            data-brand="<?php echo strtolower(htmlspecialchars($s['systemUnitBrand'] ?? '')); ?>"
                            data-category="<?php echo strtolower(htmlspecialchars($s['systemUnitCategory'] ?? '')); ?>"
                            data-processor="<?php echo strtolower(htmlspecialchars($s['specificationProcessor'] ?? '')); ?>"
                            data-memory="<?php echo strtolower(htmlspecialchars($s['specificationMemory'] ?? '')); ?>"
                            data-storage="<?php echo strtolower(htmlspecialchars($s['specificationStorage'] ?? '')); ?>"
                            data-year="<?php echo strtolower(htmlspecialchars($s['yearAcquired'] ?? '')); ?>"
                            data-location="<?php echo strtolower(htmlspecialchars($s['location_name'] ?? '')); ?>"
                            data-employee="<?php echo strtolower(htmlspecialchars($s['employeeName'] ?? '')); ?>"
                            data-status="<?php echo $status; ?>">
                        <td class="row-counter"></td>
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
                            <?php if ($s['location_name']): ?>
                                <div class="location-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($s['location_name']); ?></div>
                            <?php else: ?>
                                <span style="color:var(--text-light);font-style:italic">—</span>
                            <?php endif; ?>
                        </td>
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
            <div class="footer-info"><span id="suRecordCount"></span></div>
            <div class="pagination-controls" id="suPaginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="suPerPageSelect" onchange="changePerPageSU()">
                        <option value="10">10</option><option value="25" selected>25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
        </div>
    </div>

    <!-- ── MONITORS TAB ── -->
    <div class="sub-tab-content" id="subtab-monitors">
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

        <div class="data-table-container">
            <div class="table-header">
                <h2 class="table-title"><i class="fas fa-list"></i> Monitor Inventory</h2>
                <div class="table-controls">
                    <div class="filter-group">
                        <select id="monAssignFilter" onchange="filterMonitors()">
                            <option value="">All Assignments</option>
                            <option value="employee">Assigned to Employee</option>
                            <option value="location">Assigned to Location</option>
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="monitorSearch" placeholder="Search serial, brand, size..." oninput="filterMonitors()">
                    </div>
                    <button class="btn btn-primary" onclick="openAddMonitor()">
                        <i class="fas fa-plus"></i> Add Monitor
                    </button>
                </div>
            </div>

        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Brand &amp; Model</th>
                        <th>Size</th>
                        <th>Year</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="monitorTableBody">
                    <?php foreach ($monitors as $m): ?>
                    <?php
                        $status = $m['employeeId'] ? 'Active' : 'Available';
                        $monAssignType = $m['employeeId'] ? 'employee' : ($m['location_id'] ? 'location' : 'unassigned');
                    ?>
                    <tr data-mon-id="<?php echo $m['monitorId']; ?>"
                            data-assign-type="<?php echo $monAssignType; ?>"
                            data-serial="<?php echo strtolower(htmlspecialchars($m['monitorSerial'] ?? '')); ?>"
                            data-brand="<?php echo strtolower(htmlspecialchars($m['monitorBrand'] ?? '')); ?>"
                            data-size="<?php echo strtolower(htmlspecialchars($m['monitorSize'] ?? '')); ?>"
                            data-year="<?php echo strtolower(htmlspecialchars($m['yearAcquired'] ?? '')); ?>"
                            data-location="<?php echo strtolower(htmlspecialchars($m['location_name'] ?? '')); ?>"
                            data-employee="<?php echo strtolower(htmlspecialchars($m['employeeName'] ?? '')); ?>"
                            data-status="<?php echo $status; ?>">
                        <td class="row-counter"></td>
                        <td><span class="serial-number"><?php echo htmlspecialchars($m['monitorSerial']); ?></span></td>
                        <td><div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($m['monitorBrand']); ?></div></td>
                        <td><?php echo htmlspecialchars($m['monitorSize'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($m['yearAcquired'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($m['location_name']): ?>
                                <div class="location-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($m['location_name']); ?></div>
                            <?php else: ?>
                                <span style="color:var(--text-light);font-style:italic">—</span>
                            <?php endif; ?>
                        </td>
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
            <div class="footer-info"><span id="monRecordCount"></span></div>
            <div class="pagination-controls" id="monPaginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="monPerPageSelect" onchange="changePerPageMon()">
                        <option value="10">10</option><option value="25" selected>25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
        </div>
    </div>

    <!-- ── ALL-IN-ONE TAB ── -->
    <div class="sub-tab-content" id="subtab-allinone">
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

        <div class="data-table-container">
            <div class="table-header">
                <h2 class="table-title"><i class="fas fa-list"></i> All-in-One Inventory</h2>
                <div class="table-controls">
                    <div class="filter-group">
                        <select id="aioAssignFilter" onchange="filterAllInOnes()">
                            <option value="">All Assignments</option>
                            <option value="employee">Assigned to Employee</option>
                            <option value="location">Assigned to Location</option>
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="allinoneSearch" placeholder="Search brand, processor..." oninput="filterAllInOnes()">
                    </div>
                    <button class="btn btn-primary" onclick="openAddAllInOne()">
                        <i class="fas fa-plus"></i> Add All-in-One
                    </button>
                </div>
            </div>

        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Brand &amp; Model</th>
                        <th>Specifications</th>
                        <th>Year</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="allinoneTableBody">
                    <?php foreach ($allInOnes as $a): ?>
                    <?php
                        $status = $a['employeeId'] ? 'Active' : 'Available';
                        $aioAssignType = $a['employeeId'] ? 'employee' : ($a['location_id'] ? 'location' : 'unassigned');
                    ?>
                    <tr data-aio-id="<?php echo $a['allinoneId']; ?>"
                            data-assign-type="<?php echo $aioAssignType; ?>"
                            data-serial="<?php echo strtolower(htmlspecialchars($a['allinoneSerial'] ?? '')); ?>"
                            data-brand="<?php echo strtolower(htmlspecialchars($a['allinoneBrand'] ?? '')); ?>"
                            data-processor="<?php echo strtolower(htmlspecialchars($a['specificationProcessor'] ?? '')); ?>"
                            data-memory="<?php echo strtolower(htmlspecialchars($a['specificationMemory'] ?? '')); ?>"
                            data-storage="<?php echo strtolower(htmlspecialchars($a['specificationStorage'] ?? '')); ?>"
                            data-year="<?php echo strtolower(htmlspecialchars($a['yearAcquired'] ?? '')); ?>"
                            data-location="<?php echo strtolower(htmlspecialchars($a['location_name'] ?? '')); ?>"
                            data-employee="<?php echo strtolower(htmlspecialchars($a['employeeName'] ?? '')); ?>"
                            data-status="<?php echo $status; ?>">
                        <td class="row-counter"></td>
                        <td><span class="serial-number"><?php echo htmlspecialchars($a['allinoneSerial'] ?? 'N/A'); ?></span></td>
                        <td><div style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($a['allinoneBrand']); ?></div></td>
                        <td>
                            <div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationProcessor']); ?></span></div>
                            <div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationMemory']); ?></span></div>
                            <div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value"><?php echo htmlspecialchars($a['specificationStorage']); ?></span></div>
                        </td>
                        <td><?php echo htmlspecialchars($a['yearAcquired'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($a['location_name']): ?>
                                <div class="location-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($a['location_name']); ?></div>
                            <?php else: ?>
                                <span style="color:var(--text-light);font-style:italic">—</span>
                            <?php endif; ?>
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
            <div class="footer-info"><span id="aioRecordCount"></span></div>
            <div class="pagination-controls" id="aioPaginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="aioPerPageSelect" onchange="changePerPageAIO()">
                        <option value="10">10</option><option value="25" selected>25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- ╔══════════════════════════════════════════╗
     ║  PRINTERS CATEGORY                       ║
     ╚══════════════════════════════════════════╝ -->
<div class="category-content" id="category-printers">
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-print stat-icon"></i>
            <div><div class="stat-label">Total Printers</div><div class="stat-value"><?php echo $totalPrinters; ?></div></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle stat-icon"></i>
            <div><div class="stat-label">In Use</div><div class="stat-value"><?php echo $printerInUse; ?></div></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-box-open stat-icon"></i>
            <div><div class="stat-label">Available</div><div class="stat-value"><?php echo $printerAvail; ?></div></div>
        </div>
    </div>

    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-list"></i> Printer Inventory</h2>
            <div class="table-controls">
                <div class="filter-group">
                    <select id="printerAssignFilter" onchange="filterPrinters()">
                        <option value="">All Assignments</option>
                        <option value="employee">Assigned to Employee</option>
                        <option value="location">Assigned to Location</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="printerSearch" placeholder="Search serial, brand, model..." oninput="filterPrinters()">
                </div>
                <button class="btn btn-primary" onclick="openAddPrinter()"><i class="fas fa-plus"></i> Add Printer</button>
            </div>
        </div>

        <div class="data-table">
            <table id="printerTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Brand &amp; Model</th>
                        <th>Year Acquired</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="printerTableBody">
                    <?php if (empty($printers)): ?>
                    <tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i><p>No printer records found</p></td></tr>
                    <?php else: ?>
                        <?php foreach ($printers as $p):
                            $status = $p['employeeId'] ? 'Working' : 'Available';
                            $statusClass = $status === 'Working' ? 'in-use' : 'available';
                        ?>
                        <?php $prAssignType = $p['employeeId'] ? 'employee' : ($p['location_id'] ? 'location' : 'unassigned'); ?>
                        <tr data-printer-id="<?php echo $p['printerId']; ?>"
                            data-assign-type="<?php echo $prAssignType; ?>"
                            data-serial="<?php echo strtolower($p['printerSerial'] ?? ''); ?>"
                            data-brand="<?php echo strtolower($p['printerBrand'] . ' ' . $p['printerModel']); ?>"
                            data-employee="<?php echo strtolower($p['employeeName'] ?? ''); ?>"
                            data-year="<?php echo $p['yearAcquired'] ?? ''; ?>"
                            data-location="<?php echo strtolower(htmlspecialchars($p['location_name'] ?? '')); ?>"
                            data-status="<?php echo $status; ?>">
                            <td class="row-counter"></td>
                            <td><span class="serial-number"><?php echo htmlspecialchars($p['printerSerial'] ?? 'N/A'); ?></span></td>
                            <td>
                                <div class="brand-model">
                                    <strong><?php echo htmlspecialchars($p['printerBrand']); ?></strong>
                                    <span><i class="fas fa-tag" style="font-size:11px;margin-right:4px;color:var(--text-light)"></i><?php echo htmlspecialchars($p['printerModel']); ?></span>
                                </div>
                            </td>
                            <td><span class="year-acquired"><?php echo htmlspecialchars($p['yearAcquired'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php if ($p['location_name']): ?>
                                    <div class="location-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($p['location_name']); ?></div>
                                <?php else: ?>
                                    <span style="color:var(--text-light);font-style:italic">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['employeeName']): ?>
                                    <div class="assigned-employee"><i class="fas fa-user"></i><?php echo htmlspecialchars($p['employeeName']); ?></div>
                                    <div style="font-size:12px;color:var(--text-light);margin-top:2px;padding-left:18px">ID: <?php echo htmlspecialchars($p['employeeId']); ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($p['lastMaintenanceDate'])): ?>
                                    <div class="maintenance-info"><i class="fas fa-tools"></i><?php echo date('M d, Y', strtotime($p['lastMaintenanceDate'])); ?></div>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-clock"></i> No record</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" title="Edit" onclick="editPrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="btn-action btn-delete" title="Delete" onclick="deletePrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-info"><span id="prRecordCount"></span></div>
            <div class="pagination-controls" id="prPaginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="prPerPageSelect" onchange="changePerPagePR()">
                        <option value="10">10</option><option value="25" selected>25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- ╔══════════════════════════════════════════╗
     ║  OTHER EQUIPMENT CATEGORY                ║
     ╚══════════════════════════════════════════╝ -->
<div class="category-content" id="category-other">
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-box stat-icon"></i>
            <div><div class="stat-label">Total Items</div><div class="stat-value"><?php echo $totalOther; ?></div></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle stat-icon"></i>
            <div><div class="stat-label">In Use</div><div class="stat-value"><?php echo $otherInUse; ?></div></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-box-open stat-icon"></i>
            <div><div class="stat-label">Available</div><div class="stat-value"><?php echo $otherAvail; ?></div></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-tools stat-icon"></i>
            <div><div class="stat-label">Under Maintenance</div><div class="stat-value"><?php echo $otherMaint; ?></div></div>
        </div>
    </div>

    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-list"></i> Equipment Inventory</h2>
            <div class="table-controls">
                <div class="filter-group">
                    <select id="otherAssignFilter" onchange="filterOtherEquipment()">
                        <option value="">All Assignments</option>
                        <option value="employee">Assigned to Employee</option>
                        <option value="location">Assigned to Location</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="otherSearch" placeholder="Search equipment..." oninput="filterOtherEquipment()">
                </div>
                <button class="btn btn-primary" onclick="openAddOtherEquipment()">
                    <i class="fas fa-plus"></i> Add Equipment
                </button>
            </div>
        </div>

        <div class="data-table">
            <table id="otherTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Equipment Type</th>
                        <th>Brand &amp; Model</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Year Acquired</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="otherTableBody">
                    <?php if (empty($otherEquipment)): ?>
                    <tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i><p>No equipment records found</p></td></tr>
                    <?php else: ?>
                        <?php foreach ($otherEquipment as $o):
                            $displayStatus = $o['employeeId'] ? 'In Use' : $o['status'];
                            $statusClass = match($displayStatus) {
                                'Available'        => 'available',
                                'In Use'           => 'in-use',
                                'Under Maintenance'=> 'maintenance',
                                'Disposed'         => 'disposed',
                                default            => 'available'
                            };
                        ?>
                        <?php $otherAssignType = $o['employeeId'] ? 'employee' : ($o['location_id'] ? 'location' : 'unassigned'); ?>
                        <tr data-equipment-id="<?php echo $o['otherEquipmentId']; ?>"
                            data-assign-type="<?php echo $otherAssignType; ?>"
                            data-serial="<?php echo strtolower($o['serialNumber']); ?>"
                            data-type="<?php echo strtolower($o['equipmentType']); ?>"
                            data-brand="<?php echo strtolower($o['brand'] . ' ' . $o['model']); ?>"
                            data-location="<?php echo strtolower($o['location_name']); ?>"
                            data-employee="<?php echo strtolower($o['employeeName'] ?? ''); ?>"
                            data-status="<?php echo $displayStatus; ?>"
                            data-year="<?php echo $o['yearAcquired']; ?>">
                            <td class="row-counter"></td>
                            <td><span class="serial-number"><?php echo htmlspecialchars($o['serialNumber']); ?></span></td>
                            <td>
                                <div class="equipment-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($o['equipmentType']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="brand-model">
                                    <strong><?php echo htmlspecialchars($o['brand']); ?></strong>
                                    <span><?php echo htmlspecialchars($o['model']); ?></span>
                                </div>
                                <?php if($o['details']): ?>
                                    <div class="equipment-details"><?php echo htmlspecialchars(substr($o['details'], 0, 50)) . (strlen($o['details']) > 50 ? '...' : ''); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="location-badge">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($o['location_name']); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($o['employeeName']): ?>
                                    <div class="assigned-employee"><i class="fas fa-user"></i><?php echo htmlspecialchars($o['employeeName']); ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo $displayStatus; ?></span></td>
                            <td><span class="year-acquired"><?php echo htmlspecialchars($o['yearAcquired']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" title="View Details" onclick="viewOtherEquipment(<?php echo $o['otherEquipmentId']; ?>)"><i class="fas fa-eye"></i></button>
                                    <button class="btn-action btn-edit" title="Edit" onclick="editOtherEquipment(<?php echo $o['otherEquipmentId']; ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="btn-action btn-delete" title="Delete" onclick="deleteOtherEquipment(<?php echo $o['otherEquipmentId']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-info"><span id="otherRecordCount"></span></div>
            <div class="pagination-controls" id="otherPaginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="otherPerPageSelect" onchange="changePerPageOther()">
                        <option value="10">10</option><option value="25" selected>25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- ╔══════════════════════════════════════════╗
     ║  MODALS                                  ║
     ╚══════════════════════════════════════════╝ -->

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
                        <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Assignment</h6>
                        <div class="mb-3">
                            <label class="form-label d-block">Assign To:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="suAssignType" id="suTypeLocation" value="location" checked onchange="toggleSUAssignmentType()">
                                <label class="btn btn-outline-primary" for="suTypeLocation"><i class="fas fa-building"></i> Location / Office</label>
                                <input type="radio" class="btn-check" name="suAssignType" id="suTypeEmployee" value="employee" onchange="toggleSUAssignmentType()">
                                <label class="btn btn-outline-primary" for="suTypeEmployee"><i class="fas fa-user"></i> Specific Employee</label>
                            </div>
                        </div>
                        <div id="suLocationContainer">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="small text-muted">Division</label>
                                    <select class="form-select" id="suLocDivision">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisionsData as $div): ?>
                                            <option value="<?= $div['location_id'] ?>"><?= $div['location_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Section</label>
                                    <select class="form-select" id="suLocSection" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="suLocUnit" disabled>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="suLocation" name="location_id">
                        </div>
                        <div id="suEmployeeContainer" style="display:none;">
                            <div class="mb-3">
                                <label for="suEmployeeSearch" class="form-label">Select Employee</label>
                                <input type="text" class="form-control" id="suEmployeeSearch" data-emp-search="suEmployee" placeholder="Type to search employee..." autocomplete="off">
                                <input type="hidden" id="suEmployee">
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
                        <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Assignment</h6>
                        <div class="mb-3">
                            <label class="form-label d-block">Assign To:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="monAssignType" id="monTypeLocation" value="location" checked onchange="toggleMonAssignmentType()">
                                <label class="btn btn-outline-primary" for="monTypeLocation"><i class="fas fa-building"></i> Location / Office</label>
                                <input type="radio" class="btn-check" name="monAssignType" id="monTypeEmployee" value="employee" onchange="toggleMonAssignmentType()">
                                <label class="btn btn-outline-primary" for="monTypeEmployee"><i class="fas fa-user"></i> Specific Employee</label>
                            </div>
                        </div>
                        <div id="monLocationContainer">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="small text-muted">Division</label>
                                    <select class="form-select" id="monLocDivision">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisionsData as $div): ?>
                                            <option value="<?= $div['location_id'] ?>"><?= $div['location_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Section</label>
                                    <select class="form-select" id="monLocSection" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="monLocUnit" disabled>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="monLocation" name="location_id">
                        </div>
                        <div id="monEmployeeContainer" style="display:none;">
                            <div class="mb-3">
                                <label for="monEmployeeSearch" class="form-label">Select Employee</label>
                                <input type="text" class="form-control" id="monEmployeeSearch" data-emp-search="monEmployee" placeholder="Type to search employee..." autocomplete="off">
                                <input type="hidden" id="monEmployee">
                            </div>
                        </div>
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
                            <div class="col-md-6">
                                <label for="aioBrand" class="form-label">Brand &amp; Model *</label>
                                <input type="text" class="form-control" id="aioBrand" required placeholder="e.g., HP All-in-One 24-df1033">
                            </div>
                            <div class="col-md-6">
                                <label for="aioSerial" class="form-label">Serial Number *</label>
                                <input type="text" class="form-control" id="aioSerial" required placeholder="e.g., AIO-SN12345">
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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="aioYear" class="form-label">Year Acquired *</label>
                                <input type="text" class="form-control" id="aioYear" required placeholder="e.g., 2023">
                            </div>

                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Assignment</h6>
                        <div class="mb-3">
                            <label class="form-label d-block">Assign To:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="aioAssignType" id="aioTypeLocation" value="location" checked onchange="toggleAIOAssignmentType()">
                                <label class="btn btn-outline-primary" for="aioTypeLocation"><i class="fas fa-building"></i> Location / Office</label>
                                <input type="radio" class="btn-check" name="aioAssignType" id="aioTypeEmployee" value="employee" onchange="toggleAIOAssignmentType()">
                                <label class="btn btn-outline-primary" for="aioTypeEmployee"><i class="fas fa-user"></i> Specific Employee</label>
                            </div>
                        </div>
                        <div id="aioLocationContainer">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="small text-muted">Division</label>
                                    <select class="form-select" id="aioLocDivision">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisionsData as $div): ?>
                                            <option value="<?= $div['location_id'] ?>"><?= $div['location_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Section</label>
                                    <select class="form-select" id="aioLocSection" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="aioLocUnit" disabled>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="aioLocation" name="location_id">
                        </div>
                        <div id="aioEmployeeContainer" style="display:none;">
                            <div class="mb-3">
                                <label for="aioEmployeeSearch" class="form-label">Select Employee</label>
                                <input type="text" class="form-control" id="aioEmployeeSearch" data-emp-search="aioEmployee" placeholder="Type to search employee..." autocomplete="off">
                                <input type="hidden" id="aioEmployee">
                            </div>
                        </div>
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

<!-- Printer Modal -->
<div class="modal fade" id="printerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printerModalTitle"><i class="fas fa-plus-circle"></i> Add New Printer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="printerForm">
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i> Printer Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Brand <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="printerBrand" required placeholder="e.g. HP, Canon, Epson">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="printerModel" required placeholder="Enter model number">
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="printerSerial" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Year Acquired <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="printerYear" required min="2000" max="2030" placeholder="YYYY">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Assignment</h6>
                        <div class="mb-3">
                            <label class="form-label d-block">Assign To:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="printerAssignType" id="printerTypeLocation" value="location" checked onchange="togglePrinterAssignmentType()">
                                <label class="btn btn-outline-primary" for="printerTypeLocation"><i class="fas fa-building"></i> Location / Office</label>
                                <input type="radio" class="btn-check" name="printerAssignType" id="printerTypeEmployee" value="employee" onchange="togglePrinterAssignmentType()">
                                <label class="btn btn-outline-primary" for="printerTypeEmployee"><i class="fas fa-user"></i> Specific Employee</label>
                            </div>
                        </div>
                        <div id="printerLocationContainer">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="small text-muted">Division</label>
                                    <select class="form-select" id="printerLocDivision">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisionsData as $div): ?>
                                            <option value="<?= $div['location_id'] ?>"><?= $div['location_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Section</label>
                                    <select class="form-select" id="printerLocSection" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="printerLocUnit" disabled>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="printerLocation" name="location_id">
                        </div>
                        <div id="printerEmployeeContainer" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Select Employee <small class="text-muted">(Optional)</small></label>
                                <input type="text" class="form-control" id="printerEmployeeSearch" data-emp-search="printerEmployee" placeholder="Type to search employee..." autocomplete="off">
                                <input type="hidden" id="printerEmployee">
                                <small class="form-text"><i class="fas fa-info-circle"></i> If assigned, status automatically becomes "Working"</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePrinter()"><i class="fas fa-save"></i> Save Printer</button>
            </div>
        </div>
    </div>
</div>

<!-- Other Equipment Modals -->
<?php include '../../includes/components/other_equipment_modals.php'; ?>


<!-- Pass data to JS -->
<script>
    var defaultPerPage     = <?php echo $defaultPerPage; ?>;
    var printerData        = <?php echo json_encode($printers); ?>;
    var otherEquipmentData = <?php echo json_encode($otherEquipment); ?>;
    var locationsData      = <?php echo json_encode($locations); ?>;
    // Use window.employeesData so it's accessible even when loaded as a dashboard module
    window.employeesData   = <?php echo json_encode($employees); ?>;
</script>

<script src="assets/js/location_manager.js?v=<?php echo time()?>"></script>
<script src="assets/js/autocomplete.js?v=<?php echo time()?>"></script>
<script src="assets/js/equipment.js?v=<?php echo time()?>"></script>