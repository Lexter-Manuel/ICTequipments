<?php
// ajax/process_account.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/permissions.php';

// Only Super Admin can perform these actions
requireSuperAdmin();

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? '';
$superAdminId = $_SESSION['user_id'] ?? null;

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function redirectWithMessage($msg, $type = 'success') {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $type !== 'error', 'message' => $msg]);
        exit;
    }
    $_SESSION['account_message'] = $msg;
    $_SESSION['account_message_type'] = $type;
    // Redirect back to the page the form was submitted from
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $back);
    exit;
}

switch ($action) {

    /* =========================================================
       ADD NEW ADMIN
       ========================================================= */
    case 'add':
        $userName   = trim($_POST['user_name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';
        $status     = $_POST['status'] ?? 'Active';

        // Validate
        if (!$userName || !$email || !$password) {
            redirectWithMessage('All required fields must be filled in.', 'error');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithMessage('Please enter a valid email address.', 'error');
        }
        $minPwLen = (int) getSystemSetting('password_min_length', 8);
        if ($minPwLen < 6) $minPwLen = 8;
        if (strlen($password) < $minPwLen) {
            redirectWithMessage("Password must be at least {$minPwLen} characters long.", 'error');
        }
        if ($password !== $confirm) {
            redirectWithMessage('Passwords do not match.', 'error');
        }
        if (!in_array($status, ['Active', 'Inactive'])) {
            $status = 'Active';
        }

        // Check email uniqueness
        $checkStmt = $db->prepare("SELECT id FROM tbl_accounts WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            redirectWithMessage("An account with email <strong>{$email}</strong> already exists.", 'error');
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $insertStmt = $db->prepare("
            INSERT INTO tbl_accounts (user_name, email, password, role, status, created_by, created_at)
            VALUES (?, ?, ?, 'Admin', ?, ?, NOW())
        ");
        $insertStmt->execute([$userName, $email, $hashedPassword, $status, $superAdminId]);

        logActivity(ACTION_CREATE, MODULE_ACCOUNTS,
            "Created Admin account for {$userName} ({$email}), Status: {$status}.");

        redirectWithMessage("Admin account for <strong>{$userName}</strong> has been created successfully.");
        break;

    /* =========================================================
       EDIT ADMIN
       ========================================================= */
    case 'edit':
        $accountId  = (int)($_POST['account_id'] ?? 0);
        $userName   = trim($_POST['user_name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';
        $status     = $_POST['status'] ?? 'Active';

        if (!$accountId || !$userName || !$email) {
            redirectWithMessage('Required fields are missing.', 'error');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithMessage('Please enter a valid email address.', 'error');
        }
        if (!in_array($status, ['Active', 'Inactive'])) {
            $status = 'Active';
        }

        // Ensure the account exists and is an Admin (not Super Admin)
        $checkStmt = $db->prepare("SELECT id, role FROM tbl_accounts WHERE id = ?");
        $checkStmt->execute([$accountId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing || $existing['role'] !== 'Admin') {
            redirectWithMessage('Account not found or cannot be edited.', 'error');
        }

        // Check email uniqueness (excluding current)
        $checkEmail = $db->prepare("SELECT id FROM tbl_accounts WHERE email = ? AND id != ?");
        $checkEmail->execute([$email, $accountId]);
        if ($checkEmail->fetch()) {
            redirectWithMessage("Email <strong>{$email}</strong> is already in use by another account.", 'error');
        }

        // Build update query
        if ($password) {
            $minPwLen = (int) getSystemSetting('password_min_length', 8);
            if ($minPwLen < 6) $minPwLen = 8;
            if (strlen($password) < $minPwLen) {
                redirectWithMessage("Password must be at least {$minPwLen} characters long.", 'error');
            }
            if ($password !== $confirm) {
                redirectWithMessage('Passwords do not match.', 'error');
            }
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $updateStmt = $db->prepare("
                UPDATE tbl_accounts
                SET user_name = ?, email = ?, password = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND role = 'Admin'
            ");
            $updateStmt->execute([$userName, $email, $hashedPassword, $status, $accountId]);
        } else {
            $updateStmt = $db->prepare("
                UPDATE tbl_accounts
                SET user_name = ?, email = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND role = 'Admin'
            ");
            $updateStmt->execute([$userName, $email, $status, $accountId]);
        }

        logActivity(ACTION_UPDATE, MODULE_ACCOUNTS,
            "Updated Admin account for {$userName} ({$email}), Status: {$status}.");

        redirectWithMessage("Account for <strong>{$userName}</strong> has been updated successfully.");
        break;

    /* =========================================================
       TOGGLE STATUS
       ========================================================= */
    case 'activate':
    case 'deactivate':
        $accountId = (int)($_POST['account_id'] ?? 0);
        $newStatus = $action === 'activate' ? 'Active' : 'Inactive';

        $stmt = $db->prepare("
            UPDATE tbl_accounts SET status = ?, updated_at = NOW()
            WHERE id = ? AND role = 'Admin'
        ");
        $stmt->execute([$newStatus, $accountId]);

        $label = $newStatus === 'Active' ? 'activated' : 'deactivated';
        logActivity(ACTION_UPDATE, MODULE_ACCOUNTS, "Account ID {$accountId} {$label}.");
        redirectWithMessage("Account has been <strong>{$label}</strong> successfully.");
        break;

    case 'unlock':
        $accountId = (int)($_POST['account_id'] ?? 0);

        $stmt = $db->prepare("
            UPDATE tbl_accounts
            SET status = 'Active', failed_login_attempts = 0, locked_until = NULL, updated_at = NOW()
            WHERE id = ? AND role = 'Admin'
        ");
        $stmt->execute([$accountId]);

        logActivity(ACTION_UPDATE, MODULE_ACCOUNTS, "Unlocked account ID {$accountId}.");
        redirectWithMessage("Account has been <strong>unlocked</strong> successfully.");
        break;

    case 'delete':
        $accountId = (int)($_POST['account_id'] ?? 0);

        $checkStmt = $db->prepare("SELECT role FROM tbl_accounts WHERE id = ?");
        $checkStmt->execute([$accountId]);
        $acc = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc || $acc['role'] !== 'Admin') {
            redirectWithMessage('Account not found or cannot be deleted.', 'error');
        }

        $stmt = $db->prepare("DELETE FROM tbl_accounts WHERE id = ? AND role = 'Admin'");
        $stmt->execute([$accountId]);

        logActivity(ACTION_DELETE, MODULE_ACCOUNTS, "Deleted Admin account ID {$accountId}.");
        redirectWithMessage("Admin account has been <strong>deleted</strong> successfully.");
        break;

    default:
        redirectWithMessage('Invalid action.', 'error');
}