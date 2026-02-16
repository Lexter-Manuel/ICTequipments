

var currentOtherId = null;
var locationManager;

function filterOtherEquipment() {
    currentPage = 1;
    applyTableState();
}

function changePerPage() {
    perPage = parseInt(document.getElementById('perPageSelect').value);
    currentPage = 1;
    applyTableState();
}

function applyTableState() {
    // 1. Get filter values
    const searchTerm = document.getElementById('otherSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    // 2. Get all rows that have data attributes
    const allRows = Array.from(document.querySelectorAll('#otherTableBody tr[data-id]'));

    // 3. Filter Logic
    filteredRows = allRows.filter(row => {
        const serial = row.dataset.serial || '';
        const type = row.dataset.type || '';
        const brand = row.dataset.brand || '';
        const status = row.dataset.status || '';

        // Check if row matches search text
        const matchesSearch = serial.includes(searchTerm) || 
                              type.includes(searchTerm) || 
                              brand.includes(searchTerm);
        
        // Check if row matches status dropdown
        const matchesStatus = !statusFilter || status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    // 4. Pagination Logic
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    
    if (currentPage > totalPages) currentPage = totalPages;
    const start = (currentPage - 1) * perPage;
    const end = Math.min(start + perPage, total);

    // 5. Show/Hide Rows
    allRows.forEach(r => r.style.display = 'none'); // Hide all first
    
    filteredRows.forEach((row, idx) => {
        if (idx >= start && idx < end) {
            row.style.display = ''; // Show only current page rows
        }
    });

    // 6. Update UI Counts
    const countEl = document.getElementById('recordCount');
    if (countEl) {
        countEl.innerHTML = `Showing <strong>${total === 0 ? 0 : start + 1}–${end}</strong> of <strong>${total}</strong> records`;
    }

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const container = document.getElementById('paginationControls');
    if (!container) return;

    let html = `<button class="page-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="fas fa-chevron-left"></i></button>`;

    html += `<span class="px-2">Page ${currentPage} of ${totalPages}</span>`;

    html += `<button class="page-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
        <i class="fas fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

function goToPage(page) {
    if (page < 1) return;
    currentPage = page;
    applyTableState();
}

// ==========================================
// CRUD OPERATIONS (AJAX)
// ==========================================

function openAddOtherEquipment() {
    currentOtherId = null;
    document.getElementById('otherForm').reset();
    document.getElementById('otherModalTitle').textContent = 'Add Equipment';
    
    if(locationManager) locationManager.reset();
    
    // Reset toggle to default
    if(document.getElementById('typeLocation')) {
        document.getElementById('typeLocation').checked = true;
        toggleAssignmentType();
    }

    new bootstrap.Modal(document.getElementById('otherModal')).show();
}


function editOtherEquipment(id) {
    fetch(`../ajax/manage_otherequipment.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
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
                document.getElementById('otherEmployee').value = o.employeeId || '';
                document.getElementById('otherDetails').value = o.details;
                
                if (data.data.location_id) {
                    locationManager.setLocation(data.data.location_id);
                } else {
                    document.getElementById('typeLocation').checked = true;
                    toggleAssignmentType();
                    // Set hierarchy
                    setLocationHierarchy(data.data.location_id);
                }
                
                var modal = new bootstrap.Modal(document.getElementById('otherModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading equipment details: ' + error));
}

function saveOtherEquipment() {
    // 1. Basic Validation
    var typeInput = document.getElementById('otherType');
    if (!typeInput || !typeInput.value.trim()) {
        alert('Please enter equipment type');
        return;
    }

    // 2. Create FormData
    var formEl = document.getElementById('otherForm');
    var formData = new FormData(formEl);

    // 3. ACTION (Create/Update)
    formData.append('action', currentOtherId ? 'update' : 'create');
    if (currentOtherId) {
        formData.append('otherEquipmentId', currentOtherId);
    }

    // 4. MANUAL APPEND (Safety Net)
    // This ensures data is sent even if HTML 'name' attributes are missing
    if (!formData.get('type')) formData.append('type', document.getElementById('otherType').value);
    if (!formData.get('brand')) formData.append('brand', document.getElementById('otherBrand').value);
    if (!formData.get('model')) formData.append('model', document.getElementById('otherModel').value);
    if (!formData.get('serial')) formData.append('serial', document.getElementById('otherSerial').value);
    if (!formData.get('year')) formData.append('year', document.getElementById('otherYear').value);
    if (!formData.get('status')) formData.append('status', document.getElementById('otherStatus').value);
    if (!formData.get('details')) formData.append('details', document.getElementById('otherDetails').value);
    

    var locId = document.getElementById('otherLocation').value;
    var empId = document.getElementById('otherEmployee').value;
    

    if(document.getElementById('typeEmployee').checked && empId) {
         formData.set('location_id', ''); 
         formData.set('employee_id', empId);
    } else {
         formData.set('location_id', locId);
         formData.set('employee_id', ''); 
    }

    // 5. Send Request
    fetch('../ajax/manage_otherequipment.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('otherModal')).hide();
            location.reload(); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error(error);
        alert('System Error: ' + error);
    });
}

function deleteOtherEquipment(id) {
    if (!confirm('Delete this equipment?')) return;

    let formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('../ajax/manage_otherequipment.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Reload page to update table
        } else {
            alert(data.message);
        }
    });
}

// Helper
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==========================================
// VIEW EQUIPMENT DETAILS
// ==========================================
function viewOtherEquipment(id) {
    var modalBody = document.getElementById('viewOtherContent');
    modalBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    var modal = new bootstrap.Modal(document.getElementById('viewOtherModal'));
    modal.show();

    fetch(`../ajax/manage_otherequipment.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var item = data.data;
                var statusBadge = getStatusBadge(item.status, item.employeeId);
                
                // Determine assignment display
                var assignmentHtml = '<span class="text-muted">Unassigned</span>';
                if (item.employeeId) {
                    assignmentHtml = `<i class="fas fa-user text-primary"></i> <strong>${escapeHtml(item.employeeName || 'Unknown Employee')}</strong>`;
                } else if (item.location_name) {
                    assignmentHtml = `<i class="fas fa-map-marker-alt text-danger"></i> <strong>${escapeHtml(item.location_name)}</strong>`;
                }

                var html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Equipment Type</h6>
                            <p class="fw-bold mb-0" style="font-size:16px">${escapeHtml(item.equipmentType)}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Serial Number</h6>
                            <p class="fw-bold mb-0 text-primary" style="font-family:monospace; font-size:16px">${escapeHtml(item.serialNumber)}</p>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Brand & Model</label>
                            <div class="fw-bold">${escapeHtml(item.brand)} ${escapeHtml(item.model)}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Current Status</label>
                            <div>${statusBadge}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="small text-muted">Current Assignment</label>
                            <div class="p-2 bg-light border rounded">${assignmentHtml}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Year Acquired</label>
                            <div>${escapeHtml(item.yearAcquired)}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="small text-muted">Details / Specs</label>
                            <div class="p-2 border rounded bg-white small" style="min-height:60px">
                                ${item.details ? escapeHtml(item.details) : '<span class="text-muted font-italic">No details provided</span>'}
                            </div>
                        </div>
                    </div>
                `;
                modalBody.innerHTML = html;
                
                // Set the Edit button in the View modal to point to this item
                var editBtn = document.querySelector('#viewOtherModal .btn-primary');
                if(editBtn) editBtn.setAttribute('onclick', `editOtherEquipment(${item.otherEquipmentId}); bootstrap.Modal.getInstance(document.getElementById('viewOtherModal')).hide();`);
                
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error}</div>`;
        });
}

function getStatusBadge(status, hasEmployee) {
    var displayStatus = hasEmployee ? 'In Use' : status;
    var cls = 'secondary';
    
    switch(displayStatus) {
        case 'Available': cls = 'success'; break;
        case 'In Use': cls = 'primary'; break;
        case 'Under Maintenance': cls = 'warning'; break;
        case 'Disposed': cls = 'danger'; break;
    }
    return `<span class="badge bg-${cls}">${displayStatus}</span>`;
}

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
        console.error('LocationManager not found. Ensure location_manager.js is included.');
    }

    divSelect.addEventListener('change', calculateFinalLocation);
    document.getElementById('locSection').addEventListener('change', calculateFinalLocation);
    document.getElementById('locUnit').addEventListener('change', calculateFinalLocation);
}

// Toggle between Employee and Location inputs
function toggleAssignmentType() {
    var isEmployee = document.getElementById('typeEmployee').checked;
    
    document.getElementById('locationContainer').style.display = isEmployee ? 'none' : 'block';
    document.getElementById('employeeContainer').style.display = isEmployee ? 'block' : 'none';
    
    if (isEmployee) {
        // If employee, clear location ID
        document.getElementById('otherLocation').value = '';
    } else {
        // If location, clear employee and recalculate location
        document.getElementById('otherEmployee').value = '';
        calculateFinalLocation();
    }
}

// 1. Load Divisions (Root Locations)
function loadDivisions() {
    fetch('../ajax/get_locations.php?type=1') // Type 1 = Division
        .then(res => res.json())
        .then(data => {
            var html = '<option value="">Select Division</option>';
            data.forEach(d => html += `<option value="${d.location_id}">${d.location_name}</option>`);
            document.getElementById('locDivision').innerHTML = html;
        });
}

// 2. Load Children (Sections AND Direct Units)
function loadLocationChildren(divisionId) {
    var sectionSelect = document.getElementById('locSection');
    var unitSelect = document.getElementById('locUnit');
    
    // Reset
    sectionSelect.innerHTML = '<option value="">--</option>';
    sectionSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;
    
    if (!divisionId) return;

    // Fetch all children of this division
    fetch(`../ajax/get_locations.php?parent=${divisionId}`)
        .then(res => res.json())
        .then(data => {
            // Separate Sections (Type 2) and Units (Type 3)
            var sections = data.filter(x => x.location_type_id == 2);
            var units = data.filter(x => x.location_type_id == 3);

            // Populate Sections
            if (sections.length > 0) {
                var html = '<option value="">Select Section</option>';
                sections.forEach(s => html += `<option value="${s.location_id}">${s.location_name}</option>`);
                sectionSelect.innerHTML = html;
                sectionSelect.disabled = false;
            }

            // Populate Direct Units (Units connected directly to Division)
            if (units.length > 0) {
                var html = '<option value="">Select Unit</option>';
                units.forEach(u => html += `<option value="${u.location_id}">${u.location_name}</option>`);
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
}

// 3. Load Units for a specific Section
function loadSectionUnits(sectionId) {
    var unitSelect = document.getElementById('locUnit');
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;

    if (!sectionId) {
        // If section deselected, reload direct units from division
        var divId = document.getElementById('locDivision').value;
        loadLocationChildren(divId); 
        return;
    }

    fetch(`../ajax/get_locations.php?parent=${sectionId}&type=3`) // Type 3 = Unit
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                var html = '<option value="">Select Unit</option>';
                data.forEach(u => html += `<option value="${u.location_id}">${u.location_name}</option>`);
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
}

// 4. Calculate Final Location ID (Prioritize Unit -> Section -> Division)
function calculateFinalLocation() {
    var divId = document.getElementById('locDivision').value;
    var secId = document.getElementById('locSection').value;
    var unitId = document.getElementById('locUnit').value;

    var final = '';
    if (unitId) final = unitId;
    else if (secId) final = secId;
    else if (divId) final = divId;

    document.getElementById('otherLocation').value = final;
}

// Helper: Pre-fill dropdowns when Editing
function setLocationHierarchy(locationId) {
    if (!locationId) return;

    fetch(`../ajax/get_location_path.php?id=${locationId}`)
        .then(res => res.json())
        .then(path => {
            // path = { division_id: 1, section_id: 5, unit_id: 10 }
            
            if (path.division_id) {
                document.getElementById('locDivision').value = path.division_id;
                
                // Trigger load of children
                loadLocationChildren(path.division_id);
                
                // Wait for AJAX to finish (simple timeout for now, or use async/await)
                setTimeout(() => {
                    if (path.section_id) {
                        document.getElementById('locSection').value = path.section_id;
                        loadSectionUnits(path.section_id);
                        
                        setTimeout(() => {
                            if (path.unit_id) document.getElementById('locUnit').value = path.unit_id;
                            calculateFinalLocation();
                        }, 200);
                    } else if (path.unit_id) {
                        // Direct unit under division
                        document.getElementById('locUnit').value = path.unit_id;
                        calculateFinalLocation();
                    }
                }, 200);
            }
        });
}

