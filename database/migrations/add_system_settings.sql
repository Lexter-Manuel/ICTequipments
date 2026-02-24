-- ============================================================
-- Migration: Add system_settings table & extend tbl_accounts
-- Run against: nia-inventory database
-- ============================================================

-- System-wide key/value settings
CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key`   VARCHAR(100) NOT NULL PRIMARY KEY,
    `setting_value` TEXT         NULL,
    `setting_group` VARCHAR(50)  NOT NULL DEFAULT 'general',
    `label`         VARCHAR(200) NULL,
    `description`   VARCHAR(500) NULL,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    `updated_by`    INT(11)      NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `description`) VALUES
-- Organization
('org_name',            'NIA UPRIIS',                               'organization', 'Organization Name',        'Full name of the organization'),
('org_short_name',      'UPRIIS',                                   'organization', 'Short Name / Acronym',     'Abbreviated name used in headers'),
('org_address',         '',                                         'organization', 'Office Address',           'Physical address of the office'),
('org_contact_email',   '',                                         'organization', 'Contact Email',            'Primary contact email'),
('org_contact_phone',   '',                                         'organization', 'Contact Phone',            'Primary contact phone number'),

-- Security & Session
('session_timeout',     '3600',                                     'security',     'Session Timeout (seconds)','Auto-logout after inactivity'),
('max_login_attempts',  '5',                                        'security',     'Max Login Attempts',       'Failed attempts before lockout'),
('lockout_duration',    '900',                                      'security',     'Lockout Duration (seconds)','How long account stays locked'),
('password_min_length', '8',                                        'security',     'Minimum Password Length',  'Minimum characters for passwords'),
('enforce_2fa',         '0',                                        'security',     'Enforce 2FA',              'Require two-factor authentication for all users'),

-- Maintenance Defaults
('maint_default_frequency',     'quarterly',                        'maintenance',  'Default Frequency',        'Default maintenance schedule frequency'),
('maint_overdue_threshold_days','7',                                'maintenance',  'Overdue Threshold (days)', 'Days past due before flagged overdue'),
('maint_reminder_days_before',  '7',                                'maintenance',  'Reminder Lead Days',       'Days before due date to show reminders'),
('maint_auto_schedule',         '1',                                'maintenance',  'Auto-Schedule Next',       'Automatically create next schedule after completion'),

-- System
('date_format',         'M d, Y',                                   'system',       'Date Display Format',      'PHP date format for display'),
('items_per_page',      '25',                                       'system',       'Default Items Per Page',   'Default pagination size'),
('enable_activity_log', '1',                                        'system',       'Enable Activity Logging',  'Log user actions in the system'),
('backup_retention_days','30',                                      'system',       'Log Retention (days)',      'Days to retain activity logs before cleanup');
