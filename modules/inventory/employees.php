<?php
// modules/inventory/employees.php
// Employee Management using the location table system

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Get messages from session if they exist
$message = '';
$messageType = '';
if (isset($_SESSION['employee_message'])) {
    $message = $_SESSION['employee_message'];
    $messageType = $_SESSION['employee_message_type'];
    // Clear the messages from session
    unset($_SESSION['employee_message']);
    unset($_SESSION['employee_message_type']);
}

// Fetch all divisions (location_type_id = 1) from location table
$divisionStmt = $db->query("
    SELECT location_id, location_name 
    FROM location 
    WHERE location_type_id = 1 AND is_deleted = '0'
    ORDER BY location_name ASC
");
$divisions = $divisionStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sections (location_type_id = 2) with their parent division
$sectionStmt = $db->query("
    SELECT location_id, location_name, parent_location_id 
    FROM location 
    WHERE location_type_id = 2 AND is_deleted = '0'
    ORDER BY location_name ASC
");
$sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all units (location_type_id = 3) with their parent section/division
$unitStmt = $db->query("
    SELECT location_id, location_name, parent_location_id 
    FROM location 
    WHERE location_type_id = 3 AND is_deleted = '0'
    ORDER BY location_name ASC
");
$units = $unitStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all employees with their location info
$employeeStmt = $db->query("
    SELECT 
        e.*,
        l.location_name,
        l.location_type_id,
        lt.name as location_type_name
    FROM tbl_employee e
    LEFT JOIN location l ON e.location_id = l.location_id
    LEFT JOIN location_type lt ON l.location_type_id = lt.id
    ORDER BY e.lastName ASC, e.firstName ASC
");
$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalEmployees = count($employees);
$permanentCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Permanent'));
$casualCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Casual'));
$jobOrderCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Job Order'));
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        Employee Management
    </h1>
    <button class="add-btn" onclick="toggleForm()">
        <i class="fas fa-user-plus"></i>
        Add New Employee
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
    <div><?php echo $message; ?></div>
</div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-users stat-icon"></i>
        <div class="stat-label">Total Employees</div>
        <div class="stat-value"><?php echo $totalEmployees; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-check stat-icon"></i>
        <div class="stat-label">Permanent</div>
        <div class="stat-value"><?php echo $permanentCount; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-clock stat-icon"></i>
        <div class="stat-label">Casual</div>
        <div class="stat-value"><?php echo $casualCount; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-tie stat-icon"></i>
        <div class="stat-label">Job Order</div>
        <div class="stat-value"><?php echo $jobOrderCount; ?></div>
    </div>
</div>

<!-- Add Employee Form -->
<div class="form-container" id="employeeFormContainer">
    <div class="form-header">
        <h2 class="form-title">
            <i class="fas fa-user-plus"></i>
            Add New Employee
        </h2>
        <button class="btn-close-form" onclick="toggleForm()">
            <i class="fas fa-times"></i>
            Close
        </button>
    </div>
    
    <form id="employeeForm" method="POST" action="/ICTequipments/ajax/process_employee.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="hidden" id="locationId" name="locationId" value="">
        
        <div class="row">
            <!-- Personal Information Section -->
            <div class="col-md-8">
                <h6 class="form-section-title">
                    <i class="fas fa-id-card"></i>
                    Personal Information
                </h6>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="employeeId" class="form-label">Employee ID <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="employeeId" name="employeeId" required min="1">
                    </div>
                    <div class="col-md-4">
                        <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label for="middleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middleName" name="middleName" maxlength="100">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label for="suffixName" class="form-label">Suffix</label>
                        <select class="form-select" id="suffixName" name="suffixName">
                            <option value="">None</option>
                            <option value="Jr.">Jr.</option>
                            <option value="Sr.">Sr.</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                            <option value="V">V</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="position" name="position" required maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label for="birthDate" class="form-label">Birth Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="birthDate" name="birthDate" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                        <select class="form-select" id="sex" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="employmentStatus" class="form-label">Employment Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="employmentStatus" name="employmentStatus" required>
                            <option value="">Select Status</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                </div>

                <h6 class="form-section-title mt-4">
                    <i class="fas fa-building"></i>
                    Organization Assignment
                </h6>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="divisionSelect" class="form-label">Division <span class="text-danger">*</span></label>
                        <select class="form-select" id="divisionSelect" required>
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $division): ?>
                            <option value="<?php echo $division['location_id']; ?>">
                                <?php echo htmlspecialchars($division['location_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="sectionSelect" class="form-label">Section (Optional)</label>
                        <select class="form-select" id="sectionSelect" disabled>
                            <option value="">Select Division first</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="unitSelect" class="form-label">Unit (Optional)</label>
                        <select class="form-select" id="unitSelect" disabled>
                            <option value="">Select Section first</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Section -->
            <div class="col-md-4">
                <h6 class="form-section-title">
                    <i class="fas fa-camera"></i>
                    Employee Photo
                </h6>
                <div class="photo-upload-container">
                    <label for="photoInput" class="photo-upload-label">
                        <img id="photoPreview" src="" alt="Photo Preview" class="photo-preview">
                        <div id="uploadPlaceholder" class="upload-placeholder">
                            <i class="fas fa-camera fa-3x"></i>
                            <p class="mt-2">Click to upload photo</p>
                            <small class="text-muted">JPEG or PNG (Max 5MB)</small>
                        </div>
                    </label>
                    <input type="file" class="d-none" id="photoInput" name="photo" accept="image/jpeg,image/jpg,image/png">
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Add Employee
                </button>
                <button type="reset" class="btn-cancel">
                    <i class="fas fa-undo"></i>
                    Reset Form
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Employee List -->
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">
            <i class="fas fa-list"></i>
            Employee Directory
        </h3>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search employees...">
        </div>
    </div>
    
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <?php if (count($employees) > 0): ?>
                    <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td>
                            <?php if (!empty($employee['photoPath'])): ?>
                                <?php 
                                // Check if photoPath already contains directory structure
                                if (strpos($employee['photoPath'], '/') !== false || strpos($employee['photoPath'], '\\') !== false) {
                                    // Path already includes directory
                                    $photoUrl = '../../' . $employee['photoPath'];
                                } else {
                                    // Just filename, construct full path
                                    $photoUrl = '../../public/uploads/employees/' . $employee['photoPath'];
                                }
                                ?>
                                <?php if (file_exists(str_replace('../../', '', $photoUrl))): ?>
                                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($employee['firstName']); ?>" 
                                         class="employee-photo-thumb">
                                <?php else: ?>
                                    <div class="employee-photo-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                            <div class="employee-photo-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: var(--primary-green);"><?php echo htmlspecialchars($employee['employeeId']); ?></strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?php 
                                $fullName = trim($employee['firstName'] . ' ' . 
                                               ($employee['middleName'] ? $employee['middleName'] . ' ' : '') . 
                                               $employee['lastName'] . ' ' . 
                                               ($employee['suffixName'] ? $employee['suffixName'] : ''));
                                echo htmlspecialchars($fullName);
                                ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-light);">
                                <?php echo htmlspecialchars($employee['sex']); ?> • 
                                <?php 
                                $birthDate = new DateTime($employee['birthDate']);
                                $age = $birthDate->diff(new DateTime())->y;
                                echo $age . ' years old';
                                ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($employee['location_name'] ?? 'N/A'); ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">
                                <?php echo htmlspecialchars($employee['location_type_name'] ?? ''); ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $employee['employmentStatus'])); ?>">
                                <?php echo htmlspecialchars($employee['employmentStatus']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-medium);">
                            <i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i>
                            <p style="margin-top: 16px;">No employees found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Location data from PHP
const sectionsData = <?php echo json_encode($sections); ?>;
const unitsData = <?php echo json_encode($units); ?>;

console.log('Divisions loaded from PHP');
console.log('Sections:', sectionsData);
console.log('Units:', unitsData);

// Get DOM elements
const divisionSelect = document.getElementById('divisionSelect');
const sectionSelect = document.getElementById('sectionSelect');
const unitSelect = document.getElementById('unitSelect');
const locationIdInput = document.getElementById('locationId');
const photoInput = document.getElementById('photoInput');
const photoPreview = document.getElementById('photoPreview');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');

// Division change handler
divisionSelect.addEventListener('change', function() {
    const divisionId = this.value;
    console.log('Division selected:', divisionId);
    
    // Reset section and unit
    sectionSelect.innerHTML = '<option value="">Select Section (Optional)</option>';
    unitSelect.innerHTML = '<option value="">Select Section first</option>';
    unitSelect.disabled = true;
    
    if (divisionId) {
        // Update hidden location_id to division
        locationIdInput.value = divisionId;
        console.log('Location ID set to division:', divisionId);
        
        // Enable section dropdown
        sectionSelect.disabled = false;
        
        // Filter sections that belong to this division (parent_location_id matches division)
        const divisionSections = sectionsData.filter(section => 
            section.parent_location_id == divisionId
        );
        
        console.log('Found sections for division:', divisionSections);
        
        // Populate sections
        divisionSections.forEach(section => {
            const option = document.createElement('option');
            option.value = section.location_id;
            option.textContent = section.location_name;
            sectionSelect.appendChild(option);
        });
        
        // Check for units directly under this division (some units might not have a section)
        const divisionUnits = unitsData.filter(unit => 
            unit.parent_location_id == divisionId
        );
        
        console.log('Found units directly under division:', divisionUnits);
        
        if (divisionUnits.length > 0) {
            unitSelect.innerHTML = '<option value="">Select Unit (Optional)</option>';
            unitSelect.disabled = false;
            divisionUnits.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.location_id;
                option.textContent = unit.location_name;
                unitSelect.appendChild(option);
            });
        }
        
        // If no sections available
        if (divisionSections.length === 0 && divisionUnits.length === 0) {
            sectionSelect.innerHTML = '<option value="">No sections/units available</option>';
            sectionSelect.disabled = true;
        }
    } else {
        // No division selected
        sectionSelect.disabled = true;
        sectionSelect.innerHTML = '<option value="">Select Division first</option>';
        locationIdInput.value = '';
        console.log('Division cleared');
    }
});

// Section change handler
sectionSelect.addEventListener('change', function() {
    const sectionId = this.value;
    console.log('Section selected:', sectionId);
    
    // Reset unit
    unitSelect.innerHTML = '<option value="">Select Unit (Optional)</option>';
    
    if (sectionId) {
        // Update hidden location_id to section
        locationIdInput.value = sectionId;
        console.log('Location ID set to section:', sectionId);
        
        // Enable unit dropdown
        unitSelect.disabled = false;
        
        // Filter units that belong to this section
        const sectionUnits = unitsData.filter(unit => 
            unit.parent_location_id == sectionId
        );
        
        console.log('Found units for section:', sectionUnits);
        
        // Populate units
        sectionUnits.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.location_id;
            option.textContent = unit.location_name;
            unitSelect.appendChild(option);
        });
        
        // If no units available
        if (sectionUnits.length === 0) {
            unitSelect.innerHTML = '<option value="">No units available</option>';
            unitSelect.disabled = true;
        }
    } else {
        // Section deselected, revert to division
        locationIdInput.value = divisionSelect.value;
        unitSelect.disabled = true;
        unitSelect.innerHTML = '<option value="">Select Section first</option>';
        console.log('Section cleared, reverting to division');
    }
});

// Unit change handler
unitSelect.addEventListener('change', function() {
    const unitId = this.value;
    console.log('Unit selected:', unitId);
    
    if (unitId) {
        // Update hidden location_id to unit
        locationIdInput.value = unitId;
        console.log('Location ID set to unit:', unitId);
    } else {
        // Unit deselected, revert to section or division
        locationIdInput.value = sectionSelect.value || divisionSelect.value;
        console.log('Unit cleared, reverting to section or division');
    }
});

// Photo Preview
photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a JPEG or PNG image.');
            this.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must not exceed 5MB.');
            this.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            photoPreview.src = e.target.result;
            photoPreview.classList.add('active');
            uploadPlaceholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        photoPreview.classList.remove('active');
        uploadPlaceholder.style.display = 'flex';
    }
});

// Form Validation
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    const employeeId = parseInt(this.employeeId.value);
    const firstName = this.firstName.value.trim();
    const middleName = this.middleName.value.trim();
    const lastName = this.lastName.value.trim();
    const position = this.position.value.trim();
    const sex = this.sex.value;
    const employmentStatus = this.employmentStatus.value;
    const birthDate = new Date(this.birthDate.value);
    const today = new Date();
    const locationId = this.locationId.value;
    const submitBtn = this.querySelector('.btn-submit');
    
    // Validate Employee ID
    if (!employeeId || employeeId <= 0) {
        alert('Please enter a valid Employee ID (must be greater than 0).');
        this.employeeId.focus();
        e.preventDefault();
        return;
    }
    
    if (employeeId > 2147483647) {
        alert('Employee ID is too large. Maximum value is 2147483647.');
        this.employeeId.focus();
        e.preventDefault();
        return;
    }
    
    // Validate First Name
    if (firstName.length === 0) {
        alert('First Name is required.');
        this.firstName.focus();
        e.preventDefault();
        return;
    }
    
    if (firstName.length > 100) {
        alert('First Name must not exceed 100 characters.');
        this.firstName.focus();
        e.preventDefault();
        return;
    }
    
    // Validate first name contains only letters, spaces, hyphens, and apostrophes
    if (!/^[a-zA-Z\s\-'.]+$/.test(firstName)) {
        alert('First Name can only contain letters, spaces, hyphens, and apostrophes.');
        this.firstName.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Middle Name (optional but check format if provided)
    if (middleName.length > 100) {
        alert('Middle Name must not exceed 100 characters.');
        this.middleName.focus();
        e.preventDefault();
        return;
    }
    
    if (middleName && !/^[a-zA-Z\s\-'.]+$/.test(middleName)) {
        alert('Middle Name can only contain letters, spaces, hyphens, and apostrophes.');
        this.middleName.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Last Name
    if (lastName.length === 0) {
        alert('Last Name is required.');
        this.lastName.focus();
        e.preventDefault();
        return;
    }
    
    if (lastName.length > 100) {
        alert('Last Name must not exceed 100 characters.');
        this.lastName.focus();
        e.preventDefault();
        return;
    }
    
    // Validate last name contains only letters, spaces, hyphens, and apostrophes
    if (!/^[a-zA-Z\s\-'.]+$/.test(lastName)) {
        alert('Last Name can only contain letters, spaces, hyphens, and apostrophes.');
        this.lastName.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Position
    if (position.length === 0) {
        alert('Position is required.');
        this.position.focus();
        e.preventDefault();
        return;
    }
    
    if (position.length > 100) {
        alert('Position must not exceed 100 characters.');
        this.position.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Sex
    if (!sex) {
        alert('Please select Sex.');
        this.sex.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Employment Status
    if (!employmentStatus) {
        alert('Please select Employment Status.');
        this.employmentStatus.focus();
        e.preventDefault();
        return;
    }
    
    // Validate Birth Date
    if (!this.birthDate.value) {
        alert('Birth Date is required.');
        this.birthDate.focus();
        e.preventDefault();
        return;
    }
    
    if (isNaN(birthDate.getTime())) {
        alert('Please enter a valid Birth Date.');
        this.birthDate.focus();
        e.preventDefault();
        return;
    }
    
    // Check if birth date is in the future
    if (birthDate > today) {
        alert('Birth Date cannot be in the future.');
        this.birthDate.focus();
        e.preventDefault();
        return;
    }
    
    // Calculate age
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    // Validate minimum age
    if (age < 18) {
        alert('Employee must be at least 18 years old. Current age: ' + age + ' years.');
        this.birthDate.focus();
        e.preventDefault();
        return;
    }
    
    // Validate maximum age
    if (age > 100) {
        alert('Invalid birth date. Please check the date entered.');
        this.birthDate.focus();
        e.preventDefault();
        return;
    }
    
    // Validate location is selected
    if (!locationId) {
        alert('Please select at least a Division for the employee.');
        divisionSelect.focus();
        e.preventDefault();
        return;
    }
    
    // Validate photo file if selected
    const photoFile = photoInput.files[0];
    if (photoFile) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(photoFile.type)) {
            alert('Photo must be a JPEG or PNG image.');
            e.preventDefault();
            return;
        }
        
        if (photoFile.size > 5 * 1024 * 1024) {
            alert('Photo file size must not exceed 5MB.');
            e.preventDefault();
            return;
        }
    }
    
    // All validations passed - disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Employee...';
    
    // Send form data via AJAX
    const formData = new FormData(this);
    
    fetch('/ICTequipments/ajax/process_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display success message
            const messageDiv = document.querySelector('.alert') || document.createElement('div');
            messageDiv.className = 'alert alert-success';
            messageDiv.innerHTML = '<i class="fas fa-check-circle"></i><div>' + data.message + '</div>';
            
            // Insert message if it doesn't exist
            if (!document.querySelector('.alert')) {
                document.getElementById('employeeFormContainer').parentElement.insertBefore(messageDiv, document.getElementById('employeeFormContainer'));
            } else {
                document.querySelector('.alert').replaceWith(messageDiv);
            }
            
            // Reset form
            document.getElementById('employeeForm').reset();
            document.querySelector('.btn-cancel').click();
            
            // Reload employees page within dashboard after 2 seconds
            setTimeout(() => {
                if (window.dashboardApp) {
                    window.dashboardApp.loadPage('employees', false); // false = don't use cache to get fresh data
                }
            }, 2000);
        } else {
            // Display error message
            const messageDiv = document.querySelector('.alert') || document.createElement('div');
            messageDiv.className = 'alert alert-danger';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i><div>' + data.message + '</div>';
            
            // Insert message if it doesn't exist
            if (!document.querySelector('.alert')) {
                document.getElementById('employeeFormContainer').parentElement.insertBefore(messageDiv, document.getElementById('employeeFormContainer'));
            } else {
                document.querySelector('.alert').replaceWith(messageDiv);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageDiv = document.querySelector('.alert') || document.createElement('div');
        messageDiv.className = 'alert alert-danger';
        messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i><div>An error occurred: ' + error.message + '</div>';
        
        // Insert message if it doesn't exist
        if (!document.querySelector('.alert')) {
            document.getElementById('employeeFormContainer').parentElement.insertBefore(messageDiv, document.getElementById('employeeFormContainer'));
        } else {
            document.querySelector('.alert').replaceWith(messageDiv);
        }
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Employee';
    });
});

// Reset form handler
document.querySelector('.btn-cancel').addEventListener('click', function() {
    photoPreview.classList.remove('active');
    uploadPlaceholder.style.display = 'flex';
    
    // Reset dropdowns
    divisionSelect.value = '';
    sectionSelect.innerHTML = '<option value="">Select Division first</option>';
    sectionSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">Select Section first</option>';
    unitSelect.disabled = true;
    locationIdInput.value = '';
    
    // Re-enable submit button
    const submitBtn = document.querySelector('.btn-submit');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Employee';
});

// Toggle form visibility
function toggleForm() {
    const form = document.getElementById('employeeFormContainer');
    form.classList.toggle('active');
    if (form.classList.contains('active')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#employeeTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Check for success parameter in URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === '1') {
    // Remove success parameter from URL
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>