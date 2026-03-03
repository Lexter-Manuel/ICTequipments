/**
 * roster.js - Employee Roster JavaScript
 * Handles filtering, search, profile view, edit modal, and location hierarchy
 */

// ========================================
// GLOBAL VARIABLES
// ========================================
var editCropper = null;
var editCurrentImageFile = null;
var editCropModal;
var currentEmployeeId = null;
var currentEmployeeEquipment = []; // Stores all equipment for FAB panel
var currentProfileData = null; // Stores full profile data for equipment detail lookup
var fabMaintenanceQueue = [];
var fabQueueIndex = 0;

// Table State
var currentPage = 1;
var perPage = 25;
var sortCol = 'name';
var sortDir = 'asc';
var filteredRows = [];

var LocationHierarchy = {
    populateSections: function(divisionId, sectionSelectId = 'edit_section') {
        var sectionSelect = document.getElementById(sectionSelectId);
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        sectionSelect.disabled = true;
        
        if (!divisionId) return;

        var filteredSections = sectionsData.filter(s => s.parent_location_id == divisionId);
        if (filteredSections.length > 0) {
            filteredSections.forEach(s => {
                var opt = document.createElement('option');
                opt.value = s.location_id;
                opt.textContent = s.location_name;
                sectionSelect.appendChild(opt);
            });
            sectionSelect.disabled = false;
        }
    },

    populateUnits: function(parentId, unitSelectId = 'edit_unit') {
        var unitSelect = document.getElementById(unitSelectId);
        unitSelect.innerHTML = '<option value="">Select Unit</option>';
        unitSelect.disabled = true;
        
        if (!parentId) return;

        var filteredUnits = unitsData.filter(u => u.parent_location_id == parentId);
        if (filteredUnits.length > 0) {
            filteredUnits.forEach(u => {
                var opt = document.createElement('option');
                opt.value = u.location_id;
                opt.textContent = u.location_name;
                unitSelect.appendChild(opt);
            });
            unitSelect.disabled = false;
        }
    },

    setupLocationDropdowns: function(locationId, divisionSelectId = 'edit_division', sectionSelectId = 'edit_section', unitSelectId = 'edit_unit') {
        var divSelect = document.getElementById(divisionSelectId);
        var secSelect = document.getElementById(sectionSelectId);
        var unitSelect = document.getElementById(unitSelectId);

        var unit = unitsData.find(u => u.location_id == locationId);
        var section = sectionsData.find(s => s.location_id == locationId);
        
        var targetDiv = '', targetSec = '', targetUnit = '';

        if (unit) {
            targetUnit = unit.location_id;
            var parentIsSection = sectionsData.find(s => s.location_id == unit.parent_location_id);
            
            if (parentIsSection) {
                targetSec = parentIsSection.location_id;
                targetDiv = parentIsSection.parent_location_id;
            } else {
                targetDiv = unit.parent_location_id;
            }
        } else if (section) {
            targetSec = section.location_id;
            targetDiv = section.parent_location_id;
        } else {
            targetDiv = locationId;
        }

        divSelect.value = targetDiv;
        this.populateSections(targetDiv, sectionSelectId);
        
        if (targetSec) {
            secSelect.value = targetSec;
            secSelect.disabled = false;
            this.populateUnits(targetSec, unitSelectId);
        } else {
            secSelect.value = "";
            this.populateUnits(targetDiv, unitSelectId);
        }
        
        if (targetUnit) {
            unitSelect.value = targetUnit;
            unitSelect.disabled = false;
        }
    },

    getFinalLocationId: function(divisionSelectId = 'edit_division', sectionSelectId = 'edit_section', unitSelectId = 'edit_unit') {
        var unitValue = document.getElementById(unitSelectId).value;
        var sectionValue = document.getElementById(sectionSelectId).value;
        var divisionValue = document.getElementById(divisionSelectId).value;
        
        return unitValue || sectionValue || divisionValue || '';
    }
};

// ========================================
// UTILITY FUNCTIONS
// ========================================
function showAlert(type, message) {
    var alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 5000);
}

function calculateAge(birthDate) {
    if (!birthDate) return '—'; 
    var birthDateObj = new Date(birthDate);
    var today = new Date();
    var age = today.getFullYear() - birthDateObj.getFullYear();
    var m = today.getMonth() - birthDateObj.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDateObj.getDate())) {
        age--;
    }
    return age;
}

function formatDate(dateString) {
    if (!dateString) return '—';
    var date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

// ========================================
// VIEW EMPLOYEE PROFILE
// ========================================
function viewEmployee(employeeId) {
    currentEmployeeId = employeeId;
    
    fetch(`${BASE_URL}ajax/get_employee_profile.php?employee_id=${employeeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentProfileData = data; // Save data for dynamic specs viewer
                renderEmployeeProfile(data);
                document.getElementById('roster-list-view').style.display = 'none';
                document.getElementById('employee-profile-view').style.display = 'block';
                window.scrollTo(0, 0);
                buildFabEquipmentList(data);
                showMaintenanceFab(true);
            } else {
                showAlert('danger', data.message || 'Failed to load profile');
            }
        })
        .catch(error => {
            console.error(error);
            showAlert('danger', 'Error loading profile: ' + error);
        });
}

function closeEmployeeProfile() {
    document.getElementById('employee-profile-view').style.display = 'none';
    document.getElementById('roster-list-view').style.display = 'block';
    currentEmployeeId = null;
    showMaintenanceFab(false);
}

function editEmployeeFromProfile() {
    if (currentEmployeeId) {
        editEmployee(currentEmployeeId);
    }
}

// ========================================
// RENDER EMPLOYEE PROFILE
// ========================================
function renderEmployeeProfile(data) {
    var emp = data.employee;
    renderPersonalInformation(emp);
    renderEmploymentDetails(emp);
    renderEquipment(data);
    renderPrinters(data);
    renderSoftwareLicenses(data);
}

function renderPersonalInformation(emp) {
    var container = document.getElementById('profile-personal-info');
    var age = calculateAge(emp.birthDate);
    
    var statusClass = 'status-permanent';
    if(emp.employmentStatus === 'Casual') statusClass = 'status-casual';
    if(emp.employmentStatus === 'Job Order') statusClass = 'status-job-order';
    if(emp.employmentStatus === 'Contract of Service') statusClass = 'status-job-order';
    
    var isActive = emp.is_active == 1;
    var isArchived = emp.is_archive == 1;
    
    var fullName = [emp.firstName, emp.middleName, emp.lastName, emp.suffixName]
        .filter(Boolean)
        .join(' ');
    
    var html = `
        <div class="profile-header-integrated">
            <div class="profile-photo-section">
                ${emp.photoPath 
                    ? `<img src="uploads/${emp.photoPath}" class="profile-avatar-large" alt="${fullName}">` 
                    : `<div class="profile-avatar-placeholder-large"><i class="fas fa-user"></i></div>`
                }
            </div>
            <div class="profile-identity-section">
                <h1 class="profile-name-large">${fullName}</h1>
                <div class="profile-badges-group">
                    <span class="profile-id-badge"><i class="fas fa-id-card"></i> ${emp.employeeId}</span>
                    <span class="status-badge ${statusClass}">${emp.employmentStatus}</span>
                    <span class="active-badge active-badge-${isActive ? 'yes' : 'no'}">
                        <i class="fas fa-${isActive ? 'check-circle' : 'times-circle'}"></i>
                        ${isActive ? 'Active' : 'Inactive'}
                    </span>
                    ${isArchived ? `<span class="active-badge active-badge-archived"><i class="fas fa-archive"></i> Archived</span>` : ''}
                </div>
                <div class="profile-position-large">
                    <i class="fas fa-briefcase"></i> ${emp.position || '—'}
                </div>
            </div>
        </div>
        
        <div class="detail-grid">
            ${createDetailItem('Sex', emp.sex, 'venus-mars')}
            ${createDetailItem('Birth Date', formatDate(emp.birthDate), 'calendar')}
            ${createDetailItem('Age', age !== '—' ? age + ' years old' : '—', 'birthday-cake')}
        </div>
    `;
    
    container.innerHTML = html;
}

function renderEmploymentDetails(emp) {
    var container = document.getElementById('profile-employment-info');
    
    var locationText = '—';
    if (emp.location_name) {
        if (emp.parent_location_name) {
            locationText = `${emp.parent_location_name} › ${emp.location_name}`;
        } else {
            locationText = emp.location_name;
        }
    }
    
    var html = `
        ${createDetailItem('Position', emp.position, 'briefcase')}
        ${createDetailItem('Employment Status', emp.employmentStatus, 'user-tag')}
        ${createDetailItem('Assignment', locationText, 'map-marker-alt')}
        ${createDetailItem('Location Type', emp.location_type_name || '—', 'building')}
    `;
    
    container.innerHTML = html;
}

function renderEquipment(data) {
    var countsContainer = document.getElementById('equipment-counts');
    var gridContainer = document.getElementById('equipment-grid');
    
    var systemUnitsCount = data.systemUnits ? data.systemUnits.length : 0;
    var allinonesCount = data.allinones ? data.allinones.length : 0;
    var monitorsCount = data.monitors ? data.monitors.length : 0;
    var otherCount = data.other ? data.other.length : 0;
    var totalCount = systemUnitsCount + allinonesCount + monitorsCount + otherCount;
    
    countsContainer.innerHTML = `
        <div class="asset-count-item">
            <i class="fas fa-desktop"></i>
            <span>${systemUnitsCount} System Units</span>
        </div>
        <div class="asset-count-item">
            <i class="fas fa-computer"></i>
            <span>${allinonesCount} All-in-Ones</span>
        </div>
        <div class="asset-count-item">
            <i class="fas fa-tv"></i>
            <span>${monitorsCount} Monitors</span>
        </div>
        <div class="asset-count-item">
            <i class="fas fa-server"></i>
            <span>${otherCount} Other</span>
        </div>
    `;
    
    var equipmentHtml = '';
    
    if (data.systemUnits && data.systemUnits.length > 0) {
        data.systemUnits.forEach(item => {
            equipmentHtml += createEquipmentCard('System Unit', item.systemUnitBrand, item.systemUnitSerial, 'desktop', item.systemunitId, 'systemunit');
        });
    }
    if (data.allinones && data.allinones.length > 0) {
        data.allinones.forEach(item => {
            equipmentHtml += createEquipmentCard('All-in-One PC', item.allinoneBrand, item.allinoneSerial || 'N/A', 'computer', item.allinoneId, 'allinone');
        });
    }
    if (data.monitors && data.monitors.length > 0) {
        data.monitors.forEach(item => {
            equipmentHtml += createEquipmentCard('Monitor', item.monitorBrand, item.monitorSerial, 'tv', item.monitorId, 'monitor');
        });
    }
    if (data.other && data.other.length > 0) {
        data.other.forEach(item => {
            const realType = item.equipmentType || 'other'; 
            const displayType = item.equipmentType || 'Other Equipment';
            equipmentHtml += createEquipmentCard(displayType, item.brand, item.serialNumber, 'server', item.otherEquipmentId, realType);
        });
    }
    
    if (totalCount === 0) {
        equipmentHtml = `
            <div class="empty-state-card">
                <i class="fas fa-box-open"></i>
                <p>No equipment currently assigned.</p>
            </div>
        `;
    }
    
    gridContainer.innerHTML = equipmentHtml;
}

function renderSoftwareLicenses(data) {
    var container = document.getElementById('software-licenses-container');
    var countBadge = document.getElementById('software-count');
    
    var licenses = data.softwareLicenses || data.software || [];
    countBadge.textContent = licenses.length;
    
    if (licenses.length === 0) {
        container.innerHTML = `
            <div class="empty-state-card">
                <i class="fas fa-key"></i>
                <p>No software licenses assigned.</p>
            </div>
        `;
        return;
    }
    
    var html = '<div class="software-licenses-grid">';
    licenses.forEach(license => {
        html += `
            <div class="software-license-card">
                <div class="software-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="software-info">
                    <h4>${license.licenseSoftware || 'Unknown Software'}</h4>
                    <div class="software-details">
                        <span><i class="fas fa-tag"></i> ${license.licenseDetails || 'N/A'}</span>
                        <span><i class="fas fa-calendar"></i> Expires: ${formatDate(license.expiryDate) || '—'}</span>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

function renderPrinters(data) {
    var container = document.getElementById('printers-grid');
    var countBadge = document.getElementById('printer-count');
    
    var printers = data.printers || [];
    countBadge.textContent = printers.length;
    
    if (printers.length === 0) {
        container.innerHTML = `
            <div class="empty-state-card">
                <i class="fas fa-print"></i>
                <p>No printers assigned.</p>
            </div>
        `;
        return;
    }
    
    var html = '';
    printers.forEach(printer => {
        html += createEquipmentCard(
            'Printer', 
            printer.printerBrand || printer.brand || 'Unknown Brand', 
            printer.printerSerial || printer.serial || 'N/A', 
            'print', 
            printer.printerId || printer.id, 
            'printer'
        );
    });
    
    container.innerHTML = html;
}

// ========================================
// HELPER FUNCTIONS FOR RENDERING
// ========================================
function createDetailItem(label, value, icon) {
    return `
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-${icon}"></i>
            </div>
            <div class="detail-content">
                <div class="detail-label">${label}</div>
                <div class="detail-value">${value || '—'}</div>
            </div>
        </div>
    `;
}

function createEquipmentCard(type, brand, serial, icon, id, equipmentType) {
    const safeBrand = (brand || 'Unknown').replace(/'/g, "\\'");
    const safeSerial = (serial || 'N/A').replace(/'/g, "\\'");
    const safeType = (equipmentType || 'other').replace(/'/g, "\\'");

    return `
        <div class="equipment-card">
            <div class="eq-header">
                <div class="eq-icon"><i class="fas fa-${icon}"></i></div>
                <div class="eq-type">${type}</div>
            </div>
            <div class="eq-body">
                <h4 class="eq-brand">${brand || 'Unknown Brand'}</h4>
                <div class="eq-serial"><i class="fas fa-barcode"></i> ${serial || 'N/A'}</div>
            </div>
            <div class="eq-footer">
                <div class="row g-2">
                    <div class="col-6">
                        <button class="btn btn-sm btn-light w-100 border" 
                                onclick="viewEquipmentDetails('${safeType}', ${id}, '${icon}')">
                            <i class="fas fa-eye text-secondary"></i> View
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-sm btn-primary w-100" 
                                onclick="openRosterMaintenance(${id}, '${safeType}', '${safeBrand}', '${safeSerial}')">
                            <i class="fas fa-tools"></i> Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// ========================================
// DYNAMIC EQUIPMENT VIEWER (Partner Merge)
// ========================================
function findEquipmentItem(type, id) {
    if (!currentProfileData) return null;
    var normalType = type.toLowerCase();

    if (normalType === 'systemunit') return (currentProfileData.systemUnits || []).find(function(i) { return i.systemunitId == id; });
    if (normalType === 'allinone') return (currentProfileData.allinones || []).find(function(i) { return i.allinoneId == id; });
    if (normalType === 'monitor') return (currentProfileData.monitors || []).find(function(i) { return i.monitorId == id; });
    if (normalType === 'printer') return (currentProfileData.printers || []).find(function(i) { return i.printerId == id; });
    return (currentProfileData.other || []).find(function(i) { return i.otherEquipmentId == id; });
}

function buildSpecRows(type, item) {
    if (!item) return '';
    var normalType = type.toLowerCase();
    var rows = '';

    if (normalType === 'systemunit') {
        rows += specRow('Category', item.systemUnitCategory);
        rows += specRow('Processor', item.specificationProcessor);
        rows += specRow('Memory', item.specificationMemory);
        rows += specRow('GPU', item.specificationGPU);
        rows += specRow('Storage', item.specificationStorage);
        rows += specRow('Year Acquired', item.yearAcquired);
    } else if (normalType === 'allinone') {
        rows += specRow('Processor', item.specificationProcessor);
        rows += specRow('Memory', item.specificationMemory);
        rows += specRow('GPU', item.specificationGPU);
        rows += specRow('Storage', item.specificationStorage);
    } else if (normalType === 'monitor') {
        rows += specRow('Screen Size', item.monitorSize);
        rows += specRow('Year Acquired', item.yearAcquired);
    } else if (normalType === 'printer') {
        rows += specRow('Model', item.printerModel);
        rows += specRow('Year Acquired', item.yearAcquired);
    } else {
        rows += specRow('Type', item.equipmentType);
        rows += specRow('Model', item.model);
        rows += specRow('Year Acquired', item.yearAcquired);
    }
    return rows;
}

function specRow(label, value) {
    return `
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted">${label}:</span>
            <span class="fw-bold text-dark">${value || '—'}</span>
        </li>`;
}

function viewEquipmentDetails(type, id, icon) {
    var item = findEquipmentItem(type, id);
    var normalType = type.toLowerCase();

    var brand = '—', serial = '—';
    if (item) {
        if (normalType === 'systemunit')       { brand = item.systemUnitBrand; serial = item.systemUnitSerial; }
        else if (normalType === 'allinone')    { brand = item.allinoneBrand;   serial = item.allinoneSerial; }
        else if (normalType === 'monitor')     { brand = item.monitorBrand;    serial = item.monitorSerial; }
        else if (normalType === 'printer')     { brand = item.printerBrand;    serial = item.printerSerial; }
        else                                   { brand = item.brand;           serial = item.serialNumber; }
    }

    document.getElementById('detailBrand').innerText = brand || '—';
    document.getElementById('detailSerial').innerText = 'SN: ' + (serial || 'N/A');
    document.getElementById('detailType').innerText = type.toUpperCase();
    document.getElementById('detailIcon').className = 'fas fa-' + icon + ' fa-3x text-secondary';

    var owner = document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Unknown';
    var locationEl = document.querySelector('.profile-badges-group .status-badge');
    var location = locationEl ? 'Current Assignment' : 'N/A';

    var specsHtml = buildSpecRows(type, item);
    specsHtml += specRow('Assigned To', owner);
    specsHtml += specRow('Location', location);

    document.getElementById('detailSpecsList').innerHTML = specsHtml;

    var modal = new bootstrap.Modal(document.getElementById('equipmentDetailsModal'));
    modal.show();
}

// ========================================
// EMPLOYEE EDIT/SAVE
// ========================================
function editEmployee(employeeId) {
    var employee = rosterData.find(emp => emp.employeeId == employeeId);
    if (!employee) {
        showAlert('danger', 'Employee data not found.');
        return;
    }
    
    document.getElementById('edit_employeeId').value = employee.employeeId;
    document.getElementById('edit_firstName').value = employee.firstName || '';
    document.getElementById('edit_middleName').value = employee.middleName || '';
    document.getElementById('edit_lastName').value = employee.lastName || '';
    document.getElementById('edit_suffixName').value = employee.suffixName || '';
    document.getElementById('edit_sex').value = employee.sex || '';
    document.getElementById('edit_birthDate').value = employee.birthDate || '';
    document.getElementById('edit_position').value = employee.position || '';
    document.getElementById('edit_employmentStatus').value = employee.employmentStatus || '';
    
    LocationHierarchy.setupLocationDropdowns(employee.location_id);
    
    var previewImage = document.getElementById('edit_previewImage');
    var photoPreview = document.getElementById('edit_photoPreview');
    var uploadPlaceholder = document.getElementById('edit_uploadPlaceholder');
    var changePhotoBtn = document.getElementById('edit_changePhotoBtn');
    
    if (employee.photoPath) {
        previewImage.src = 'uploads/' + employee.photoPath;
        photoPreview.classList.add('active');
        uploadPlaceholder.style.display = 'none';
        changePhotoBtn.style.display = 'block';
    } else {
        photoPreview.classList.remove('active');
        uploadPlaceholder.style.display = 'flex';
        changePhotoBtn.style.display = 'none';
    }
    
    document.getElementById('edit_croppedImage').value = '';
    document.getElementById('edit_photoInput').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

function saveEmployee() {
    var form = document.getElementById('editEmployeeForm');
    var formData = new FormData(form);
    
    formData.append('action', 'update');
    var finalLoc = LocationHierarchy.getFinalLocationId();
                   
    if(!finalLoc) { 
        showAlert('warning', "Please select a location assignment"); 
        return; 
    }
    formData.set('locationId', finalLoc);

    fetch(`${BASE_URL}ajax/process_employee.php`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showAlert('success', 'Employee updated successfully!');
            setTimeout(() => reloadCurrentPage(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(err => showAlert('danger', 'Error updating employee: ' + err));
}

// ========================================
// CROPPER LOGIC & EVENT LISTENERS
// ========================================
if (typeof bootstrap !== 'undefined') {
    var cropEl = document.getElementById('edit_cropModal');
    if(cropEl) editCropModal = new bootstrap.Modal(cropEl);
}

var divSelect = document.getElementById('edit_division');
var secSelect = document.getElementById('edit_section');

if (divSelect) {
    divSelect.addEventListener('change', function() {
        LocationHierarchy.populateSections(this.value);
        LocationHierarchy.populateUnits(this.value);
    });
}

if (secSelect) {
    secSelect.addEventListener('change', function() {
        var secId = this.value;
        if (secId) {
            LocationHierarchy.populateUnits(secId);
        } else {
            LocationHierarchy.populateUnits(document.getElementById('edit_division').value);
        }
    });
}

applyTableState();
updateSortIcons();

var cropModalEl = document.getElementById('edit_cropModal');
if (cropModalEl) {
    cropModalEl.addEventListener('shown.bs.modal', function () {
        var imageToCrop = document.getElementById('edit_imageToCrop');
        if (!editCropper && imageToCrop.src) {
            editCropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 2, autoCropArea: 0.8 });
        }
    });
    cropModalEl.addEventListener('hidden.bs.modal', function () {
        if (editCropper) { editCropper.destroy(); editCropper = null; }
    });
}

var photoUploadBox = document.getElementById('edit_photoUploadBox');
if (photoUploadBox) {
    photoUploadBox.addEventListener('click', function(e) {
        if (!e.target.closest('.photo-preview.active')) document.getElementById('edit_photoInput').click();
    });
}

var photoInput = document.getElementById('edit_photoInput');
if (photoInput) {
    photoInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            editCurrentImageFile = file;
            var reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('edit_imageToCrop').src = event.target.result;
                editCropModal.show();
            };
            reader.readAsDataURL(file);
        }
    });
}

var cropButton = document.getElementById('edit_cropButton');
if (cropButton) {
    cropButton.addEventListener('click', function() {
        if (!editCropper) return;
        var canvas = editCropper.getCroppedCanvas({ width: 400, height: 400 });
        var croppedBase64 = canvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('edit_croppedImage').value = croppedBase64;
        document.getElementById('edit_previewImage').src = croppedBase64;
        document.getElementById('edit_photoPreview').classList.add('active');
        document.getElementById('edit_uploadPlaceholder').style.display = 'none';
        document.getElementById('edit_changePhotoBtn').style.display = 'block';
        editCropModal.hide();
    });
}

var changePhotoBtn = document.getElementById('edit_changePhotoBtn');
if (changePhotoBtn) {
    changePhotoBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('edit_photoInput').click();
    });
}

// ========================================
// TABLE FILTERING, SORTING, PAGINATION
// ========================================
function filterRoster() { currentPage = 1; applyTableState(); }
function changePerPage() { perPage = parseInt(document.getElementById('perPageSelect').value); currentPage = 1; applyTableState(); }
function sortByCol(col) {
    if (sortCol === col) { sortDir = sortDir === 'asc' ? 'desc' : 'asc'; } else { sortCol = col; sortDir = 'asc'; }
    applyTableState(); updateSortIcons();
}
function updateSortIcons() {
    document.querySelectorAll('th.sortable').forEach(th => {
        var icon = th.querySelector('.sort-icon i');
        if (!icon) return;
        if (th.dataset.col === sortCol) {
            icon.className = sortDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
            th.classList.add('sort-active');
        } else {
            icon.className = 'fas fa-sort';
            th.classList.remove('sort-active');
        }
    });
}
function applyTableState() {
    var searchTerm = document.getElementById('rosterSearch').value.toLowerCase();
    var statusFilter = document.getElementById('statusFilter').value;
    var activeFilter = document.getElementById('activeFilter').value;
    var allRows = Array.from(document.querySelectorAll('#rosterTableBody tr[data-status]'));

    filteredRows = allRows.filter(row => {
        var name = row.dataset.name || '';
        var empid = row.dataset.empid || '';
        var status = row.dataset.status || '';
        var active = row.dataset.active;
        var matchesSearch = name.includes(searchTerm) || empid.includes(searchTerm);
        var matchesStatus = !statusFilter || status === statusFilter;
        var matchesActive = activeFilter === '' || active === activeFilter;
        return matchesSearch && matchesStatus && matchesActive;
    });
    
    filteredRows.sort((a, b) => {
        var valA = a.dataset[sortCol] || '';
        var valB = b.dataset[sortCol] || '';
        if(sortCol === 'empid') return sortDir === 'asc' ? parseInt(valA) - parseInt(valB) : parseInt(valB) - parseInt(valA);
        return sortDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
    });
    
    allRows.forEach(r => r.style.display = 'none');
    var total = filteredRows.length;
    var start = (currentPage - 1) * perPage;
    var end = Math.min(start + perPage, total);
    filteredRows.forEach((row, idx) => {
        if(idx >= start && idx < end) { row.style.display = ''; document.getElementById('rosterTableBody').appendChild(row); }
    });
    document.getElementById('recordCount').innerHTML = 'Showing <strong>' + Math.min(start+1, total) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> employee(s)';
    renderPagination(total);
}
function renderPagination(total) {
    var totalPages = Math.max(1, Math.ceil(total / perPage));
    var container = document.getElementById('paginationControls');
    container.innerHTML = '';
    if (totalPages <= 1) return;

    function makeBtn(html, page, disabled, active) {
        var btn = document.createElement('button');
        btn.innerHTML = html; btn.className = active ? 'active' : ''; btn.disabled = !!disabled;
        if (!disabled) btn.onclick = function() { currentPage = page; applyTableState(); };
        return btn;
    }
    function makeEllipsis() {
        var span = document.createElement('span'); span.textContent = '…'; span.style.cssText = 'padding: 0 4px; color: var(--text-medium); font-size: var(--text-sm); line-height: 32px;';
        return span;
    }

    container.appendChild(makeBtn('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false));
    if (totalPages <= 7) {
        for (var i = 1; i <= totalPages; i++) container.appendChild(makeBtn(i, i, false, i === currentPage));
    } else {
        var shown = new Set([1, totalPages, currentPage]);
        if (currentPage > 1) shown.add(currentPage - 1);
        if (currentPage < totalPages) shown.add(currentPage + 1);

        var sorted = Array.from(shown).sort(function(a, b) { return a - b; });
        var prev = 0;
        sorted.forEach(function(p) {
            if (prev && p - prev > 1) container.appendChild(makeEllipsis());
            container.appendChild(makeBtn(p, p, false, p === currentPage));
            prev = p;
        });
    }
    container.appendChild(makeBtn('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false));
}

// ========================================
// MAINTENANCE & REPORT GEN
// ========================================
function generateEmployeeReport() {
    if (!currentEmployeeId) { showAlert('warning', 'No employee selected.'); return; }
    window.open(BASE_URL + 'includes/generative/generate_employee_checklist_report.php?employeeId=' + currentEmployeeId, '_blank');
}

function openRosterMaintenance(equipmentId, typeString, brand, serial) {
    var owner = document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Current Employee';
    var location = document.querySelector('.profile-badges-group .status-badge') ? document.querySelector('.profile-badges-group .status-badge').innerText : 'Assigned Location';
    openMaintenanceModal({ equipmentId: equipmentId, equipmentType: typeString, typeName: typeString, brand: brand, serial: serial, owner: owner, location: location });
}

// ========================================
// FLOATING ACTION BUTTON
// ========================================
function showMaintenanceFab(show) {
    var wrapper = document.getElementById('fabMaintenanceWrapper');
    if (!wrapper) return;
    wrapper.setAttribute('data-hidden', show ? 'false' : 'true');
    if (!show) toggleFabPanel(false);
}

function toggleFabPanel(forceState) {
    var panel = document.getElementById('fabEquipmentPanel');
    if (!panel) return;
    var shouldOpen = typeof forceState === 'boolean' ? forceState : !panel.classList.contains('open');
    panel.classList.toggle('open', shouldOpen);
}

function buildFabEquipmentList(data) {
    var list = document.getElementById('fabEquipmentList');
    var badge = document.getElementById('fabEquipmentCount');
    if (!list) return;

    currentEmployeeEquipment = [];
    var html = '';

    if (data.systemUnits && data.systemUnits.length) {
        data.systemUnits.forEach(function(item) { currentEmployeeEquipment.push({ id: item.systemunitId, type: 'systemunit', typeName: 'System Unit', brand: item.systemUnitBrand, serial: item.systemUnitSerial, icon: 'desktop' }); });
    }
    if (data.allinones && data.allinones.length) {
        data.allinones.forEach(function(item) { currentEmployeeEquipment.push({ id: item.allinoneId, type: 'allinone', typeName: 'All-in-One PC', brand: item.allinoneBrand, serial: item.allinoneSerial || 'N/A', icon: 'computer' }); });
    }
    if (data.monitors && data.monitors.length) {
        data.monitors.forEach(function(item) { currentEmployeeEquipment.push({ id: item.monitorId, type: 'monitor', typeName: 'Monitor', brand: item.monitorBrand, serial: item.monitorSerial, icon: 'tv' }); });
    }
    if (data.printers && data.printers.length) {
        data.printers.forEach(function(item) { currentEmployeeEquipment.push({ id: item.printerId, type: 'printer', typeName: 'Printer', brand: item.printerBrand, serial: item.printerSerial, icon: 'print' }); });
    }
    if (data.other && data.other.length) {
        data.other.forEach(function(item) { currentEmployeeEquipment.push({ id: item.otherEquipmentId, type: item.equipmentType || 'other', typeName: item.equipmentType || 'Other', brand: item.brand, serial: item.serialNumber, icon: 'server' }); });
    }

    if (badge) badge.textContent = currentEmployeeEquipment.length;

    if (currentEmployeeEquipment.length === 0) {
        html = '<div class="fab-panel-empty"><i class="fas fa-box-open"></i>No equipment assigned to this employee.</div>';
    } else {
        currentEmployeeEquipment.forEach(function(eq, idx) {
            var iconClass = 'type-other';
            if (eq.type === 'systemunit') iconClass = 'type-systemunit';
            else if (eq.type === 'allinone') iconClass = 'type-allinone';
            else if (eq.type === 'monitor') iconClass = 'type-monitor';
            else if (eq.type === 'printer') iconClass = 'type-printer';

            html += '<div class="fab-eq-item" onclick="fabStartMaintenance(' + idx + ')">'
                + '<div class="fab-eq-icon ' + iconClass + '"><i class="fas fa-' + eq.icon + '"></i></div>'
                + '<div class="fab-eq-info">'
                + '  <div class="fab-eq-name">' + (eq.brand || 'Unknown') + '</div>'
                + '  <div class="fab-eq-serial">' + eq.typeName + ' &bull; ' + (eq.serial || 'N/A') + '</div>'
                + '</div>'
                + '<div class="fab-eq-go"><i class="fas fa-wrench"></i></div>'
                + '</div>';
        });
    }
    list.innerHTML = html;
    var footer = document.getElementById('fabPanelFooter');
    if (footer) footer.style.display = currentEmployeeEquipment.length > 0 ? '' : 'none';
}

function fabStartMaintenance(index) {
    var eq = currentEmployeeEquipment[index];
    if (!eq) return;
    toggleFabPanel(false);
    openRosterMaintenance(eq.id, eq.type, eq.brand, eq.serial);
}

function fabPerformAll() {
    if (currentEmployeeEquipment.length === 0) return;
    fabMaintenanceQueue = currentEmployeeEquipment.slice();
    fabQueueIndex = 0;
    window._fabQueueActive = true;
    toggleFabPanel(false);
    fabOpenNext();
}

function fabOpenNext() {
    if (fabQueueIndex >= fabMaintenanceQueue.length) {
        window._fabQueueActive = false;
        showAlert('success', 'All equipment maintenance completed!');
        if (currentEmployeeId) viewEmployee(currentEmployeeId);
        return;
    }
    var eq = fabMaintenanceQueue[fabQueueIndex];
    fabQueueIndex++;
    openRosterMaintenance(eq.id, eq.type, eq.brand, eq.serial);
}

document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('fabMaintenanceWrapper');
    if (!wrapper) return;
    if (!wrapper.contains(e.target)) toggleFabPanel(false);
});

// ========================================
// ARCHIVE / RESTORE
// ========================================
function archiveEmployee(employeeId, fullName) {
    Alerts.confirmAction({
        title: 'Archive ' + fullName + '?',
        message: 'All assigned equipment will be unassigned.',
        confirmText: 'Yes, Archive',
        type: 'warning',
        icon: 'fa-box-archive',
        onConfirm: function() {
            var formData = new FormData();
            formData.append('action', 'archive');
            formData.append('employeeId', employeeId);

            fetch(BASE_URL + 'ajax/archive_employee.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    Alerts.success(data.message);
                    setTimeout(function() { reloadCurrentPage(); }, 800);
                } else {
                    Alerts.error(data.message || 'Failed to archive employee.');
                }
            })
            .catch(function(err) {
                console.error('Archive error:', err);
                Alerts.error('An error occurred while archiving the employee.');
            });
        }
    });
}

function restoreEmployee(employeeId, fullName) {
    Alerts.confirmAction({
        title: 'Restore ' + fullName + '?',
        message: 'This employee will be restored from the archive.',
        confirmText: 'Yes, Restore',
        type: 'primary',
        icon: 'fa-rotate-left',
        onConfirm: function() {
            var formData = new FormData();
            formData.append('action', 'restore');
            formData.append('employeeId', employeeId);

            fetch(BASE_URL + 'ajax/archive_employee.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    Alerts.success(data.message);
                    setTimeout(function() { reloadCurrentPage(); }, 800);
                } else {
                    Alerts.error(data.message || 'Failed to restore employee.');
                }
            })
            .catch(function(err) {
                console.error('Restore error:', err);
                Alerts.error('An error occurred while restoring the employee.');
            });
        }
    });
}

function filterArchivedTable() {
    var searchTerm = (document.getElementById('archivedSearch') ? document.getElementById('archivedSearch').value : '').toLowerCase();
    var rows = document.querySelectorAll('#archivedTableBody tr');
    var counter = 1;
    rows.forEach(function(row) {
        var name = row.dataset.name || '';
        var empid = row.dataset.empid || '';
        var match = name.includes(searchTerm) || empid.includes(searchTerm);
        row.style.display = match ? '' : 'none';
        var counterCell = row.querySelector('td.row-counter');
        if (counterCell && match) counterCell.textContent = counter++;
    });
}