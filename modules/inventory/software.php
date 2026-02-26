<?php
/**
 * Software License Management Module
 */

require_once '../../config/database.php';
$db = getDB();

$stmt = $db->query("
    SELECT s.*, CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
    FROM tbl_software s
    LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
    ORDER BY s.softwareId DESC
");
$softwareList = $stmt->fetchAll();

$totalLicenses = count($softwareList);
$activeLicenses = $expiringSoon = $expiredLicenses = $subscriptionCount = $perpetualCount = 0;
$today = new DateTime();

foreach ($softwareList as &$software) {
    if ($software['licenseType'] === 'Subscription') $subscriptionCount++;
    if ($software['licenseType'] === 'Perpetual')    $perpetualCount++;

    if ($software['expiryDate']) {
        $expiryDate = new DateTime($software['expiryDate']);
        $interval   = $today->diff($expiryDate);
        $daysLeft   = $interval->invert ? -$interval->days : $interval->days;

        if ($daysLeft < 0)       { $software['status'] = 'Expired';       $software['statusClass'] = 'disposed';     $expiredLicenses++; }
        elseif ($daysLeft <= 30) { $software['status'] = 'Expiring Soon'; $software['statusClass'] = 'maintenance';  $expiringSoon++; }
        else                     { $software['status'] = 'Active';         $software['statusClass'] = 'in-use';       $activeLicenses++; }
        $software['daysLeft'] = $daysLeft;
    } else {
        $software['status'] = 'Active'; $software['statusClass'] = 'in-use';
        $software['daysLeft'] = null;   $activeLicenses++;
    }
}
unset($software);

$stmtEmployees = $db->query("SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName FROM tbl_employee ORDER BY firstName, lastName");
$employees = $stmtEmployees->fetchAll();
?>

<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/software.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-key"></i>
        </div>
        <div>
            <h1 class="page-title">Software Licenses</h1>
        </div>
    </div>
    <div class="header-actions">
        <button class="btn btn-secondary"><i class="fas fa-download"></i> Export</button>
        <button class="btn btn-primary" onclick="openAddSoftware()"><i class="fas fa-plus"></i> Add License</button>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-key stat-icon"></i>
        <div><div class="stat-label">Total Licenses</div><div class="stat-value"><?php echo $totalLicenses; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle stat-icon"></i>
        <div><div class="stat-label">Active</div><div class="stat-value"><?php echo $activeLicenses; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-exclamation-circle stat-icon"></i>
        <div><div class="stat-label">Expiring Soon</div><div class="stat-value"><?php echo $expiringSoon; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-times-circle stat-icon"></i>
        <div><div class="stat-label">Expired</div><div class="stat-value"><?php echo $expiredLicenses; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-sync-alt stat-icon"></i>
        <div><div class="stat-label">Subscription</div><div class="stat-value"><?php echo $subscriptionCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-infinity stat-icon"></i>
        <div><div class="stat-label">Perpetual</div><div class="stat-value"><?php echo $perpetualCount; ?></div></div>
    </div>
</div>

<!-- Table -->
<div class="data-table-container">
    <div class="table-header">
        <h2 class="table-title"><i class="fas fa-list"></i> License Inventory</h2>
        <div class="table-controls">
            <div class="filter-group">
                <select id="statusFilter" onchange="filterSoftware()">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Expiring Soon">Expiring Soon</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="typeFilter" onchange="filterSoftware()">
                    <option value="">All Types</option>
                    <option value="Subscription">Subscription</option>
                    <option value="Perpetual">Perpetual</option>
                </select>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="softwareSearch" placeholder="Search software, license details..." oninput="filterSoftware()">
            </div>
        </div>
    </div>

    <div class="data-table">
        <table id="softwareTable">
            <thead>
                <tr>
                    <th>Software Name</th>
                    <th>License Details</th>
                    <th>Type</th>
                    <th>Expiry Date</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="softwareTableBody">
                <?php if (empty($softwareList)): ?>
                <tr><td colspan="7" class="empty-state"><i class="fas fa-inbox"></i><p>No software license records found</p></td></tr>
                <?php else: ?>
                    <?php foreach ($softwareList as $s): ?>
                    <tr data-software-id="<?php echo $s['softwareId']; ?>"
                        data-name="<?php echo strtolower($s['licenseSoftware']); ?>"
                        data-details="<?php echo strtolower($s['licenseDetails']); ?>"
                        data-type="<?php echo $s['licenseType'] ?? ''; ?>"
                        data-status="<?php echo $s['status']; ?>"
                        data-employee="<?php echo strtolower($s['employeeName'] ?? ''); ?>">
                        <td>
                            <div class="software-name">
                                <div class="software-icon"><i class="fas fa-compact-disc"></i></div>
                                <div class="software-info">
                                    <strong><?php echo htmlspecialchars($s['licenseSoftware']); ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><div style="font-size:14px;color:var(--text-dark)"><?php echo htmlspecialchars($s['licenseDetails']); ?></div></td>
                        <td>
                            <?php $typeCls = $s['licenseType'] === 'Subscription' ? 'available' : 'in-use'; ?>
                            <span class="status-badge status-<?php echo $typeCls; ?>"><?php echo htmlspecialchars($s['licenseType'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <?php if ($s['expiryDate']): ?>
                                <div>
                                    <div class="year-acquired"><?php echo date('M d, Y', strtotime($s['expiryDate'])); ?></div>
                                    <?php if ($s['daysLeft'] !== null): ?>
                                        <?php if ($s['daysLeft'] > 0): ?>
                                            <div style="font-size:12px;color:<?php echo $s['daysLeft'] <= 30 ? '#b45309' : 'var(--text-light)'; ?>;margin-top:2px">
                                                <i class="fas fa-clock"></i> <?php echo $s['daysLeft']; ?> days left
                                            </div>
                                        <?php elseif ($s['daysLeft'] < 0): ?>
                                            <div style="font-size:12px;color:#dc2626;margin-top:2px">
                                                <i class="fas fa-exclamation-circle"></i> Expired <?php echo abs($s['daysLeft']); ?> days ago
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size:12px;color:#dc2626;margin-top:2px"><i class="fas fa-exclamation-circle"></i> Expires today</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-infinity"></i> No Expiry</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['employeeName']): ?>
                                <div class="assigned-employee"><i class="fas fa-user"></i><?php echo htmlspecialchars($s['employeeName']); ?></div>
                                <div style="font-size:12px;color:var(--text-light);margin-top:2px;padding-left:18px">ID: <?php echo htmlspecialchars($s['employeeId']); ?></div>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge status-<?php echo $s['statusClass']; ?>"><?php echo htmlspecialchars($s['status']); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" title="View Details" onclick="viewSoftware(<?php echo $s['softwareId']; ?>)"><i class="fas fa-eye"></i></button>
                                <button class="btn-action btn-edit" title="Edit" onclick="editSoftware(<?php echo $s['softwareId']; ?>)"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-delete" title="Delete" onclick="deleteSoftware(<?php echo $s['softwareId']; ?>)"><i class="fas fa-trash"></i></button>
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="softwareModal" tabindex="-1" aria-hidden="true">

<!-- Software Detail View Modal -->
<div class="modal fade" id="softwareDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark, #1a6b3c)); color: #fff; border-bottom: none;">
                <h5 class="modal-title" id="softwareDetailModalTitle">
                    <i class="fas fa-key"></i> Software License Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="softwareDetailContent">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                <button type="button" class="btn btn-primary" id="softwareDetailEditBtn" style="display:none;"><i class="fas fa-edit"></i> Edit</button>
            </div>
        </div>
    </div>
</div>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="softwareModalTitle"><i class="fas fa-plus-circle"></i> Add New License</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="softwareForm">
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i> License Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Software Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="softwareName" required placeholder="e.g. Microsoft Office 365">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">License Details <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="softwareDetails" required placeholder="License key or details">
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label class="form-label">License Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="softwareType" required>
                                    <option value="">Select Type</option>
                                    <option value="Subscription">Subscription</option>
                                    <option value="Perpetual">Perpetual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="softwareExpiry">
                                <small class="form-text"><i class="fas fa-info-circle"></i> Leave empty for perpetual licenses</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-lock"></i> Credentials (Optional)</h6>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="softwareEmail" placeholder="Account email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" id="softwarePassword" placeholder="Account password">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title"><i class="fas fa-user"></i> Assignment</h6>
                        <label class="form-label">Assigned Employee <small class="text-muted">(Optional)</small></label>
                        <select class="form-select" id="softwareEmployee">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['employeeId']; ?>"><?php echo htmlspecialchars($emp['fullName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSoftware()"><i class="fas fa-save"></i> Save License</button>
            </div>
        </div>
    </div>
</div>

<script>var softwareData = <?php echo json_encode($softwareList); ?>;</script>
<script src="assets/js/software.js?v=<?php echo time()?>"></script>