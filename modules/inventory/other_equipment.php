<?php
/**
 * Other ICT Equipment Module
 */

require_once '../../config/database.php';
$db = getDB();

$stmt = $db->query("
    SELECT o.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName, l.location_name
    FROM tbl_otherequipment o
    LEFT JOIN tbl_employee e ON o.employeeId = e.employeeId
    LEFT JOIN location l ON o.location_id = l.location_id
    ORDER BY o.otherEquipmentId DESC
");
$equipment = $stmt->fetchAll();

$stmtLoc = $db->query("SELECT location_id, location_name FROM location WHERE is_deleted = '0' ORDER BY location_name ASC");
$locations = $stmtLoc->fetchAll();

$stmtEmp = $db->query("SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName FROM tbl_employee ORDER BY firstName ASC");
$employees = $stmtEmp->fetchAll();

$totalItems       = count($equipment);
$inUseCount       = count(array_filter($equipment, fn($o) => $o['employeeId'] != null || $o['status'] == 'In Use'));
$availableCount   = count(array_filter($equipment, fn($o) => $o['employeeId'] == null && $o['status'] == 'Available'));
$maintenanceCount = count(array_filter($equipment, fn($o) => $o['status'] == 'Under Maintenance'));
?>

<?php include '../../includes/components/location_loader.php'; ?>

<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/autocomplete.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/other_equipment.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-server"></i>
        </div>
        <div>
            <h1 class="page-title">Other ICT Equipment</h1>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary"><i class="fas fa-download"></i> Export</button>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-box stat-icon"></i>
        <div><div class="stat-label">Total Items</div><div class="stat-value"><?php echo $totalItems; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle stat-icon"></i>
        <div><div class="stat-label">In Use</div><div class="stat-value"><?php echo $inUseCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-box-open stat-icon"></i>
        <div><div class="stat-label">Available</div><div class="stat-value"><?php echo $availableCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-tools stat-icon"></i>
        <div><div class="stat-label">Under Maintenance</div><div class="stat-value"><?php echo $maintenanceCount; ?></div></div>
    </div>
</div>

<!-- Table Container -->
<div class="data-table-container">
    <div class="table-header">
        <h2 class="table-title"><i class="fas fa-list"></i> Equipment Inventory</h2>
        <div class="table-controls">
            <div class="filter-group">
                <select id="statusFilter" onchange="filterOtherEquipment()">
                    <option value="">All Statuses</option>
                    <option value="Operational">Operational</option>
                    <option value="For Replacement">For Replacement</option>
                    <option value="Disposed">Disposed</option>
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
                <?php if (empty($equipment)): ?>
                <tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i><p>No equipment records found</p></td></tr>
                <?php else: ?>
                    <?php foreach ($equipment as $o):
                        $displayStatus = $o['employeeId'] ? 'In Use' : $o['status'];
                        $statusClass = match($displayStatus) {
                            'Available'        => 'available',
                            'In Use'           => 'in-use',
                            'Under Maintenance'=> 'maintenance',
                            'Disposed'         => 'disposed',
                            default            => 'available'
                        };
                    ?>
                    <tr data-equipment-id="<?php echo $o['otherEquipmentId']; ?>"
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

<?php include '../../includes/components/other_equipment_modals.php'; ?>

<script>
    var otherEquipmentData = <?php echo json_encode($equipment); ?>;
    var locationsData      = <?php echo json_encode($locations); ?>;
    var employeesData      = <?php echo json_encode($employees); ?>;
</script>
<script src="assets/js/location_manager.js?v=<?php echo time()?>"></script>
<script src="assets/js/autocomplete.js?v=<?php echo time()?>"></script>
<script src="assets/js/other_equipment.js?v=<?php echo time()?>"></script>