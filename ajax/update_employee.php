<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

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
            // Return relative path for database storage
            return ['success' => true, 'path' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to save image file.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid base64 image data.'];
}

function deleteOldPhoto($photoPath) {
    if (!empty($photoPath)) {
        $fullPath = '../public/uploads/' . $photoPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        else {
            error_log("Old photo not found for deletion: " . $fullPath);
        }
    }
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get employee ID
        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        
        // Validate required fields
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
        
        // Get current employee data for photo path
        $currentStmt = $db->prepare("SELECT photoPath FROM tbl_employee WHERE employeeId = ?");
        $currentStmt->execute([$employeeId]);
        $currentEmployee = $currentStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentEmployee) {
            echo json_encode([
                'success' => false,
                'message' => 'Employee not found.'
            ]);
            exit;
        }
        
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
            $suffixName,
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Employee updated successfully!'
        ]);
        
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