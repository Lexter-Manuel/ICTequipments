<?php
/**
 * Unified Equipment Inventory Module — Redesigned
 * Single table with filter dropdowns replacing multi-tab layout.
 * Views: All Equipment, By Type, By Location (with batch maintenance scheduling)
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
           COALESCE(l.location_name, el.location_name) AS location_name
    FROM tbl_equipment eq
    INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
    LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
    LEFT JOIN location l ON eq.location_id = l.location_id
    LEFT JOIN location el ON e.location_id = el.location_id
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

// Build unified flat array for JS
$unifiedRows = [];
foreach ($allEquipment as $eq) {
    $specs = $specsMap[$eq['equipment_id']] ?? [];
    $assignStatus = $eq['employee_id'] ? 'Active' : ($eq['status'] ?: 'Available');
    $unifiedRows[] = [
        'id'            => (int)$eq['equipment_id'],
        'type_id'       => (int)$eq['type_id'],
        'typeName'      => $eq['typeName'],
        'brand'         => $eq['brand'],
        'model'         => $eq['model'],
        'serial'        => $eq['serial_number'],
        'property_no'   => $eq['property_number'],
        'status'        => $assignStatus,
        'year'          => $eq['year_acquired'],
        'employee_id'   => $eq['employee_id'],
        'employeeName'  => $eq['employeeName'],
        'location_id'   => $eq['location_id'],
        'location_name' => $eq['location_name'],
        'lastMaint'     => $specs['Maintenance Date'] ?? null,
        'nextDue'       => $specs['Next Maintenance Date'] ?? null,
        'specs'         => $specs,
    ];
}

// Collect unique type names for dropdown
$typeNames = array_values(array_unique(array_column($allEquipment, 'typeName')));
sort($typeNames);

// Stats
$totalEquip = count($unifiedRows);
$totalActive = count(array_filter($unifiedRows, fn($r) => $r['employee_id'] != null));
$totalAvailable = count(array_filter($unifiedRows, fn($r) => $r['employee_id'] == null && $r['status'] === 'Available'));
$totalMaint = count(array_filter($unifiedRows, fn($r) => $r['status'] === 'Under Maintenance'));

// ── Fetch Employees ──
$stmtEmployees = $db->query("SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName FROM tbl_employee WHERE is_archive = '0' ORDER BY firstName, lastName");
$employees = $stmtEmployees->fetchAll();

// ── Fetch Locations ──
$stmtLoc = $db->query("SELECT location_id, location_name FROM location WHERE is_deleted = '0' ORDER BY location_name ASC");
$locations = $stmtLoc->fetchAll();

// ── Fetch Divisions for By-Location view (location_type_id = 1 = Division) ──
$stmtDiv = $db->query("
    SELECT l.location_id, l.location_name,
           (SELECT COUNT(*) FROM location c WHERE c.parent_location_id = l.location_id AND c.is_deleted = '0') as child_count
    FROM location l
    WHERE l.location_type_id = 1 AND l.is_deleted = '0'
    ORDER BY l.location_name
");
$divisions = $stmtDiv->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/components/location_loader.php'; ?>

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
            <p class="page-subtitle" id="viewSubtitle">All equipment across all categories</p>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary exportEquipment"><i class="fas fa-download"></i> Export</button>
    </div>
</div>

<!-- ══════════════════════════════════════════
     STATS ROW (updates dynamically)
     ══════════════════════════════════════════ -->
<div class="stats-grid" id="statsRow" style="grid-template-columns: repeat(4,1fr); margin-bottom: var(--space-5);">
    <div class="stat-card">
        <i class="stat-icon fas fa-desktop"></i>
        <div class="stat-label">Total Equipment<div class="stat-value" id="statTotal"><?php echo $totalEquip; ?></div></div>
        
    </div>
    <div class="stat-card">
        <i class="stat-icon fas fa-user"></i>
        <div class="stat-label">Active / In Use<div class="stat-value" id="statActive"><?php echo $totalActive; ?></div></div>
        
    </div>
    <div class="stat-card">
        <i class="stat-icon fas fa-check-circle"></i>
        <div class="stat-label">Available<div class="stat-value" id="statAvailable"><?php echo $totalAvailable; ?></div></div>
        
    </div>
    <div class="stat-card">
        <i class="stat-icon fas fa-tools"></i>
        <div class="stat-label">Under Maintenance<div class="stat-value" id="statMaint"><?php echo $totalMaint; ?></div></div>
        
    </div>
</div>

<!-- ══════════════════════════════════════════
     UNIFIED TOOLBAR
     ══════════════════════════════════════════ -->
<div class="data-table-container">
    <div class="table-header">
        <h2 class="table-title"><i class="fas fa-list"></i> <span id="tableTitle">Equipment List</span></h2>
        <div class="table-controls">
            <!-- View Selector -->
            <div class="filter-group" style="min-width:170px;flex:0 0 auto;">
                <select id="viewSelector" onchange="EqUnified.switchView(this.value)">
                    <option value="all">All Equipment</option>
                    <option value="computers">Computers</option>
                    <option value="printers">Printers</option>
                    <option value="other">Other Equipment</option>
                    <option value="location">By Location</option>
                </select>
            </div>

            <!-- Type Filter (hidden in By Location view) -->
            <div class="filter-group" id="typeFilterGroup" style="min-width:160px;flex:0 0 auto;">
                <select id="typeFilter" onchange="EqUnified.applyFilters()">
                    <option value="">All Types</option>
                    <?php foreach ($typeNames as $tn): ?>
                        <option value="<?php echo htmlspecialchars($tn); ?>"><?php echo htmlspecialchars($tn); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="filter-group" id="statusFilterGroup" style="min-width:140px;flex:0 0 auto;">
                <select id="statusFilter" onchange="EqUnified.applyFilters()">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Available">Available</option>
                    <option value="In Use">In Use</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                </select>
            </div>

            <!-- Search -->
            <div class="search-box" id="searchGroup">
                <i class="fas fa-search"></i>
                <input type="text" id="unifiedSearch" placeholder="Search serial, brand, employee, location..." oninput="EqUnified.applyFilters()">
            </div>

            <!-- Add Button -->
            <div class="dropdown" id="addBtnGroup">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus"></i> Add
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="openAddSystemUnit(); return false;"><i class="fas fa-tower-broadcast"></i> System Unit</a></li>
                    <li><a class="dropdown-item" href="#" onclick="openAddMonitor(); return false;"><i class="fas fa-tv"></i> Monitor</a></li>
                    <li><a class="dropdown-item" href="#" onclick="openAddAllInOne(); return false;"><i class="fas fa-computer"></i> All-in-One PC</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="openAddPrinter(); return false;"><i class="fas fa-print"></i> Printer</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="openAddOtherEquipment(); return false;"><i class="fas fa-server"></i> Other Equipment</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         ALL/FILTERED TABLE VIEW
         ══════════════════════════════════════════ -->
    <div id="tableView">
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Serial Number</th>
                        <th>Brand / Model</th>
                        <th>Specs</th>
                        <th>Year</th>
                        <th>Location</th>
                        <th>Assigned To</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="unifiedTableBody">
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-info"><span id="unifiedRecordCount"></span></div>
            <div class="pagination-controls" id="unifiedPagination"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="unifiedPerPage" onchange="EqUnified.changePerPage()">
                        <option value="10">10</option><option value="25">25</option>
                        <option value="50">50</option><option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BY LOCATION VIEW
         ══════════════════════════════════════════ -->
    <div id="locationView" style="display:none;">
        <!-- Division selector -->
        <div class="loc-view-toolbar">
            <div class="loc-view-selector">
                <label><i class="fas fa-building"></i> Division:</label>
                <select id="locDivisionSelect" onchange="EqUnified.loadLocationTree()">
                    <option value="">Select a Division</option>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?php echo $div['location_id']; ?>"><?php echo htmlspecialchars($div['location_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="loc-view-search">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="locSearch" placeholder="Search serial, brand, employee, section, unit..." oninput="EqUnified.filterLocationResults()">
                </div>
            </div>
        </div>

        <!-- Location tree content -->
        <div id="locationContent">
            <div class="loc-empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Select a Division</h3>
                <p>Choose a division above to see equipment grouped by section and unit.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     VIEW EQUIPMENT DETAIL MODAL
     ══════════════════════════════════════════ -->
<div class="modal fade" id="viewEquipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content view-equipment-modal">
            <div class="modal-header view-modal-header">
                <div class="view-modal-title-wrap">
                    <span class="view-modal-icon" id="viewEquipmentIcon"><i class="fas fa-info-circle"></i></span>
                    <div>
                        <h5 class="modal-title" id="viewEquipmentModalTitle">Equipment Details</h5>
                        <span class="view-modal-subtitle" id="viewEquipmentSubtitle"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="viewEquipmentContent">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                <button type="button" class="btn btn-primary" id="viewEquipmentEditBtn"><i class="fas fa-edit"></i> Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     EXISTING MODALS (keep all CRUD modals)
     ══════════════════════════════════════════ -->

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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="suMaintenanceDate" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="suMaintenanceDate">
                                <small class="form-text text-muted">Optional — leave blank if none</small>
                            </div>
                            <div class="col-md-6">
                                <label for="suNextMaintenanceDate" class="form-label">Next Maintenance Date</label>
                                <input type="date" class="form-control" id="suNextMaintenanceDate">
                                <small class="form-text text-muted">Optional — schedule next maintenance</small>
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
                                    <select class="form-select" id="suLocSection" disabled><option value="">Select Section</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="suLocUnit" disabled><option value="">Select Unit</option></select>
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
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="monMaintenanceDate" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="monMaintenanceDate">
                                <small class="form-text text-muted">Optional — leave blank if none</small>
                            </div>
                            <div class="col-md-6">
                                <label for="monNextMaintenanceDate" class="form-label">Next Maintenance Date</label>
                                <input type="date" class="form-control" id="monNextMaintenanceDate">
                                <small class="form-text text-muted">Optional — schedule next maintenance</small>
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
                                    <select class="form-select" id="monLocSection" disabled><option value="">Select Section</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="monLocUnit" disabled><option value="">Select Unit</option></select>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="aioYear" class="form-label">Year Acquired *</label>
                                <input type="text" class="form-control" id="aioYear" required placeholder="e.g., 2023">
                            </div>
                            <div class="col-md-6">
                                <label for="aioMaintenanceDate" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="aioMaintenanceDate">
                                <small class="form-text text-muted">Optional</small>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="aioNextMaintenanceDate" class="form-label">Next Maintenance Date</label>
                                <input type="date" class="form-control" id="aioNextMaintenanceDate">
                                <small class="form-text text-muted">Optional — schedule next maintenance</small>
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
                                    <select class="form-select" id="aioLocSection" disabled><option value="">Select Section</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="aioLocUnit" disabled><option value="">Select Unit</option></select>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="printerSerial" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Year Acquired <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="printerYear" required min="2000" max="2030" placeholder="YYYY">
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label for="printerMaintenanceDate" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="printerMaintenanceDate">
                                <small class="form-text text-muted">Optional</small>
                            </div>
                            <div class="col-md-6">
                                <label for="printerNextMaintenanceDate" class="form-label">Next Maintenance Date</label>
                                <input type="date" class="form-control" id="printerNextMaintenanceDate">
                                <small class="form-text text-muted">Optional — schedule next maintenance</small>
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
                                    <select class="form-select" id="printerLocSection" disabled><option value="">Select Section</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="printerLocUnit" disabled><option value="">Select Unit</option></select>
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

<!-- ══════════════════════════════════════════
     BATCH SCHEDULE MAINTENANCE MODAL (Location View)
     ══════════════════════════════════════════ -->
<div class="modal fade" id="locBatchScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl); overflow: hidden;">
            <div class="modal-header" style="background: var(--primary-green); color: #fff; border: none; padding: var(--space-4) var(--space-5);">
                <h5 class="modal-title" id="locBatchTitle">
                    <i class="fas fa-calendar-check me-2"></i> Schedule Maintenance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: var(--space-5);">
                <!-- Step 1: Configure -->
                <div id="locBatchConfig">
                    <div id="locBatchInfo" style="background: var(--bg-light); border-radius: var(--radius-lg); padding: var(--space-4); margin-bottom: var(--space-4);"></div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date (Shared Due Date)</label>
                            <input type="date" class="form-control" id="locBatchStartDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Frequency</label>
                            <select class="form-select" id="locBatchFrequency">
                                <option value="Monthly">Monthly (30 days)</option>
                                <option value="Quarterly">Quarterly (90 days)</option>
                                <option value="Semi-Annual" selected>Semi-Annual (180 days)</option>
                                <option value="Annual">Annual (365 days)</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info" style="font-size: var(--text-sm);">
                        <i class="fas fa-info-circle me-1"></i>
                        All unscheduled equipment under this location will share the same due date.
                        Equipment that already has an active schedule will be skipped.
                    </div>
                </div>

                <!-- Step 2: Result -->
                <div id="locBatchResult" style="display: none;">
                    <div id="locBatchResultContent" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm"></span> Processing…
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: var(--space-3) var(--space-5);">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="locBatchCancelBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="locBatchConfirmBtn" onclick="EqUnified.executeBatchSchedule()">
                    <i class="fas fa-check"></i> Create Schedules
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pass data to JS -->
<script>
    var defaultPerPage     = <?php echo $defaultPerPage; ?>;
    var allEquipmentData   = <?php echo json_encode($unifiedRows, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var equipmentTypeNames = <?php echo json_encode($typeNames); ?>;
    var locationsData      = <?php echo json_encode($locations); ?>;
    window.employeesData   = <?php echo json_encode($employees); ?>;
</script>

<script src="assets/js/location_manager.js?v=<?php echo time()?>"></script>
<script src="assets/js/autocomplete.js?v=<?php echo time()?>"></script>
<script src="assets/js/equipment.js?v=<?php echo time()?>"></script>
<script src="assets/js/equipment-unified.js?v=<?php echo time()?>"></script>