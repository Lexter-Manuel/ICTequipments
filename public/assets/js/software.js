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

// ============================================================
// VIEW SOFTWARE LICENSE DETAILS
// ============================================================
function viewSoftware(id) {
    var modalBody = document.getElementById('softwareDetailContent');
    modalBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

    var modal = new bootstrap.Modal(document.getElementById('softwareDetailModal'));
    modal.show();

    fetch('../ajax/manage_software.php?action=get&software_id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var item = data.data;

                // Status badge
                var statusCls = 'success';
                if (item.status === 'Expired') statusCls = 'danger';
                else if (item.status === 'Expiring Soon') statusCls = 'warning';

                // Type badge
                var typeCls = item.license_type === 'Subscription' ? 'info' : 'primary';

                // Expiry info
                var expiryHtml = '<span class="text-muted"><i class="fas fa-infinity"></i> No Expiry (Perpetual)</span>';
                if (item.expiry_date) {
                    var expDate = new Date(item.expiry_date);
                    var formattedDate = expDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    expiryHtml = '<div>' + formattedDate + '</div>';
                    if (item.days_until_expiry !== null) {
                        if (item.days_until_expiry > 0) {
                            var daysColor = item.days_until_expiry <= 30 ? '#b45309' : 'var(--text-light)';
                            expiryHtml += '<div style="font-size:12px;color:' + daysColor + ';margin-top:2px"><i class="fas fa-clock"></i> ' + item.days_until_expiry + ' days remaining</div>';
                        } else if (item.days_until_expiry < 0) {
                            expiryHtml += '<div style="font-size:12px;color:#dc2626;margin-top:2px"><i class="fas fa-exclamation-circle"></i> Expired ' + Math.abs(item.days_until_expiry) + ' days ago</div>';
                        } else {
                            expiryHtml += '<div style="font-size:12px;color:#dc2626;margin-top:2px"><i class="fas fa-exclamation-circle"></i> Expires today</div>';
                        }
                    }
                }

                var html =
                    '<div class="row mb-3">' +
                        '<div class="col-md-8">' +
                            '<h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Software Name</h6>' +
                            '<p class="fw-bold mb-0" style="font-size:16px"><i class="fas fa-compact-disc text-primary me-2"></i>' + escapeHtml(item.software_name) + '</p>' +
                        '</div>' +
                        '<div class="col-md-4 text-end">' +
                            '<span class="badge bg-' + statusCls + '" style="font-size:13px">' + escapeHtml(item.status) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<hr class="my-2">' +
                    '<div class="row g-3">' +
                        '<div class="col-md-12"><label class="small text-muted">License Details</label><div class="p-2 border rounded bg-white" style="font-family:monospace;font-size:14px">' + escapeHtml(item.license_details) + '</div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">License Type</label><div><span class="badge bg-' + typeCls + '">' + escapeHtml(item.license_type || 'N/A') + '</span></div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">Expiry Date</label><div>' + expiryHtml + '</div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">Email</label><div>' + (item.email ? escapeHtml(item.email) : '<span class="text-muted fst-italic">Not provided</span>') + '</div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">Password</label><div>' + (item.password ? '<span class="password-masked" onclick="this.textContent=\'' + escapeHtml(item.password).replace(/'/g, "\\'") + '\'" style="cursor:pointer" title="Click to reveal">••••••••</span>' : '<span class="text-muted fst-italic">Not provided</span>') + '</div></div>' +
                        '<div class="col-md-12"><label class="small text-muted">Assigned To</label><div class="p-2 bg-light border rounded">' +
                            (item.employee_name ? '<i class="fas fa-user text-primary me-1"></i><strong>' + escapeHtml(item.employee_name) + '</strong>' : '<span class="text-muted fst-italic">Unassigned</span>') +
                        '</div></div>' +
                    '</div>';
                modalBody.innerHTML = html;

                var editBtn = document.getElementById('softwareDetailEditBtn');
                editBtn.style.display = '';
                editBtn.setAttribute('onclick', 'editSoftware(' + item.software_id + '); bootstrap.Modal.getInstance(document.getElementById("softwareDetailModal")).hide();');
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(function(err) { modalBody.innerHTML = '<div class="alert alert-danger">Error: ' + err + '</div>'; });
}

document.addEventListener('DOMContentLoaded', function () {
    applyTableState();
});