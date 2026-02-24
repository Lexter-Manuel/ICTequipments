<?php
/**
 * AJAX endpoint for audit trail data
 * Returns paginated activity logs with filtering
 */
require_once '../config/database.php';
require_once '../config/session-check.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Pagination
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(100, max(10, (int) ($_GET['per_page'] ?? 25)));
    $offset  = ($page - 1) * $perPage;
    
    // Filters
    $action  = $_GET['action'] ?? '';
    $module  = $_GET['module'] ?? '';
    $email   = $_GET['email'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo   = $_GET['date_to'] ?? '';
    $search   = $_GET['search'] ?? '';
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($action) {
        $where[] = "al.action = :action";
        $params[':action'] = $action;
    }
    if ($module) {
        $where[] = "al.module = :module";
        $params[':module'] = $module;
    }
    if ($email) {
        $where[] = "al.email LIKE :email";
        $params[':email'] = "%$email%";
    }
    if ($dateFrom) {
        $where[] = "DATE(al.timestamp) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    if ($dateTo) {
        $where[] = "DATE(al.timestamp) <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    if ($search) {
        $where[] = "(al.description LIKE :search OR al.action LIKE :search2 OR al.email LIKE :search3)";
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM activity_log al $whereClause");
    $countStmt->execute($params);
    $totalCount = (int) $countStmt->fetchColumn();
    
    // Get paginated results
    $sql = "SELECT al.id, al.user_id, al.email, al.action, al.module, al.description,
                   al.ip_address, al.success, al.timestamp,
                   a.user_name
            FROM activity_log al
            LEFT JOIN tbl_accounts a ON al.user_id = a.id
            $whereClause
            ORDER BY al.timestamp DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get distinct actions and modules for filters
    $actions = $db->query("SELECT DISTINCT action FROM activity_log ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
    $modules = $db->query("SELECT DISTINCT module FROM activity_log WHERE module IS NOT NULL AND module != '' ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'data' => $logs,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $totalCount,
            'total_pages' => ceil($totalCount / $perPage),
        ],
        'filters' => [
            'actions' => $actions,
            'modules' => $modules,
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Audit trail error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
