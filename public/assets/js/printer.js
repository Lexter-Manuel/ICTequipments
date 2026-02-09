

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