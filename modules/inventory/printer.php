<?php
// modules/inventory/printer.php
// Printer management fragment using same design as computer.php (sample data only)

$samplePrinters = [
	[
		'printerId' => 1,
		'name' => 'Printer A',
		'brand' => 'HP',
		'model' => 'LaserJet Pro M404dn',
		'serial' => 'HP-PR-2024-001',
		'location' => 'IT Room',
		'yearAcquired' => 2024,
		'employeeId' => 20373,
		'employeeName' => 'Lexter N. Manuel',
		'status' => 'Working'
	],
	[
		'printerId' => 2,
		'name' => 'Printer B',
		'brand' => 'Canon',
		'model' => 'imageCLASS LBP6030',
		'serial' => 'CN-PR-2024-002',
		'location' => 'Office 101',
		'yearAcquired' => 2023,
		'employeeId' => 111,
		'employeeName' => 'Benjamin Abad',
		'status' => 'Needs Toner'
	],
	[
		'printerId' => 3,
		'name' => 'Printer C',
		'brand' => 'Epson',
		'model' => 'WorkForce WF-2860',
		'serial' => 'EP-PR-2024-003',
		'location' => 'Reception',
		'yearAcquired' => 2024,
		'employeeId' => null,
		'employeeName' => null,
		'status' => 'Available'
	],
	[
		'printerId' => 4,
		'name' => 'Printer D',
		'brand' => 'Brother',
		'model' => 'HL-L2395DW',
		'serial' => 'BR-PR-2023-004',
		'location' => 'Warehouse',
		'yearAcquired' => 2022,
		'employeeId' => null,
		'employeeName' => null,
		'status' => 'Offline'
	]
];
?>

<style>
/* Reused styles (page header, filters, table, badges, buttons) */
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
	<div class="filter-group">
		<label><i class="fas fa-circle-check"></i> Status:</label>
		<select id="statusFilter">
			<option value="">All Status</option>
			<option value="Working">Working</option>
			<option value="Available">Available</option>
			<option value="Needs Toner">Needs Toner</option>
			<option value="Offline">Offline</option>
		</select>
	</div>
	<div class="filter-group" style="flex:1">
		<label><i class="fas fa-search"></i> Search:</label>
		<input type="text" id="printerSearch" placeholder="Serial, name, brand, model, location..." />
	</div>
</div>

<!-- Data Table -->
<div class="data-table">
	<table>
		<thead>
			<tr>
				<th>Serial</th>
				<th>Name / Brand</th>
				<th>Model</th>
				<th>Location</th>
				<th>Year</th>
				<th>Assigned To</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody id="printerTableBody">
			<?php foreach ($samplePrinters as $p): ?>
			<tr>
				<td><strong style="color:var(--primary-green)"><?php echo $p['serial']; ?></strong></td>
				<td>
					<div style="font-weight:600"><?php echo $p['name']; ?></div>
					<div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> <?php echo $p['brand']; ?></div>
				</td>
				<td><?php echo $p['model']; ?></td>
				<td><?php echo $p['location']; ?></td>
				<td><?php echo $p['yearAcquired']; ?></td>
				<td>
					<?php if ($p['employeeName']): ?>
						<div style="font-weight:600"><?php echo $p['employeeName']; ?></div>
						<div style="font-size:12px;color:var(--text-light)">ID: <?php echo $p['employeeId']; ?></div>
					<?php else: ?>
						<span style="color:var(--text-light);font-style:italic">Unassigned</span>
					<?php endif; ?>
				</td>
				<td>
					<?php
						$cls = strtolower(str_replace(' ', '', $p['status']));
					?>
					<span class="status-badge status-<?php echo $cls; ?>"><?php echo $p['status']; ?></span>
				</td>
				<td>
					<div class="action-buttons">
						<button class="btn-icon" title="View"><i class="fas fa-eye"></i></button>
						<button class="btn-icon" title="Edit" onclick="editPrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-edit"></i></button>
						<button class="btn-icon" title="Delete" onclick="deletePrinter(<?php echo $p['printerId']; ?>)"><i class="fas fa-trash"></i></button>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
	// Client-side sample manipulation (in-memory)
	let printers = <?php echo json_encode($samplePrinters, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;

	function renderPrinters(){
		const tbody = document.getElementById('printerTableBody');
		const q = document.getElementById('printerSearch').value.trim().toLowerCase();
		const status = document.getElementById('statusFilter').value;
		tbody.innerHTML = '';
		const list = printers.filter(p => {
			if(status && p.status !== status) return false;
			if(!q) return true;
			return [p.serial,p.name,p.brand,p.model,p.location,p.status].join(' ').toLowerCase().includes(q);
		});
		if(list.length === 0){
			tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-medium);padding:20px">No printers found</td></tr>';
			return;
		}
		list.forEach(p => {
			const tr = document.createElement('tr');
			const cls = p.status.toLowerCase().replace(/\s+/g,'');
			tr.innerHTML = `
				<td><strong style="color:var(--primary-green)">${escapeHtml(p.serial)}</strong></td>
				<td><div style="font-weight:600">${escapeHtml(p.name)}</div><div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> ${escapeHtml(p.brand)}</div></td>
				<td>${escapeHtml(p.model)}</td>
				<td>${escapeHtml(p.location)}</td>
				<td>${escapeHtml(p.yearAcquired)}</td>
				<td>${p.employeeName ? `<div style="font-weight:600">${escapeHtml(p.employeeName)}</div><div style="font-size:12px;color:var(--text-light)">ID: ${escapeHtml(p.employeeId)}</div>` : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>'}</td>
				<td><span class="status-badge status-${cls}">${escapeHtml(p.status)}</span></td>
				<td><div class="action-buttons"><button class="btn-icon" title="View"><i class="fas fa-eye"></i></button><button class="btn-icon" title="Edit" onclick="editPrinter(${p.printerId})"><i class="fas fa-edit"></i></button><button class="btn-icon" title="Delete" onclick="deletePrinter(${p.printerId})"><i class="fas fa-trash"></i></button></div></td>
			`;
			tbody.appendChild(tr);
		});
	}

	function escapeHtml(s){
		return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
	}

	let currentEditId = null;

	function openAddPrinter(){
		currentEditId = null;
		document.getElementById('printerModalTitle').textContent = 'Add New Printer';
		document.getElementById('printerForm').reset();
		const modal = new bootstrap.Modal(document.getElementById('printerModal'));
		modal.show();
	}

	function editPrinter(id){
		const p = printers.find(x=>x.printerId===id);
		if(!p) return alert('Printer not found');
		currentEditId = id;
		document.getElementById('printerModalTitle').textContent = 'Edit Printer';
		document.getElementById('printerName').value = p.name;
		document.getElementById('printerBrand').value = p.brand;
		document.getElementById('printerModel').value = p.model;
		document.getElementById('printerSerial').value = p.serial;
		document.getElementById('printerLocation').value = p.location;
		document.getElementById('printerYear').value = p.yearAcquired;
		document.getElementById('printerStatus').value = p.status;
		const modal = new bootstrap.Modal(document.getElementById('printerModal'));
		modal.show();
	}

	function savePrinter(){
		const name = document.getElementById('printerName').value.trim();
		const brand = document.getElementById('printerBrand').value.trim();
		const model = document.getElementById('printerModel').value.trim();
		const serial = document.getElementById('printerSerial').value.trim();
		const location = document.getElementById('printerLocation').value.trim();
		const year = document.getElementById('printerYear').value.trim();
		const status = document.getElementById('printerStatus').value;

		if(!name || !brand || !model || !serial || !location || !year){
			alert('Please fill in all fields');
			return;
		}

		if(currentEditId !== null){
			const p = printers.find(x=>x.printerId===currentEditId);
			p.name = name;
			p.brand = brand;
			p.model = model;
			p.serial = serial;
			p.location = location;
			p.yearAcquired = year;
			p.status = status;
		} else {
			const id = printers.length ? Math.max(...printers.map(p=>p.printerId)) + 1 : 1;
			printers.push({printerId:id,name:name,brand:brand,model:model,serial:serial,location:location,yearAcquired:year,employeeId:null,employeeName:null,status:status});
		}

		renderPrinters();
		bootstrap.Modal.getInstance(document.getElementById('printerModal')).hide();
	}

	function deletePrinter(id){
		if(!confirm('Are you sure you want to delete this printer?')) return;
		printers = printers.filter(x=>x.printerId!==id);
		renderPrinters();
	}

	document.getElementById('printerSearch').addEventListener('input', renderPrinters);
	document.getElementById('statusFilter').addEventListener('change', renderPrinters);
	document.getElementById('savePrinterBtn').addEventListener('click', savePrinter);

	// Initial render
	renderPrinters();
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
							<label for="printerName" class="form-label">Printer Name *</label>
							<input type="text" class="form-control" id="printerName" placeholder="e.g., Printer A" required>
						</div>
						<div class="col-md-6">
							<label for="printerBrand" class="form-label">Brand *</label>
							<input type="text" class="form-control" id="printerBrand" placeholder="e.g., HP, Canon" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="printerModel" class="form-label">Model *</label>
							<input type="text" class="form-control" id="printerModel" placeholder="e.g., LaserJet Pro M404dn" required>
						</div>
						<div class="col-md-6">
							<label for="printerSerial" class="form-label">Serial Number *</label>
							<input type="text" class="form-control" id="printerSerial" placeholder="e.g., HP-PR-2024-001" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label for="printerLocation" class="form-label">Location *</label>
							<input type="text" class="form-control" id="printerLocation" placeholder="e.g., IT Room" required>
						</div>
						<div class="col-md-6">
							<label for="printerYear" class="form-label">Year Acquired *</label>
							<input type="number" class="form-control" id="printerYear" placeholder="e.g., 2024" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-12">
							<label for="printerStatus" class="form-label">Status *</label>
							<select class="form-select" id="printerStatus" required>
								<option value="">Select Status</option>
								<option value="Working">Working</option>
								<option value="Available">Available</option>
								<option value="Needs Toner">Needs Toner</option>
								<option value="Offline">Offline</option>
							</select>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer border-top">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="savePrinterBtn" style="background-color: var(--primary-green); border-color: var(--primary-green);">Save Printer</button>
			</div>
		</div>
	</div>
</div>
