<?php
// modules/inventory/roster.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
$db = Database::getInstance()->getConnection();

// Fetch Employees
$employeeStmt = $db->query("
    SELECT 
        e.*,
        COALESCE(e.is_active, 1) AS is_active,
        l.location_name,
        l.location_type_id,
        lt.name AS location_type_name,
        parent_loc.location_name AS parent_location_name
    FROM tbl_employee e
    LEFT JOIN location l ON e.location_id = l.location_id
    LEFT JOIN location_type lt ON l.location_type_id = lt.id
    LEFT JOIN location parent_loc ON l.parent_location_id = parent_loc.location_id
    ORDER BY e.lastName ASC, e.firstName ASC
");
$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

// Dropdowns for Edit Modal
$divisions = $db->query("SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$sections = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 2 AND is_deleted = '0' ORDER BY location_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$units = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 3 AND is_deleted = '0' ORDER BY location_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalEmployees = count($employees);
$activeCount = count(array_filter($employees, fn($e) => ($e['is_active'] ?? 1) == 1));
$casualCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Casual'));
$jobOrderCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Job Order'));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<link rel="stylesheet" href="assets/css/roster.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-checklist.css?v=<?php echo time(); ?>">

<!-- ========================================
     ROSTER LIST VIEW
     ======================================== -->
<div id="roster-list-view">
    <div class="page-header">
        <h2 class="page-title"><i class="fas fa-address-book"></i> Employee Roster</h2>
        <div class="header-meta">
            <span class="last-updated"><i class="fas fa-sync-alt"></i> <?php echo date('M d, Y'); ?></span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value"><?php echo $totalEmployees; ?></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-check stat-icon"></i>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo $activeCount; ?></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-clock stat-icon"></i>
            <div class="stat-label">Casual</div>
            <div class="stat-value"><?php echo $casualCount; ?></div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-tie stat-icon"></i>
            <div class="stat-label">Job Order</div>
            <div class="stat-value"><?php echo $jobOrderCount; ?></div>
        </div>
    </div>

    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-list"></i> All Employees</h2>
            <div class="table-controls">
                <div class="filter-group">
                    <select id="statusFilter" onchange="filterRoster()">
                        <option value="">All Statuses</option>
                        <option value="Permanent">Permanent</option>
                        <option value="Casual">Casual</option>
                        <option value="Job Order">Job Order</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="activeFilter" onchange="filterRoster()">
                        <option value="">Active & Inactive</option>
                        <option value="1" selected>Active Only</option>
                        <option value="0">Inactive Only</option>
                    </select>
                </div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="rosterSearch" placeholder="Search employees..." oninput="filterRoster()">
                </div>
            </div>
        </div>

        <div class="data-table">
            <table id="rosterTable">
                <thead>
                    <tr>
                        <th class="col-photo">Photo</th>
                        <th class="sortable" data-col="empid">ID <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="sortable" data-col="name">Full Name <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="sortable" data-col="position">Position <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="sortable" data-col="assignment">Assignment <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="sortable" data-col="status">Status <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="sortable" data-col="active">Active <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="rosterTableBody">
                    <?php foreach ($employees as $emp): 
                        $fullName = trim(implode(' ', array_filter([$emp['firstName'], $emp['middleName'], $emp['lastName'], $emp['suffixName']])));
                        $statusClass = strtolower(str_replace(' ', '-', $emp['employmentStatus']));
                        $isActive = isset($emp['is_active']) ? (int)$emp['is_active'] : 1;
                        
                        $locationParts = [];
                        if ($emp['parent_location_name']) $locationParts[] = $emp['parent_location_name'];
                        if ($emp['location_name']) $locationParts[] = $emp['location_name'];
                        $locationBreadcrumb = implode(' › ', $locationParts);
                    ?>
                    <tr data-status="<?php echo htmlspecialchars($emp['employmentStatus']); ?>"
                        data-name="<?php echo strtolower(htmlspecialchars($fullName)); ?>"
                        data-position="<?php echo strtolower(htmlspecialchars($emp['position'])); ?>"
                        data-empid="<?php echo htmlspecialchars($emp['employeeId']); ?>"
                        data-assignment="<?php echo strtolower(htmlspecialchars($locationBreadcrumb)); ?>"
                        data-active="<?php echo $isActive; ?>"
                        class="<?php echo $isActive ? '' : 'row-inactive'; ?>">
                        <td>
                            <?php if ($emp['photoPath']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($emp['photoPath']); ?>" class="employee-photo-thumb">
                            <?php else: ?>
                                <div class="employee-photo-placeholder"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="emp-id"><?php echo htmlspecialchars($emp['employeeId']); ?></span></td>
                        <td>
                            <div class="emp-name"><?php echo htmlspecialchars($fullName); ?></div>
                            <div class="emp-sex"><?php echo htmlspecialchars($emp['sex']); ?></div>
                        </td>
                        <td><span class="emp-position"><?php echo htmlspecialchars($emp['position']); ?></span></td>
                        <td>
                            <?php if ($locationBreadcrumb): ?>
                                <div class="location-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($locationBreadcrumb); ?></div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($emp['employmentStatus']); ?></span></td>
                        <td>
                            <span class="active-badge active-badge-<?php echo $isActive ? 'yes' : 'no'; ?>">
                                <i class="fas fa-<?php echo $isActive ? 'check-circle' : 'times-circle'; ?>"></i>
                                <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" onclick="viewEmployee(<?php echo $emp['employeeId']; ?>)" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-edit" onclick="editEmployee(<?php echo $emp['employeeId']; ?>)" title="Edit Employee">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-toggle btn-toggle-<?php echo $isActive ? 'deactivate' : 'activate'; ?>"
                                        onclick="toggleActiveStatus(<?php echo $emp['employeeId']; ?>, <?php echo $isActive; ?>, this)"
                                        title="<?php echo $isActive ? 'Deactivate' : 'Activate'; ?> Employee">
                                    <i class="fas fa-<?php echo $isActive ? 'user-slash' : 'user-check'; ?>"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="data-table-cards" id="mobileCardsContainer"></div>

        <div class="table-footer">
            <div class="footer-info"><span id="recordCount"></span></div>
            <div class="pagination-controls" id="paginationControls"></div>
            <div class="per-page-control">
                <label>Rows:
                    <select id="perPageSelect" onchange="changePerPage()">
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- ========================================
     EMPLOYEE PROFILE VIEW
     ======================================== -->
<div id="employee-profile-view" style="display: none;">
    
    <div class="page-header">
        <button class="btn-back" onclick="closeEmployeeProfile()">
            <i class="fas fa-arrow-left"></i> Back to Roster
        </button>
        <div class="header-actions">
            <button class="btn btn-success" onclick="generateEmployeeReport()">
                <i class="fas fa-file-pdf"></i> Generate Report
            </button>
            <button class="btn btn-secondary" onclick="editEmployeeFromProfile()">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
    </div>

    <!-- Personal Information with Integrated Profile Header -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-user-circle"></i> Personal Information</h2>
        </div>
        <div class="profile-section-content">
            <div id="profile-personal-info"></div>
        </div>
    </div>

    <!-- Employment Details -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-briefcase"></i> Employment Details</h2>
        </div>
        <div class="profile-section-content">
            <div class="detail-grid" id="profile-employment-info"></div>
        </div>
    </div>

    <!-- Assigned Equipment -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title"><i class="fas fa-laptop"></i> Assigned Equipment</h2>
        </div>
        <div class="profile-section-content">
            <div class="asset-counts" id="equipment-counts"></div>
            <div class="equipment-grid" id="equipment-grid"></div>
        </div>
    </div>

    <!-- Printers -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-print"></i> Assigned Printers 
                <span class="count-badge" id="printer-count">0</span>
            </h2>
        </div>
        <div class="profile-section-content">
            <div class="equipment-grid" id="printers-grid"></div>
        </div>
    </div>

    <!-- Software Licenses -->
    <div class="data-table-container">
        <div class="table-header">
            <h2 class="table-title">
                <i class="fas fa-key"></i> Software Licenses 
                <span class="count-badge" id="software-count">0</span>
            </h2>
        </div>
        <div class="profile-section-content">
            <div id="software-licenses-container"></div>
        </div>
    </div>

    <!-- Floating Maintenance FAB -->
    <div class="fab-maintenance-wrapper" id="fabMaintenanceWrapper" data-hidden="true">
        <div class="fab-equipment-panel" id="fabEquipmentPanel">
            <div class="fab-panel-header">
                <i class="fas fa-tools"></i>
                <span>Perform Maintenance</span>
                <button class="fab-panel-close" onclick="toggleFabPanel(false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="fab-panel-body" id="fabEquipmentList">
                <!-- Populated dynamically -->
            </div>
            <div class="fab-panel-footer" id="fabPanelFooter" style="display:none;">
                <button class="fab-perform-all-btn" onclick="fabPerformAll()">
                    <i class="fas fa-play-circle"></i> Perform All Maintenance
                </button>
            </div>
        </div>
        <button class="fab-maintenance-btn" id="fabMaintenanceBtn" onclick="toggleFabPanel()" title="Perform Maintenance on Equipment">
            <i class="fas fa-tools fab-icon-main"></i>
            <span class="fab-badge" id="fabEquipmentCount">0</span>
        </button>
    </div>
</div>

<!-- Maintenance Modal (reusable component) -->
<?php include __DIR__ . '/../../includes/components/maintenance_modal.php'; ?>

<div class="modal fade" id="equipmentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Equipment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i id="detailIcon" class="fas fa-desktop fa-3x text-secondary"></i>
                    </div>
                    <h5 id="detailBrand" class="fw-bold mb-1">Brand Name</h5>
                    <p id="detailSerial" class="text-muted font-monospace small">SN: 12345678</p>
                    <span id="detailType" class="badge bg-secondary">Type</span>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Assigned To:</span>
                        <span id="detailOwner" class="fw-bold text-dark">Employee Name</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Location:</span>
                        <span id="detailLocation" class="text-dark">Location Name</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Status:</span>
                        <span class="text-success fw-bold">Active</span> </li>
                </ul>
            </div>
            <div class="modal-footer border-0 bg-light justify-content-center">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/components/roster_edit_modal.php'; ?>
<?php include '../../includes/components/cropper_modal.php'; ?>

<script>
var rosterData = <?php 
    echo json_encode(array_map(function($emp) {
        $emp['photo_url'] = !empty($emp['photoPath']) ? 'uploads/' . $emp['photoPath'] : null;
        return $emp;
    }, $employees)); 
?>;
var sectionsData = <?php echo json_encode($sections); ?>;
var unitsData = <?php echo json_encode($units); ?>;
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script src="assets/js/roster.js?v=<?php echo time(); ?>"></script>