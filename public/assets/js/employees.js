var cropper = null;
var currentImageFile = null;


var photoInput = document.getElementById('photoInput');
var photoUploadBox = document.getElementById('photoUploadBox');
var uploadPlaceholder = document.getElementById('uploadPlaceholder');
var photoPreview = document.getElementById('photoPreview');
var previewImage = document.getElementById('previewImage');
var changePhotoBtn = document.getElementById('changePhotoBtn');
var imageToCrop = document.getElementById('imageToCrop');
var cropButton = document.getElementById('cropButton');
var croppedImageInput = document.getElementById('croppedImage');

// Form elements
var divisionSelect = document.getElementById('division');
var sectionSelect = document.getElementById('section');
var unitSelect = document.getElementById('unit');
var locationIdInput = document.getElementById('locationId');
var employeeForm = document.getElementById('employeeForm');

var cropModal;
if (typeof bootstrap !== 'undefined') {
    cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
}


var cropModalElement = document.getElementById('cropModal');
if (cropModalElement) {
    cropModalElement.addEventListener('shown.bs.modal', function () {
        if (!cropper && imageToCrop.src) {
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }
    });

    cropModalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });
}


if (photoUploadBox) {
    photoUploadBox.addEventListener('click', function(e) {
        if (!e.target.closest('.photo-preview.active')) {
            photoInput.click();
        }
    });
}


photoInput.addEventListener('change', function(e) {
    var file = e.target.files[0];
    
    if (file) {
        // Validate file type
        var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG or PNG).');
            photoInput.value = '';
            return;
        }
        
        // Validate file size (5MB max)
        if (file.size > 20 * 1024 * 1024) {
            alert('Image file size must not exceed 20MB.');
            photoInput.value = '';
            return;
        }
        
        currentImageFile = file;
        
        // Read file and show in crop modal
        var reader = new FileReader();
        reader.onload = function(event) {
            imageToCrop.src = event.target.result;
            cropModal.show();
        };
        reader.readAsDataURL(file);
    }
});

/**
 * Handle crop button click
 */
cropButton.addEventListener('click', function() {
    if (!cropper) {
        alert('No image to crop');
        return;
    }
    
    // Get cropped canvas
    var canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Convert to base64
    var croppedBase64 = canvas.toDataURL('image/jpeg', 0.9);
    
    // Store cropped image data in hidden input
    croppedImageInput.value = croppedBase64;
    
    // Show preview
    previewImage.src = croppedBase64;
    photoPreview.classList.add('active');
    uploadPlaceholder.style.display = 'none';
    changePhotoBtn.style.display = 'block';
    
    // Close modal
    cropModal.hide();
    
    // Reset file input
    photoInput.value = '';
});

/**
 * Handle change photo button
 */
changePhotoBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    photoInput.click();
});

divisionSelect.addEventListener('change', function() {
    var divisionId = this.value;
    
    // Reset dependent dropdowns
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    unitSelect.innerHTML = '<option value="">Select Unit</option>';
    sectionSelect.disabled = true;
    unitSelect.disabled = true;
    
    if (divisionId) {
        // Set location ID to division
        locationIdInput.value = divisionId;
        
        // Filter sections by parent division
        var divisionSections = sectionsData.filter(s => s.parent_location_id == divisionId);
        
        // Filter units that are directly under this division (no section parent)
        var divisionUnits = unitsData.filter(u => u.parent_location_id == divisionId);
        
        // Enable section dropdown if there are sections
        if (divisionSections.length > 0) {
            sectionSelect.disabled = false;
            divisionSections.forEach(section => {
                var option = document.createElement('option');
                option.value = section.location_id;
                option.textContent = section.location_name;
                sectionSelect.appendChild(option);
            });
        }
        
        // Enable unit dropdown if there are units directly under division
        if (divisionUnits.length > 0) {
            unitSelect.disabled = false;
            divisionUnits.forEach(unit => {
                var option = document.createElement('option');
                option.value = unit.location_id;
                option.textContent = unit.location_name;
                unitSelect.appendChild(option);
            });
        }
    } else {
        locationIdInput.value = '';
    }
});

sectionSelect.addEventListener('change', function() {
    var sectionId = this.value;
    var divisionId = divisionSelect.value;
    
    // Get units that are directly under the division (if any)
    var divisionUnits = unitsData.filter(u => u.parent_location_id == divisionId);
    
    // Reset unit dropdown with division units first
    unitSelect.innerHTML = '<option value="">Select Unit</option>';
    
    if (sectionId) {
        // Set location ID to section
        locationIdInput.value = sectionId;
        
        // Filter units by parent section
        var sectionUnits = unitsData.filter(u => u.parent_location_id == sectionId);
        
        // Add both division units and section units to the dropdown
        var allUnits = [...divisionUnits, ...sectionUnits];
        
        if (allUnits.length > 0) {
            unitSelect.disabled = false;
            allUnits.forEach(unit => {
                var option = document.createElement('option');
                option.value = unit.location_id;
                option.textContent = unit.location_name;
                unitSelect.appendChild(option);
            });
        } else {
            unitSelect.disabled = true;
        }
    } else {
        // If section is cleared, show only division units
        if (divisionUnits.length > 0) {
            unitSelect.disabled = false;
            divisionUnits.forEach(unit => {
                var option = document.createElement('option');
                option.value = unit.location_id;
                option.textContent = unit.location_name;
                unitSelect.appendChild(option);
            });
        } else {
            unitSelect.disabled = true;
        }
        // Set back to division
        locationIdInput.value = divisionId;
    }
});

unitSelect.addEventListener('change', function() {
    var unitId = this.value;
    
    if (unitId) {
        // Set location ID to unit
        locationIdInput.value = unitId;
    } else {
        // If unit is cleared, set back to section
        locationIdInput.value = sectionSelect.value;
    }
});

employeeForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    var submitBtn = document.querySelector('.btn-submit');
    var employeeId = this.employeeId.value;
    var firstName = this.firstName.value.trim();
    var lastName = this.lastName.value.trim();
    var position = this.position.value.trim();
    var birthDate = new Date(this.birthDate.value);
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var sex = this.sex.value;
    var employmentStatus = this.employmentStatus.value;
    var locationId = locationIdInput.value;
    
    // Validate Employee ID
    if (!employeeId || employeeId < 1) {
        alert('Please enter a valid Employee ID.');
        this.employeeId.focus();
        return;
    }
    
    // Validate First Name
    if (!firstName) {
        alert('First Name is required.');
        this.firstName.focus();
        return;
    }
    
    // Validate Last Name
    if (!lastName) {
        alert('Last Name is required.');
        this.lastName.focus();
        return;
    }
    
    // Validate Position
    if (!position) {
        alert('Position is required.');
        this.position.focus();
        return;
    }
    
    // Validate Sex
    if (!sex) {
        alert('Please select Sex.');
        this.sex.focus();
        return;
    }
    
    // Validate Employment Status
    if (!employmentStatus) {
        alert('Please select Employment Status.');
        this.employmentStatus.focus();
        return;
    }
    
    // Validate Birth Date
    if (!this.birthDate.value) {
        alert('Birth Date is required.');
        this.birthDate.focus();
        return;
    }
    
    if (isNaN(birthDate.getTime())) {
        alert('Please enter a valid Birth Date.');
        this.birthDate.focus();
        return;
    }
    
    // Check if birth date is in the future
    if (birthDate > today) {
        alert('Birth Date cannot be in the future.');
        this.birthDate.focus();
        return;
    }
    
    // Calculate age
    var age = today.getFullYear() - birthDate.getFullYear();
    var monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    // Validate minimum age
    if (age < 18) {
        alert('Employee must be at least 18 years old. Current age: ' + age + ' years.');
        this.birthDate.focus();
        return;
    }
    
    // Validate maximum age
    if (age > 100) {
        alert('Invalid birth date. Please check the date entered.');
        this.birthDate.focus();
        return;
    }
    
    // Validate location is selected
    if (!locationId) {
        alert('Please select at least a Division for the employee.');
        divisionSelect.focus();
        return;
    }
    
    // All validations passed - disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Employee...';
    
    // Send form data via AJAX
    var formData = new FormData(this);

    // Append equipment section data as JSON
    var eqData = collectEquipmentData();
    formData.append('equipmentData', JSON.stringify(eqData));
    
    fetch('../ajax/process_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display success message
            showMessage(data.message, 'success');
            
            // Reset form
            employeeForm.reset();
            resetPhotoUpload();
            document.querySelector('.btn-cancel').click();
            
            // Reload employees page within dashboard after 2 seconds
            setTimeout(() => {
                if (window.dashboardApp) {
                    window.dashboardApp.loadPage('employees', false);
                } else {
                    location.reload();
                }
            }, 2000);
        } else {
            // Display error message
            showMessage(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred: ' + error.message, 'danger');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Employee';
    });
});


function showMessage(message, type) {
    var messageDiv = document.querySelector('.alert') || document.createElement('div');
    messageDiv.className = 'alert alert-' + type;
    messageDiv.innerHTML = '<i class="fas fa-' + 
        (type === 'success' ? 'check-circle' : 'exclamation-triangle') + 
        '"></i><div>' + message + '</div>';
    
    // Insert message if it doesn't exist
    if (!document.querySelector('.alert')) {
        var formContainer = document.getElementById('employeeFormContainer');
        formContainer.parentElement.insertBefore(messageDiv, formContainer);
    } else {
        document.querySelector('.alert').replaceWith(messageDiv);
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

function resetPhotoUpload() {
    photoPreview.classList.remove('active');
    uploadPlaceholder.style.display = 'flex';
    changePhotoBtn.style.display = 'none';
    croppedImageInput.value = '';
    photoInput.value = '';
}

document.querySelector('.btn-cancel').addEventListener('click', function() {
    resetPhotoUpload();
    
    // Reset dropdowns
    divisionSelect.value = '';
    sectionSelect.innerHTML = '<option value="">Select Division first</option>';
    sectionSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">Select Section first</option>';
    unitSelect.disabled = true;
    locationIdInput.value = '';
    
    // Clear all equipment sections
    var container = document.getElementById('equipmentSectionsContainer');
    if (container) container.innerHTML = '';
    equipmentCounters = {};

    // Re-enable submit button
    var submitBtn = document.querySelector('.btn-submit');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Employee';
});

function toggleForm() {
    var form = document.getElementById('employeeFormContainer');
    form.classList.toggle('active');
    if (form.classList.contains('active')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

var searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}


var urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === '1') {
    // Remove success parameter from URL
    window.history.replaceState({}, document.title, window.location.pathname);
}


// ============================================================
//  EQUIPMENT ASSIGNMENT — Dynamic section builder
// ============================================================

if (typeof equipmentCounters === 'undefined') { var equipmentCounters = {}; }

var EQUIPMENT_CONFIG = {
    computer: {
        label: 'System Unit', icon: 'fa-desktop', color: '#4f46e5',
        fields: [
            { name: 'brand',     label: 'Brand',         type: 'text',   required: true,  placeholder: 'e.g. Dell, HP, Lenovo' },
            { name: 'serial',    label: 'Serial Number', type: 'text',   required: true,  placeholder: 'e.g. SU-2024-001' },
            { name: 'category',  label: 'Category',      type: 'select', required: false, options: ['Pre-Built', 'Custom Built'] },
            { name: 'processor', label: 'Processor',     type: 'text',   required: false, placeholder: 'e.g. Intel Core i5' },
            { name: 'memory',    label: 'Memory (RAM)',  type: 'text',   required: false, placeholder: 'e.g. 8GB DDR4' },
            { name: 'storage',   label: 'Storage',       type: 'text',   required: false, placeholder: 'e.g. 256GB SSD' },
            { name: 'gpu',       label: 'GPU',           type: 'text',   required: false, placeholder: 'e.g. Intel UHD 630' },
            { name: 'year',      label: 'Year Acquired', type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    },
    allinone: {
        label: 'All-in-One PC', icon: 'fa-computer', color: '#0891b2',
        fields: [
            { name: 'brand',     label: 'Brand',         type: 'text',   required: true,  placeholder: 'e.g. HP, Dell' },
            { name: 'processor', label: 'Processor',     type: 'text',   required: false, placeholder: 'e.g. Ryzen 7' },
            { name: 'memory',    label: 'Memory (RAM)',  type: 'text',   required: false, placeholder: 'e.g. 16GB' },
            { name: 'storage',   label: 'Storage',       type: 'text',   required: false, placeholder: 'e.g. 512GB SSD' },
            { name: 'gpu',       label: 'GPU',           type: 'text',   required: false, placeholder: 'e.g. Intel Integrated' },
            { name: 'year',      label: 'Year Acquired', type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    },
    monitor: {
        label: 'Monitor', icon: 'fa-tv', color: '#059669',
        fields: [
            { name: 'brand',  label: 'Brand',         type: 'text',   required: true,  placeholder: 'e.g. Dell, LG, Samsung' },
            { name: 'serial', label: 'Serial Number', type: 'text',   required: true,  placeholder: 'e.g. MO-2024-001' },
            { name: 'size',   label: 'Screen Size',   type: 'text',   required: false, placeholder: 'e.g. 24 inches' },
            { name: 'year',   label: 'Year Acquired', type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    },
    printer: {
        label: 'Printer', icon: 'fa-print', color: '#d97706',
        fields: [
            { name: 'brand',  label: 'Brand',         type: 'text', required: true,  placeholder: 'e.g. HP, Canon, Epson' },
            { name: 'model',  label: 'Model',         type: 'text', required: true,  placeholder: 'e.g. LaserJet Pro' },
            { name: 'serial', label: 'Serial Number', type: 'text', required: true,  placeholder: 'e.g. PR-2024-001' },
            { name: 'year',   label: 'Year Acquired', type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    },
    laptop: {
        label: 'Laptop', icon: 'fa-laptop', color: '#7c3aed',
        fields: [
            { name: 'brand',  label: 'Brand',         type: 'text', required: true,  placeholder: 'e.g. Lenovo, Asus' },
            { name: 'model',  label: 'Model',         type: 'text', required: false, placeholder: 'e.g. ThinkPad X1' },
            { name: 'serial', label: 'Serial Number', type: 'text', required: false, placeholder: 'e.g. LT-2024-001' },
            { name: 'year',   label: 'Year Acquired', type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    },
    software: {
        label: 'Software License', icon: 'fa-key', color: '#be185d',
        fields: [
            { name: 'name',    label: 'Software Name',   type: 'text',  required: true,  placeholder: 'e.g. Microsoft Office' },
            { name: 'details', label: 'License Details', type: 'text',  required: false, placeholder: 'e.g. Office 365 E3' },
            { name: 'type',    label: 'License Type',    type: 'select', required: false, options: ['Perpetual', 'Subscription'] },
            { name: 'expiry',  label: 'Expiry Date',     type: 'date',  required: false },
            { name: 'email',   label: 'License Email',   type: 'email', required: false, placeholder: 'account@example.com' },
        ]
    },
    other: {
        label: 'Other Equipment', icon: 'fa-server', color: '#475569',
        fields: [
            { name: 'eq_type', label: 'Equipment Type', type: 'text',   required: true,  placeholder: 'e.g. Projector, Scanner' },
            { name: 'brand',   label: 'Brand',          type: 'text',   required: false, placeholder: 'e.g. Epson' },
            { name: 'model',   label: 'Model',          type: 'text',   required: false, placeholder: 'e.g. EB-2247U' },
            { name: 'serial',  label: 'Serial Number',  type: 'text',   required: false, placeholder: 'e.g. OE-2024-001' },
            { name: 'year',    label: 'Year Acquired',  type: 'number', required: false, placeholder: 'YYYY', extraAttrs: 'min="1990" max="' + (new Date().getFullYear() + 1) + '"' },
        ]
    }
};

function addEquipmentSection(type) {
    if (!equipmentCounters[type]) equipmentCounters[type] = 0;
    equipmentCounters[type]++;
    var idx = equipmentCounters[type];
    var cfg = EQUIPMENT_CONFIG[type];
    var uid = type + '_' + idx;

    var container = document.getElementById('equipmentSectionsContainer');
    var card = document.createElement('div');
    card.className    = 'eq-section-card';
    card.id           = 'eq_card_' + uid;
    card.dataset.type = type;

    var fieldsHtml = '';
    cfg.fields.forEach(function(f) {
        var fieldName = 'eq[' + uid + '][' + f.name + ']';
        var inputHtml = '';
        if (f.type === 'select') {
            var opts = (f.options || []).map(function(o) {
                return '<option value="' + o + '">' + o + '</option>';
            }).join('');
            inputHtml = '<select class="form-select form-select-sm" name="' + fieldName + '">'
                + '<option value="">Select…</option>' + opts + '</select>';
        } else {
            inputHtml = '<input type="' + f.type + '" class="form-control form-control-sm"'
                + ' name="' + fieldName + '"'
                + (f.placeholder ? ' placeholder="' + f.placeholder + '"' : '')
                + (f.required    ? ' required' : '')
                + (f.extraAttrs  ? ' ' + f.extraAttrs : '')
                + '>';
        }
        fieldsHtml += '<div class="eq-field">'
            + '<label class="eq-field-label">' + f.label
            + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>'
            + inputHtml + '</div>';
    });

    card.innerHTML = ''
        + '<div class="eq-card-header" style="--eq-color:' + cfg.color + '">'
        +   '<div class="eq-card-title">'
        +     '<i class="fas ' + cfg.icon + '"></i>'
        +     '<span>' + cfg.label + ' #' + idx + '</span>'
        +   '</div>'
        +   '<button type="button" class="eq-card-remove" onclick="removeEquipmentSection(\'' + uid + '\')">'
        +     '<i class="fas fa-times"></i>'
        +   '</button>'
        + '</div>'
        + '<input type="hidden" name="eq[' + uid + '][_type]" value="' + type + '">'
        + '<div class="eq-card-fields">' + fieldsHtml + '</div>';

    container.appendChild(card);
    requestAnimationFrame(function() { card.classList.add('eq-visible'); });
}

function removeEquipmentSection(uid) {
    var card = document.getElementById('eq_card_' + uid);
    if (!card) return;
    card.classList.add('eq-removing');
    setTimeout(function() { if (card.parentNode) card.remove(); }, 350);
}

function collectEquipmentData() {
    var result = {};
    var inputs = document.querySelectorAll('#equipmentSectionsContainer [name^="eq["]');
    inputs.forEach(function(input) {
        var match = input.name.match(/^eq\[([^\]]+)\]\[([^\]]+)\]$/);
        if (!match) return;
        var uid = match[1], field = match[2];
        if (!result[uid]) result[uid] = {};
        result[uid][field] = input.value;
    });
    return result;
}