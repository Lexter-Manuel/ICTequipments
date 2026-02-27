<?php
// modules/users/accounts.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../config/database.php';
require_once '../../config/permissions.php';

// Only Super Admin can access
requireSuperAdmin();

$db = Database::getInstance()->getConnection();

$message = $messageType = '';
if (isset($_SESSION['account_message'])) {
    $message = $_SESSION['account_message'];
    $messageType = $_SESSION['account_message_type'];
    unset($_SESSION['account_message'], $_SESSION['account_message_type']);
}

// Fetch all Admin accounts (exclude Super Admin)
$accountStmt = $db->query("
    SELECT a.*, 
           creator.user_name AS created_by_name
    FROM tbl_accounts a
    LEFT JOIN tbl_accounts creator ON a.created_by = creator.id
    WHERE a.role = 'Admin'
    ORDER BY a.created_at DESC
");
$accounts = $accountStmt->fetchAll(PDO::FETCH_ASSOC);

$totalAdmins  = count($accounts);
$activeCount  = count(array_filter($accounts, fn($a) => $a['status'] === 'Active'));
$inactiveCount = count(array_filter($accounts, fn($a) => $a['status'] === 'Inactive'));
$lockedCount  = count(array_filter($accounts, fn($a) => $a['status'] === 'Locked'));
?>

<link rel="stylesheet" href="assets/css/inventory.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/accounts.css?v=<?php echo time(); ?>">

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <h1 class="page-title">Account Management</h1>
            <p class="page-subtitle">Manage ICT Admin accounts</p>
        </div>
    </div>
    <button class="add-btn" onclick="toggleForm()">
        <i class="fas fa-user-plus"></i> Add New Admin
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
    <div><?php echo htmlspecialchars($message); ?></div>
</div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-users-cog stat-icon"></i>
        <div><div class="stat-label">Total Admins</div><div class="stat-value"><?php echo $totalAdmins; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-check stat-icon"></i>
        <div><div class="stat-label">Active</div><div class="stat-value"><?php echo $activeCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-slash stat-icon"></i>
        <div><div class="stat-label">Inactive</div><div class="stat-value"><?php echo $inactiveCount; ?></div></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-lock stat-icon"></i>
        <div><div class="stat-label">Locked</div><div class="stat-value"><?php echo $lockedCount; ?></div></div>
    </div>
</div>

<!-- Add Admin Form -->
<div class="form-container" id="accountFormContainer">
    <div class="form-header">
        <h2 class="form-title"><i class="fas fa-user-plus"></i> <span id="formTitleText">Add New Admin</span></h2>
        <button class="btn-close-form" onclick="toggleForm()"><i class="fas fa-times"></i> Close</button>
    </div>

    <form id="accountForm" method="POST" action="../ajax/process_account.php" autocomplete="off">
        <input type="hidden" name="action" value="add" id="formAction">
        <input type="hidden" name="account_id" id="accountId" value="">

        <div class="row">
            <!-- Account Information -->
            <div class="col-md-6">
                <h6 class="form-section-title"><i class="fas fa-id-badge"></i> Account Information</h6>

                <div class="mb-3">
                    <label for="userName" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="userName" name="user_name" required maxlength="100"
                           placeholder="e.g. Juan dela Cruz">
                    <small class="form-hint">Enter the full name of the ICT staff member.</small>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required maxlength="255"
                           placeholder="e.g. jdelacruz@upriis.local">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Password Section -->
            <div class="col-md-6">
                <h6 class="form-section-title"><i class="fas fa-key"></i> <span id="passwordSectionTitle">Set Password</span></h6>

                <div class="mb-3" id="newPasswordGroup">
                    <label for="password" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="8" placeholder="Minimum 8 characters" autocomplete="new-password">
                        <button type="button" class="btn-toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="password-strength-bar" id="strengthBar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <small class="form-hint" id="strengthLabel"></small>
                </div>

                <div class="mb-3" id="confirmPasswordGroup">
                    <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger" id="confirmRequired">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password"
                               placeholder="Re-enter password" autocomplete="new-password">
                        <button type="button" class="btn-toggle-password" onclick="togglePassword('confirmPassword')">
                            <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                        </button>
                    </div>
                    <small class="form-hint text-danger" id="passwordMatchMsg" style="display:none">
                        <i class="fas fa-exclamation-circle"></i> Passwords do not match.
                    </small>
                </div>

                <div class="mb-3 edit-only-hint" id="editPasswordHint" style="display:none">
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        Leave password fields blank to keep the current password unchanged.
                    </div>
                </div>

                <div class="account-role-badge">
                    <i class="fas fa-shield-alt"></i> Role: <strong>Admin (ICT)</strong>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-cancel" onclick="toggleForm()"><i class="fas fa-times"></i> Cancel</button>
            <button type="submit" class="btn-submit" id="formSubmitBtn"><i class="fas fa-save"></i> Add Admin</button>
        </div>
    </form>
</div>

<!-- Accounts Table -->
<div class="data-table">
    <div class="table-header">
        <h3 class="table-title"><i class="fas fa-list"></i> Admin Accounts</h3>
        <div class="table-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search accounts..." oninput="filterTable()">
            </div>
            <select class="filter-select" id="statusFilter" onchange="filterTable()">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Locked">Locked</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="accounts-table" id="accountsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-user-shield"></i>
                        <p>No admin accounts yet. Click <strong>Add New Admin</strong> to create one.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($accounts as $i => $acc): ?>
                <tr data-status="<?php echo $acc['status']; ?>">
                    <td class="row-counter"><?php echo $i + 1; ?></td>
                    <td>
                        <div class="account-info">
                            <div class="account-avatar">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <span class="account-name"><?php echo htmlspecialchars($acc['user_name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($acc['email']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($acc['status']); ?>">
                            <?php echo $acc['status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($acc['last_login']): ?>
                            <span title="<?php echo $acc['last_login']; ?>">
                                <?php echo date('M d, Y g:i A', strtotime($acc['last_login'])); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Never</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($acc['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit"
                                    onclick="editAccount(<?php echo htmlspecialchars(json_encode($acc)); ?>)"
                                    title="Edit Account">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($acc['status'] === 'Locked'): ?>
                            <button class="btn-action btn-unlock"
                                    onclick="unlockAccount(<?php echo $acc['id']; ?>, '<?php echo htmlspecialchars($acc['user_name']); ?>')"
                                    title="Unlock Account">
                                <i class="fas fa-unlock"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn-action btn-toggle-status"
                                    onclick="toggleStatus(<?php echo $acc['id']; ?>, '<?php echo $acc['status']; ?>', '<?php echo htmlspecialchars($acc['user_name']); ?>')"
                                    title="<?php echo $acc['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>">
                                <i class="fas fa-<?php echo $acc['status'] === 'Active' ? 'ban' : 'check-circle'; ?>"></i>
                            </button>
                            <button class="btn-action btn-delete"
                                    onclick="deleteAccount(<?php echo $acc['id']; ?>, '<?php echo htmlspecialchars($acc['user_name']); ?>')"
                                    title="Delete Account">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="confirm-modal">
        <div class="confirm-icon" id="confirmIcon"><i class="fas fa-question-circle"></i></div>
        <h3 id="confirmTitle">Confirm Action</h3>
        <p id="confirmMessage">Are you sure?</p>
        <div class="confirm-actions">
            <button class="btn-cancel" onclick="closeConfirmModal()"><i class="fas fa-times"></i> Cancel</button>
            <button class="btn-confirm-ok" id="confirmOkBtn"><i class="fas fa-check"></i> Confirm</button>
        </div>
    </div>
</div>

<script src="assets/js/accounts.js?v=<?php echo time(); ?>"></script>