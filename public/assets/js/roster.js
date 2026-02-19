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
                                onclick="openMaintenanceModal(${id}, '${safeType}', '${safeBrand}', '${safeSerial}')">
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
    document.getElementById('recordCount').textContent = `Showing ${Math.min(start+1, total)}–${end} of ${total}`;
    renderPagination(total);
}

function renderPagination(total) {
    var totalPages = Math.ceil(total / perPage);
    var container = document.getElementById('paginationControls');
    container.innerHTML = '';
    if (totalPages <= 1) return;
    
    var prevBtn = document.createElement('button');
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { currentPage--; applyTableState(); };
    container.appendChild(prevBtn);
    
    var span = document.createElement('span');
    span.textContent = ` Page ${currentPage} of ${totalPages} `;
    container.appendChild(span);
    
    var nextBtn = document.createElement('button');
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { currentPage++; applyTableState(); };
    container.appendChild(nextBtn);
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

var EQUIPMENT_TYPE_MAP = {};

fetchEquipmentTypeMap();

function fetchEquipmentTypeMap() {
    fetch(`${BASE_URL}ajax/get_equipment_types.php`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                res.data.forEach(type => {
                    const key = type.typeName.toLowerCase().replace(/[^a-z0-9]/g, '');
                    EQUIPMENT_TYPE_MAP[key] = type.typeId;
                });
            }
        })
        .catch(err => console.error("Failed to load equipment types:", err));
}

function openMaintenanceModal(equipmentId, typeString, brand, serial) {
    // 1. Show Modal & Loading State
    const modalEl = document.getElementById('maintenanceModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const container = document.getElementById('modal-maintenance-container');
    const loader = document.getElementById('modal-maintenance-loader');
    
    loader.style.display = 'block';
    container.innerHTML = '';

    // 2. Resolve Type ID
    const cleanType = typeString.toLowerCase().replace(/[^a-z0-9]/g, '');
    const typeId = EQUIPMENT_TYPE_MAP[cleanType];

    if (!typeId) {
        // Retry logic if map isn't loaded yet
        if (Object.keys(EQUIPMENT_TYPE_MAP).length === 0) {
            setTimeout(() => openMaintenanceModal(equipmentId, typeString, brand, serial), 500);
            return;
        }
        container.innerHTML = `<div class="alert alert-danger m-3">Unknown Equipment Type: ${typeString}</div>`;
        loader.style.display = 'none';
        return;
    }

    // 3. Fetch Templates & Check Schedule
    Promise.all([
        fetch(`${BASE_URL}ajax/manage_templates.php?action=list_by_type&type=${typeId}`).then(r => r.json()),
        fetch(`${BASE_URL}ajax/get_maintenance_assets.php?type=${typeId}`).then(r => r.json())
    ]).then(([tmplRes, assetRes]) => {
        loader.style.display = 'none';

        // Check if we have templates
        if (!tmplRes.success || tmplRes.data.length === 0) {
            container.innerHTML = `<div class="alert alert-warning m-4 text-center">No checklist templates found for <strong>${typeString}</strong>.<br>Please create one in "Maintenance Templates" first.</div>`;
            return;
        }

        // Build Template Options
        let optionsHtml = tmplRes.data.map(t => 
            `<option value="${t.templateId}">${t.templateName} (${t.frequency})</option>`
        ).join('');

        // Find existing schedule (if any)
        const asset = assetRes.data ? assetRes.data.find(a => a.equipmentId == equipmentId) : null;

        // 4. Render "Selection Stage" UI
        container.innerHTML = `
            <div class="row justify-content-center p-4">
                <div class="col-md-8 text-center">
                    <div class="bg-primary-xlight text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Maintenance Selection</h5>
                    <p class="text-muted small mb-4">Select the checklist template to use for this <strong>${brand}</strong>.</p>
                    
                    <div class="card bg-light border-0 p-3 mb-4 text-start">
                         <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">1. Asset Details</label>
                            <input type="text" class="form-control" value="${brand} - ${serial}" readonly disabled>
                         </div>
                         <div class="mb-0">
                            <label class="form-label fw-bold small text-muted text-uppercase">2. Select Checklist Template</label>
                            <select class="form-select form-select-lg shadow-sm border-primary" id="rosterTemplateSelect">
                                ${optionsHtml}
                            </select>
                         </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" id="btnStartRosterMaint">
                            Start Inspection <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // 5. Attach "Start" Click Handler
        document.getElementById('btnStartRosterMaint').onclick = function() {
            const selectedTemplateId = document.getElementById('rosterTemplateSelect').value;
            
            // Auto-create schedule if missing
            if (!asset) {
                container.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Initializing schedule...</p></div>`;
                
                fetch(`${BASE_URL}ajax/quick_add_schedule.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ equipmentId: equipmentId, equipmentType: typeId })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        launchConductor(res.scheduleId, selectedTemplateId, typeString, brand, serial);
                    } else {
                        container.innerHTML = `<div class="alert alert-danger">Failed to create schedule: ${res.message}</div>`;
                    }
                });
            } else {
                launchConductor(asset.scheduleId, selectedTemplateId, typeString, brand, serial);
            }
        };

    }).catch(err => {
        container.innerHTML = `<div class="alert alert-danger m-3">Error: ${err.message}</div>`;
        loader.style.display = 'none';
    });
}

// Helper to launch the checklist
function launchConductor(scheduleId, templateId, typeString, brand, serial) {
    currentAssetData = {
        owner: document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Current Employee',
        location: 'Assigned Location',
        serial: serial,
        brand: brand,
        type: typeString
    };
    startMaintenanceSequence(scheduleId, templateId, 'modal-maintenance-container');
}
// Helper to actually switch to the checklist
function launchConductor(scheduleId, templateId, typeString, brand, serial) {
    // Set global data for the conductor
    currentAssetData = {
        owner: document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Current Employee',
        location: 'Assigned Location',
        serial: serial,
        brand: brand,
        type: typeString
    };
    
    // Call the Conductor (from maintenance-conductor.js)
    startMaintenanceSequence(scheduleId, templateId, 'modal-maintenance-container');
}
function autoCreateScheduleAndProceed(equipmentId, typeId, typeString, brand, serial) {
    const loader = document.getElementById('modal-maintenance-loader');
    loader.querySelector('p').innerText = "Initializing new maintenance schedule...";

    fetch(`${BASE_URL}ajax/quick_add_schedule.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ equipmentId: equipmentId, equipmentType: typeId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            loadAndStartMaintenance(res.scheduleId, typeId, typeString, brand, serial);
        } else {
            document.getElementById('modal-maintenance-container').innerHTML = 
                `<div class="alert alert-danger m-3">Failed to create schedule: ${res.message}</div>`;
            loader.style.display = 'none';
        }
    });
}

function loadAndStartMaintenance(scheduleId, typeId, typeString, brand, serial) {
    currentAssetData = {
        owner: document.querySelector('.profile-name-large') ? document.querySelector('.profile-name-large').innerText : 'Current Employee',
        location: document.querySelector('.profile-badges-group .status-badge') ? 'Assigned Location' : 'N/A',
        serial: serial,
        brand: brand,
        type: typeString
    };

    fetch(`${BASE_URL}ajax/manage_templates.php?action=list_by_type&type=${typeId}`)
        .then(r => r.json())
        .then(tmplRes => {
            if (tmplRes.success && tmplRes.data.length > 0) {
                const templateId = tmplRes.data[0].templateId; // Pick first template
                
                document.getElementById('modal-maintenance-loader').style.display = 'none';
                
                // LAUNCH THE CONDUCTOR
                startMaintenanceSequence(scheduleId, templateId, 'modal-maintenance-container');
            } else {
                document.getElementById('modal-maintenance-container').innerHTML = 
                    `<div class="alert alert-warning m-3">No Checklist Template found for this equipment type.</div>`;
                document.getElementById('modal-maintenance-loader').style.display = 'none';
            }
        });
}