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

<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
.page-header h2{font-family:'Crimson Pro',serif;font-size:22px;color:var(--text-dark);font-weight:700}
.header-actions{display:flex;gap:12px}
.btn{padding:10px 18px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.btn-primary{background:var(--primary-green);color:white}
.btn-secondary{background:white;color:var(--text-dark);border:1px solid var(--border-color)}
.filters-bar{background:var(--bg-light);padding:12px;border-radius:8px;margin-bottom:16px;display:flex;gap:12px;align-items:center}
.filter-group{display:flex;align-items:center;gap:8px}
.filter-group select,.filter-group input{padding:8px 10px;border:1px solid var(--border-color);border-radius:6px;background:white}
.data-table{overflow-x:auto;border-radius:8px;border:1px solid var(--border-color)}
.data-table table{width:100%;border-collapse:collapse}
.data-table thead{background:linear-gradient(135deg,var(--primary-green),var(--accent-green));color:white}
.data-table th{padding:12px;text-align:left;font-weight:600;font-size:13px}
.data-table td{padding:12px;border-bottom:1px solid var(--border-color);color:var(--text-dark)}
.status-badge{display:inline-block;padding:6px 10px;border-radius:12px;font-size:12px;font-weight:700;text-transform:uppercase}
.status-working{background:rgba(34,197,94,0.12);color:#16a34a}
.status-available{background:rgba(59,130,246,0.12);color:#2563eb}
.status-needstoner{background:rgba(245,158,11,0.12);color:#d97706}
.status-offline{background:rgba(220,38,38,0.08);color:#dc2626}
.action-buttons{display:flex;gap:8px}
.btn-icon{width:36px;height:36px;padding:0;border-radius:6px;border:1px solid var(--border-color);background:white;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
.btn-icon:hover{background:var(--primary-green);color:white;border-color:var(--primary-green)}
.btn-danger:hover{background:#dc2626;border-color:#dc2626;color:white !important}
</style>

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
						<button class="btn-icon btn-danger" style="color: black" title="Delete" onclick="deletePrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
// Filter printers with AJAX
function filterPrinters() {
	const search = document.getElementById('printerSearch').value;
	
	fetch(`../../ajax/manage_printer.php?action=list&search=${encodeURIComponent(search)}`)
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				renderPrinters(data.data);
			} else {
				alert('Error: ' + data.message);
			}
		})
		.catch(error => alert('Error loading printers: ' + error));
}

function renderPrinters(printers) {
	const tbody = document.getElementById('printerTableBody');
	tbody.innerHTML = '';
	
	if (printers.length === 0) {
		tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-medium);padding:20px">No printers found</td></tr>';
		return;
	}
	
	printers.forEach(p => {
		const cls = p.status.toLowerCase().replace(/\s+/g,'');
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td><strong style="color:var(--primary-green)">${escapeHtml(p.serial_number)}</strong></td>
			<td><div style="font-weight:600">${escapeHtml(p.brand)}</div><div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> ${escapeHtml(p.model)}</div></td>
			<td>${escapeHtml(p.year_acquired)}</td>
			<td>${p.employee_name ? `<div style="font-weight:600">${escapeHtml(p.employee_name)}</div><div style="font-size:12px;color:var(--text-light)">ID: ${escapeHtml(p.employee_id)}</div>` : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>'}</td>
			<td><span class="status-badge status-${cls}">${escapeHtml(p.status)}</span></td>
			<td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editPrinter(${p.printer_id})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" style="color: black" title="Delete" onclick="deletePrinter(${p.printer_id})"><i class="fas fa-trash"></i></button></div></td>
		`;
		tbody.appendChild(tr);
	});
}

function escapeHtml(text) {
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

let currentEditId = null;

function openAddPrinter() {
	currentEditId = null;
	document.getElementById('printerModalTitle').textContent = 'Add New Printer';
	document.getElementById('printerForm').reset();
	const modal = new bootstrap.Modal(document.getElementById('printerModal'));
	modal.show();
}

function editPrinter(id) {
	fetch(`../../ajax/manage_printer.php?action=get&printer_id=${id}`)
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				currentEditId = id;
				const p = data.data;
				document.getElementById('printerModalTitle').textContent = 'Edit Printer';
				document.getElementById('printerBrand').value = p.brand;
				document.getElementById('printerModel').value = p.model;
				document.getElementById('printerSerial').value = p.serial_number;
				document.getElementById('printerYear').value = p.year_acquired;
				document.getElementById('printerEmployee').value = p.employee_id || '';
				const modal = new bootstrap.Modal(document.getElementById('printerModal'));
				modal.show();
			} else {
				alert('Error: ' + data.message);
			}
		})
		.catch(error => alert('Error loading printer: ' + error));
}

function savePrinter() {
	const formData = new FormData();
	formData.append('action', currentEditId ? 'update' : 'create');
	if (currentEditId) formData.append('printer_id', currentEditId);
	formData.append('brand', document.getElementById('printerBrand').value);
	formData.append('model', document.getElementById('printerModel').value);
	formData.append('serial_number', document.getElementById('printerSerial').value);
	formData.append('year_acquired', document.getElementById('printerYear').value);
	formData.append('employee_id', document.getElementById('printerEmployee').value);
	
	fetch('../../ajax/manage_printer.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			alert(data.message);
			bootstrap.Modal.getInstance(document.getElementById('printerModal')).hide();
			location.reload(); // Reload to show updated data
		} else {
			alert('Error: ' + data.message);
		}
	})
	.catch(error => alert('Error saving printer: ' + error));
}

function deletePrinter(id) {
	if (!confirm('Are you sure you want to delete this printer?')) return;
	
	const formData = new FormData();
	formData.append('action', 'delete');
	formData.append('printer_id', id);
	
	fetch('../../ajax/manage_printer.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			alert(data.message);
			location.reload(); // Reload to show updated data
		} else {
			alert('Error: ' + data.message);
		}
	})
	.catch(error => alert('Error deleting printer: ' + error));
}
</script>

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