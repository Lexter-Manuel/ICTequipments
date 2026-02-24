<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceHelper.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Get database connection
$db = getDB();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listSoftware($db);
            break;
            
        case 'get':
            getSoftware($db);
            break;
            
        case 'create':
            createSoftware($db);
            break;
            
        case 'update':
            updateSoftware($db);
            break;
            
        case 'delete':
            deleteSoftware($db);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * List all software licenses with optional filtering
 */
function listSoftware($db) {
    $search = $_GET['search'] ?? '';
    
    $sql = "
        SELECT 
            s.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (
            s.licenseSoftware LIKE :search OR
            s.licenseDetails LIKE :search OR
            s.licenseType LIKE :search
        )";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY s.softwareId DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $softwareList = $stmt->fetchAll();
    
    // Format data for frontend
    $formattedSoftware = array_map(function($s) {
        // Calculate status based on expiry date
        $status = 'Active';
        $daysUntilExpiry = null;
        
        if ($s['expiryDate']) {
            $expiryDate = new DateTime($s['expiryDate']);
            $today = new DateTime();
            $interval = $today->diff($expiryDate);
            $daysUntilExpiry = $interval->invert ? -$interval->days : $interval->days;
            
            if ($daysUntilExpiry < 0) {
                $status = 'Expired';
            } elseif ($daysUntilExpiry <= 30) {
                $status = 'Expiring Soon';
            }
        }
        
        return [
            'software_id' => $s['softwareId'],
            'software_name' => $s['licenseSoftware'],
            'license_details' => $s['licenseDetails'],
            'license_type' => $s['licenseType'],
            'expiry_date' => $s['expiryDate'],
            'email' => $s['email'],
            'employee_id' => $s['employeeId'],
            'employee_name' => $s['employeeName'],
            'status' => $status,
            'days_until_expiry' => $daysUntilExpiry
        ];
    }, $softwareList);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedSoftware
    ]);
}

/**
 * Get single software license details
 */
function getSoftware($db) {
    $softwareId = $_GET['software_id'] ?? null;
    
    if (!$softwareId) {
        throw new Exception('Software ID is required');
    }
    
    $stmt = $db->prepare("
        SELECT 
            s.*,
            CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) as employeeName
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.softwareId = :softwareId
    ");
    
    $stmt->execute([':softwareId' => $softwareId]);
    $software = $stmt->fetch();
    
    if (!$software) {
        throw new Exception('Software license not found');
    }
    
    // Calculate status
    $status = 'Active';
    $daysUntilExpiry = null;
    
    if ($software['expiryDate']) {
        $expiryDate = new DateTime($software['expiryDate']);
        $today = new DateTime();
        $interval = $today->diff($expiryDate);
        $daysUntilExpiry = $interval->invert ? -$interval->days : $interval->days;
        
        if ($daysUntilExpiry < 0) {
            $status = 'Expired';
        } elseif ($daysUntilExpiry <= 30) {
            $status = 'Expiring Soon';
        }
    }
    
    // Format data for frontend
    $formattedSoftware = [
        'software_id' => $software['softwareId'],
        'software_name' => $software['licenseSoftware'],
        'license_details' => $software['licenseDetails'],
        'license_type' => $software['licenseType'],
        'expiry_date' => $software['expiryDate'],
        'email' => $software['email'],
        'password' => $software['password'],
        'employee_id' => $software['employeeId'],
        'employee_name' => $software['employeeName'],
        'status' => $status,
        'days_until_expiry' => $daysUntilExpiry
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $formattedSoftware
    ]);
}

/**
 * Create new software license
 */
function createSoftware($db) {
    // Validate required fields
    $required = ['software_name', 'license_details', 'license_type'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_software (
            licenseSoftware,
            licenseDetails,
            licenseType,
            expiryDate,
            email,
            password,
            employeeId
        ) VALUES (
            :software_name,
            :license_details,
            :license_type,
            :expiry_date,
            :email,
            :password,
            :employeeId
        )
    ");
    
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $employeeId = !empty($_POST['employee_id']) ? $_POST['employee_id'] : null;
    
    $stmt->execute([
        ':software_name' => $_POST['software_name'],
        ':license_details' => $_POST['license_details'],
        ':license_type' => $_POST['license_type'],
        ':expiry_date' => $expiryDate,
        ':email' => $email,
        ':password' => $password,
        ':employeeId' => $employeeId
    ]);

    $newId = $db->lastInsertId();

    logActivity(ACTION_CREATE, MODULE_SOFTWARE,
        "Added license for '{$_POST['software_name']}' (Type: {$_POST['license_type']}, Expires: " . ($expiryDate ?: 'No expiry') . ")."
        . ($employeeId ? " Assigned to employee ID {$employeeId}." : ""));

    try {
            $maint = new MaintenanceHelper($db);
            $maint->initScheduleByType('Software License', $newId);
        } catch (Exception $e) {
            error_log("Failed to schedule maintenance for Software License ID $newId: " . $e->getMessage());
        }
    
    echo json_encode([
        'success' => true,
        'message' => 'Software license added successfully',
        'software_id' => $newId
    ]);
}

function updateSoftware($db) {
    $softwareId = $_POST['software_id'] ?? null;
    
    if (!$softwareId) {
        throw new Exception('Software ID is required');
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_software WHERE softwareId = :id");
    $stmt->execute([':id' => $softwareId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Software license not found');
    }
    
    $stmt = $db->prepare("
        UPDATE tbl_software SET
            licenseSoftware = :software_name,
            licenseDetails = :license_details,
            licenseType = :license_type,
            expiryDate = :expiry_date,
            email = :email,
            password = :password,
            employeeId = :employeeId
        WHERE softwareId = :softwareId
    ");
    
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $employeeId = !empty($_POST['employee_id']) ? $_POST['employee_id'] : null;
    
    $stmt->execute([
        ':software_name' => $_POST['software_name'],
        ':license_details' => $_POST['license_details'],
        ':license_type' => $_POST['license_type'],
        ':expiry_date' => $expiryDate,
        ':email' => $email,
        ':password' => $password,
        ':employeeId' => $employeeId,
        ':softwareId' => $softwareId
    ]);

    logActivity(ACTION_UPDATE, MODULE_SOFTWARE,
        "Updated license for '{$_POST['software_name']}' (ID: {$softwareId}, Type: {$_POST['license_type']}, Expires: " . ($expiryDate ?: 'No expiry') . ").");
    
    echo json_encode([
        'success' => true,
        'message' => 'Software license updated successfully'
    ]);
}

/**
 * Delete software license
 */
function deleteSoftware($db) {
    $softwareId = $_POST['software_id'] ?? null;
    
    if (!$softwareId) {
        throw new Exception('Software ID is required');
    }

    // Fetch details before deleting
    $row = $db->prepare("SELECT licenseSoftware, licenseType FROM tbl_software WHERE softwareId = :id");
    $row->execute([':id' => $softwareId]);
    $item = $row->fetch();
    
    $stmt = $db->prepare("DELETE FROM tbl_software WHERE softwareId = :id");
    $stmt->execute([':id' => $softwareId]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception('Software license not found or already deleted');
    }

    logActivity(ACTION_DELETE, MODULE_SOFTWARE,
        "Deleted license for '" . ($item['licenseSoftware'] ?? 'Unknown') . "' (ID: {$softwareId}, Type: " . ($item['licenseType'] ?? 'Unknown') . ").");
    
    echo json_encode([
        'success' => true,
        'message' => 'Software license deleted successfully'
    ]);
}