var currentPage = 1;
var perPage = 25;
var filteredRows = [];

function filterSoftware() {
    currentPage = 1;
    applyTableState();
}

function changePerPage() {
    perPage = parseInt(document.getElementById('perPageSelect').value);
    currentPage = 1;
    applyTableState();
}

function applyTableState() {
    const searchTerm   = document.getElementById('softwareSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter   = document.getElementById('typeFilter').value;
    const allRows      = Array.from(document.querySelectorAll('#softwareTableBody tr[data-software-id]'));

    // 1. Filter
    filteredRows = allRows.filter(row => {
        const name     = row.dataset.name     || '';
        const details  = row.dataset.details  || '';
        const employee = row.dataset.employee || '';
        const type     = row.dataset.type     || '';
        const status   = row.dataset.status   || '';

        const matchesSearch  = name.includes(searchTerm)
                             || details.includes(searchTerm)
                             || employee.includes(searchTerm);
        const matchesStatus  = !statusFilter || status === statusFilter;
        const matchesType    = !typeFilter   || type   === typeFilter;

        return matchesSearch && matchesStatus && matchesType;
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
        countEl.innerHTML = `Showing <strong>${showing}–${end}</strong> of <strong>${total}</strong> license(s)`;
    }

    renderPaginationControls('paginationControls', currentPage, totalPages, 'goToPage');
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

function openAddSoftware() {
    currentEditId = null;
    document.getElementById('softwareModalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New License';
    document.getElementById('softwareForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('softwareModal'));
    modal.show();
}

function editSoftware(id) {
    fetch(`../ajax/manage_software.php?action=get&software_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentEditId = id;
                const s = data.data;
                document.getElementById('softwareModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit License';
                document.getElementById('softwareName').value     = s.software_name   || s.licenseSoftware || '';
                document.getElementById('softwareDetails').value  = s.license_details || s.licenseDetails  || '';
                document.getElementById('softwareType').value     = s.license_type    || s.licenseType     || '';
                document.getElementById('softwareExpiry').value   = s.expiry_date
                    ? (s.expiry_date.includes(' ') ? s.expiry_date.split(' ')[0] : s.expiry_date)
                    : '';
                document.getElementById('softwareEmail').value    = s.email    || '';
                document.getElementById('softwarePassword').value = s.password || '';
                document.getElementById('softwareEmployee').value = s.employee_id || s.employeeId || '';
                const modal = new bootstrap.Modal(document.getElementById('softwareModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading software license: ' + error));
}

function saveSoftware() {
    const requiredIds = ['softwareName', 'softwareDetails', 'softwareType'];
    for (var id of requiredIds) {
        if (!document.getElementById(id).value.trim()) {
            alert('Please fill in all required fields marked with *');
            return;
        }
    }

    const formData = new FormData();
    formData.append('action', currentEditId ? 'update' : 'create');
    if (currentEditId) formData.append('software_id', currentEditId);
    formData.append('software_name',    document.getElementById('softwareName').value);
    formData.append('license_details',  document.getElementById('softwareDetails').value);
    formData.append('license_type',     document.getElementById('softwareType').value);
    formData.append('expiry_date',      document.getElementById('softwareExpiry').value);
    formData.append('email',            document.getElementById('softwareEmail').value);
    formData.append('password',         document.getElementById('softwarePassword').value);
    formData.append('employee_id',      document.getElementById('softwareEmployee').value);

    fetch('../ajax/manage_software.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('softwareModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error saving software license: ' + error));
}

// ========================================
// DELETE
// ========================================
function deleteSoftware(id) {
    if (!confirm('Are you sure you want to delete this software license? This action cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('software_id', id);

    fetch('../ajax/manage_software.php', {
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
    .catch(error => alert('Error deleting software license: ' + error));
}

document.addEventListener('DOMContentLoaded', function () {
    applyTableState();
});