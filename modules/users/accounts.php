<?php
// modules/users/accounts.php
session_start();
require_once '../../config/database.php';
require_once '../../config/permissions.php';

// Only Super Admin can access
requireSuperAdmin();

// Rest of the page...
?>