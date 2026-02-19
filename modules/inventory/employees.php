<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../config/database.php';
$db = Database::getInstance()->getConnection();

$message = $messageType = '';
if (isset($_SESSION['employee_message'])) {
    $message = $_SESSION['employee_message'];
    $messageType = $_SESSION['employee_message_type'];
    unset($_SESSION['employee_message'], $_SESSION['employee_message_type']);
}

$divisionStmt = $db->query("SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name ASC");
$divisions = $divisionStmt->fetchAll(PDO::FETCH_ASSOC);

$sectionStmt = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 2 AND is_deleted = '0' ORDER BY location_name ASC");
$sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

$unitStmt = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 3 AND is_deleted = '0' ORDER BY location_name ASC");
$units = $unitStmt->fetchAll(PDO::FETCH_ASSOC);

$employeeStmt = $db->query("
    SELECT e.*, l.location_name, l.location_type_id, lt.name as location_type_name
    FROM tbl_employee e
    LEFT JOIN location l ON e.location_id = l.location_id
    LEFT JOIN location_type lt ON l.location_type_id = lt.id
    ORDER BY e.lastName ASC, e.firstName ASC
");
$employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);

$totalEmployees = count($employees);
$permanentCount = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Permanent'));
$casualCount    = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Casual'));
$jobOrderCount  = count(array_filter($employees, fn($e) => $e['employmentStatus'] === 'Job Order'));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time()?>">
<link rel="stylesheet" href="assets/css/employees.css?v=<?php echo time()?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h1 class="page-title">Employee Management</h1>
        </div>
    </div>
    <button class="add-btn" onclick="toggleForm()">
        <i class="fas fa-user-plus"></i> Add New Employee
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
        <div><div class="stat-label">Total Employees</div><div class="stat-value"><?php echo $totalEmployees; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-check stat-icon"></i>
        <div><div class="stat-label">Permanent</div><div class="stat-value"><?php echo $permanentCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-clock stat-icon"></i>
        <div><div class="stat-label">Casual</div><div class="stat-value"><?php echo $casualCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-tie stat-icon"></i>
        <div><div class="stat-label">Job Order</div><div class="stat-value"><?php echo $jobOrderCount; ?></div></div>
    </div>
</div>

<!-- Add Employee Form -->
<div class="form-container" id="employeeFormContainer">
    <div class="form-header">
        <h2 class="form-title"><i class="fas fa-user-plus"></i> Add New Employee</h2>
        <button class="btn-close-form" onclick="toggleForm()"><i class="fas fa-times"></i> Close</button>
    </div>

    <form id="employeeForm" method="POST" action="ajax/process_employee.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="hidden" id="locationId" name="locationId" value="">
        <input type="hidden" id="croppedImage" name="croppedImage" value="">

        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-8">
                <h6 class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</h6>

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
                            <option value="Jr.">Jr.</option><option value="Sr.">Sr.</option>
                            <option value="II">II</option><option value="III">III</option>
                            <option value="IV">IV</option><option value="V">V</option>
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
                            <option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option>
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

                <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Location Assignment</h6>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
                        <select class="form-select" id="division" name="division" required>
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $division): ?>
                                <option value="<?php echo $division['location_id']; ?>"><?php echo htmlspecialchars($division['location_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section <small class="text-muted">(Optional)</small></label>
                        <select class="form-select" id="section" name="section" disabled>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="unit" class="form-label">Unit <small class="text-muted">(Optional)</small></label>
                        <select class="form-select" id="unit" name="unit" disabled>
                            <option value="">Select Unit</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Photo Upload -->
            <div class="col-md-4">
                <h6 class="form-section-title"><i class="fas fa-camera"></i> Employee Photo</h6>
                <div class="photo-upload-container">
                    <div class="photo-upload-box" id="photoUploadBox">
                        <input type="file" id="photoInput" name="photo" accept="image/*" hidden>
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload photo</p>
                            <small>JPG, PNG (Max 5MB)</small>
                        </div>
                        <div class="photo-preview" id="photoPreview">
                            <img id="previewImage" src="" alt="Preview">
                        </div>
                    </div>
                    <button type="button" class="btn-change-photo" id="changePhotoBtn" style="display:none">
                        <i class="fas fa-sync-alt"></i> Change Photo
                    </button>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Add Employee</button>
        </div>
    </form>
</div>

<?php include '../../includes/components/cropper_modal.php'; ?>

<script>
    var sectionsData = <?php echo json_encode($sections); ?>;
    var unitsData    = <?php echo json_encode($units); ?>;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script src="assets/js/employees.js?v=<?php echo time(); ?>"></script>