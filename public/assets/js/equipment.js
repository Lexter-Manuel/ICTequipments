/**
 * equipment.js — Unified Equipment Inventory JavaScript
 * Combines: computer_management.js, printer.js, other_equipment.js
 * Shared utils (escapeHtml, getPaginationRange, renderPaginationControls) loaded from utils.js
 */

var equipmentTypesCache = [];       // Cached list from registry
var typeDropdownActiveIdx = -1;     // Keyboard nav index

// ============================================================
// CATEGORY & SUB-TAB SWITCHING
// ============================================================
function switchCategory(name, btn) {
    document.querySelectorAll('.category-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('#categoryTabs .toggle-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('category-' + name).classList.add('active');
    btn.classList.add('active');
}

function switchSubTab(name, btn) {
    document.querySelectorAll('.sub-tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.subtoggle-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('subtab-' + name).classList.add('active');
    btn.classList.add('active');
}

function reloadEquipmentPage() {
    reloadCurrentPage('equipment');
}

// Backward-compatible aliases — delegate to shared utils.js
function renderPaginationGeneric(containerId, currentPage, totalPages, goToFn) {
    renderPaginationControls(containerId, currentPage, totalPages, goToFn);
}

// ============================================================
// SYSTEM UNITS MANAGEMENT
// ============================================================
var currentSystemUnitId = null;

function filterSystemUnits() {
    // Use client-side filtering if DOM rows are present (initial server render)
    var domRows = document.querySelectorAll('#systemunitTableBody tr[data-su-id]');
    if (domRows.length > 0) {
        suCurrentPage = 1;
        applySystemUnitTableState();
        return;
    }
    // Fallback: AJAX search (used after a dynamic re-render)
    var search = document.getElementById('systemunitSearch').value;
    fetch('../ajax/manage_systemunit.php?action=list&search=' + encodeURIComponent(search))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                renderSystemUnits(data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) { alert('Error loading system units: ' + error); });
}

function renderSystemUnits(units) {
    var tbody = document.getElementById('systemunitTableBody');
    tbody.innerHTML = '';
    if (units.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-medium);padding:20px">No system units found</td></tr>';
        return;
    }
    units.forEach(function(s) {
        var cls = s.status.toLowerCase();
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><strong style="color:var(--primary-green)">' + escapeHtml(s.systemUnitSerial) + '</strong></td>' +
            '<td><div style="font-weight:600">' + escapeHtml(s.systemUnitBrand) + '</div><div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> ' + escapeHtml(s.systemUnitCategory) + '</div></td>' +
            '<td><div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value">' + escapeHtml(s.specificationProcessor) + '</span></div><div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value">' + escapeHtml(s.specificationMemory) + '</span></div><div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value">' + escapeHtml(s.specificationStorage) + '</span></div></td>' +
            '<td>' + escapeHtml(s.yearAcquired) + '</td>' +
            '<td>' + (s.employeeName ? '<div style="font-weight:600">' + escapeHtml(s.employeeName) + '</div><div style="font-size:12px;color:var(--text-light)">ID: ' + escapeHtml(s.employeeId) + '</div>' : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>') + '</td>' +
            '<td>' + (s.lastMaintenanceDate ? '<div class="maintenance-info"><i class="fas fa-tools"></i>' + escapeHtml(s.lastMaintenanceDate) + '</div>' : '<span class="text-muted"><i class="fas fa-clock"></i> No record</span>') + '</td>' +
            '<td><span class="status-badge status-' + cls + '">' + escapeHtml(s.status) + '</span></td>' +
            '<td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editSystemUnit(' + s.systemunitId + ')"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteSystemUnit(' + s.systemunitId + ')"><i class="fas fa-trash"></i></button></div></td>';
        tbody.appendChild(tr);
    });
}

function openAddSystemUnit() {
    currentSystemUnitId = null;
    document.getElementById('systemunitModalTitle').textContent = 'Add New System Unit';
    document.getElementById('systemunitForm').reset();
    new bootstrap.Modal(document.getElementById('systemunitModal')).show();
}

function editSystemUnit(id) {
    fetch('../ajax/manage_systemunit.php?action=get&systemunit_id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                currentSystemUnitId = id;
                var s = data.data;
                document.getElementById('systemunitModalTitle').textContent = 'Edit System Unit';
                document.getElementById('suCategory').value = s.systemUnitCategory;
                document.getElementById('suBrand').value = s.systemUnitBrand;
                document.getElementById('suProcessor').value = s.specificationProcessor;
                document.getElementById('suMemory').value = s.specificationMemory;
                document.getElementById('suGPU').value = s.specificationGPU;
                document.getElementById('suStorage').value = s.specificationStorage;
                document.getElementById('suSerial').value = s.systemUnitSerial;
                document.getElementById('suYear').value = s.yearAcquired;
                empSearch.set('suEmployeeSearch', 'suEmployee', s.employeeId || '');
                new bootstrap.Modal(document.getElementById('systemunitModal')).show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) { alert('Error loading system unit: ' + error); });
}

function saveSystemUnit() {
    var formData = new FormData();
    formData.append('action', currentSystemUnitId ? 'update' : 'create');
    if (currentSystemUnitId) formData.append('systemunit_id', currentSystemUnitId);
    formData.append('category', document.getElementById('suCategory').value);
    formData.append('brand', document.getElementById('suBrand').value);
    formData.append('processor', document.getElementById('suProcessor').value);
    formData.append('memory', document.getElementById('suMemory').value);
    formData.append('gpu', document.getElementById('suGPU').value);
    formData.append('storage', document.getElementById('suStorage').value);
    formData.append('serial', document.getElementById('suSerial').value);
    formData.append('year', document.getElementById('suYear').value);
    formData.append('employee_id', document.getElementById('suEmployee').value);

    fetch('../ajax/manage_systemunit.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('systemunitModal')).hide();
                reloadEquipmentPage();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(function(error) { alert('Error saving system unit: ' + error); });
}

function deleteSystemUnit(id) {
    if (!confirm('Are you sure you want to delete this system unit?')) return;
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('systemunit_id', id);

    fetch('../ajax/manage_systemunit.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { alert(data.message); reloadEquipmentPage(); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error deleting system unit: ' + error); });
}

// ============================================================
// MONITORS MANAGEMENT
// ============================================================
var currentMonitorId = null;

function filterMonitors() {
    var domRows = document.querySelectorAll('#monitorTableBody tr[data-mon-id]');
    if (domRows.length > 0) {
        monCurrentPage = 1;
        applyMonitorTableState();
        return;
    }
    var search = document.getElementById('monitorSearch').value;
    fetch('../ajax/manage_monitor.php?action=list&search=' + encodeURIComponent(search))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { renderMonitors(data.data); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading monitors: ' + error); });
}

function renderMonitors(monitors) {
    var tbody = document.getElementById('monitorTableBody');
    tbody.innerHTML = '';
    if (monitors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-medium);padding:20px">No monitors found</td></tr>';
        return;
    }
    monitors.forEach(function(m) {
        var cls = m.status.toLowerCase();
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><strong style="color:var(--primary-green)">' + escapeHtml(m.monitorSerial) + '</strong></td>' +
            '<td><div style="font-weight:600">' + escapeHtml(m.monitorBrand) + '</div></td>' +
            '<td>' + escapeHtml(m.monitorSize) + '</td>' +
            '<td>' + escapeHtml(m.yearAcquired) + '</td>' +
            '<td>' + (m.employeeName ? '<div style="font-weight:600">' + escapeHtml(m.employeeName) + '</div><div style="font-size:12px;color:var(--text-light)">ID: ' + escapeHtml(m.employeeId) + '</div>' : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>') + '</td>' +
            '<td>' + (m.lastMaintenanceDate ? '<div class="maintenance-info"><i class="fas fa-tools"></i>' + escapeHtml(m.lastMaintenanceDate) + '</div>' : '<span class="text-muted"><i class="fas fa-clock"></i> No record</span>') + '</td>' +
            '<td><span class="status-badge status-' + cls + '">' + escapeHtml(m.status) + '</span></td>' +
            '<td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editMonitor(' + m.monitorId + ')"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteMonitor(' + m.monitorId + ')"><i class="fas fa-trash"></i></button></div></td>';
        tbody.appendChild(tr);
    });
}

function openAddMonitor() {
    currentMonitorId = null;
    document.getElementById('monitorModalTitle').textContent = 'Add New Monitor';
    document.getElementById('monitorForm').reset();
    new bootstrap.Modal(document.getElementById('monitorModal')).show();
}

function editMonitor(id) {
    fetch('../ajax/manage_monitor.php?action=get&monitor_id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                currentMonitorId = id;
                var m = data.data;
                document.getElementById('monitorModalTitle').textContent = 'Edit Monitor';
                document.getElementById('monBrand').value = m.monitorBrand;
                document.getElementById('monSize').value = m.monitorSize;
                document.getElementById('monSerial').value = m.monitorSerial;
                document.getElementById('monYear').value = m.yearAcquired;
                empSearch.set('monEmployeeSearch', 'monEmployee', m.employeeId || '');
                new bootstrap.Modal(document.getElementById('monitorModal')).show();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading monitor: ' + error); });
}

function saveMonitor() {
    var formData = new FormData();
    formData.append('action', currentMonitorId ? 'update' : 'create');
    if (currentMonitorId) formData.append('monitor_id', currentMonitorId);
    formData.append('brand', document.getElementById('monBrand').value);
    formData.append('size', document.getElementById('monSize').value);
    formData.append('monitorSerial', document.getElementById('monSerial').value);
    formData.append('year', document.getElementById('monYear').value);
    formData.append('employee_id', document.getElementById('monEmployee').value);

    fetch('../ajax/manage_monitor.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('monitorModal')).hide();
                reloadEquipmentPage();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error saving monitor: ' + error); });
}

function deleteMonitor(id) {
    if (!confirm('Are you sure you want to delete this monitor?')) return;
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('monitor_id', id);

    fetch('../ajax/manage_monitor.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { alert(data.message); reloadEquipmentPage(); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error deleting monitor: ' + error); });
}

// ============================================================
// ALL-IN-ONE MANAGEMENT
// ============================================================
var currentAllInOneId = null;

function filterAllInOnes() {
    var domRows = document.querySelectorAll('#allinoneTableBody tr[data-aio-id]');
    if (domRows.length > 0) {
        aioCurrentPage = 1;
        applyAIOTableState();
        return;
    }
    var search = document.getElementById('allinoneSearch').value;
    fetch('../ajax/manage_allinone.php?action=list&search=' + encodeURIComponent(search))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { renderAllInOnes(data.data); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading all-in-ones: ' + error); });
}

function renderAllInOnes(units) {
    var tbody = document.getElementById('allinoneTableBody');
    tbody.innerHTML = '';
    if (units.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-medium);padding:20px">No all-in-one PCs found</td></tr>';
        return;
    }
    units.forEach(function(a) {
        var cls = a.status.toLowerCase();
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><div style="font-weight:600">' + escapeHtml(a.allinoneBrand) + '</div></td>' +
            '<td><div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value">' + escapeHtml(a.specificationProcessor) + '</span></div><div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value">' + escapeHtml(a.specificationMemory) + '</span></div><div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value">' + escapeHtml(a.specificationStorage) + '</span></div></td>' +
            '<td>' + escapeHtml(a.yearAcquired || 'N/A') + '</td>' +
            '<td>' + (a.employeeName ? '<div style="font-weight:600">' + escapeHtml(a.employeeName) + '</div><div style="font-size:12px;color:var(--text-light)">ID: ' + escapeHtml(a.employeeId) + '</div>' : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>') + '</td>' +
            '<td>' + (a.lastMaintenanceDate ? '<div class="maintenance-info"><i class="fas fa-tools"></i>' + escapeHtml(a.lastMaintenanceDate) + '</div>' : '<span class="text-muted"><i class="fas fa-clock"></i> No record</span>') + '</td>' +
            '<td><span class="status-badge status-' + cls + '">' + escapeHtml(a.status) + '</span></td>' +
            '<td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editAllInOne(' + a.allinoneId + ')"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteAllInOne(' + a.allinoneId + ')"><i class="fas fa-trash"></i></button></div></td>';
        tbody.appendChild(tr);
    });
}

function openAddAllInOne() {
    currentAllInOneId = null;
    document.getElementById('allinoneModalTitle').textContent = 'Add New All-in-One';
    document.getElementById('allinoneForm').reset();
    new bootstrap.Modal(document.getElementById('allinoneModal')).show();
}

function editAllInOne(id) {
    fetch('../ajax/manage_allinone.php?action=get&allinone_id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                currentAllInOneId = id;
                var a = data.data;
                document.getElementById('allinoneModalTitle').textContent = 'Edit All-in-One';
                document.getElementById('aioBrand').value = a.allinoneBrand;
                document.getElementById('aioProcessor').value = a.specificationProcessor;
                document.getElementById('aioMemory').value = a.specificationMemory;
                document.getElementById('aioGPU').value = a.specificationGPU;
                document.getElementById('aioStorage').value = a.specificationStorage;
                document.getElementById('aioYear').value = a.yearAcquired || '';
                empSearch.set('aioEmployeeSearch', 'aioEmployee', a.employeeId || '');
                new bootstrap.Modal(document.getElementById('allinoneModal')).show();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading all-in-one: ' + error); });
}

function saveAllInOne() {
    var formData = new FormData();
    formData.append('action', currentAllInOneId ? 'update' : 'create');
    if (currentAllInOneId) formData.append('allinone_id', currentAllInOneId);
    formData.append('brand', document.getElementById('aioBrand').value);
    formData.append('processor', document.getElementById('aioProcessor').value);
    formData.append('memory', document.getElementById('aioMemory').value);
    formData.append('gpu', document.getElementById('aioGPU').value);
    formData.append('storage', document.getElementById('aioStorage').value);
    formData.append('year_acquired', document.getElementById('aioYear').value);
    formData.append('employee_id', document.getElementById('aioEmployee').value);

    fetch('../ajax/manage_allinone.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('allinoneModal')).hide();
                reloadEquipmentPage();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error saving all-in-one: ' + error); });
}

function deleteAllInOne(id) {
    if (!confirm('Are you sure you want to delete this all-in-one PC?')) return;
    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('allinone_id', id);

    fetch('../ajax/manage_allinone.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { alert(data.message); reloadEquipmentPage(); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error deleting all-in-one: ' + error); });
}

// ============================================================
// SYSTEM UNITS PAGINATION
// ============================================================
var suCurrentPage = 1;
var suPerPage = (typeof defaultPerPage !== 'undefined') ? defaultPerPage : 25;
var suFilteredRows = [];

function filterSystemUnitsTable() {
    suCurrentPage = 1;
    applySystemUnitTableState();
}

function changePerPageSU() {
    suPerPage = parseInt(document.getElementById('suPerPageSelect').value);
    suCurrentPage = 1;
    applySystemUnitTableState();
}

function applySystemUnitTableState() {
    var searchTerm = (document.getElementById('systemunitSearch') ? document.getElementById('systemunitSearch').value.toLowerCase() : '');
    var statusFilter = (document.getElementById('suStatusFilter') ? document.getElementById('suStatusFilter').value : '');
    var allRows = Array.from(document.querySelectorAll('#systemunitTableBody tr[data-su-id]'));

    suFilteredRows = allRows.filter(function(row) {
        var serial   = row.dataset.serial   || '';
        var brand    = row.dataset.brand    || '';
        var employee = row.dataset.employee || '';
        var status   = row.dataset.status   || '';
        var matchesSearch = serial.includes(searchTerm) || brand.includes(searchTerm) || employee.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    var total      = suFilteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / suPerPage));
    if (suCurrentPage > totalPages) suCurrentPage = totalPages;

    var start = (suCurrentPage - 1) * suPerPage;
    var end   = Math.min(start + suPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    suFilteredRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    var countEl = document.getElementById('suRecordCount');
    if (countEl) {
        countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> unit(s)';
    }

    renderPaginationGeneric('suPaginationControls', suCurrentPage, totalPages, 'goToSUPage');
}

function goToSUPage(page) {
    var totalPages = Math.max(1, Math.ceil(suFilteredRows.length / suPerPage));
    if (page < 1 || page > totalPages) return;
    suCurrentPage = page;
    applySystemUnitTableState();
}

// ============================================================
// MONITORS PAGINATION
// ============================================================
var monCurrentPage = 1;
var monPerPage = (typeof defaultPerPage !== 'undefined') ? defaultPerPage : 25;
var monFilteredRows = [];

function filterMonitorsTable() {
    monCurrentPage = 1;
    applyMonitorTableState();
}

function changePerPageMon() {
    monPerPage = parseInt(document.getElementById('monPerPageSelect').value);
    monCurrentPage = 1;
    applyMonitorTableState();
}

function applyMonitorTableState() {
    var searchTerm = (document.getElementById('monitorSearch') ? document.getElementById('monitorSearch').value.toLowerCase() : '');
    var statusFilter = (document.getElementById('monStatusFilter') ? document.getElementById('monStatusFilter').value : '');
    var allRows = Array.from(document.querySelectorAll('#monitorTableBody tr[data-mon-id]'));

    monFilteredRows = allRows.filter(function(row) {
        var serial   = row.dataset.serial   || '';
        var brand    = row.dataset.brand    || '';
        var employee = row.dataset.employee || '';
        var status   = row.dataset.status   || '';
        var matchesSearch = serial.includes(searchTerm) || brand.includes(searchTerm) || employee.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    var total      = monFilteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / monPerPage));
    if (monCurrentPage > totalPages) monCurrentPage = totalPages;

    var start = (monCurrentPage - 1) * monPerPage;
    var end   = Math.min(start + monPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    monFilteredRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    var countEl = document.getElementById('monRecordCount');
    if (countEl) {
        countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> monitor(s)';
    }

    renderPaginationGeneric('monPaginationControls', monCurrentPage, totalPages, 'goToMonPage');
}

function goToMonPage(page) {
    var totalPages = Math.max(1, Math.ceil(monFilteredRows.length / monPerPage));
    if (page < 1 || page > totalPages) return;
    monCurrentPage = page;
    applyMonitorTableState();
}

// ============================================================
// ALL-IN-ONE PAGINATION
// ============================================================
var aioCurrentPage = 1;
var aioPerPage = (typeof defaultPerPage !== 'undefined') ? defaultPerPage : 25;
var aioFilteredRows = [];

function filterAllInOnesTable() {
    aioCurrentPage = 1;
    applyAIOTableState();
}

function changePerPageAIO() {
    aioPerPage = parseInt(document.getElementById('aioPerPageSelect').value);
    aioCurrentPage = 1;
    applyAIOTableState();
}

function applyAIOTableState() {
    var searchTerm = (document.getElementById('allinoneSearch') ? document.getElementById('allinoneSearch').value.toLowerCase() : '');
    var statusFilter = (document.getElementById('aioStatusFilter') ? document.getElementById('aioStatusFilter').value : '');
    var allRows = Array.from(document.querySelectorAll('#allinoneTableBody tr[data-aio-id]'));

    aioFilteredRows = allRows.filter(function(row) {
        var brand    = row.dataset.brand    || '';
        var employee = row.dataset.employee || '';
        var status   = row.dataset.status   || '';
        var matchesSearch = brand.includes(searchTerm) || employee.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    var total      = aioFilteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / aioPerPage));
    if (aioCurrentPage > totalPages) aioCurrentPage = totalPages;

    var start = (aioCurrentPage - 1) * aioPerPage;
    var end   = Math.min(start + aioPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    aioFilteredRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    var countEl = document.getElementById('aioRecordCount');
    if (countEl) {
        countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> all-in-one(s)';
    }

    renderPaginationGeneric('aioPaginationControls', aioCurrentPage, totalPages, 'goToAIOPage');
}

function goToAIOPage(page) {
    var totalPages = Math.max(1, Math.ceil(aioFilteredRows.length / aioPerPage));
    if (page < 1 || page > totalPages) return;
    aioCurrentPage = page;
    applyAIOTableState();
}

// ============================================================
// PRINTERS MANAGEMENT
// ============================================================
var printerCurrentPage = 1;
var printerPerPage = (typeof defaultPerPage !== "undefined") ? defaultPerPage : 25;
var printerFilteredRows = [];
var currentPrinterId = null;

function filterPrinters() {
    printerCurrentPage = 1;
    applyPrinterTableState();
}

function changePerPagePR() {
    printerPerPage = parseInt(document.getElementById('prPerPageSelect').value);
    printerCurrentPage = 1;
    applyPrinterTableState();
}

function applyPrinterTableState() {
    var searchTerm   = document.getElementById('printerSearch').value.toLowerCase();
    var statusFilter = document.getElementById('printerStatusFilter').value;
    var allRows      = Array.from(document.querySelectorAll('#printerTableBody tr[data-printer-id]'));

    printerFilteredRows = allRows.filter(function(row) {
        var serial   = row.dataset.serial   || '';
        var brand    = row.dataset.brand    || '';
        var employee = row.dataset.employee || '';
        var year     = row.dataset.year     || '';
        var status   = row.dataset.status   || '';

        var matchesSearch = serial.includes(searchTerm) || brand.includes(searchTerm) || employee.includes(searchTerm) || year.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    var total      = printerFilteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / printerPerPage));
    if (printerCurrentPage > totalPages) printerCurrentPage = totalPages;

    var start = (printerCurrentPage - 1) * printerPerPage;
    var end   = Math.min(start + printerPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    printerFilteredRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    var countEl = document.getElementById('prRecordCount');
    if (countEl) {
        countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> printer(s)';
    }

    renderPaginationGeneric('prPaginationControls', printerCurrentPage, totalPages, 'goToPrinterPage');
}

function goToPrinterPage(page) {
    var totalPages = Math.max(1, Math.ceil(printerFilteredRows.length / printerPerPage));
    if (page < 1 || page > totalPages) return;
    printerCurrentPage = page;
    applyPrinterTableState();
}

function openAddPrinter() {
    currentPrinterId = null;
    document.getElementById('printerModalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New Printer';
    document.getElementById('printerForm').reset();
    new bootstrap.Modal(document.getElementById('printerModal')).show();
}

function editPrinter(id) {
    fetch('../ajax/manage_printer.php?action=get&printer_id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                currentPrinterId = id;
                var p = data.data;
                document.getElementById('printerModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Printer';
                document.getElementById('printerBrand').value  = p.brand || p.printerBrand || '';
                document.getElementById('printerModel').value  = p.model || p.printerModel || '';
                document.getElementById('printerSerial').value = p.serial_number || p.printerSerial || '';
                document.getElementById('printerYear').value   = p.year_acquired || p.yearAcquired || '';
                empSearch.set('printerEmployeeSearch', 'printerEmployee', p.employee_id || p.employeeId || '');
                new bootstrap.Modal(document.getElementById('printerModal')).show();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading printer: ' + error); });
}

function savePrinter() {
    var requiredIds = ['printerBrand', 'printerModel', 'printerSerial', 'printerYear'];
    for (var i = 0; i < requiredIds.length; i++) {
        if (!document.getElementById(requiredIds[i]).value.trim()) {
            alert('Please fill in all required fields marked with *');
            return;
        }
    }

    var formData = new FormData();
    formData.append('action', currentPrinterId ? 'update' : 'create');
    if (currentPrinterId) formData.append('printer_id', currentPrinterId);
    formData.append('brand',         document.getElementById('printerBrand').value);
    formData.append('model',         document.getElementById('printerModel').value);
    formData.append('serial_number', document.getElementById('printerSerial').value);
    formData.append('year_acquired', document.getElementById('printerYear').value);
    formData.append('employee_id',   document.getElementById('printerEmployee').value);

    fetch('../ajax/manage_printer.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('printerModal')).hide();
                reloadEquipmentPage();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error saving printer: ' + error); });
}

function deletePrinter(id) {
    if (!confirm('Are you sure you want to delete this printer? This action cannot be undone.')) return;

    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('printer_id', id);

    fetch('../ajax/manage_printer.php', { method: 'POST', body: formData })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) { alert(data.message); reloadEquipmentPage(); }
            else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error deleting printer: ' + error); });
}

// ============================================================
// OTHER EQUIPMENT MANAGEMENT
// ============================================================
var otherCurrentPage = 1;
var otherPerPage = (typeof defaultPerPage !== "undefined") ? defaultPerPage : 25;
var otherFilteredRows = [];
var currentOtherId = null;
var locationManager;

function filterOtherEquipment() {
    otherCurrentPage = 1;
    applyOtherTableState();
}

function changePerPageOther() {
    otherPerPage = parseInt(document.getElementById('otherPerPageSelect').value);
    otherCurrentPage = 1;
    applyOtherTableState();
}

function applyOtherTableState() {
    var searchTerm   = document.getElementById('otherSearch').value.toLowerCase();
    var statusFilter = document.getElementById('otherStatusFilter').value;
    var allRows      = Array.from(document.querySelectorAll('#otherTableBody tr[data-equipment-id]'));

    otherFilteredRows = allRows.filter(function(row) {
        var serial = row.dataset.serial || '';
        var type   = row.dataset.type   || '';
        var brand  = row.dataset.brand  || '';
        var status = row.dataset.status || '';

        var matchesSearch = serial.includes(searchTerm) || type.includes(searchTerm) || brand.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    var total      = otherFilteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / otherPerPage));
    if (otherCurrentPage > totalPages) otherCurrentPage = totalPages;

    var start = (otherCurrentPage - 1) * otherPerPage;
    var end   = Math.min(start + otherPerPage, total);

    allRows.forEach(function(r) { r.style.display = 'none'; });
    otherFilteredRows.forEach(function(row, idx) {
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    var countEl = document.getElementById('otherRecordCount');
    if (countEl) {
        countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> records';
    }

    renderPaginationGeneric('otherPaginationControls', otherCurrentPage, totalPages, 'goToOtherPage');
}

function goToOtherPage(page) {
    var totalPages = Math.max(1, Math.ceil(otherFilteredRows.length / otherPerPage));
    if (page < 1 || page > totalPages) return;
    otherCurrentPage = page;
    applyOtherTableState();
}

function openAddOtherEquipment() {
    currentOtherId = null;
    document.getElementById('otherForm').reset();
    document.getElementById('otherModalTitle').textContent = 'Add Equipment';

    if (locationManager) locationManager.reset();

    // Reset toggle to default
    if (document.getElementById('typeLocation')) {
        document.getElementById('typeLocation').checked = true;
        toggleAssignmentType();
    }

    // Reset type autocomplete state
    var dd = document.getElementById('typeDropdown');
    var sb = document.getElementById('typeSuggestionBanner');
    if (dd) dd.style.display = 'none';
    if (sb) sb.style.display = 'none';
    typeDropdownActiveIdx = -1;
    loadEquipmentTypesCache();

    new bootstrap.Modal(document.getElementById('otherModal')).show();
}

function editOtherEquipment(id) {
    fetch('../ajax/manage_otherequipment.php?action=get&id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                currentOtherId = id;
                var o = data.data;

                document.getElementById('otherModalTitle').textContent = 'Edit Equipment';
                document.getElementById('otherId').value = o.otherEquipmentId;
                document.getElementById('otherType').value = o.equipmentType;
                document.getElementById('otherBrand').value = o.brand;
                document.getElementById('otherModel').value = o.model;
                document.getElementById('otherSerial').value = o.serialNumber;
                document.getElementById('otherYear').value = o.yearAcquired;
                document.getElementById('otherStatus').value = o.status;
                document.getElementById('otherLocation').value = o.location_id || '';
                empSearch.set('otherEmployeeSearch', 'otherEmployee', o.employeeId || '');
                document.getElementById('otherDetails').value = o.details;

                if (data.data.location_id) {
                    locationManager.setLocation(data.data.location_id);
                } else {
                    document.getElementById('typeLocation').checked = true;
                    toggleAssignmentType();
                    setLocationHierarchy(data.data.location_id);
                }

                new bootstrap.Modal(document.getElementById('otherModal')).show();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { alert('Error loading equipment details: ' + error); });
}

function saveOtherEquipment() {
    var typeInput = document.getElementById('otherType');
    if (!typeInput || !typeInput.value.trim()) {
        alert('Please enter equipment type');
        return;
    }

    var formEl = document.getElementById('otherForm');
    var formData = new FormData(formEl);

    formData.append('action', currentOtherId ? 'update' : 'create');
    if (currentOtherId) formData.append('otherEquipmentId', currentOtherId);

    if (!formData.get('type'))    formData.append('type', document.getElementById('otherType').value);
    if (!formData.get('brand'))   formData.append('brand', document.getElementById('otherBrand').value);
    if (!formData.get('model'))   formData.append('model', document.getElementById('otherModel').value);
    if (!formData.get('serial'))  formData.append('serial', document.getElementById('otherSerial').value);
    if (!formData.get('year'))    formData.append('year', document.getElementById('otherYear').value);
    if (!formData.get('status'))  formData.append('status', document.getElementById('otherStatus').value);
    if (!formData.get('details')) formData.append('details', document.getElementById('otherDetails').value);

    var locId = document.getElementById('otherLocation').value;
    var empId = document.getElementById('otherEmployee').value;

    if (document.getElementById('typeEmployee').checked && empId) {
        formData.set('location_id', '');
        formData.set('employee_id', empId);
    } else {
        formData.set('location_id', locId);
        formData.set('employee_id', '');
    }

    fetch('../ajax/manage_otherequipment.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('otherModal')).hide();
                reloadEquipmentPage();
            } else { alert('Error: ' + data.message); }
        })
        .catch(function(error) { console.error(error); alert('System Error: ' + error); });
}

function deleteOtherEquipment(id) {
    if (!confirm('Delete this equipment?')) return;

    var formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('../ajax/manage_otherequipment.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { alert(data.message); reloadEquipmentPage(); }
            else { alert(data.message); }
        });
}

// ============================================================
// VIEW OTHER EQUIPMENT DETAILS
// ============================================================
function viewOtherEquipment(id) {
    var modalBody = document.getElementById('viewOtherContent');
    modalBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

    var modal = new bootstrap.Modal(document.getElementById('viewOtherModal'));
    modal.show();

    fetch('../ajax/manage_otherequipment.php?action=get&id=' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var item = data.data;
                var statusBadge = getStatusBadge(item.status, item.employeeId);

                var assignmentHtml = '<span class="text-muted">Unassigned</span>';
                if (item.employeeId) {
                    assignmentHtml = '<i class="fas fa-user text-primary"></i> <strong>' + escapeHtml(item.employeeName || 'Unknown Employee') + '</strong>';
                } else if (item.location_name) {
                    assignmentHtml = '<i class="fas fa-map-marker-alt text-danger"></i> <strong>' + escapeHtml(item.location_name) + '</strong>';
                }

                var html =
                    '<div class="row mb-3">' +
                        '<div class="col-md-6">' +
                            '<h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Equipment Type</h6>' +
                            '<p class="fw-bold mb-0" style="font-size:16px">' + escapeHtml(item.equipmentType) + '</p>' +
                        '</div>' +
                        '<div class="col-md-6 text-end">' +
                            '<h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Serial Number</h6>' +
                            '<p class="fw-bold mb-0 text-primary" style="font-family:monospace; font-size:16px">' + escapeHtml(item.serialNumber) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<hr class="my-2">' +
                    '<div class="row g-3">' +
                        '<div class="col-md-6"><label class="small text-muted">Brand & Model</label><div class="fw-bold">' + escapeHtml(item.brand) + ' ' + escapeHtml(item.model) + '</div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">Current Status</label><div>' + statusBadge + '</div></div>' +
                        '<div class="col-md-12"><label class="small text-muted">Current Assignment</label><div class="p-2 bg-light border rounded">' + assignmentHtml + '</div></div>' +
                        '<div class="col-md-6"><label class="small text-muted">Year Acquired</label><div>' + escapeHtml(item.yearAcquired) + '</div></div>' +
                        '<div class="col-md-12"><label class="small text-muted">Details / Specs</label><div class="p-2 border rounded bg-white small" style="min-height:60px">' + (item.details ? escapeHtml(item.details) : '<span class="text-muted font-italic">No details provided</span>') + '</div></div>' +
                    '</div>';
                modalBody.innerHTML = html;

                var editBtn = document.querySelector('#viewOtherModal .btn-primary');
                if (editBtn) editBtn.setAttribute('onclick', 'editOtherEquipment(' + item.otherEquipmentId + '); bootstrap.Modal.getInstance(document.getElementById("viewOtherModal")).hide();');
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(function(error) {
            modalBody.innerHTML = '<div class="alert alert-danger">Error: ' + error + '</div>';
        });
}

function getStatusBadge(status, hasEmployee) {
    var displayStatus = hasEmployee ? 'In Use' : status;
    var cls = 'secondary';
    switch (displayStatus) {
        case 'Available': cls = 'success'; break;
        case 'In Use': cls = 'primary'; break;
        case 'Under Maintenance': cls = 'warning'; break;
        case 'Disposed': cls = 'danger'; break;
    }
    return '<span class="badge bg-' + cls + '">' + displayStatus + '</span>';
}

// ============================================================
// LOCATION MANAGEMENT (OTHER EQUIPMENT)
// ============================================================
var divSelect = document.getElementById('locDivision');

if (divSelect) {
    loadDivisions();

    if (typeof LocationManager !== 'undefined') {
        locationManager = new LocationManager({
            divisionId: 'locDivision',
            sectionId: 'locSection',
            unitId: 'locUnit',
            locationIdInput: 'otherLocation'
        });
    } else {
        console.warn('LocationManager not found. Ensure location_manager.js is included.');
    }

    divSelect.addEventListener('change', calculateFinalLocation);
    document.getElementById('locSection').addEventListener('change', calculateFinalLocation);
    document.getElementById('locUnit').addEventListener('change', calculateFinalLocation);
}

function toggleAssignmentType() {
    var isEmployee = document.getElementById('typeEmployee').checked;
    document.getElementById('locationContainer').style.display = isEmployee ? 'none' : 'block';
    document.getElementById('employeeContainer').style.display = isEmployee ? 'block' : 'none';

    if (isEmployee) {
        document.getElementById('otherLocation').value = '';
    } else {
        empSearch.clear('otherEmployeeSearch', 'otherEmployee');
        calculateFinalLocation();
    }
}

function loadDivisions() {
    fetch('../ajax/get_locations.php?type=1')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var html = '<option value="">Select Division</option>';
            data.forEach(function(d) { html += '<option value="' + d.location_id + '">' + d.location_name + '</option>'; });
            document.getElementById('locDivision').innerHTML = html;
        });
}

function loadLocationChildren(divisionId) {
    var sectionSelect = document.getElementById('locSection');
    var unitSelect = document.getElementById('locUnit');
    sectionSelect.innerHTML = '<option value="">--</option>';
    sectionSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;
    if (!divisionId) return;

    fetch('../ajax/get_locations.php?parent=' + divisionId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var sections = data.filter(function(x) { return x.location_type_id == 2; });
            var units = data.filter(function(x) { return x.location_type_id == 3; });

            if (sections.length > 0) {
                var html = '<option value="">Select Section</option>';
                sections.forEach(function(s) { html += '<option value="' + s.location_id + '">' + s.location_name + '</option>'; });
                sectionSelect.innerHTML = html;
                sectionSelect.disabled = false;
            }
            if (units.length > 0) {
                var html2 = '<option value="">Select Unit</option>';
                units.forEach(function(u) { html2 += '<option value="' + u.location_id + '">' + u.location_name + '</option>'; });
                unitSelect.innerHTML = html2;
                unitSelect.disabled = false;
            }
        });
}

function loadSectionUnits(sectionId) {
    var unitSelect = document.getElementById('locUnit');
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;
    if (!sectionId) {
        var divId = document.getElementById('locDivision').value;
        loadLocationChildren(divId);
        return;
    }

    fetch('../ajax/get_locations.php?parent=' + sectionId + '&type=3')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.length > 0) {
                var html = '<option value="">Select Unit</option>';
                data.forEach(function(u) { html += '<option value="' + u.location_id + '">' + u.location_name + '</option>'; });
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
}

function calculateFinalLocation() {
    var divId  = document.getElementById('locDivision').value;
    var secId  = document.getElementById('locSection').value;
    var unitId = document.getElementById('locUnit').value;
    var final = '';
    if (unitId)      final = unitId;
    else if (secId)  final = secId;
    else if (divId)  final = divId;
    document.getElementById('otherLocation').value = final;
}

function setLocationHierarchy(locationId) {
    if (!locationId) return;
    fetch('../ajax/get_location_path.php?id=' + locationId)
        .then(function(res) { return res.json(); })
        .then(function(path) {
            if (path.division_id) {
                document.getElementById('locDivision').value = path.division_id;
                loadLocationChildren(path.division_id);
                setTimeout(function() {
                    if (path.section_id) {
                        document.getElementById('locSection').value = path.section_id;
                        loadSectionUnits(path.section_id);
                        setTimeout(function() {
                            if (path.unit_id) document.getElementById('locUnit').value = path.unit_id;
                            calculateFinalLocation();
                        }, 200);
                    } else if (path.unit_id) {
                        document.getElementById('locUnit').value = path.unit_id;
                        calculateFinalLocation();
                    }
                }, 200);
            }
        });
}

// ============================================================
// INITIALIZATION
// ============================================================
(function init() {
    // Apply defaultPerPage to all per-page selects so the dropdown
    // reflects the value saved in system settings
    var pp = (typeof defaultPerPage !== 'undefined') ? String(defaultPerPage) : '25';
    ['suPerPageSelect', 'monPerPageSelect', 'aioPerPageSelect', 'prPerPageSelect', 'otherPerPageSelect'].forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var opt = el.querySelector('option[value="' + pp + '"]');
        if (opt) {
            el.value = pp;
        }
    });

    // System Units
    if (document.getElementById('systemunitTableBody')) {
        applySystemUnitTableState();
    }
    // Monitors
    if (document.getElementById('monitorTableBody')) {
        applyMonitorTableState();
    }
    // All-in-Ones
    if (document.getElementById('allinoneTableBody')) {
        applyAIOTableState();
    }
    // Printers
    if (document.getElementById('printerTableBody')) {
        applyPrinterTableState();
    }
    // Other equipment
    if (document.getElementById('otherTableBody')) {
        applyOtherTableState();
    }
})();

// ==============================================
// EQUIPMENT TYPE AUTOCOMPLETE / COMBOBOX
// ==============================================

function loadEquipmentTypesCache() {
    return fetch('../ajax/get_equipment_types.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) equipmentTypesCache = data.data;
            return equipmentTypesCache;
        })
        .catch(function() { return equipmentTypesCache; });
}
loadEquipmentTypesCache();

function fuzzyScore(query, target) {
    var q = query.toLowerCase(), t = target.toLowerCase();
    if (t === q) return 0;
    if (t.startsWith(q)) return 1;
    if (t.includes(q)) return 2;
    var words = q.split(/\s+/);
    if (words.every(function(w) { return t.includes(w); })) return 3;
    var tWords = t.split(/[\s()\-\/]+/);
    if (words.some(function(w) { return tWords.some(function(tw) { return tw.startsWith(w) || w.startsWith(tw); }); })) return 4;
    return -1;
}

function highlightMatch(text, query) {
    if (!query) return escapeHtml(text);
    var idx = text.toLowerCase().indexOf(query.toLowerCase());
    if (idx === -1) return escapeHtml(text);
    return escapeHtml(text.substring(0, idx)) + '<span class="type-match">' + escapeHtml(text.substring(idx, idx + query.length)) + '</span>' + escapeHtml(text.substring(idx + query.length));
}

function onEquipmentTypeInput(input) {
    var query = input.value.trim();
    var dropdown = document.getElementById('typeDropdown');
    var banner = document.getElementById('typeSuggestionBanner');
    typeDropdownActiveIdx = -1;
    if (banner) banner.style.display = 'none';
    if (!dropdown) return;
    if (!query) { showTypeDropdown(equipmentTypesCache, '', dropdown); return; }
    var scored = equipmentTypesCache.map(function(t) { return Object.assign({}, t, { score: fuzzyScore(query, t.typeName) }); }).filter(function(t) { return t.score >= 0; }).sort(function(a, b) { return a.score - b.score; });
    showTypeDropdown(scored, query, dropdown);
    var exactMatch = scored.find(function(t) { return t.typeName.toLowerCase() === query.toLowerCase(); });
    if (!exactMatch && scored.length > 0 && scored[0].score <= 4 && banner) {
        showSuggestionBanner(query, scored[0].typeName, banner, input);
    }
}

function onEquipmentTypeFocus(input) {
    var query = input.value.trim();
    var dropdown = document.getElementById('typeDropdown');
    if (!dropdown) return;
    if (equipmentTypesCache.length === 0) {
        loadEquipmentTypesCache().then(function() {
            var scored = equipmentTypesCache.map(function(t) { return Object.assign({}, t, { score: query ? fuzzyScore(query, t.typeName) : 5 }); }).filter(function(t) { return !query || t.score >= 0; }).sort(function(a, b) { return a.score - b.score; });
            showTypeDropdown(scored, query, dropdown);
        });
    } else {
        var scored = equipmentTypesCache.map(function(t) { return Object.assign({}, t, { score: query ? fuzzyScore(query, t.typeName) : 5 }); }).filter(function(t) { return !query || t.score >= 0; }).sort(function(a, b) { return a.score - b.score; });
        showTypeDropdown(scored, query, dropdown);
    }
}

function showTypeDropdown(items, query, dropdown) {
    if (items.length === 0 && !query) { dropdown.style.display = 'none'; return; }
    var html = '';
    if (items.length === 0) {
        html += '<div class="type-no-match"><i class="fas fa-info-circle"></i> No existing type found</div>';
    } else {
        items.forEach(function(item, idx) {
            var safe = item.typeName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            html += '<div class="type-item" data-index="' + idx + '" data-typename="' + item.typeName.replace(/"/g, '&quot;') + '" onmousedown="selectEquipmentType(\'' + safe + '\')" onmouseenter="typeDropdownActiveIdx=' + idx + ';highlightDropdownItem()"><i class="fas fa-tag"></i><span>' + highlightMatch(item.typeName, query) + '</span><span class="type-context">' + (item.context || '') + '</span></div>';
        });
    }
    if (query && !items.find(function(t) { return t.typeName.toLowerCase() === query.toLowerCase(); })) {
        var sq = query.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += '<div class="type-new-label" onmousedown="selectEquipmentType(\'' + sq + '\')"><i class="fas fa-plus-circle"></i> Add "<strong>' + escapeHtml(query) + '</strong>" as new type</div>';
    }
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function selectEquipmentType(typeName) {
    var input = document.getElementById('otherType');
    if (input) input.value = typeName;
    var dd = document.getElementById('typeDropdown');
    var sb = document.getElementById('typeSuggestionBanner');
    if (dd) dd.style.display = 'none';
    if (sb) sb.style.display = 'none';
    typeDropdownActiveIdx = -1;
}

function showSuggestionBanner(typed, suggested, banner) {
    var safe = suggested.replace(/'/g, "\\'");
    banner.innerHTML = '<i class="fas fa-lightbulb"></i><span>Did you mean <span class="suggestion-use" onclick="selectEquipmentType(\'' + safe + '\')">' + escapeHtml(suggested) + '</span>? This type already exists in the registry.</span>';
    banner.style.display = 'flex';
}

function highlightDropdownItem() {
    var items = document.querySelectorAll('#typeDropdown .type-item');
    items.forEach(function(el, i) { el.classList.toggle('active', i === typeDropdownActiveIdx); });
}

document.addEventListener('keydown', function(e) {
    var dropdown = document.getElementById('typeDropdown');
    if (!dropdown || dropdown.style.display === 'none') return;
    var input = document.getElementById('otherType');
    if (document.activeElement !== input) return;
    var items = dropdown.querySelectorAll('.type-item');
    var maxIdx = items.length - 1;
    if (e.key === 'ArrowDown') { e.preventDefault(); typeDropdownActiveIdx = Math.min(typeDropdownActiveIdx + 1, maxIdx); highlightDropdownItem(); if (items[typeDropdownActiveIdx]) items[typeDropdownActiveIdx].scrollIntoView({ block: 'nearest' }); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); typeDropdownActiveIdx = Math.max(typeDropdownActiveIdx - 1, 0); highlightDropdownItem(); if (items[typeDropdownActiveIdx]) items[typeDropdownActiveIdx].scrollIntoView({ block: 'nearest' }); }
    else if (e.key === 'Enter' && typeDropdownActiveIdx >= 0 && items[typeDropdownActiveIdx]) { e.preventDefault(); selectEquipmentType(items[typeDropdownActiveIdx].getAttribute('data-typename') || items[typeDropdownActiveIdx].textContent.trim()); }
    else if (e.key === 'Escape') { dropdown.style.display = 'none'; typeDropdownActiveIdx = -1; }
});

document.addEventListener('click', function(e) {
    var wrapper = document.querySelector('.equipment-type-wrapper');
    var dropdown = document.getElementById('typeDropdown');
    if (wrapper && dropdown && !wrapper.contains(e.target)) { dropdown.style.display = 'none'; typeDropdownActiveIdx = -1; }
});