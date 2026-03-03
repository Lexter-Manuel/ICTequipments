<?php
// ajax/process_employee.php
// Backend processing for employee management with CropperJS image handling

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';
require_once '../config/config.php';

// Set JSON header for AJAX responses
header('Content-Type: application/json');

// Get database connection
$db = Database::getInstance()->getConnection();

/**
 * Handle cropped image upload from base64 data
 */
function handleCroppedImage($base64Data, $employeeId) {
    // Create upload directory if it doesn't exist
    $uploadDir = '../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Extract base64 image data
    if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
        $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif
        
        // Validate image type
        if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
            return ['success' => false, 'message' => 'Invalid image type. Only JPG and PNG are allowed.'];
        }
        
        // Decode base64
        $imageData = base64_decode($base64Data);
        
        if ($imageData === false) {
            return ['success' => false, 'message' => 'Failed to decode image data.'];
        }
        
        // Generate unique filename
        $filename = 'employee_' . $employeeId . '_' . time() . '.' . $type;
        $filepath = $uploadDir . $filename;
        
        // Save the file
        if (file_put_contents($filepath, $imageData)) {
            // Return only filename for database storage
            return ['success' => true, 'path' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to save image file.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid base64 image data.'];
}

/**
 * Delete old employee photo
 */
function deleteOldPhoto($photoPath) {
    if (!empty($photoPath)) {
        $fullPath = '../public/uploads/' . $photoPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

/**
 * Schedule Semi-Annual maintenance for a newly inserted equipment item.
 * Updated for unified tbl_equipment schema.
 *
 * @param PDO    $db          Database connection
 * @param int    $equipmentId The new equipment's primary-key ID
 * @param int    $typeId      Numeric typeId from tbl_equipment_type_registry
 */
function scheduleMaintenanceForEquipment($db, $equipmentId, $typeId) {
    // 1. Safety check — skip if a schedule already exists
    $stmtChk = $db->prepare("
        SELECT scheduleId FROM tbl_maintenance_schedule
        WHERE equipmentId = ? AND equipmentType = ? AND isActive = 1
    ");
    $stmtChk->execute([$equipmentId, $typeId]);
    if ($stmtChk->fetch()) {
        return; // Already has a schedule
    }

    // 2. Resolve the equipment's location via the master view
    $stmtLoc = $db->prepare("
        SELECT location_name FROM view_maintenance_master
        WHERE id = ? AND type_id = ?
    ");
    $stmtLoc->execute([$equipmentId, $typeId]);
    $locationRow  = $stmtLoc->fetch(PDO::FETCH_ASSOC);
    $locationName = $locationRow['location_name'] ?? null;

    // 3. Determine nextDueDate — sync with a neighbour in the same location if possible
    $nextDueDate = date('Y-m-d'); // default: today
    if ($locationName) {
        $stmtNeighbour = $db->prepare("
            SELECT ms.nextDueDate
            FROM tbl_maintenance_schedule ms
            JOIN view_maintenance_master v
              ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE v.location_name = ?
              AND ms.isActive = 1
              AND ms.nextDueDate >= CURDATE()
            ORDER BY ms.nextDueDate ASC
            LIMIT 1
        ");
        $stmtNeighbour->execute([$locationName]);
        $neighbour = $stmtNeighbour->fetch(PDO::FETCH_ASSOC);
        if (!empty($neighbour['nextDueDate'])) {
            $nextDueDate = $neighbour['nextDueDate'];
        }
    }

    // 4. Insert the Semi-Annual schedule
    $db->prepare("
        INSERT INTO tbl_maintenance_schedule
            (equipmentType, equipmentId, maintenanceFrequency, nextDueDate, isActive)
        VALUES (?, ?, 'Semi-Annual', ?, 1)
    ")->execute([$typeId, $equipmentId, $nextDueDate]);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                // Validate required fields
                // intval() strips leading zeros correctly: "001245" → 1245 stored in int(11)
                // FILTER_VALIDATE_INT would reject "001245" entirely, causing false validation failures
                $employeeId       = intval($_POST['employeeId'] ?? 0);
                $firstName        = trim($_POST['firstName'] ?? '');
                $lastName         = trim($_POST['lastName'] ?? '');
                $position         = trim($_POST['position'] ?? '');
                $birthDate        = $_POST['birthDate'] ?? '';
                $sex              = $_POST['sex'] ?? '';
                $employmentStatus = $_POST['employmentStatus'] ?? '';
                // FILTER_VALIDATE_INT returns false for '' or non-numeric — use intval fallback
                $locationId = !empty($_POST['locationId']) ? (int)$_POST['locationId'] : 0;
                
                // Validate required fields
                if (!$employeeId || empty($firstName) || empty($lastName) || empty($position) || 
                    empty($birthDate) || empty($sex) || empty($employmentStatus) || !$locationId) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Please fill in all required fields.'
                    ]);
                    exit;
                }
                
                // Check if employee ID already exists
                $checkStmt = $db->prepare("SELECT employeeId FROM tbl_employee WHERE employeeId = ?");
                $checkStmt->execute([$employeeId]);
                if ($checkStmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Employee ID already exists. Please use a different ID.'
                    ]);
                    exit;
                }
                
                // Handle cropped image if provided
                $photoPath = null;
                if (!empty($_POST['croppedImage'])) {
                    $imageResult = handleCroppedImage($_POST['croppedImage'], $employeeId);
                    if ($imageResult['success']) {
                        $photoPath = $imageResult['path'];
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => $imageResult['message']
                        ]);
                        exit;
                    }
                }
                
                // Prepare optional fields
                $middleName = trim($_POST['middleName'] ?? '');
                $suffixName = $_POST['suffixName'] ?? null;
                
                // Insert employee into database
                $sql = "INSERT INTO tbl_employee (
                    employeeId, firstName, middleName, lastName, suffixName,
                    position, birthDate, sex, employmentStatus, location_id, photoPath,
                    createdAt, updatedAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $employeeId,
                    $firstName,
                    $middleName ?: null,
                    $lastName,
                    $suffixName,
                    $position,
                    $birthDate,
                    $sex,
                    $employmentStatus,
                    $locationId,
                    $photoPath
                ]);
                
                // Set success message in session
                $_SESSION['employee_message'] = 'Employee added successfully!';
                $_SESSION['employee_message_type'] = 'success';

                logActivity(ACTION_CREATE, MODULE_EMPLOYEES,
                    "Added employee {$firstName} {$lastName} (Employee ID: {$employeeId}, Position: {$position}, Status: {$employmentStatus}).");

                // ── Process equipment sections ──────────────────────────────
                $equipmentSaved = 0;
                $equipmentErrors = [];
                
                // Get the employee's location from database (use authoritative value, not form input alone)
                $empLocStmt = $db->prepare("SELECT location_id FROM tbl_employee WHERE employeeId = ? LIMIT 1");
                $empLocStmt->execute([$employeeId]);
                $empLocRow = $empLocStmt->fetch(PDO::FETCH_ASSOC);
                $empLocation = $empLocRow['location_id'] ?? $locationId;
                
                if (!empty($_POST['equipmentData'])) {
                    $equipmentItems = json_decode($_POST['equipmentData'], true);
                    if (is_array($equipmentItems)) {
                        foreach ($equipmentItems as $uid => $item) {
                            $type = $item['_type'] ?? '';
                            try {
                                if ($type === 'software') {
                                    // Software still in its own table
                                    $db->prepare("INSERT INTO tbl_software
                                        (licenseSoftware, licenseDetails, licenseType, expiryDate, email, employeeId)
                                        VALUES (?,?,?,?,?,?)")
                                      ->execute([
                                        trim($item['name']    ?? ''),
                                        trim($item['details'] ?? ''),
                                        trim($item['type']    ?? ''),
                                        !empty($item['expiry']) ? $item['expiry'] : null,
                                        trim($item['email']   ?? ''),
                                        $employeeId
                                      ]);
                                    $equipmentSaved++;
                                } else {
                                    // Map _type to typeId in tbl_equipment_type_registry
                                    $typeNameMap = [
                                        'computer' => 'System Unit',
                                        'allinone' => 'All-in-One',
                                        'monitor'  => 'Monitor',
                                        'printer'  => 'Printer',
                                        'laptop'   => 'Laptop',
                                    ];
                                    $eqTypeName = $typeNameMap[$type] ?? trim($item['eq_type'] ?? 'Other');

                                    // Resolve typeId from registry
                                    $stmtType = $db->prepare("SELECT typeId FROM tbl_equipment_type_registry WHERE typeName = ? LIMIT 1");
                                    $stmtType->execute([$eqTypeName]);
                                    $registryRow = $stmtType->fetch(PDO::FETCH_ASSOC);
                                    $typeId = $registryRow ? (int)$registryRow['typeId'] : null;

                                    if (!$typeId) {
                                        $equipmentErrors[] = "Unknown equipment type: {$eqTypeName}";
                                        continue;
                                    }

                                    // Insert into tbl_equipment
                                    $db->prepare("INSERT INTO tbl_equipment
                                        (type_id, employee_id, location_id, brand, model, serial_number, status, year_acquired)
                                        VALUES (?,?,?,?,?,?,'Active',?)")
                                      ->execute([
                                        $typeId,
                                        $employeeId,
                                        $empLocation,
                                        trim($item['brand']  ?? ''),
                                        trim($item['model']  ?? ''),
                                        trim($item['serial'] ?? '') ?: null,
                                        $item['year'] ?: null,
                                      ]);
                                    $newEqId = (int)$db->lastInsertId();

                                    // Build specs based on type
                                    $specs = [];
                                    switch ($type) {
                                        case 'computer':
                                            $specs['Category']  = trim($item['category']  ?? 'Pre-Built');
                                            $specs['Processor'] = trim($item['processor'] ?? '');
                                            $specs['Memory']    = trim($item['memory']    ?? '');
                                            $specs['GPU']       = trim($item['gpu']       ?? '');
                                            $specs['Storage']   = trim($item['storage']   ?? '');
                                            break;
                                        case 'allinone':
                                            $specs['Processor'] = trim($item['processor'] ?? '');
                                            $specs['Memory']    = trim($item['memory']    ?? '');
                                            $specs['GPU']       = trim($item['gpu']       ?? '');
                                            $specs['Storage']   = trim($item['storage']   ?? '');
                                            break;
                                        case 'monitor':
                                            $specs['Monitor Size'] = trim($item['size'] ?? '');
                                            break;
                                        case 'printer':
                                            // model already in tbl_equipment.model
                                            break;
                                        case 'laptop':
                                        case 'other':
                                            // Generic specs from form
                                            if (!empty($item['processor'])) $specs['Processor'] = trim($item['processor']);
                                            if (!empty($item['memory']))    $specs['Memory']    = trim($item['memory']);
                                            if (!empty($item['storage']))   $specs['Storage']   = trim($item['storage']);
                                            break;
                                    }

                                    // Insert specs
                                    $specStmt = $db->prepare("INSERT INTO tbl_equipment_specs (equipment_id, spec_key, spec_value) VALUES (?,?,?)");
                                    foreach ($specs as $sk => $sv) {
                                        if ($sv !== '') {
                                            $specStmt->execute([$newEqId, $sk, $sv]);
                                        }
                                    }

                                    // Schedule maintenance
                                    scheduleMaintenanceForEquipment($db, $newEqId, $typeId);
                                    $equipmentSaved++;
                                }
                            } catch (Exception $eqEx) {
                                $equipmentErrors[] = "Failed to save {$type}: " . $eqEx->getMessage();
                                error_log("Equipment insert error [{$uid}]: " . $eqEx->getMessage());
                            }
                        }
                    }
                }

                // Active/Inactive status is derived at query time in roster.php
                // by counting equipment rows — no column write needed here.

                $msg = 'Employee added successfully!';
                if ($equipmentSaved > 0) {
                    $msg .= " {$equipmentSaved} equipment item(s) assigned.";
                }
                if (!empty($equipmentErrors)) {
                    $msg .= ' Some equipment could not be saved: ' . implode('; ', $equipmentErrors);
                }
                // ── End equipment processing ────────────────────────────────
                
                echo json_encode([
                    'success' => true,
                    'message' => $msg
                ]);
                break;
                
            case 'update':
                // employeeId IS the primary key — use intval so leading-zero IDs like "001245" work
                $employeeId = intval($_POST['employeeId'] ?? 0);
                if (!$employeeId) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid employee ID.'
                    ]);
                    exit;
                }
                
                // Validate required fields
                $firstName        = trim($_POST['firstName'] ?? '');
                $lastName         = trim($_POST['lastName'] ?? '');
                $position         = trim($_POST['position'] ?? '');
                $birthDate        = $_POST['birthDate'] ?? '';
                $sex              = $_POST['sex'] ?? '';
                $employmentStatus = $_POST['employmentStatus'] ?? '';
                $locationId       = !empty($_POST['locationId']) ? (int)$_POST['locationId'] : 0;
                
                if (empty($firstName) || empty($lastName) || empty($position) || 
                    empty($birthDate) || empty($sex) || empty($employmentStatus) || !$locationId) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Please fill in all required fields.'
                    ]);
                    exit;
                }
                
                // Get current photo path
                $currentStmt = $db->prepare("SELECT photoPath FROM tbl_employee WHERE employeeId = ?");
                $currentStmt->execute([$employeeId]);
                $currentEmployee = $currentStmt->fetch(PDO::FETCH_ASSOC);
                if (!$currentEmployee) {
                    echo json_encode(['success' => false, 'message' => 'Employee not found.']);
                    exit;
                }
                $photoPath = $currentEmployee['photoPath'];
                
                // Handle cropped image if provided
                if (!empty($_POST['croppedImage'])) {
                    if ($photoPath) deleteOldPhoto($photoPath);
                    $imageResult = handleCroppedImage($_POST['croppedImage'], $employeeId);
                    if ($imageResult['success']) {
                        $photoPath = $imageResult['path'];
                    } else {
                        echo json_encode(['success' => false, 'message' => $imageResult['message']]);
                        exit;
                    }
                }
                
                // Prepare optional fields
                $middleName = trim($_POST['middleName'] ?? '');
                $suffixName = $_POST['suffixName'] ?? null;
                
                // Update employee — WHERE employeeId (PK), no 'id' column exists
                $sql = "UPDATE tbl_employee SET
                    firstName = ?,
                    middleName = ?,
                    lastName = ?,
                    suffixName = ?,
                    position = ?,
                    birthDate = ?,
                    sex = ?,
                    employmentStatus = ?,
                    location_id = ?,
                    photoPath = ?,
                    updatedAt = NOW()
                WHERE employeeId = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $firstName,
                    $middleName ?: null,
                    $lastName,
                    $suffixName ?: null,
                    $position,
                    $birthDate,
                    $sex,
                    $employmentStatus,
                    $locationId,
                    $photoPath,
                    $employeeId
                ]);
                
                $_SESSION['employee_message'] = 'Employee updated successfully!';
                $_SESSION['employee_message_type'] = 'success';

                logActivity(ACTION_UPDATE, MODULE_EMPLOYEES,
                    "Updated employee {$firstName} {$lastName} (Employee ID: {$employeeId}, Position: {$position}, Status: {$employmentStatus}).");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee updated successfully!'
                ]);
                break;
                
            case 'delete':
                $employeeId = intval($_POST['employeeId'] ?? 0);
                // Also accept legacy 'id' field name from older callers
                if (!$employeeId) {
                    $employeeId = intval($_POST['id'] ?? 0);
                }
                if (!$employeeId) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid employee ID.'
                    ]);
                    exit;
                }
                
                // Get employee photo path before deletion
                $stmt = $db->prepare("SELECT photoPath FROM tbl_employee WHERE employeeId = ?");
                $stmt->execute([$employeeId]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete employee from database
                $deleteStmt = $db->prepare("DELETE FROM tbl_employee WHERE employeeId = ?");
                $deleteStmt->execute([$employeeId]);
                
                // Delete photo file if exists
                if ($employee && $employee['photoPath']) {
                    deleteOldPhoto($employee['photoPath']);
                }
                
                $_SESSION['employee_message'] = 'Employee deleted successfully!';
                $_SESSION['employee_message_type'] = 'success';

                logActivity(ACTION_DELETE, MODULE_EMPLOYEES,
                    "Deleted employee (ID: {$employeeId}).");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee deleted successfully!'
                ]);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action.'
                ]);
                break;
        }
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>