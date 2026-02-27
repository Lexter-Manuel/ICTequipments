<?php
/**
 * Printer Management Module - Database Integrated
 */

require_once '../../config/database.php';
$db = getDB();

$stmt = $db->query("
    SELECT 
        p.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName,
        m.lastMaintenanceDate
    FROM tbl_printer p
    LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId
    LEFT JOIN tbl_maintenance_schedule m ON (
        p.printerId = m.equipmentId 
        AND (LOWER(TRIM(m.equipmentType)) = 'printer' OR m.equipmentType = '4')
    )
    ORDER BY p.printerId DESC
");
$printers = $stmt->fetchAll();

$stmtEmployees = $db->query("
    SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName
    FROM tbl_employee ORDER BY firstName, lastName
");
$employees = $stmtEmployees->fetchAll();

$totalPrinters  = count($printers);
$inUseCount     = count(array_filter($printers, fn($p) => $p['employeeId'] != null));
$availableCount = count(array_filter($printers, fn($p) => $p['employeeId'] == null));
?>

<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/printer.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-print"></i>
        </div>
        <div>
            <h1 class="page-title">Printer Management</h1>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary"><i class="fas fa-download"></i> Export</button>
        <button class="btn btn-primary" onclick="openAddPrinter()"><i class="fas fa-plus"></i> Add Printer</button>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-print stat-icon"></i>
        <div>
            <div class="stat-label">Total Printers</div>
            <div class="stat-value"><?php echo $totalPrinters; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle stat-icon"></i>
        <div>
            <div class="stat-label">In Use</div>
            <div class="stat-value"><?php echo $inUseCount; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-box-open stat-icon"></i>
        <div>
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo $availableCount; ?></div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="data-table-container">
    <div class="table-header">
        <h2 class="table-title"><i class="fas fa-list"></i> Printer Inventory</h2>
        <div class="table-controls">
            <div class="filter-group">
                <select id="statusFilter" onchange="filterPrinters()">
                    <option value="">All Statuses</option>
                    <option value="Operational">Operational</option>
                    <option value="For Replacement">For Replacement</option>
                    <option value="Disposed">Disposed</option>
                </select>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="printerSearch" placeholder="Search serial, brand, model..." oninput="filterPrinters()">
            </div>
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
                    <th>Assigned To</th>
                    <th>Last Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="printerTableBody">
                <?php if (empty($printers)): ?>
                <tr><td colspan="8" class="empty-state"><i class="fas fa-inbox"></i><p>No printer records found</p></td></tr>
                <?php else: ?>
                    <?php foreach ($printers as $p):
                        $status = $p['employeeId'] ? 'Working' : 'Available';
                        $statusClass = $status === 'Working' ? 'in-use' : 'available';
                    ?>
                    <tr data-printer-id="<?php echo $p['printerId']; ?>"
                        data-serial="<?php echo strtolower($p['printerSerial'] ?? ''); ?>"
                        data-brand="<?php echo strtolower($p['printerBrand'] . ' ' . $p['printerModel']); ?>"
                        data-employee="<?php echo strtolower($p['employeeName'] ?? ''); ?>"
                        data-year="<?php echo $p['yearAcquired'] ?? ''; ?>"
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

<!-- Add/Edit Printer Modal -->
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
                        <h6 class="form-section-title"><i class="fas fa-user"></i> Assignment</h6>
                        <label class="form-label">Assigned Employee <small class="text-muted">(Optional)</small></label>
                        <select class="form-select" id="printerEmployee">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text"><i class="fas fa-info-circle"></i> If assigned, status automatically becomes "Working"</small>
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

<script>var printerData = <?php echo json_encode($printers); ?>;</script>
<script src="assets/js/printer.js?v=<?php echo time()?>"></script>