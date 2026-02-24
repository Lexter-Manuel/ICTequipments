<?php
/**
 * AJAX handler for System Settings management
 * Super Admin only
 */
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/permissions.php';

header('Content-Type: application/json');

// All actions are Super Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$db = getDB();

switch ($action) {

    // ──────────────────────────────────────────────
    // Save a group of settings
    // ──────────────────────────────────────────────
    case 'save_settings':
        $group    = sanitizeInput($input['group'] ?? '');
        $settings = $input['settings'] ?? [];

        if (!$group || empty($settings)) {
            jsonResponse(['success' => false, 'message' => 'No settings provided']);
        }

        // Validate the group exists
        $validGroups = ['organization', 'security', 'maintenance', 'system'];
        if (!in_array($group, $validGroups)) {
            jsonResponse(['success' => false, 'message' => 'Invalid settings group']);
        }

        // Get valid keys for this group
        $stmt = $db->prepare("SELECT setting_key FROM system_settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        $validKeys = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $updated = 0;
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW(), updated_by = ? WHERE setting_key = ? AND setting_group = ?");

        foreach ($settings as $key => $value) {
            $key = sanitizeInput($key);
            if (!in_array($key, $validKeys)) continue;

            $value = trim($value);

            // Specific validations
            switch ($key) {
                case 'session_timeout':
                    $value = max(300, min(86400, intval($value)));
                    break;
                case 'max_login_attempts':
                    $value = max(3, min(20, intval($value)));
                    break;
                case 'lockout_duration':
                    $value = max(60, min(86400, intval($value)));
                    break;
                case 'password_min_length':
                    $value = max(6, min(32, intval($value)));
                    break;
                case 'maint_overdue_threshold_days':
                case 'maint_reminder_days_before':
                    $value = max(1, min(90, intval($value)));
                    break;
                case 'items_per_page':
                    if (!in_array(intval($value), [10, 25, 50, 100])) $value = '25';
                    break;
                case 'backup_retention_days':
                    $value = max(7, min(365, intval($value)));
                    break;
                case 'org_contact_email':
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) continue 2;
                    break;
                case 'enforce_2fa':
                case 'maint_auto_schedule':
                case 'enable_activity_log':
                    $value = ($value === '1') ? '1' : '0';
                    break;
            }

            $stmt->execute([(string)$value, $_SESSION['user_id'], $key, $group]);
            $updated++;
        }

        logActivity(ACTION_UPDATE, MODULE_SETTINGS, "Updated $group settings ($updated values)");
        jsonResponse(['success' => true, 'message' => ucfirst($group) . " settings saved ($updated updated)"]);
        break;

    // ──────────────────────────────────────────────
    // Purge old activity logs
    // ──────────────────────────────────────────────
    case 'purge_old_logs':
        // Get retention days from settings
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'backup_retention_days'");
        $stmt->execute();
        $retentionDays = intval($stmt->fetchColumn()) ?: 30;

        $del = $db->prepare("DELETE FROM tbl_activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $del->execute([$retentionDays]);
        $deletedCount = $del->rowCount();

        logActivity(ACTION_DELETE, MODULE_AUDIT_TRAIL, "Purged $deletedCount activity log entries older than $retentionDays days");
        jsonResponse(['success' => true, 'message' => "Purged $deletedCount log entries older than $retentionDays days"]);
        break;

    // ──────────────────────────────────────────────
    // Clear login attempts
    // ──────────────────────────────────────────────
    case 'clear_login_attempts':
        $del = $db->query("DELETE FROM login_attempts");
        $deletedCount = $del->rowCount();

        logActivity(ACTION_DELETE, MODULE_SETTINGS, "Cleared all login attempt records ($deletedCount entries)");
        jsonResponse(['success' => true, 'message' => "Cleared $deletedCount login attempt records"]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action']);
}
