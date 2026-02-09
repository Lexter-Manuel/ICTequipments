<?php
/**
 * Printer Management Module - Database Integrated
 * Full CRUD operations with database backend
 */

require_once '../../config/database.php';

$db = getDB();

// Fetch printers with employee data
$stmt = $db->query("
    SELECT 
        p.*,
        CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
    FROM tbl_printer p
    LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId
    ORDER BY p.printerId DESC
");
$printers = $stmt->fetchAll();

// Fetch employees for dropdown
$stmtEmployees = $db->query("
    SELECT employeeId, CONCAT_WS(' ', firstName, middleName, lastName) as fullName
    FROM tbl_employee
    ORDER BY firstName, lastName
");
$employees = $stmtEmployees->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
	<h2>
		<i class="fas fa-print"></i>
		Printer Management
	</h2>
	<div class="header-actions">
		<button class="btn btn-secondary">
			<i class="fas fa-download"></i>
			Export
		</button>
		<button class="btn btn-primary" onclick="openAddPrinter()">
			<i class="fas fa-plus"></i>
			Add Printer
		</button>
	</div>
</div>

<!-- Filters -->
<div class="filters-bar">
	<div class="filter-group" style="flex:1">
		<label><i class="fas fa-search"></i> Search:</label>
		<input type="text" id="printerSearch" placeholder="Serial, brand, model..." oninput="filterPrinters()" />
	</div>
</div>

<!-- Data Table -->
<div class="data-table">
	<table>
		<thead>
			<tr>
				<th>Serial</th>
				<th>Brand / Model</th>
				<th>Year</th>
				<th>Assigned To</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody id="printerTableBody">
			<?php foreach ($printers as $p): ?>
			<?php $status = $p['employeeId'] ? 'Working' : 'Available'; ?>
			<tr>
				<td><strong style="color:var(--primary-green)"><?php echo htmlspecialchars($p['printerSerial'] ?? 'N/A'); ?></strong></td>
				<td>
					<div style="font-weight:600"><?php echo htmlspecialchars($p['printerBrand']); ?></div>
					<div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($p['printerModel']); ?></div>
				</td>
				<td><?php echo htmlspecialchars($p['yearAcquired'] ?? 'N/A'); ?></td>
				<td>
					<?php if ($p['employeeName']): ?>
						<div style="font-weight:600"><?php echo htmlspecialchars($p['employeeName']); ?></div>
						<div style="font-size:12px;color:var(--text-light)">ID: <?php echo htmlspecialchars($p['employeeId']); ?></div>
					<?php else: ?>
						<span style="color:var(--text-light);font-style:italic">Unassigned</span>
					<?php endif; ?>
				</td>
				<td>
					<?php $cls = strtolower(str_replace(' ', '', $status)); ?>
					<span class="status-badge status-<?php echo $cls; ?>"><?php echo htmlspecialchars($status); ?></span>
				</td>
				<td>
					<div class="action-buttons">
						<button class="btn-icon" title="Edit" onclick="editPrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-edit"></i></button>
						<button class="btn-icon btn-danger" title="Delete" onclick="deletePrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<!-- Bootstrap Modal for Add/Edit Printer -->
<div class="modal fade" id="printerModal" tabindex="-1" aria-labelledby="printerModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header border-bottom">
				<h5 class="modal-title" id="printerModalTitle">Add New Printer</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="printerForm">
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="printerBrand" class="form-label">Brand *</label>
							<input type="text" class="form-control" id="printerBrand" required>
						</div>
						<div class="col-md-6">
							<label for="printerModel" class="form-label">Model *</label>
							<input type="text" class="form-control" id="printerModel" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="printerSerial" class="form-label">Serial Number *</label>
							<input type="text" class="form-control" id="printerSerial" required>
						</div>
						<div class="col-md-6">
							<label for="printerYear" class="form-label">Year Acquired *</label>
							<input type="number" class="form-control" id="printerYear" required min="2000" max="2030">
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-12">
							<label for="printerEmployee" class="form-label">Assigned Employee</label>
							<select class="form-select" id="printerEmployee">
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
				<button type="button" class="btn btn-primary" onclick="savePrinter()" style="background-color: var(--primary-green); border-color: var(--primary-green);">Save Printer</button>
			</div>
		</div>
	</div>
</div>