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
        if (file.size > 5 * 1024 * 1024) {
            alert('Image file size must not exceed 5MB.');
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

