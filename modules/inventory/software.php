<?php
// modules/inventory/software.php
// Software License Management with sample data

// Sample data for testing (no database required)
$sampleSoftware = [
    [
        'softwareId' => 1,
        'licenseSoftware' => 'Microsoft Office 365 Business Premium',
        'licenseDetails' => '5-User License',
        'licenseType' => 'Subscription',
        'expiryDate' => '2026-12-31',
        'email' => 'admin@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => 325
    ],
    [
        'softwareId' => 2,
        'licenseSoftware' => 'Adobe Creative Cloud',
        'licenseDetails' => 'All Apps - Single User',
        'licenseType' => 'Subscription',
        'expiryDate' => '2026-06-15',
        'email' => 'design@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => 126
    ],
    [
        'softwareId' => 3,
        'licenseSoftware' => 'AutoCAD 2024',
        'licenseDetails' => 'Professional License',
        'licenseType' => 'Subscription',
        'expiryDate' => '2026-03-20',
        'email' => 'engineering@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Expiring Soon',
        'daysUntilExpiry' => 39
    ],
    [
        'softwareId' => 4,
        'licenseSoftware' => 'Windows Server 2022 Standard',
        'licenseDetails' => '16-Core License',
        'licenseType' => 'Perpetual',
        'expiryDate' => null,
        'email' => null,
        'password' => null,
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => null
    ],
    [
        'softwareId' => 5,
        'licenseSoftware' => 'Adobe Photoshop CS6',
        'licenseDetails' => 'Extended License',
        'licenseType' => 'Perpetual',
        'expiryDate' => null,
        'email' => null,
        'password' => null,
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => null
    ],
    [
        'softwareId' => 6,
        'licenseSoftware' => 'Kaspersky Endpoint Security',
        'licenseDetails' => '50 Devices',
        'licenseType' => 'Subscription',
        'expiryDate' => '2026-02-28',
        'email' => 'ict@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Expiring Soon',
        'daysUntilExpiry' => 19
    ],
    [
        'softwareId' => 7,
        'licenseSoftware' => 'Zoom Business',
        'licenseDetails' => '10-Host License',
        'licenseType' => 'Subscription',
        'expiryDate' => '2026-01-15',
        'email' => 'meetings@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Expired',
        'daysUntilExpiry' => -25
    ],
    [
        'softwareId' => 8,
        'licenseSoftware' => 'Microsoft SQL Server 2022',
        'licenseDetails' => 'Standard Edition - 2 Core',
        'licenseType' => 'Perpetual',
        'expiryDate' => null,
        'email' => null,
        'password' => null,
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => null
    ],
    [
        'softwareId' => 9,
        'licenseSoftware' => 'Slack Business+',
        'licenseDetails' => '25 Users',
        'licenseType' => 'Subscription',
        'expiryDate' => '2027-01-01',
        'email' => 'workspace@nia-upriis.gov.ph',
        'password' => '••••••••',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => 356
    ],
    [
        'softwareId' => 10,
        'licenseSoftware' => 'WinRAR',
        'licenseDetails' => 'Single User License',
        'licenseType' => 'Perpetual',
        'expiryDate' => null,
        'email' => null,
        'password' => null,
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Active',
        'daysUntilExpiry' => null
    ]
];

// Calculate statistics
$totalLicenses = count($sampleSoftware);
$activeLicenses = count(array_filter($sampleSoftware, fn($s) => $s['status'] === 'Active'));
$expiringSoon = count(array_filter($sampleSoftware, fn($s) => $s['status'] === 'Expiring Soon'));
$expiredLicenses = count(array_filter($sampleSoftware, fn($s) => $s['status'] === 'Expired'));
$subscriptionLicenses = count(array_filter($sampleSoftware, fn($s) => $s['licenseType'] === 'Subscription'));
$perpetualLicenses = count(array_filter($sampleSoftware, fn($s) => $s['licenseType'] === 'Perpetual'));
?>

<link rel="stylesheet" href="assets/css/software.css">

<!-- Page Header -->
<div class="page-header">
    <h2>
        <i class="fas fa-key"></i>
        Software License Management
    </h2>
    <div class="header-actions">
        <button class="btn btn-secondary">
            <i class="fas fa-download"></i>
            Export Report
        </button>
        <button class="btn btn-primary" onclick="openAddLicenseModal()">
            <i class="fas fa-plus"></i>
            Add License
        </button>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-value"><?php echo $totalLicenses; ?></div>
        <div class="stat-label">Total Licenses</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value"><?php echo $activeLicenses; ?></div>
        <div class="stat-label">Active</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-value"><?php echo $expiringSoon; ?></div>
        <div class="stat-label">Expiring Soon</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-value"><?php echo $expiredLicenses; ?></div>
        <div class="stat-label">Expired</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-sync-alt"></i>
        </div>
        <div class="stat-value"><?php echo $subscriptionLicenses; ?></div>
        <div class="stat-label">Subscription</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-infinity"></i>
        </div>
        <div class="stat-value"><?php echo $perpetualLicenses; ?></div>
        <div class="stat-label">Perpetual</div>
    </div>
</div>

<!-- Filters Bar -->
<div class="filters-bar">
    <div class="filter-group">
        <label><i class="fas fa-tag"></i> License Type:</label>
        <select id="filterLicenseType">
            <option value="">All Types</option>
            <option value="Subscription">Subscription</option>
            <option value="Perpetual">Perpetual</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="fas fa-circle-check"></i> Status:</label>
        <select id="filterStatus">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Expiring Soon">Expiring Soon</option>
            <option value="Expired">Expired</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label><i class="fas fa-search"></i> Search:</label>
        <input type="text" id="searchLicense" placeholder="Software name, email...">
    </div>
</div>

<!-- Data Table -->
<div class="data-table">
    <table id="softwareTable">
        <thead>
            <tr>
                <th>Software Name</th>
                <th>License Type</th>
                <th>License Details</th>
                <th>Expiry Date</th>
                <th>Credentials</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sampleSoftware as $software): ?>
            <tr data-type="<?php echo $software['licenseType']; ?>" data-status="<?php echo $software['status']; ?>">
                <td>
                    <div class="software-name"><?php echo htmlspecialchars($software['licenseSoftware']); ?></div>
                    <div class="software-details">ID: <?php echo $software['softwareId']; ?></div>
                </td>
                <td>
                    <span class="license-type license-<?php echo strtolower($software['licenseType']); ?>">
                        <?php echo $software['licenseType']; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($software['licenseDetails']); ?></td>
                <td>
                    <?php if ($software['expiryDate']): ?>
                        <div class="expiry-info">
                            <div class="expiry-date"><?php echo date('M d, Y', strtotime($software['expiryDate'])); ?></div>
                            <?php if ($software['daysUntilExpiry'] > 0): ?>
                                <div class="expiry-countdown <?php echo $software['daysUntilExpiry'] <= 30 ? 'warning' : ''; ?>">
                                    <i class="fas fa-clock"></i> <?php echo $software['daysUntilExpiry']; ?> days left
                                </div>
                            <?php elseif ($software['daysUntilExpiry'] < 0): ?>
                                <div class="expiry-countdown danger">
                                    <i class="fas fa-exclamation-circle"></i> Expired <?php echo abs($software['daysUntilExpiry']); ?> days ago
                                </div>
                            <?php else: ?>
                                <div class="expiry-countdown danger">
                                    <i class="fas fa-exclamation-circle"></i> Expires today
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span style="color: var(--text-light); font-style: italic;">
                            <i class="fas fa-infinity"></i> No Expiry
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($software['email'] || $software['password']): ?>
                        <div class="credentials">
                            <?php if ($software['email']): ?>
                                <div class="credential-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($software['email']); ?></span>
                                    <button class="btn-copy" onclick="copyToClipboard('<?php echo htmlspecialchars($software['email']); ?>')" title="Copy">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if ($software['password']): ?>
                                <div class="credential-item">
                                    <i class="fas fa-lock"></i>
                                    <span><?php echo $software['password']; ?></span>
                                    <button class="btn-copy" onclick="showPassword(<?php echo $software['softwareId']; ?>)" title="Show">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span style="color: var(--text-light); font-style: italic;">Not provided</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($software['employeeName']): ?>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($software['employeeName']); ?></div>
                        <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo $software['employeeId']; ?></div>
                    <?php else: ?>
                        <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $software['status'])); ?>">
                        <?php echo $software['status']; ?>
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon" onclick="viewLicense(<?php echo $software['softwareId']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" onclick="editLicense(<?php echo $software['softwareId']; ?>)" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" onclick="deleteLicense(<?php echo $software['softwareId']; ?>)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="assets/js/software.js"></script>