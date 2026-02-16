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

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const container = document.getElementById('paginationControls');
    if (!container) return;

    var html = `<button class="page-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="fas fa-chevron-left"></i></button>`;

    getPaginationRange(currentPage, totalPages).forEach(p => {
        if (p === '...') {
            html += `<span class="page-ellipsis">…</span>`;
        } else {
            html += `<button class="page-btn ${p === currentPage ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
        }
    });

    html += `<button class="page-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
        <i class="fas fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

function getPaginationRange(current, total) {
    if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
    if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
    if (current >= total - 3) return [1, '...', total-4, total-3, total-2, total-1, total];
    return [1, '...', current-1, current, current+1, '...', total];
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
            location.reload();
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
            location.reload();
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