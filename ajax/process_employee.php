<?php
// ajax/process_employee.php
// Backend processing for employee management with CropperJS image handling

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';

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

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                // Validate required fields
                $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
                $firstName = trim($_POST['firstName'] ?? '');
                $lastName = trim($_POST['lastName'] ?? '');
                $position = trim($_POST['position'] ?? '');
                $birthDate = $_POST['birthDate'] ?? '';
                $sex = $_POST['sex'] ?? '';
                $employmentStatus = $_POST['employmentStatus'] ?? '';
                $locationId = filter_input(INPUT_POST, 'locationId', FILTER_VALIDATE_INT);
                
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
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee added successfully!'
                ]);
                break;
                
            case 'update':
                // Get employee ID
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid employee ID.'
                    ]);
                    exit;
                }
                
                // Validate required fields
                $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
                $firstName = trim($_POST['firstName'] ?? '');
                $lastName = trim($_POST['lastName'] ?? '');
                $position = trim($_POST['position'] ?? '');
                $birthDate = $_POST['birthDate'] ?? '';
                $sex = $_POST['sex'] ?? '';
                $employmentStatus = $_POST['employmentStatus'] ?? '';
                $locationId = filter_input(INPUT_POST, 'locationId', FILTER_VALIDATE_INT);
                
                if (!$employeeId || empty($firstName) || empty($lastName) || empty($position) || 
                    empty($birthDate) || empty($sex) || empty($employmentStatus) || !$locationId) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Please fill in all required fields.'
                    ]);
                    exit;
                }
                
                // Check if employee ID exists for another record
                $checkStmt = $db->prepare("SELECT id, photoPath FROM tbl_employee WHERE employeeId = ? AND id != ?");
                $checkStmt->execute([$employeeId, $id]);
                if ($checkStmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Employee ID already exists for another employee.'
                    ]);
                    exit;
                }
                
                // Get current employee data for photo path
                $currentStmt = $db->prepare("SELECT photoPath FROM tbl_employee WHERE id = ?");
                $currentStmt->execute([$id]);
                $currentEmployee = $currentStmt->fetch(PDO::FETCH_ASSOC);
                $photoPath = $currentEmployee['photoPath'];
                
                // Handle cropped image if provided
                if (!empty($_POST['croppedImage'])) {
                    // Delete old photo if exists
                    if ($photoPath) {
                        deleteOldPhoto($photoPath);
                    }
                    
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
                
                // Update employee in database
                $sql = "UPDATE tbl_employee SET
                    employeeId = ?,
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
                    updated_at = NOW()
                    WHERE id = ?";
                
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
                    $photoPath,
                    $id
                ]);
                
                $_SESSION['employee_message'] = 'Employee updated successfully!';
                $_SESSION['employee_message_type'] = 'success';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee updated successfully!'
                ]);
                break;
                
            case 'delete':
                // Get employee ID
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if (!$id) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid employee ID.'
                    ]);
                    exit;
                }
                
                // Get employee photo path before deletion
                $stmt = $db->prepare("SELECT photoPath FROM tbl_employee WHERE id = ?");
                $stmt->execute([$id]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Delete employee from database
                $deleteStmt = $db->prepare("DELETE FROM tbl_employee WHERE id = ?");
                $deleteStmt->execute([$id]);
                
                // Delete photo file if exists
                if ($employee && $employee['photoPath']) {
                    deleteOldPhoto($employee['photoPath']);
                }
                
                $_SESSION['employee_message'] = 'Employee deleted successfully!';
                $_SESSION['employee_message_type'] = 'success';
                
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