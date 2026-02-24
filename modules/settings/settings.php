<?php
/**
 * System Settings — Super Admin only
 * Manages organization info, security, maintenance defaults, system config
 */
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/permissions.php';

requireSuperAdmin();

$db = getDB();

// Load all settings into an associative array
$allSettings = [];
$stmt = $db->query("SELECT setting_key, setting_value, setting_group, label, description FROM system_settings ORDER BY setting_group, setting_key");
foreach ($stmt->fetchAll() as $row) {
    $allSettings[$row['setting_key']] = $row;
}

// Helper to get setting value
function sv($key) {
    global $allSettings;
    return $allSettings[$key]['setting_value'] ?? '';
}

// Get system stats for the dashboard cards
$accountCount = $db->query("SELECT COUNT(*) FROM tbl_accounts")->fetchColumn();
$activeAccounts = $db->query("SELECT COUNT(*) FROM tbl_accounts WHERE status = 'Active'")->fetchColumn();
$logCount = $db->query("SELECT COUNT(*) FROM tbl_activity_logs")->fetchColumn();
$equipmentCount = $db->query("
    SELECT (SELECT COUNT(*) FROM tbl_systemunit)
         + (SELECT COUNT(*) FROM tbl_monitor)
         + (SELECT COUNT(*) FROM tbl_allinone)
         + (SELECT COUNT(*) FROM tbl_printer)
         + (SELECT COUNT(*) FROM tbl_otherequipment) AS total
")->fetchColumn();
?>

<link rel="stylesheet" href="assets/css/profile-settings.css?v=<?php echo time(); ?>">

<div class="ps-page">

    <!-- ── PAGE HEADER ── -->
    <div class="ps-page-header">
        <div class="ps-header-left">
            <div class="ps-header-icon" style="background: linear-gradient(135deg, #0369a1 0%, #075985 100%);">
                <i class="fas fa-cog"></i>
            </div>
            <div>
                <h1 class="ps-page-title">System Settings</h1>
                <p class="ps-page-subtitle">Configure system-wide preferences and defaults</p>
            </div>
        </div>
    </div>

    <!-- Stats bar -->
    <div class="ps-stats-bar">
        <div class="ps-stat-chip">
            <i class="fas fa-users"></i>
            <span><strong><?php echo $accountCount; ?></strong> Accounts (<?php echo $activeAccounts; ?> active)</span>
        </div>
        <div class="ps-stat-chip">
            <i class="fas fa-boxes-stacked"></i>
            <span><strong><?php echo $equipmentCount; ?></strong> Equipment items</span>
        </div>
        <div class="ps-stat-chip">
            <i class="fas fa-list"></i>
            <span><strong><?php echo number_format($logCount); ?></strong> Activity log entries</span>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="toggle-nav ps-settings-tabs" id="settingsTabs">
        <button class="toggle-btn active" onclick="switchSettingsTab('organization', this)">
            <i class="fas fa-building"></i> Organization
        </button>
        <button class="toggle-btn" onclick="switchSettingsTab('security', this)">
            <i class="fas fa-shield-alt"></i> Security
        </button>
        <button class="toggle-btn" onclick="switchSettingsTab('maintenance', this)">
            <i class="fas fa-tools"></i> Maintenance
        </button>
        <button class="toggle-btn" onclick="switchSettingsTab('system', this)">
            <i class="fas fa-sliders-h"></i> System
        </button>
        <button class="toggle-btn" onclick="switchSettingsTab('data', this)">
            <i class="fas fa-database"></i> Data
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════
         TAB PANELS
         ═══════════════════════════════════════════════════════ -->

    <!-- Organization -->
    <div class="ps-settings-panel active" id="panel-organization">
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon"><i class="fas fa-building"></i></div>
                <div>
                    <h3 class="ps-card-title">Organization Information</h3>
                    <p class="ps-card-desc">Basic details about your organization</p>
                </div>
            </div>
            <div class="ps-card-body">
                <form id="formOrganization" onsubmit="return settingsAction.saveGroup(event, 'organization')">
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Organization Name</label>
                            <input type="text" class="ps-input" name="org_name" value="<?php echo htmlspecialchars(sv('org_name')); ?>">
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Short Name / Acronym</label>
                            <input type="text" class="ps-input" name="org_short_name" value="<?php echo htmlspecialchars(sv('org_short_name')); ?>">
                        </div>
                    </div>
                    <div class="ps-form-group">
                        <label class="ps-label">Office Address</label>
                        <input type="text" class="ps-input" name="org_address" value="<?php echo htmlspecialchars(sv('org_address')); ?>" placeholder="Enter street address, city, province">
                    </div>
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Contact Email</label>
                            <input type="email" class="ps-input" name="org_contact_email" value="<?php echo htmlspecialchars(sv('org_contact_email')); ?>" placeholder="e.g. admin@upriis.nia.gov.ph">
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Contact Phone</label>
                            <input type="text" class="ps-input" name="org_contact_phone" value="<?php echo htmlspecialchars(sv('org_contact_phone')); ?>" placeholder="e.g. (044) 123-4567">
                        </div>
                    </div>
                    <div class="ps-card-actions">
                        <button type="submit" class="ps-btn ps-btn-primary"><i class="fas fa-save"></i> Save Organization Info</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security -->
    <div class="ps-settings-panel" id="panel-security">
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon" style="background: var(--color-warning-bg); color: var(--color-warning);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 class="ps-card-title">Security & Authentication</h3>
                    <p class="ps-card-desc">Session timeouts, login restrictions, and password policies</p>
                </div>
            </div>
            <div class="ps-card-body">
                <form id="formSecurity" onsubmit="return settingsAction.saveGroup(event, 'security')">
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Session Timeout</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="session_timeout" value="<?php echo htmlspecialchars(sv('session_timeout')); ?>" min="300" max="86400">
                                <span class="ps-input-unit">seconds</span>
                            </div>
                            <span class="ps-help-text">Current: <?php echo round(intval(sv('session_timeout')) / 60); ?> minutes. Range: 5 min – 24 hours</span>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Max Login Attempts</label>
                            <input type="number" class="ps-input" name="max_login_attempts" value="<?php echo htmlspecialchars(sv('max_login_attempts')); ?>" min="3" max="20">
                            <span class="ps-help-text">Failed attempts before account lockout</span>
                        </div>
                    </div>
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Lockout Duration</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="lockout_duration" value="<?php echo htmlspecialchars(sv('lockout_duration')); ?>" min="60" max="86400">
                                <span class="ps-input-unit">seconds</span>
                            </div>
                            <span class="ps-help-text">Current: <?php echo round(intval(sv('lockout_duration')) / 60); ?> minutes</span>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Minimum Password Length</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="password_min_length" value="<?php echo htmlspecialchars(sv('password_min_length')); ?>" min="6" max="32">
                                <span class="ps-input-unit">chars</span>
                            </div>
                        </div>
                    </div>
                    <div class="ps-form-group">
                        <label class="ps-toggle-row">
                            <input type="checkbox" name="enforce_2fa" value="1" <?php echo sv('enforce_2fa') === '1' ? 'checked' : ''; ?>>
                            <span class="ps-toggle-switch"></span>
                            <span class="ps-toggle-label">
                                <strong>Enforce Two-Factor Authentication</strong>
                                <small>Require 2FA for all user accounts</small>
                            </span>
                        </label>
                    </div>
                    <div class="ps-card-actions">
                        <button type="submit" class="ps-btn ps-btn-primary"><i class="fas fa-save"></i> Save Security Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Maintenance -->
    <div class="ps-settings-panel" id="panel-maintenance">
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon"><i class="fas fa-tools"></i></div>
                <div>
                    <h3 class="ps-card-title">Maintenance Defaults</h3>
                    <p class="ps-card-desc">Default settings for preventive maintenance scheduling</p>
                </div>
            </div>
            <div class="ps-card-body">
                <form id="formMaintenance" onsubmit="return settingsAction.saveGroup(event, 'maintenance')">
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Default Frequency</label>
                            <select class="ps-input" name="maint_default_frequency">
                                <?php
                                $freqOptions = ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'semi-annual' => 'Semi-Annual', 'annual' => 'Annual'];
                                $currentFreq = sv('maint_default_frequency');
                                foreach ($freqOptions as $val => $label) {
                                    $selected = ($val === $currentFreq) ? 'selected' : '';
                                    echo "<option value=\"$val\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                            <span class="ps-help-text">Used when creating new maintenance schedules</span>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Overdue Threshold</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="maint_overdue_threshold_days" value="<?php echo htmlspecialchars(sv('maint_overdue_threshold_days')); ?>" min="1" max="90">
                                <span class="ps-input-unit">days</span>
                            </div>
                            <span class="ps-help-text">Days past due before flagged as overdue</span>
                        </div>
                    </div>
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Reminder Lead Time</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="maint_reminder_days_before" value="<?php echo htmlspecialchars(sv('maint_reminder_days_before')); ?>" min="1" max="60">
                                <span class="ps-input-unit">days</span>
                            </div>
                            <span class="ps-help-text">Show reminders this many days before due date</span>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">&nbsp;</label>
                            <label class="ps-toggle-row">
                                <input type="checkbox" name="maint_auto_schedule" value="1" <?php echo sv('maint_auto_schedule') === '1' ? 'checked' : ''; ?>>
                                <span class="ps-toggle-switch"></span>
                                <span class="ps-toggle-label">
                                    <strong>Auto-Schedule Next Maintenance</strong>
                                    <small>Automatically create next schedule after completion</small>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="ps-card-actions">
                        <button type="submit" class="ps-btn ps-btn-primary"><i class="fas fa-save"></i> Save Maintenance Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- System -->
    <div class="ps-settings-panel" id="panel-system">
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon" style="background: var(--color-info-bg); color: var(--color-info);">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h3 class="ps-card-title">System Preferences</h3>
                    <p class="ps-card-desc">Display formats, pagination, and logging</p>
                </div>
            </div>
            <div class="ps-card-body">
                <form id="formSystem" onsubmit="return settingsAction.saveGroup(event, 'system')">
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-label">Date Display Format</label>
                            <select class="ps-input" name="date_format">
                                <?php
                                $dateFormats = ['M d, Y' => 'Feb 24, 2026', 'Y-m-d' => '2026-02-24', 'd/m/Y' => '24/02/2026', 'F j, Y' => 'February 24, 2026'];
                                $currentFmt = sv('date_format');
                                foreach ($dateFormats as $val => $label) {
                                    $selected = ($val === $currentFmt) ? 'selected' : '';
                                    echo "<option value=\"$val\" $selected>$label ($val)</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Default Items Per Page</label>
                            <select class="ps-input" name="items_per_page">
                                <?php
                                $perPageOptions = [10, 25, 50, 100];
                                $currentPP = sv('items_per_page');
                                foreach ($perPageOptions as $val) {
                                    $selected = ($val == $currentPP) ? 'selected' : '';
                                    echo "<option value=\"$val\" $selected>$val items</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="ps-form-row">
                        <div class="ps-form-group">
                            <label class="ps-toggle-row">
                                <input type="checkbox" name="enable_activity_log" value="1" <?php echo sv('enable_activity_log') === '1' ? 'checked' : ''; ?>>
                                <span class="ps-toggle-switch"></span>
                                <span class="ps-toggle-label">
                                    <strong>Enable Activity Logging</strong>
                                    <small>Record user actions in the activity log</small>
                                </span>
                            </label>
                        </div>
                        <div class="ps-form-group">
                            <label class="ps-label">Log Retention</label>
                            <div class="ps-input-with-unit">
                                <input type="number" class="ps-input" name="backup_retention_days" value="<?php echo htmlspecialchars(sv('backup_retention_days')); ?>" min="7" max="365">
                                <span class="ps-input-unit">days</span>
                            </div>
                            <span class="ps-help-text">Activity logs older than this will be eligible for cleanup</span>
                        </div>
                    </div>
                    <div class="ps-card-actions">
                        <button type="submit" class="ps-btn ps-btn-primary"><i class="fas fa-save"></i> Save System Preferences</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Info (read-only) -->
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon" style="background: var(--bg-light); color: var(--text-medium);">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="ps-card-title">System Information</h3>
                    <p class="ps-card-desc">Read-only environment details</p>
                </div>
            </div>
            <div class="ps-card-body">
                <div class="ps-info-grid">
                    <div class="ps-info-item">
                        <span class="ps-info-label">Application</span>
                        <span class="ps-info-value"><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></span>
                    </div>
                    <div class="ps-info-item">
                        <span class="ps-info-label">PHP Version</span>
                        <span class="ps-info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="ps-info-item">
                        <span class="ps-info-label">Database</span>
                        <span class="ps-info-value">MySQL <?php echo $db->query("SELECT VERSION()")->fetchColumn(); ?></span>
                    </div>
                    <div class="ps-info-item">
                        <span class="ps-info-label">Server</span>
                        <span class="ps-info-value"><?php echo php_uname('s') . ' ' . php_uname('r'); ?></span>
                    </div>
                    <div class="ps-info-item">
                        <span class="ps-info-label">Timezone</span>
                        <span class="ps-info-value"><?php echo date_default_timezone_get(); ?></span>
                    </div>
                    <div class="ps-info-item">
                        <span class="ps-info-label">Max Upload Size</span>
                        <span class="ps-info-value"><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Management -->
    <div class="ps-settings-panel" id="panel-data">
        <div class="ps-card">
            <div class="ps-card-header">
                <div class="ps-card-icon" style="background: var(--color-danger-bg); color: var(--color-danger);">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <h3 class="ps-card-title">Data Management</h3>
                    <p class="ps-card-desc">Activity log cleanup and maintenance tools</p>
                </div>
            </div>
            <div class="ps-card-body">
                <div class="ps-data-actions">

                    <!-- Purge Old Logs -->
                    <div class="ps-data-action-card">
                        <div class="ps-data-action-info">
                            <h4><i class="fas fa-broom"></i> Purge Old Activity Logs</h4>
                            <p>Remove activity log entries older than the configured retention period (<?php echo sv('backup_retention_days'); ?> days).
                               This helps keep the database lean and queries fast.</p>
                            <span class="ps-data-stat">
                                <?php
                                $retDays = intval(sv('backup_retention_days')) ?: 30;
                                $oldCount = $db->prepare("SELECT COUNT(*) FROM tbl_activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
                                $oldCount->execute([$retDays]);
                                echo $oldCount->fetchColumn() . ' eligible entries';
                                ?>
                            </span>
                        </div>
                        <button class="ps-btn ps-btn-danger-outline" onclick="settingsAction.purgeOldLogs()">
                            <i class="fas fa-trash-alt"></i> Purge Logs
                        </button>
                    </div>

                    <!-- Clear Login Attempts -->
                    <div class="ps-data-action-card">
                        <div class="ps-data-action-info">
                            <h4><i class="fas fa-unlock-alt"></i> Clear Login Attempts</h4>
                            <p>Remove all recorded login attempt entries. Useful after resolving lockout issues or
                               cleaning up after security testing.</p>
                            <span class="ps-data-stat">
                                <?php echo $db->query("SELECT COUNT(*) FROM login_attempts")->fetchColumn() . ' entries'; ?>
                            </span>
                        </div>
                        <button class="ps-btn ps-btn-danger-outline" onclick="settingsAction.clearLoginAttempts()">
                            <i class="fas fa-eraser"></i> Clear Attempts
                        </button>
                    </div>

                    <!-- Reset Page Cache -->
                    <div class="ps-data-action-card">
                        <div class="ps-data-action-info">
                            <h4><i class="fas fa-sync-alt"></i> Clear Page Cache</h4>
                            <p>Force the dashboard to reload all pages fresh. Useful after applying updates or
                               if pages show outdated content.</p>
                        </div>
                        <button class="ps-btn ps-btn-secondary" onclick="settingsAction.clearPageCache()">
                            <i class="fas fa-redo"></i> Clear Cache
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<div class="ps-toast" id="settingsToast"></div>

<script>
function switchSettingsTab(name, btn) {
    document.querySelectorAll('.ps-settings-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('#settingsTabs .toggle-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
}

var settingsAction = {

    showToast: function(msg, type) {
        var toast = document.getElementById('settingsToast');
        toast.textContent = msg;
        toast.className = 'ps-toast ps-toast-' + (type || 'success') + ' ps-toast-show';
        setTimeout(function() { toast.classList.remove('ps-toast-show'); }, 3500);
    },

    saveGroup: function(e, group) {
        e.preventDefault();
        var form = e.target;
        var btn = form.querySelector('button[type=submit]');
        var origHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        // Collect form data as key-value pairs
        var settings = {};
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            if (!input.name) return;
            if (input.type === 'checkbox') {
                settings[input.name] = input.checked ? '1' : '0';
            } else {
                settings[input.name] = input.value;
            }
        });

        fetch('../ajax/manage_settings.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'save_settings', group: group, settings: settings })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.innerHTML = origHtml;
            btn.disabled = false;
            if (data.success) {
                settingsAction.showToast(data.message || 'Settings saved successfully');
                // If items_per_page was saved, update all equipment tables immediately
                if (group === 'system' && settings['items_per_page']) {
                    settingsAction.applyPerPageToEquipment(parseInt(settings['items_per_page']));
                }
            } else {
                settingsAction.showToast(data.message || 'Failed to save', 'error');
            }
        })
        .catch(function() {
            btn.innerHTML = origHtml;
            btn.disabled = false;
            settingsAction.showToast('Network error', 'error');
        });
        return false;
    },

    purgeOldLogs: function() {
        if (!confirm('Are you sure you want to permanently delete old activity logs? This action cannot be undone.')) return;
        fetch('../ajax/manage_settings.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'purge_old_logs' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            settingsAction.showToast(data.message || (data.success ? 'Logs purged' : 'Error'), data.success ? 'success' : 'error');
            if (data.success) reloadCurrentPage();
        })
        .catch(function() { settingsAction.showToast('Network error', 'error'); });
    },

    clearLoginAttempts: function() {
        if (!confirm('Clear all login attempt records? This cannot be undone.')) return;
        fetch('../ajax/manage_settings.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'clear_login_attempts' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            settingsAction.showToast(data.message || (data.success ? 'Cleared' : 'Error'), data.success ? 'success' : 'error');
            if (data.success) reloadCurrentPage();
        })
        .catch(function() { settingsAction.showToast('Network error', 'error'); });
    },

    clearPageCache: function() {
        if (window.dashboardApp) {
            window.dashboardApp.pageCache = {};
            settingsAction.showToast('Page cache cleared');
        }
    },

    // Push a new items_per_page value to all equipment table pagination controls
    // if the equipment page is currently loaded in the dashboard
    applyPerPageToEquipment: function(newPP) {
        if (!newPP || isNaN(newPP)) return;
        var pp = String(newPP);

        // Update each select and re-run its changePerPage function if it exists
        var tables = [
            { selectId: 'suPerPageSelect',    changeFn: 'changePerPageSU',    varName: 'suPerPage'      },
            { selectId: 'monPerPageSelect',   changeFn: 'changePerPageMon',   varName: 'monPerPage'     },
            { selectId: 'aioPerPageSelect',   changeFn: 'changePerPageAIO',   varName: 'aioPerPage'     },
            { selectId: 'prPerPageSelect',    changeFn: 'changePerPagePR',    varName: 'printerPerPage' },
            { selectId: 'otherPerPageSelect', changeFn: 'changePerPageOther', varName: 'otherPerPage'   }
        ];

        var anyUpdated = false;
        tables.forEach(function(t) {
            var el = document.getElementById(t.selectId);
            if (!el) return;
            var opt = el.querySelector('option[value="' + pp + '"]');
            if (opt) {
                el.value = pp;
                // Also update the JS variable directly if accessible
                if (typeof window[t.varName] !== 'undefined') {
                    window[t.varName] = newPP;
                }
                if (typeof window[t.changeFn] === 'function') {
                    window[t.changeFn]();
                }
                anyUpdated = true;
            }
        });

        // Also update defaultPerPage for future table inits
        if (typeof window.defaultPerPage !== 'undefined') {
            window.defaultPerPage = newPP;
        }
    }
};
</script>