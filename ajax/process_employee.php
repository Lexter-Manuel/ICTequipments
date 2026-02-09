<?php
// ajax/process_employee.php
// Handles employee form submission (Add, Edit, Delete)
// Returns JSON response for AJAX requests

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';

$db = Database::getInstance()->getConnection();

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        // Validation and Sanitization
        $errors = [];   
        $response['action'] = 'add';
        
        // Required fields validation
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        $firstName = trim(filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_SPECIAL_CHARS));
        $middleName = trim(filter_input(INPUT_POST, 'middleName', FILTER_SANITIZE_SPECIAL_CHARS));
        $lastName = trim(filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_SPECIAL_CHARS));
        $suffixName = trim(filter_input(INPUT_POST, 'suffixName', FILTER_SANITIZE_SPECIAL_CHARS));
        $position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_SPECIAL_CHARS));
        $birthDate = filter_input(INPUT_POST, 'birthDate', FILTER_SANITIZE_SPECIAL_CHARS);
        $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_SPECIAL_CHARS);
        $employmentStatus = filter_input(INPUT_POST, 'employmentStatus', FILTER_SANITIZE_SPECIAL_CHARS);
        $locationId = filter_input(INPUT_POST, 'locationId', FILTER_VALIDATE_INT);
        
        // Validate required fields
        if (!$employeeId || $employeeId <= 0) {
            $errors[] = "Employee ID is required and must be a positive number.";
        }
        
        if (empty($firstName) || strlen($firstName) > 100) {
            $errors[] = "First Name is required and must not exceed 100 characters.";
        }
        
        if (!empty($middleName) && strlen($middleName) > 100) {
            $errors[] = "Middle Name must not exceed 100 characters.";
        }
        
        if (empty($lastName) || strlen($lastName) > 100) {
            $errors[] = "Last Name is required and must not exceed 100 characters.";
        }
        
        if (!empty($suffixName) && strlen($suffixName) > 50) {
            $errors[] = "Suffix must not exceed 50 characters.";
        }
        
        if (empty($position) || strlen($position) > 100) {
            $errors[] = "Position is required and must not exceed 100 characters.";
        }
        
        if (empty($birthDate)) {
            $errors[] = "Birth Date is required.";
        } else {
            // Validate date format and age
            $birthDateTime = DateTime::createFromFormat('Y-m-d', $birthDate);
            if (!$birthDateTime) {
                $errors[] = "Invalid birth date format.";
            } else {
                $age = (new DateTime())->diff($birthDateTime)->y;
                if ($age < 18) {
                    $errors[] = "Employee must be at least 18 years old.";
                } elseif ($age > 100) {
                    $errors[] = "Invalid birth date - age exceeds 100 years.";
                }
            }
        }
        
        if (!in_array($sex, ['Male', 'Female', 'Other'])) {
            $errors[] = "Invalid sex selection.";
        }
        
        if (!in_array($employmentStatus, ['Permanent', 'Casual', 'Job Order'])) {
            $errors[] = "Invalid employment status selection.";
        }
        
        if (!$locationId || $locationId <= 0) {
            $errors[] = "Location is required. Please select a Division, Section, or Unit.";
        }
        
        // Check if Employee ID already exists
        if (empty($errors)) {
            try {
                $checkStmt = $db->prepare("SELECT employeeId FROM tbl_employee WHERE employeeId = ?");
                $checkStmt->execute([$employeeId]);
                if ($checkStmt->fetch()) {
                    $errors[] = "Employee ID {$employeeId} already exists. Please use a different ID.";
                }
            } catch (PDOException $e) {
                error_log("Employee ID Check Error: " . $e->getMessage());
                $errors[] = "Database error occurred while checking Employee ID.";
            }
        }
        
        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            
            $fileType = $_FILES['photo']['type'];
            $fileSize = $_FILES['photo']['size'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Photo must be a JPEG or PNG image.";
            }
            
            if ($fileSize > $maxFileSize) {
                $errors[] = "Photo size must not exceed 5MB.";
            }
            
            if (empty($errors)) {
                // Generate unique filename
                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoPath = 'profile_' . uniqid() . '.' . uniqid() . '.' . $extension;
                
                // Create upload directory if it doesn't exist
                $uploadDir = '../public/uploads/employees/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoPath)) {
                    $errors[] = "Failed to upload photo. Please try again.";
                    $photoPath = null;
                }
            }
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                // Verify that locationId exists in location table
                $locationCheckStmt = $db->prepare("
                    SELECT l.location_id, l.location_name, lt.name as location_type
                    FROM location l
                    JOIN location_type lt ON l.location_type_id = lt.id
                    WHERE l.location_id = ? AND l.is_deleted = '0'
                ");
                $locationCheckStmt->execute([$locationId]);
                $locationData = $locationCheckStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$locationData) {
                    $errors[] = "Invalid location selected. Please refresh the page and try again.";
                } else {
                    // Insert employee record
                    $insertStmt = $db->prepare("
                        INSERT INTO tbl_employee 
                        (employeeId, firstName, middleName, lastName, suffixName, position, 
                         birthDate, sex, employmentStatus, photoPath, location_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $result = $insertStmt->execute([
                        $employeeId,
                        $firstName,
                        empty($middleName) ? null : $middleName,
                        $lastName,
                        empty($suffixName) ? null : $suffixName,
                        $position,
                        $birthDate,
                        $sex,
                        $employmentStatus,
                        $photoPath,
                        $locationId
                    ]);
                    
                    if ($result) {
                        $response['success'] = true;
                        $response['message'] = "Employee added successfully! Employee ID: {$employeeId} - {$firstName} {$lastName} assigned to {$locationData['location_name']} ({$locationData['location_type']})";
                        $response['data'] = [
                            'employeeId' => $employeeId,
                            'fullName' => "{$firstName} {$lastName}",
                            'location' => $locationData['location_name']
                        ];
                    } else {
                        $errors[] = "Failed to add employee. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                // Log the actual error for debugging
                error_log("Employee Insert Error: " . $e->getMessage());
                error_log("Error Code: " . $e->getCode());
                if (isset($e->errorInfo[0])) {
                    error_log("SQL State: " . $e->errorInfo[0]);
                }
                
                // User-friendly error message
                if ($e->getCode() == 23000) {
                    // Integrity constraint violation
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $errors[] = "Duplicate entry detected. Employee ID may already exist.";
                    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                        $errors[] = "Invalid location selected. The location may no longer exist.";
                    } else {
                        $errors[] = "Database integrity error. Please check your input.";
                    }
                } else {
                    $errors[] = "Database error occurred. Please contact the administrator. Error Code: " . $e->getCode();
                }
            }
        }
        
        // If there are errors, return them in response
        if (!empty($errors)) {
            $response['success'] = false;
            $response['message'] = implode(' | ', $errors);
            echo json_encode($response);
            exit();
        }
    }
}

// Return response as JSON
echo json_encode($response);
exit();
?>