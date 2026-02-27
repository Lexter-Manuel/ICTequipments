<?php
/**
 * manage_profile.php — AJAX handler for My Profile actions
 */
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = getDB();

switch ($action) {

    // ── Update Display Name ──
    case 'update_name':
        $name = trim($input['user_name'] ?? '');
        if (strlen($name) < 2 || strlen($name) > 100) {
            jsonResponse(['success' => false, 'message' => 'Name must be 2–100 characters']);
        }

        $stmt = $db->prepare("UPDATE tbl_accounts SET user_name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $userId]);

        $_SESSION['user_name'] = $name;
        logActivity(ACTION_UPDATE, MODULE_PROFILE, 'Changed display name');

        jsonResponse(['success' => true, 'message' => 'Display name updated']);
        break;

    // ── Update Email ──
    case 'update_email':
        $email = trim($input['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address']);
        }

        // Check if email is already taken by another user
        $check = $db->prepare("SELECT id FROM tbl_accounts WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            jsonResponse(['success' => false, 'message' => 'This email is already in use by another account']);
        }

        $stmt = $db->prepare("UPDATE tbl_accounts SET email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$email, $userId]);

        $_SESSION['email'] = $email;
        logActivity(ACTION_UPDATE, MODULE_PROFILE, 'Changed email address');

        jsonResponse(['success' => true, 'message' => 'Email updated']);
        break;

    // ── Change Password ──
    case 'change_password':
        $currentPw = $input['current_password'] ?? '';
        $newPw = $input['new_password'] ?? '';

        $minPwLen = (int) getSystemSetting('password_min_length', 8);
        if ($minPwLen < 6) $minPwLen = 8;
        if (strlen($newPw) < $minPwLen) {
            jsonResponse(['success' => false, 'message' => "New password must be at least {$minPwLen} characters"]);
        }

        // Verify current password
        $stmt = $db->prepare("SELECT password FROM tbl_accounts WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPw, $row['password'])) {
            jsonResponse(['success' => false, 'message' => 'Current password is incorrect']);
        }

        // Hash and save new password
        $hashed = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("UPDATE tbl_accounts SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashed, $userId]);

        logActivity(ACTION_UPDATE, MODULE_PROFILE, 'Changed account password');

        jsonResponse(['success' => true, 'message' => 'Password changed successfully']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}
