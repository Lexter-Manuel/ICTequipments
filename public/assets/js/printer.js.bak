/**
 * Printer Management JavaScript
 * Client-side filtering, sorting, and pagination — same pattern as other_equipment.js
 */

// ========================================
// TABLE STATE
// ========================================
var currentPage = 1;
var perPage = 25;
var filteredRows = [];
// ========================================
// FILTER & PAGINATE
// ========================================
function filterPrinters() {
    currentPage = 1;
    applyTableState();
}

function changePerPage() {
    perPage = parseInt(document.getElementById('perPageSelect').value);
    currentPage = 1;
    applyTableState();
}

function applyTableState() {
    const searchTerm   = document.getElementById('printerSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const allRows      = Array.from(document.querySelectorAll('#printerTableBody tr[data-printer-id]'));

    // 1. Filter
    filteredRows = allRows.filter(row => {
        const serial   = row.dataset.serial   || '';
        const brand    = row.dataset.brand    || '';
        const employee = row.dataset.employee || '';
        const year     = row.dataset.year     || '';
        const status   = row.dataset.status   || '';

        const matchesSearch  = serial.includes(searchTerm)
                             || brand.includes(searchTerm)
                             || employee.includes(searchTerm)
                             || year.includes(searchTerm);
        const matchesStatus  = !statusFilter || status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    // 2. Pagination
    const total      = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const end   = Math.min(start + perPage, total);

    allRows.forEach(r => r.style.display = 'none');
    filteredRows.forEach((row, idx) => {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    // 3. Footer info
    const showing = total === 0 ? 0 : start + 1;
    const countEl = document.getElementById('recordCount');
    if (countEl) {
        countEl.innerHTML = `Showing <strong>${showing}–${end}</strong> of <strong>${total}</strong> printer(s)`;
    }

    renderPaginationControls('paginationControls', currentPage, totalPages, 'goToPage');
    updateRowCounters('printerTableBody', total === 0 ? 0 : start + 1);
}

function goToPage(page) {
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / perPage));
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    applyTableState();
}

// ========================================
// ADD / EDIT
// ========================================
var currentEditId = null;

function openAddPrinter() {
    currentEditId = null;
    document.getElementById('printerModalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New Printer';
    document.getElementById('printerForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('printerModal'));
    modal.show();
}

function editPrinter(id) {
    fetch(`../ajax/manage_printer.php?action=get&printer_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentEditId = id;
                const p = data.data;
                document.getElementById('printerModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Printer';
                document.getElementById('printerBrand').value  = p.brand        || p.printerBrand  || '';
                document.getElementById('printerModel').value  = p.model        || p.printerModel  || '';
                document.getElementById('printerSerial').value = p.serial_number|| p.printerSerial || '';
                document.getElementById('printerYear').value   = p.year_acquired|| p.yearAcquired  || '';
                document.getElementById('printerEmployee').value = p.employee_id|| p.employeeId    || '';
                const modal = new bootstrap.Modal(document.getElementById('printerModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading printer: ' + error));
}

function savePrinter() {
    const requiredIds = ['printerBrand', 'printerModel', 'printerSerial', 'printerYear'];
    for (var id of requiredIds) {
        if (!document.getElementById(id).value.trim()) {
            alert('Please fill in all required fields marked with *');
            return;
        }
    }

    const formData = new FormData();
    formData.append('action', currentEditId ? 'update' : 'create');
    if (currentEditId) formData.append('printer_id', currentEditId);
    formData.append('brand',        document.getElementById('printerBrand').value);
    formData.append('model',        document.getElementById('printerModel').value);
    formData.append('serial_number',document.getElementById('printerSerial').value);
    formData.append('year_acquired',document.getElementById('printerYear').value);
    formData.append('employee_id',  document.getElementById('printerEmployee').value);

    fetch('../ajax/manage_printer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('printerModal')).hide();
            reloadCurrentPage();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error saving printer: ' + error));
}

// ========================================
// DELETE
// ========================================
function deletePrinter(id) {
    if (!confirm('Are you sure you want to delete this printer? This action cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('printer_id', id);

    fetch('../ajax/manage_printer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            reloadCurrentPage();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error deleting printer: ' + error));
}

// ========================================
// INIT
// ========================================
document.addEventListener('DOMContentLoaded', function () {
    applyTableState();
});