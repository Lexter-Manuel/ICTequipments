<?php
/**
 * alerts.php — NIA UPRIIS ICT Inventory System
 * Alert System Reference
 *
 * This file is NO LONGER needed as a PHP include.
 * The alert system is self-initializing and loaded globally
 * via dashboard.php (the shell page):
 *
 *   <link href="assets/css/alerts.css"> ── in <head>
 *   <script src="assets/js/alerts.js">  ── in <body> (before other scripts)
 *
 * alerts.js automatically injects the toast container and
 * confirm modal into the DOM on page load. The global `Alerts`
 * object is available on every page.
 *
 * ── USAGE ─────────────────────────────────────────────────
 *
 *   // Toast notifications
 *   Alerts.success('Record saved successfully');
 *   Alerts.error('Failed to save record');
 *   Alerts.warning('This field is required');
 *   Alerts.info('Processing your request...');
 *
 *   // Confirm delete dialog
 *   Alerts.confirmDelete('this printer', function() {
 *       // execute delete ...
 *   });
 *
 *   // Custom confirmation dialog
 *   Alerts.confirmAction({
 *       title:       'Unassign Equipment?',
 *       message:     'This will remove it from the employee.',
 *       confirmText: 'Yes, Unassign',
 *       type:        'warning',   // 'danger' | 'warning' | 'primary'
 *       onConfirm:   function() { ... }
 *   });
 *
 *   // Inline form alerts (inside bootstrap modals)
 *   Alerts.formError('Please fill in all required fields');
 *   Alerts.formSuccess('Profile updated');
 *   Alerts.clearFormAlert();
 */
?>