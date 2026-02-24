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
var fabMaintenanceQueue = [];
var fabQueueIndex = 0;

// Table State
var currentPage = 1;
var perPage = 25;
var sortCol = 'name';
var sortDir = 'asc';
var filteredRows = [];

var LocationHierarchy = {
    /**
     * Populate sections based on selected division
     */
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

    /**
     * Populate units based on selected parent (can be section OR division)
     */
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

    /**
     * Setup location dropdowns based on current location
     * Handles all hierarchy variations:
     * 1. Unit > Section > Division
     * 2. Unit > Division (no section)
     * 3. Section > Division (no unit)
     * 4. Division only
     */
    setupLocationDropdowns: function(locationId, divisionSelectId = 'edit_division', sectionSelectId = 'edit_section', unitSelectId = 'edit_unit') {
        var divSelect = document.getElementById(divisionSelectId);
        var secSelect = document.getElementById(sectionSelectId);
        var unitSelect = document.getElementById(unitSelectId);

        // Find the location in our data
        var unit = unitsData.find(u => u.location_id == locationId);
        var section = sectionsData.find(s => s.location_id == locationId);
        
        var targetDiv = '', targetSec = '', targetUnit = '';

        if (unit) {
            // Location is a Unit
            targetUnit = unit.location_id;
            
            // Check if unit's parent is a Section
            var parentIsSection = sectionsData.find(s => s.location_id == unit.parent_location_id);
            
            if (parentIsSection) {
                // Hierarchy: Unit > Section > Division
                targetSec = parentIsSection.location_id;
                targetDiv = parentIsSection.parent_location_id;
            } else {
                // Hierarchy: Unit > Division (no section)
                targetDiv = unit.parent_location_id;
            }
        } else if (section) {
            // Location is a Section
            targetSec = section.location_id;
            targetDiv = section.parent_location_id;
        } else {
            // Location is a Division
            targetDiv = locationId;
        }

        // Set division first
        divSelect.value = targetDiv;
        this.populateSections(targetDiv, sectionSelectId);
        
        // Set section if exists
        if (targetSec) {
            secSelect.value = targetSec;
            secSelect.disabled = false;
            this.populateUnits(targetSec, unitSelectId);
        } else {
            secSelect.value = "";
            // Try to populate units directly from division
            this.populateUnits(targetDiv, unitSelectId);
        }
        
        // Set unit if exists
        if (targetUnit) {
            unitSelect.value = targetUnit;
            unitSelect.disabled = false;
        }
    },

    /**
     * Get the final location ID for saving
     * Priority: Unit > Section > Division
     */
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
    
    // Render Personal Information (with integrated header)
    renderPersonalInformation(emp);
    
    // Render Employment Details
    renderEmploymentDetails(emp);
    
    // Render Equipment
    renderEquipment(data);
    
    // Render Printers
    renderPrinters(data);

    // Render Software Licenses
    renderSoftwareLicenses(data);

}

function renderPersonalInformation(emp) {
    var container = document.getElementById('profile-personal-info');
    var age = calculateAge(emp.birthDate);
    
    // Status class for badge
    var statusClass = 'status-permanent';
    if(emp.employmentStatus === 'Casual') statusClass = 'status-casual';
    if(emp.employmentStatus === 'Job Order') statusClass = 'status-job-order';
    if(emp.employmentStatus === 'Contract of Service') statusClass = 'status-job-order';
    
    // Active status
    var isActive = emp.is_active == 1;
    
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
    
    // Build location breadcrumb
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
    
    // Count equipment
    var systemUnitsCount = data.systemUnits ? data.systemUnits.length : 0;
    var allinonesCount = data.allinones ? data.allinones.length : 0;
    var monitorsCount = data.monitors ? data.monitors.length : 0;
    var otherCount = data.other ? data.other.length : 0;
    var totalCount = systemUnitsCount + allinonesCount + monitorsCount + otherCount;
    
    // Render counts
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
    
    // Render equipment cards
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
            // Use the actual type from DB (e.g., "Laptop", "Projector") if available
            // Fallback to 'other' if missing
            const realType = item.equipmentType || 'other'; 
            const displayType = item.equipmentType || 'Other Equipment';
            
            equipmentHtml += createEquipmentCard(
                displayType, 
                item.brand, 
                item.serialNumber, 
                'server', 
                item.otherEquipmentId, 
                realType // Pass "Laptop" so TYPE_MAPPING['laptop'] works
            );
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
    // Escape strings to prevent errors with quotes
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
                                onclick="viewEquipmentDetails('${safeType}', '${safeBrand}', '${safeSerial}', '${icon}')">
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

function viewEquipmentDetails(type, brand, serial, icon) {
    document.getElementById('detailBrand').innerText = brand;
    document.getElementById('detailSerial').innerText = "SN: " + serial;
    document.getElementById('detailType').innerText = type.toUpperCase();
    document.getElementById('detailIcon').className = `fas fa-${icon} fa-3x text-secondary`;
    
    // Grab owner info from the profile header
    const owner = document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Unknown';
    const location = document.querySelector('.profile-badges-group .status-badge') ? 'Current Assignment' : 'N/A';
    
    document.getElementById('detailOwner').innerText = owner;
    document.getElementById('detailLocation').innerText = location;

    var modal = new bootstrap.Modal(document.getElementById('equipmentDetailsModal'));
    modal.show();
}

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
    
    // Setup location dropdowns using the extracted hierarchy manager
    LocationHierarchy.setupLocationDropdowns(employee.location_id);
    
    // Photo Logic
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
    
    // Get final location using hierarchy manager
    var finalLoc = LocationHierarchy.getFinalLocationId();
                   
    if(!finalLoc) { 
        showAlert('warning', "Please select a location assignment"); 
        return; 
    }
    formData.set('locationId', finalLoc);

    fetch(`${BASE_URL}ajax/update_employee.php`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showAlert('success', 'Employee updated successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(err => showAlert('danger', 'Error updating employee: ' + err));
}

// ========================================
// CROPPER LOGIC
// ========================================

    if (typeof bootstrap !== 'undefined') {
        var cropEl = document.getElementById('edit_cropModal');
        if(cropEl) editCropModal = new bootstrap.Modal(cropEl);
    }
    
    // Setup location dropdown event listeners
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
    
    // Initial Filter
    applyTableState();
    updateSortIcons();

// Cropper modal events
var cropModalEl = document.getElementById('edit_cropModal');
if (cropModalEl) {
    cropModalEl.addEventListener('shown.bs.modal', function () {
        var imageToCrop = document.getElementById('edit_imageToCrop');
        if (!editCropper && imageToCrop.src) {
            editCropper = new Cropper(imageToCrop, { 
                aspectRatio: 1, 
                viewMode: 2, 
                autoCropArea: 0.8 
            });
        }
    });
    
    cropModalEl.addEventListener('hidden.bs.modal', function () {
        if (editCropper) { 
            editCropper.destroy(); 
            editCropper = null; 
        }
    });
}

// Photo upload box click
var photoUploadBox = document.getElementById('edit_photoUploadBox');
if (photoUploadBox) {
    photoUploadBox.addEventListener('click', function(e) {
        if (!e.target.closest('.photo-preview.active')) {
            document.getElementById('edit_photoInput').click();
        }
    });
}

// Photo input change
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

// Crop button
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

// Change photo button
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
function filterRoster() { 
    currentPage = 1; 
    applyTableState(); 
}

function changePerPage() { 
    perPage = parseInt(document.getElementById('perPageSelect').value); 
    currentPage = 1; 
    applyTableState(); 
}

function sortByCol(col) {
    if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else { 
        sortCol = col; 
        sortDir = 'asc'; 
    }
    applyTableState();
    updateSortIcons();
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
        if(sortCol === 'empid') {
            return sortDir === 'asc' ? parseInt(valA) - parseInt(valB) : parseInt(valB) - parseInt(valA);
        }
        return sortDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
    });
    
    allRows.forEach(r => r.style.display = 'none');
    var total = filteredRows.length;
    var start = (currentPage - 1) * perPage;
    var end = Math.min(start + perPage, total);
    filteredRows.forEach((row, idx) => {
        if(idx >= start && idx < end) {
            row.style.display = '';
            document.getElementById('rosterTableBody').appendChild(row);
        }
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
        btn.innerHTML = html;
        btn.className = active ? 'active' : '';
        btn.disabled = !!disabled;
        if (!disabled) {
            btn.onclick = function() { currentPage = page; applyTableState(); };
        }
        return btn;
    }

    function makeEllipsis() {
        var span = document.createElement('span');
        span.textContent = '…';
        span.style.cssText = 'padding: 0 4px; color: var(--text-medium); font-size: var(--text-sm); line-height: 32px;';
        return span;
    }

    // Prev button
    container.appendChild(makeBtn('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false));

    // Page number buttons with smart ellipsis (matches equipment utils.js pattern)
    var pages = [];
    if (totalPages <= 7) {
        for (var i = 1; i <= totalPages; i++) pages.push(i);
        pages.forEach(function(p) {
            container.appendChild(makeBtn(p, p, false, p === currentPage));
        });
    } else {
        // Always show first, last, current, and neighbours
        var shown = new Set([1, totalPages, currentPage]);
        if (currentPage > 1) shown.add(currentPage - 1);
        if (currentPage < totalPages) shown.add(currentPage + 1);

        var sorted = Array.from(shown).sort(function(a, b) { return a - b; });
        var prev = 0;
        sorted.forEach(function(p) {
            if (prev && p - prev > 1) {
                container.appendChild(makeEllipsis());
            }
            container.appendChild(makeBtn(p, p, false, p === currentPage));
            prev = p;
        });
    }

    // Next button
    container.appendChild(makeBtn('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false));
}

// ========================================
// TOGGLE ACTIVE STATUS
// ========================================
function toggleActiveStatus(employeeId, currentStatus, buttonElement) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    var action = newStatus == 1 ? 'activate' : 'deactivate';
    
    if (!confirm(`Are you sure you want to ${action} this employee?`)) {
        return;
    }
    
    fetch(`${BASE_URL}ajax/toggle_employee_status.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ employeeId: employeeId, isActive: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Employee ${action}d successfully!`);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert('danger', data.message || `Failed to ${action} employee`);
        }
    })
    .catch(err => {
        showAlert('danger', 'Error: ' + err);
    });
}

// Equipment type map now lives in maintenance-conductor.js (EQUIPMENT_TYPE_MAP)
// Just ensure it's loaded when roster page initializes
ensureEquipmentTypeMap();

// ========================================
// MAINTENANCE — thin wrapper around shared openMaintenanceModal()
// ========================================
// GENERATE EMPLOYEE CHECKLIST REPORT
// ========================================
function generateEmployeeReport() {
    if (!currentEmployeeId) {
        showAlert('warning', 'No employee selected.');
        return;
    }
    window.open(
        BASE_URL + 'includes/generative/generate_employee_checklist_report.php?employeeId=' + currentEmployeeId,
        '_blank'
    );
}

// (openMaintenanceModal lives in maintenance-conductor.js, loaded globally)
// ========================================
function openRosterMaintenance(equipmentId, typeString, brand, serial) {
    var owner = document.querySelector('.profile-name-large')
        ? document.querySelector('.profile-name-large').innerText : 'Current Employee';
    var location = document.querySelector('.profile-badges-group .status-badge')
        ? document.querySelector('.profile-badges-group .status-badge').innerText : 'Assigned Location';

    openMaintenanceModal({
        equipmentId:   equipmentId,
        equipmentType: typeString,   // string like "systemunit" — conductor resolves to numeric ID
        typeName:      typeString,
        brand:         brand,
        serial:        serial,
        owner:         owner,
        location:      location
    });
}

// ========================================
// FLOATING ACTION BUTTON — Maintenance FAB
// ========================================

/**
 * Show or hide the maintenance FAB
 */
function showMaintenanceFab(show) {
    var wrapper = document.getElementById('fabMaintenanceWrapper');
    if (!wrapper) return;
    wrapper.setAttribute('data-hidden', show ? 'false' : 'true');
    // Also close panel when hiding
    if (!show) toggleFabPanel(false);
}

/**
 * Toggle the equipment fly-out panel
 */
function toggleFabPanel(forceState) {
    var panel = document.getElementById('fabEquipmentPanel');
    if (!panel) return;
    var shouldOpen = typeof forceState === 'boolean' ? forceState : !panel.classList.contains('open');
    panel.classList.toggle('open', shouldOpen);
}

/**
 * Build the equipment list shown in the FAB panel from profile data
 */
function buildFabEquipmentList(data) {
    var list = document.getElementById('fabEquipmentList');
    var badge = document.getElementById('fabEquipmentCount');
    if (!list) return;

    currentEmployeeEquipment = [];
    var html = '';

    // Gather all equipment into a flat list
    if (data.systemUnits && data.systemUnits.length) {
        data.systemUnits.forEach(function(item) {
            currentEmployeeEquipment.push({
                id: item.systemunitId, type: 'systemunit', typeName: 'System Unit',
                brand: item.systemUnitBrand, serial: item.systemUnitSerial, icon: 'desktop'
            });
        });
    }
    if (data.allinones && data.allinones.length) {
        data.allinones.forEach(function(item) {
            currentEmployeeEquipment.push({
                id: item.allinoneId, type: 'allinone', typeName: 'All-in-One PC',
                brand: item.allinoneBrand, serial: item.allinoneSerial || 'N/A', icon: 'computer'
            });
        });
    }
    if (data.monitors && data.monitors.length) {
        data.monitors.forEach(function(item) {
            currentEmployeeEquipment.push({
                id: item.monitorId, type: 'monitor', typeName: 'Monitor',
                brand: item.monitorBrand, serial: item.monitorSerial, icon: 'tv'
            });
        });
    }
    if (data.printers && data.printers.length) {
        data.printers.forEach(function(item) {
            currentEmployeeEquipment.push({
                id: item.printerId, type: 'printer', typeName: 'Printer',
                brand: item.printerBrand, serial: item.printerSerial, icon: 'print'
            });
        });
    }
    if (data.other && data.other.length) {
        data.other.forEach(function(item) {
            var realType = item.equipmentType || 'other';
            currentEmployeeEquipment.push({
                id: item.otherEquipmentId, type: realType, typeName: item.equipmentType || 'Other',
                brand: item.brand, serial: item.serialNumber, icon: 'server'
            });
        });
    }

    // Update badge count
    if (badge) badge.textContent = currentEmployeeEquipment.length;

    // Build list HTML
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

    // Show/hide Perform All footer
    var footer = document.getElementById('fabPanelFooter');
    if (footer) {
        footer.style.display = currentEmployeeEquipment.length > 0 ? '' : 'none';
    }
}

/**
 * Start maintenance for an equipment item from the FAB panel
 */
function fabStartMaintenance(index) {
    var eq = currentEmployeeEquipment[index];
    if (!eq) return;
    toggleFabPanel(false);
    openRosterMaintenance(eq.id, eq.type, eq.brand, eq.serial);
}

/**
 * Perform All — queue every equipment item and walk through them one by one
 */
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
        // Refresh profile to reflect updates
        if (currentEmployeeId) viewEmployee(currentEmployeeId);
        return;
    }
    var eq = fabMaintenanceQueue[fabQueueIndex];
    fabQueueIndex++;
    var remaining = fabMaintenanceQueue.length - fabQueueIndex;
    console.log('[FAB Queue] Starting ' + fabQueueIndex + ' of ' + fabMaintenanceQueue.length + ' — ' + eq.typeName + ' ' + (eq.brand || ''));
    openRosterMaintenance(eq.id, eq.type, eq.brand, eq.serial);
}

// Close FAB panel when clicking outside
document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('fabMaintenanceWrapper');
    if (!wrapper) return;
    if (!wrapper.contains(e.target)) {
        toggleFabPanel(false);
    }
});