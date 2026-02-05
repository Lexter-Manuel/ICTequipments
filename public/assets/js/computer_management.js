/**
 * Computer Equipment Management JavaScript
 * Handles CRUD operations for System Units, Monitors, and All-in-One PCs
 */

// ========================================
// TAB SWITCHING
// ========================================
function switchTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active state from all buttons
    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Set active button
    event.target.closest('.tab-btn').classList.add('active');
}

// ========================================
// SYSTEM UNITS MANAGEMENT
// ========================================
let currentSystemUnitId = null;

function filterSystemUnits() {
    const search = document.getElementById('systemunitSearch').value;
    fetch(`../ajax/manage_systemunit.php?action=list&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderSystemUnits(data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading system units: ' + error));
}

function renderSystemUnits(units) {
    const tbody = document.getElementById('systemunitTableBody');
    tbody.innerHTML = '';
    
    if (units.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-medium);padding:20px">No system units found</td></tr>';
        return;
    }
    
    units.forEach(s => {
        const cls = s.status.toLowerCase();
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong style="color:var(--primary-green)">${escapeHtml(s.systemUnitSerial)}</strong></td>
            <td><div style="font-weight:600">${escapeHtml(s.systemUnitBrand)}</div><div style="font-size:12px;color:var(--text-light)"><i class="fas fa-tag"></i> ${escapeHtml(s.systemUnitCategory)}</div></td>
            <td>
                <div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value">${escapeHtml(s.specificationProcessor)}</span></div>
                <div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value">${escapeHtml(s.specificationMemory)}</span></div>
                <div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value">${escapeHtml(s.specificationStorage)}</span></div>
            </td>
            <td>${escapeHtml(s.yearAcquired)}</td>
            <td>${s.employeeName ? `<div style="font-weight:600">${escapeHtml(s.employeeName)}</div><div style="font-size:12px;color:var(--text-light)">ID: ${escapeHtml(s.employeeId)}</div>` : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>'}</td>
            <td><span class="status-badge status-${cls}">${escapeHtml(s.status)}</span></td>
            <td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editSystemUnit(${s.systemunitId})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteSystemUnit(${s.systemunitId})"><i class="fas fa-trash"></i></button></div></td>
        `;
        tbody.appendChild(tr);
    });
}

function openAddSystemUnit() {
    currentSystemUnitId = null;
    document.getElementById('systemunitModalTitle').textContent = 'Add New System Unit';
    document.getElementById('systemunitForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('systemunitModal'));
    modal.show();
}

function editSystemUnit(id) {
    fetch(`../ajax/manage_systemunit.php?action=get&systemunit_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentSystemUnitId = id;
                const s = data.data;
                document.getElementById('systemunitModalTitle').textContent = 'Edit System Unit';
                document.getElementById('suCategory').value = s.systemUnitCategory;
                document.getElementById('suBrand').value = s.systemUnitBrand;
                document.getElementById('suProcessor').value = s.specificationProcessor;
                document.getElementById('suMemory').value = s.specificationMemory;
                document.getElementById('suGPU').value = s.specificationGPU;
                document.getElementById('suStorage').value = s.specificationStorage;
                document.getElementById('suSerial').value = s.systemUnitSerial;
                document.getElementById('suYear').value = s.yearAcquired;
                document.getElementById('suEmployee').value = s.employeeId || '';
                const modal = new bootstrap.Modal(document.getElementById('systemunitModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading system unit: ' + error));
}

function saveSystemUnit() {
    const formData = new FormData();
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
    
    fetch('../ajax/manage_systemunit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('systemunitModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error saving system unit: ' + error));
}

function deleteSystemUnit(id) {
    if (!confirm('Are you sure you want to delete this system unit?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('systemunit_id', id);
    
    fetch('../ajax/manage_systemunit.php', {
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
    .catch(error => alert('Error deleting system unit: ' + error));
}

// ========================================
// MONITORS MANAGEMENT
// ========================================
let currentMonitorId = null;

function filterMonitors() {
    const search = document.getElementById('monitorSearch').value;
    fetch(`../ajax/manage_monitor.php?action=list&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMonitors(data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading monitors: ' + error));
}

function renderMonitors(monitors) {
    const tbody = document.getElementById('monitorTableBody');
    tbody.innerHTML = '';
    
    if (monitors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-medium);padding:20px">No monitors found</td></tr>';
        return;
    }
    
    monitors.forEach(m => {
        const cls = m.status.toLowerCase();
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong style="color:var(--primary-green)">${escapeHtml(m.monitorSerial)}</strong></td>
            <td><div style="font-weight:600">${escapeHtml(m.monitorBrand)}</div></td>
            <td>${escapeHtml(m.monitorSize)}</td>
            <td>${escapeHtml(m.yearAcquired)}</td>
            <td>${m.employeeName ? `<div style="font-weight:600">${escapeHtml(m.employeeName)}</div><div style="font-size:12px;color:var(--text-light)">ID: ${escapeHtml(m.employeeId)}</div>` : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>'}</td>
            <td><span class="status-badge status-${cls}">${escapeHtml(m.status)}</span></td>
            <td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editMonitor(${m.monitorId})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteMonitor(${m.monitorId})"><i class="fas fa-trash"></i></button></div></td>
        `;
        tbody.appendChild(tr);
    });
}

function openAddMonitor() {
    currentMonitorId = null;
    document.getElementById('monitorModalTitle').textContent = 'Add New Monitor';
    document.getElementById('monitorForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('monitorModal'));
    modal.show();
}

function editMonitor(id) {
    fetch(`../ajax/manage_monitor.php?action=get&monitor_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentMonitorId = id;
                const m = data.data;
                document.getElementById('monitorModalTitle').textContent = 'Edit Monitor';
                document.getElementById('monBrand').value = m.monitorBrand;
                document.getElementById('monSize').value = m.monitorSize;
                document.getElementById('monSerial').value = m.monitorSerial;
                document.getElementById('monYear').value = m.yearAcquired;
                document.getElementById('monEmployee').value = m.employeeId || '';
                const modal = new bootstrap.Modal(document.getElementById('monitorModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading monitor: ' + error));
}

function saveMonitor() {
    const formData = new FormData();
    formData.append('action', currentMonitorId ? 'update' : 'create');
    if (currentMonitorId) formData.append('monitor_id', currentMonitorId);
    formData.append('brand', document.getElementById('monBrand').value);
    formData.append('size', document.getElementById('monSize').value);
    formData.append('monitorSerial', document.getElementById('monSerial').value);
    formData.append('year', document.getElementById('monYear').value);
    formData.append('employee_id', document.getElementById('monEmployee').value);
    
    fetch('../ajax/manage_monitor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('monitorModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error saving monitor: ' + error));
}

function deleteMonitor(id) {
    if (!confirm('Are you sure you want to delete this monitor?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('monitor_id', id);
    
    fetch('../ajax/manage_monitor.php', {
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
    .catch(error => alert('Error deleting monitor: ' + error));
}

// ========================================
// ALL-IN-ONE MANAGEMENT
// ========================================
let currentAllInOneId = null;

function filterAllInOnes() {
    const search = document.getElementById('allinoneSearch').value;
    fetch(`../ajax/manage_allinone.php?action=list&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAllInOnes(data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading all-in-ones: ' + error));
}

function renderAllInOnes(units) {
    const tbody = document.getElementById('allinoneTableBody');
    tbody.innerHTML = '';
    
    if (units.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-medium);padding:20px">No all-in-one PCs found</td></tr>';
        return;
    }
    
    units.forEach(a => {
        const cls = a.status.toLowerCase();
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><div style="font-weight:600">${escapeHtml(a.allinoneBrand)}</div></td>
            <td>
                <div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value">${escapeHtml(a.specificationProcessor)}</span></div>
                <div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value">${escapeHtml(a.specificationMemory)}</span></div>
                <div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value">${escapeHtml(a.specificationStorage)}</span></div>
            </td>
            <td>${a.employeeName ? `<div style="font-weight:600">${escapeHtml(a.employeeName)}</div><div style="font-size:12px;color:var(--text-light)">ID: ${escapeHtml(a.employeeId)}</div>` : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>'}</td>
            <td><span class="status-badge status-${cls}">${escapeHtml(a.status)}</span></td>
            <td><div class="action-buttons"><button class="btn-icon" title="Edit" onclick="editAllInOne(${a.allinoneId})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-danger" title="Delete" onclick="deleteAllInOne(${a.allinoneId})"><i class="fas fa-trash"></i></button></div></td>
        `;
        tbody.appendChild(tr);
    });
}

function openAddAllInOne() {
    currentAllInOneId = null;
    document.getElementById('allinoneModalTitle').textContent = 'Add New All-in-One';
    document.getElementById('allinoneForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('allinoneModal'));
    modal.show();
}

function editAllInOne(id) {
    fetch(`../ajax/manage_allinone.php?action=get&allinone_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentAllInOneId = id;
                const a = data.data;
                document.getElementById('allinoneModalTitle').textContent = 'Edit All-in-One';
                document.getElementById('aioBrand').value = a.allinoneBrand;
                document.getElementById('aioProcessor').value = a.specificationProcessor;
                document.getElementById('aioMemory').value = a.specificationMemory;
                document.getElementById('aioGPU').value = a.specificationGPU;
                document.getElementById('aioStorage').value = a.specificationStorage;
                document.getElementById('aioEmployee').value = a.employeeId || '';
                const modal = new bootstrap.Modal(document.getElementById('allinoneModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading all-in-one: ' + error));
}

function saveAllInOne() {
    const formData = new FormData();
    formData.append('action', currentAllInOneId ? 'update' : 'create');
    if (currentAllInOneId) formData.append('allinone_id', currentAllInOneId);
    formData.append('brand', document.getElementById('aioBrand').value);
    formData.append('processor', document.getElementById('aioProcessor').value);
    formData.append('memory', document.getElementById('aioMemory').value);
    formData.append('gpu', document.getElementById('aioGPU').value);
    formData.append('storage', document.getElementById('aioStorage').value);
    formData.append('employee_id', document.getElementById('aioEmployee').value);
    
    fetch('../ajax/manage_allinone.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('allinoneModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => alert('Error saving all-in-one: ' + error));
}

function deleteAllInOne(id) {
    if (!confirm('Are you sure you want to delete this all-in-one PC?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('allinone_id', id);
    
    fetch('../ajax/manage_allinone.php', {
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
    .catch(error => alert('Error deleting all-in-one: ' + error));
}

// ========================================
// UTILITY FUNCTIONS
// ========================================
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}