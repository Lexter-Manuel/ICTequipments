<?php
/**
 * My Profile — Account settings for the current user
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';

$db = getDB();
$userId = $_SESSION['user_id'];

// Fetch current user data
$stmt = $db->prepare("SELECT id, user_name, email, role, status, last_login, last_login_ip, created_at FROM tbl_accounts WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch recent activity logs
$stmtLogs = $db->prepare("
    SELECT action, details, ip_address, created_at
    FROM tbl_activity_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$stmtLogs->execute([$userId]);
$recentLogs = $stmtLogs->fetchAll();

// Fetch active sessions / login attempts
$stmtLogins = $db->prepare("
    SELECT email, ip_address, user_agent, attempt_time, success
    FROM login_attempts
    WHERE email = ?
    ORDER BY attempt_time DESC
    LIMIT 10
");
$stmtLogins->execute([$user['email']]);
$loginHistory = $stmtLogins->fetchAll();

$initials = '';
$parts = explode(' ', trim($user['user_name']));
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>

<link rel="stylesheet" href="assets/css/profile-settings.css?v=<?php echo time(); ?>">

<div class="ps-page">

    <!-- ── PAGE HEADER ── -->
    <div class="ps-page-header">
        <div class="ps-header-left">
            <div class="ps-header-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <h1 class="ps-page-title">My Profile</h1>
                <p class="ps-page-subtitle">Manage your account information and security</p>
            </div>
        </div>
    </div>

    <div class="ps-layout">

        <!-- ── LEFT: Profile Card ── -->
        <div class="ps-sidebar-card">
            <div class="ps-profile-card">
                <div class="ps-avatar-lg"><?php echo $initials; ?></div>
                <h3 class="ps-profile-name"><?php echo htmlspecialchars($user['user_name']); ?></h3>
                <p class="ps-profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="ps-role-badge">
                    <i class="fas fa-shield-alt"></i>
                    <?php echo htmlspecialchars($user['role']); ?>
                </span>
            </div>
            <div class="ps-profile-meta">
                <div class="ps-meta-item">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <span class="ps-meta-label">Member Since</span>
                        <span class="ps-meta-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
                <div class="ps-meta-item">
                    <i class="fas fa-sign-in-alt"></i>
                    <div>
                        <span class="ps-meta-label">Last Login</span>
                        <span class="ps-meta-value"><?php echo $user['last_login'] ? date('M d, Y g:i A', strtotime($user['last_login'])) : 'N/A'; ?></span>
                    </div>
                </div>
                <div class="ps-meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <span class="ps-meta-label">Last IP</span>
                        <span class="ps-meta-value"><?php echo htmlspecialchars($user['last_login_ip'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                <div class="ps-meta-item">
                    <i class="fas fa-check-circle" style="color: var(--color-success)"></i>
                    <div>
                        <span class="ps-meta-label">Status</span>
                        <span class="ps-meta-value"><?php echo htmlspecialchars($user['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── RIGHT: Forms & Activity ── -->
        <div class="ps-main-content">

            <!-- Update Display Name -->
            <div class="ps-card">
                <div class="ps-card-header">
                    <div class="ps-card-icon"><i class="fas fa-id-card"></i></div>
                    <div>
                        <h3 class="ps-card-title">Display Name</h3>
                        <p class="ps-card-desc">This name appears across the system</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form id="formDisplayName" onsubmit="return profileAction.updateName(event)">
                        <div class="ps-form-group">
                            <label class="ps-label" for="profileName">Full Name</label>
                            <input type="text" id="profileName" class="ps-input" value="<?php echo htmlspecialchars($user['user_name']); ?>" required minlength="2" maxlength="100">
                        </div>
                        <div class="ps-card-actions">
                            <button type="submit" class="ps-btn ps-btn-primary" id="btnSaveName">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Email -->
            <div class="ps-card">
                <div class="ps-card-header">
                    <div class="ps-card-icon"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h3 class="ps-card-title">Email Address</h3>
                        <p class="ps-card-desc">Used for login and account recovery</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form id="formEmail" onsubmit="return profileAction.updateEmail(event)">
                        <div class="ps-form-group">
                            <label class="ps-label" for="profileEmail">Email</label>
                            <input type="email" id="profileEmail" class="ps-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="ps-card-actions">
                            <button type="submit" class="ps-btn ps-btn-primary" id="btnSaveEmail">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="ps-card">
                <div class="ps-card-header">
                    <div class="ps-card-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="ps-card-title">Change Password</h3>
                        <p class="ps-card-desc">Use a strong password with at least 8 characters</p>
                    </div>
                </div>
                <div class="ps-card-body">
                    <form id="formPassword" onsubmit="return profileAction.changePassword(event)">
                        <div class="ps-form-group">
                            <label class="ps-label" for="currentPassword">Current Password</label>
                            <div class="ps-input-wrapper">
                                <input type="password" id="currentPassword" class="ps-input" required autocomplete="current-password">
                                <button type="button" class="ps-input-toggle" onclick="profileAction.togglePassword(this)" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="ps-form-row">
                            <div class="ps-form-group">
                                <label class="ps-label" for="newPassword">New Password</label>
                                <div class="ps-input-wrapper">
                                    <input type="password" id="newPassword" class="ps-input" required minlength="8" autocomplete="new-password">
                                    <button type="button" class="ps-input-toggle" onclick="profileAction.togglePassword(this)" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ps-form-group">
                                <label class="ps-label" for="confirmPassword">Confirm New Password</label>
                                <div class="ps-input-wrapper">
                                    <input type="password" id="confirmPassword" class="ps-input" required minlength="8" autocomplete="new-password">
                                    <button type="button" class="ps-input-toggle" onclick="profileAction.togglePassword(this)" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="passwordStrength" class="ps-password-strength"></div>
                        <div class="ps-card-actions">
                            <button type="submit" class="ps-btn ps-btn-warning" id="btnChangePassword">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="ps-card">
                <div class="ps-card-header">
                    <div class="ps-card-icon" style="background: var(--color-info-bg); color: var(--color-info);">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h3 class="ps-card-title">Recent Activity</h3>
                        <p class="ps-card-desc">Your last 10 actions in the system</p>
                    </div>
                </div>
                <div class="ps-card-body ps-no-padding">
                    <div class="ps-activity-list">
                        <?php if (empty($recentLogs)): ?>
                            <div class="ps-empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No activity recorded yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <div class="ps-activity-item">
                                    <div class="ps-activity-dot"></div>
                                    <div class="ps-activity-content">
                                        <span class="ps-activity-action"><?php echo htmlspecialchars($log['action']); ?></span>
                                        <?php if ($log['details']): ?>
                                            <span class="ps-activity-detail"><?php echo htmlspecialchars($log['details']); ?></span>
                                        <?php endif; ?>
                                        <span class="ps-activity-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('M d, Y g:i A', strtotime($log['created_at'])); ?>
                                            &middot; <?php echo htmlspecialchars($log['ip_address']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Login History -->
            <div class="ps-card">
                <div class="ps-card-header">
                    <div class="ps-card-icon" style="background: var(--color-danger-bg); color: var(--color-danger);">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <h3 class="ps-card-title">Login History</h3>
                        <p class="ps-card-desc">Recent login attempts to your account</p>
                    </div>
                </div>
                <div class="ps-card-body ps-no-padding">
                    <div class="ps-table-wrap">
                        <table class="ps-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                    <th>Browser</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($loginHistory)): ?>
                                    <tr><td colspan="5" class="ps-empty-cell" data-label="">No login records found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($loginHistory as $loginIdx => $login): ?>
                                        <tr>
                                            <td data-label="#"><?php echo $loginIdx + 1; ?></td>
                                            <td data-label="Status">
                                                <?php if ($login['success']): ?>
                                                    <span class="ps-badge ps-badge-success"><i class="fas fa-check-circle"></i> Success</span>
                                                <?php else: ?>
                                                    <span class="ps-badge ps-badge-danger"><i class="fas fa-times-circle"></i> Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="IP"><code><?php echo htmlspecialchars($login['ip_address']); ?></code></td>
                                            <td data-label="Browser" class="ps-truncate"><?php
                                                $ua = $login['user_agent'] ?? '';
                                                if (stripos($ua, 'Chrome') !== false) echo '<i class="fab fa-chrome"></i> Chrome';
                                                elseif (stripos($ua, 'Firefox') !== false) echo '<i class="fab fa-firefox-browser"></i> Firefox';
                                                elseif (stripos($ua, 'Safari') !== false) echo '<i class="fab fa-safari"></i> Safari';
                                                elseif (stripos($ua, 'Edge') !== false) echo '<i class="fab fa-edge"></i> Edge';
                                                else echo '<i class="fas fa-globe"></i> Other';
                                            ?></td>
                                            <td data-label="Date"><?php echo date('M d, Y g:i A', strtotime($login['attempt_time'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /ps-main-content -->
    </div><!-- /ps-layout -->
</div>

<!-- Toast notification -->
<div class="ps-toast" id="profileToast"></div>

<script>
var profileAction = {

    showToast: function(msg, type) {
        var toast = document.getElementById('profileToast');
        toast.textContent = msg;
        toast.className = 'ps-toast ps-toast-' + (type || 'success') + ' ps-toast-show';
        setTimeout(function() { toast.classList.remove('ps-toast-show'); }, 3500);
    },

    setLoading: function(btn, loading) {
        if (loading) {
            btn.dataset.origText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.origText || btn.innerHTML;
            btn.disabled = false;
        }
    },

    togglePassword: function(btn) {
        var input = btn.parentElement.querySelector('input');
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    },

    updateName: function(e) {
        e.preventDefault();
        var btn = document.getElementById('btnSaveName');
        var name = document.getElementById('profileName').value.trim();
        if (!name) return;

        this.setLoading(btn, true);
        fetch('../ajax/manage_profile.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'update_name', user_name: name })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            profileAction.setLoading(btn, false);
            if (data.success) {
                profileAction.showToast('Display name updated successfully');
                // Update header dropdown name
                var headerName = document.querySelector('.username-text');
                if (headerName) headerName.textContent = name;
                var dropdownName = document.querySelector('.dropdown-user-name');
                if (dropdownName) dropdownName.textContent = name;
                // Update initials
                var parts = name.trim().split(' ');
                var initials = (parts[0] ? parts[0][0] : '') + (parts[1] ? parts[1][0] : '');
                initials = initials.toUpperCase();
                document.querySelectorAll('.avatar-initials, .avatar-lg, .ps-avatar-lg').forEach(function(el) {
                    el.textContent = initials;
                });
            } else {
                profileAction.showToast(data.message || 'Failed to update name', 'error');
            }
        })
        .catch(function() {
            profileAction.setLoading(btn, false);
            profileAction.showToast('Network error', 'error');
        });
        return false;
    },

    updateEmail: function(e) {
        e.preventDefault();
        var btn = document.getElementById('btnSaveEmail');
        var email = document.getElementById('profileEmail').value.trim();
        if (!email) return;

        this.setLoading(btn, true);
        fetch('../ajax/manage_profile.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'update_email', email: email })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            profileAction.setLoading(btn, false);
            if (data.success) {
                profileAction.showToast('Email updated successfully');
                var headerEmail = document.querySelector('.dropdown-user-email');
                if (headerEmail) headerEmail.textContent = email;
            } else {
                profileAction.showToast(data.message || 'Failed to update email', 'error');
            }
        })
        .catch(function() {
            profileAction.setLoading(btn, false);
            profileAction.showToast('Network error', 'error');
        });
        return false;
    },

    changePassword: function(e) {
        e.preventDefault();
        var btn = document.getElementById('btnChangePassword');
        var current = document.getElementById('currentPassword').value;
        var newPw = document.getElementById('newPassword').value;
        var confirm = document.getElementById('confirmPassword').value;

        if (newPw !== confirm) {
            this.showToast('New passwords do not match', 'error');
            return false;
        }
        if (newPw.length < 8) {
            this.showToast('Password must be at least 8 characters', 'error');
            return false;
        }

        this.setLoading(btn, true);
        fetch('../ajax/manage_profile.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'change_password', current_password: current, new_password: newPw })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            profileAction.setLoading(btn, false);
            if (data.success) {
                profileAction.showToast('Password changed successfully');
                document.getElementById('formPassword').reset();
            } else {
                profileAction.showToast(data.message || 'Failed to change password', 'error');
            }
        })
        .catch(function() {
            profileAction.setLoading(btn, false);
            profileAction.showToast('Network error', 'error');
        });
        return false;
    }
};

// Password strength indicator
document.getElementById('newPassword').addEventListener('input', function() {
    var pw = this.value;
    var el = document.getElementById('passwordStrength');
    if (!pw) { el.innerHTML = ''; return; }

    var score = 0;
    if (pw.length >= 8) score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    var levels = [
        { label: 'Very Weak', cls: 'danger', pct: 20 },
        { label: 'Weak', cls: 'danger', pct: 40 },
        { label: 'Fair', cls: 'warning', pct: 60 },
        { label: 'Strong', cls: 'success', pct: 80 },
        { label: 'Very Strong', cls: 'success', pct: 100 }
    ];
    var level = levels[Math.min(score, levels.length - 1)];

    el.innerHTML = '<div class="ps-strength-bar"><div class="ps-strength-fill ps-strength-' + level.cls + '" style="width:' + level.pct + '%"></div></div><span class="ps-strength-label ps-strength-' + level.cls + '">' + level.label + '</span>';
});
</script>
