<?php
// modules/inventory/computer.php
// This page manages System Units, Monitors, and All-in-One PCs in a tabbed interface

// Sample data for testing (no database required)
$sampleSystemUnits = [
    [
        'systemunitId' => 1,
        'systemUnitCategory' => 'Pre-Built',
        'systemUnitBrand' => 'Dell OptiPlex 7090',
        'specificationProcessor' => 'Intel Core i7-11700',
        'specificationMemory' => '16GB DDR4',
        'specificationGPU' => 'Intel UHD Graphics 750',
        'specificationStorage' => '512GB NVMe SSD',
        'systemUnitSerial' => 'DL-SU-2024-001',
        'yearAcquired' => 2024,
        'employeeId' => 20373,
        'employeeName' => 'Lexter N. Manuel',
        'status' => 'Active'
    ],
    [
        'systemunitId' => 2,
        'systemUnitCategory' => 'Custom Built',
        'systemUnitBrand' => 'Custom Workstation',
        'specificationProcessor' => 'AMD Ryzen 9 5950X',
        'specificationMemory' => '32GB DDR4',
        'specificationGPU' => 'NVIDIA RTX 3060 Ti',
        'specificationStorage' => '1TB NVMe SSD + 2TB HDD',
        'systemUnitSerial' => 'CB-SU-2024-002',
        'yearAcquired' => 2024,
        'employeeId' => 111,
        'employeeName' => 'Benjamin Abad',
        'status' => 'Active'
    ],
    [
        'systemunitId' => 3,
        'systemUnitCategory' => 'Pre-Built',
        'systemUnitBrand' => 'HP ProDesk 600 G6',
        'specificationProcessor' => 'Intel Core i5-10500',
        'specificationMemory' => '8GB DDR4',
        'specificationGPU' => 'Intel UHD Graphics 630',
        'specificationStorage' => '256GB SSD',
        'systemUnitSerial' => 'HP-SU-2023-001',
        'yearAcquired' => 2023,
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Available'
    ],
    [
        'systemunitId' => 4,
        'systemUnitCategory' => 'Custom Built',
        'systemUnitBrand' => 'Gaming Rig Pro',
        'specificationProcessor' => 'Intel Core i9-12900K',
        'specificationMemory' => '64GB DDR5',
        'specificationGPU' => 'NVIDIA RTX 4070',
        'specificationStorage' => '2TB NVMe SSD',
        'systemUnitSerial' => 'GR-SU-2024-003',
        'yearAcquired' => 2024,
        'employeeId' => 1514,
        'employeeName' => 'Mark Angelo Palacay',
        'status' => 'Active'
    ]
];

$sampleMonitors = [
    [
        'monitorId' => 1,
        'monitorBrand' => 'Dell UltraSharp U2722DE',
        'monitorSize' => '27 inches',
        'serial' => 'DL-MON-2024-001',
        'yearAcquired' => 2024,
        'employeeId' => 20373,
        'employeeName' => 'Lexter N. Manuel',
        'resolution' => '2560x1440 (QHD)',
        'panelType' => 'IPS',
        'status' => 'Active'
    ],
    [
        'monitorId' => 2,
        'monitorBrand' => 'LG 24MK430H',
        'monitorSize' => '24 inches',
        'serial' => 'LG-MON-2023-002',
        'yearAcquired' => 2023,
        'employeeId' => 111,
        'employeeName' => 'Benjamin Abad',
        'resolution' => '1920x1080 (Full HD)',
        'panelType' => 'IPS',
        'status' => 'Active'
    ],
    [
        'monitorId' => 3,
        'monitorBrand' => 'Samsung S27R350',
        'monitorSize' => '27 inches',
        'serial' => 'SS-MON-2024-003',
        'yearAcquired' => 2024,
        'employeeId' => null,
        'employeeName' => null,
        'resolution' => '1920x1080 (Full HD)',
        'panelType' => 'VA',
        'status' => 'Available'
    ],
    [
        'monitorId' => 4,
        'monitorBrand' => 'ASUS ProArt PA279CV',
        'monitorSize' => '27 inches',
        'serial' => 'AS-MON-2024-004',
        'yearAcquired' => 2024,
        'employeeId' => 1514,
        'employeeName' => 'Mark Angelo Palacay',
        'resolution' => '3840x2160 (4K UHD)',
        'panelType' => 'IPS',
        'status' => 'Active'
    ],
    [
        'monitorId' => 5,
        'monitorBrand' => 'ViewSonic VX2476',
        'monitorSize' => '24 inches',
        'serial' => 'VS-MON-2023-005',
        'yearAcquired' => 2023,
        'employeeId' => null,
        'employeeName' => null,
        'resolution' => '1920x1080 (Full HD)',
        'panelType' => 'IPS',
        'status' => 'In Repair'
    ]
];

$sampleAllInOne = [
    [
        'allinoneId' => 1,
        'allinoneBrand' => 'HP All-in-One 24-df1033',
        'specificationProcessor' => 'Intel Core i5-1135G7',
        'specificationMemory' => '8GB DDR4',
        'specificationGPU' => 'Intel Iris Xe Graphics',
        'specificationStorage' => '512GB SSD',
        'serial' => 'HP-AIO-2024-001',
        'yearAcquired' => 2024,
        'screenSize' => '23.8 inches',
        'employeeId' => 55555,
        'employeeName' => 'asdasfsad asdsa',
        'status' => 'Active'
    ],
    [
        'allinoneId' => 2,
        'allinoneBrand' => 'Dell Inspiron 27 7000',
        'specificationProcessor' => 'Intel Core i7-1165G7',
        'specificationMemory' => '16GB DDR4',
        'specificationGPU' => 'NVIDIA GeForce MX450',
        'specificationStorage' => '1TB SSD',
        'serial' => 'DL-AIO-2024-002',
        'yearAcquired' => 2024,
        'screenSize' => '27 inches',
        'employeeId' => null,
        'employeeName' => null,
        'status' => 'Available'
    ],
    [
        'allinoneId' => 3,
        'allinoneBrand' => 'Lenovo IdeaCentre AIO 3',
        'specificationProcessor' => 'AMD Ryzen 5 5500U',
        'specificationMemory' => '12GB DDR4',
        'specificationGPU' => 'AMD Radeon Graphics',
        'specificationStorage' => '256GB SSD + 1TB HDD',
        'serial' => 'LN-AIO-2023-003',
        'yearAcquired' => 2023,
        'screenSize' => '24 inches',
        'employeeId' => 41534,
        'employeeName' => 'sdfsd sadfsdf Jr.',
        'status' => 'Active'
    ]
];
?>

<style>
/* Tab Navigation */
.tab-navigation {
    background: white;
    border-radius: 12px 12px 0 0;
    border: 1px solid var(--border-color);
    border-bottom: none;
    padding: 0;
    display: flex;
    gap: 0;
    margin-bottom: 0;
}

.tab-btn {
    flex: 1;
    padding: 18px 24px;
    border: none;
    background: transparent;
    color: var(--text-medium);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.tab-btn:first-child {
    border-radius: 12px 0 0 0;
}

.tab-btn:last-child {
    border-radius: 0 12px 0 0;
}

.tab-btn:not(:last-child) {
    border-right: 1px solid var(--border-color);
}

.tab-btn i {
    font-size: 18px;
}

.tab-btn:hover {
    background: var(--bg-light);
    color: var(--text-dark);
}

.tab-btn.active {
    background: var(--primary-green);
    color: white;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: white;
}

.tab-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
}

.tab-btn.active .tab-badge {
    background: rgba(255, 255, 255, 0.3);
}

/* Tab Content */
.tab-content {
    display: none;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0 0 12px 12px;
    padding: 32px;
    animation: fadeInUp 0.4s ease;
}

.tab-content.active {
    display: block;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.page-header h2 {
    font-family: 'Crimson Pro', serif;
    font-size: 28px;
    color: var(--text-dark);
    font-weight: 700;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.btn-secondary {
    background: white;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-light);
}

.btn-sm {
    padding: 8px 14px;
    font-size: 13px;
}

/* Filters */
.filters-bar {
    background: var(--bg-light);
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
}

.filter-group select,
.filter-group input {
    padding: 8px 14px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
    background: white;
    min-width: 150px;
}

/* Data Table */
.data-table {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table thead {
    background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
    color: white;
}

.data-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-dark);
}

.data-table tbody tr:hover {
    background: var(--bg-light);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-active {
    background: rgba(34, 197, 94, 0.15);
    color: #16a34a;
}

.status-available {
    background: rgba(59, 130, 246, 0.15);
    color: #2563eb;
}

.status-repair {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}

/* Spec Tags */
.spec-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    margin-bottom: 4px;
}

.spec-item i {
    color: var(--primary-green);
    font-size: 12px;
}

.spec-label {
    color: var(--text-light);
    font-weight: 500;
}

.spec-value {
    color: var(--text-dark);
    font-weight: 600;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: white;
    color: var(--text-medium);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: var(--primary-green);
    color: white;
    border-color: var(--primary-green);
}

.btn-icon.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-item {
    background: linear-gradient(135deg, rgba(45, 122, 79, 0.05), rgba(61, 155, 107, 0.05));
    padding: 16px 20px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.stat-label {
    font-size: 12px;
    color: var(--text-medium);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-green);
    font-family: 'Crimson Pro', serif;
}

/* Responsive */
@media (max-width: 768px) {
    .tab-navigation {
        flex-direction: column;
    }
    
    .tab-btn {
        border-right: none !important;
        border-bottom: 1px solid var(--border-color);
    }
    
    .tab-btn:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .tab-btn:last-child {
        border-radius: 0;
        border-bottom: none;
    }
    
    .filters-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
    }
    
    .data-table {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 10px;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-medium);
}

.empty-state i {
    font-size: 48px;
    color: var(--text-light);
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 18px;
    margin-bottom: 8px;
    color: var(--text-dark);
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h2>
        <i class="fas fa-desktop"></i>
        Computer Equipment Management
    </h2>
    <div class="header-actions">
        <button class="btn btn-secondary">
            <i class="fas fa-download"></i>
            Export
        </button>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            Add Equipment
        </button>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tab-navigation">
    <button class="tab-btn active" onclick="switchTab('systemunits')">
        <i class="fas fa-server"></i>
        <span>System Units</span>
        <span class="tab-badge"><?php echo count($sampleSystemUnits); ?></span>
    </button>
    <button class="tab-btn" onclick="switchTab('monitors')">
        <i class="fas fa-tv"></i>
        <span>Monitors</span>
        <span class="tab-badge"><?php echo count($sampleMonitors); ?></span>
    </button>
    <button class="tab-btn" onclick="switchTab('allinone')">
        <i class="fas fa-laptop"></i>
        <span>All-in-One PCs</span>
        <span class="tab-badge"><?php echo count($sampleAllInOne); ?></span>
    </button>
</div>

<!-- System Units Tab -->
<div class="tab-content active" id="systemunits-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total Units</div>
            <div class="stat-value"><?php echo count($sampleSystemUnits); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($sampleSystemUnits, fn($u) => $u['status'] === 'Active')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($sampleSystemUnits, fn($u) => $u['status'] === 'Available')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pre-Built</div>
            <div class="stat-value"><?php echo count(array_filter($sampleSystemUnits, fn($u) => $u['systemUnitCategory'] === 'Pre-Built')); ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-bar">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Category:</label>
            <select>
                <option value="">All Categories</option>
                <option value="Pre-Built">Pre-Built</option>
                <option value="Custom Built">Custom Built</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-circle-check"></i> Status:</label>
            <select>
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Available">Available</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" placeholder="Serial, brand, processor...">
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand / Category</th>
                    <th>Specifications</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleSystemUnits as $unit): ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-green);"><?php echo $unit['systemUnitSerial']; ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600; margin-bottom: 4px;"><?php echo $unit['systemUnitBrand']; ?></div>
                        <div style="font-size: 12px; color: var(--text-light);">
                            <i class="fas fa-tag"></i> <?php echo $unit['systemUnitCategory']; ?>
                        </div>
                    </td>
                    <td>
                        <div class="spec-item">
                            <i class="fas fa-microchip"></i>
                            <span class="spec-value"><?php echo $unit['specificationProcessor']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-memory"></i>
                            <span class="spec-value"><?php echo $unit['specificationMemory']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-hdd"></i>
                            <span class="spec-value"><?php echo $unit['specificationStorage']; ?></span>
                        </div>
                    </td>
                    <td><?php echo $unit['yearAcquired']; ?></td>
                    <td>
                        <?php if ($unit['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo $unit['employeeName']; ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo $unit['employeeId']; ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($unit['status']); ?>">
                            <?php echo $unit['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Monitors Tab -->
<div class="tab-content" id="monitors-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total Monitors</div>
            <div class="stat-value"><?php echo count($sampleMonitors); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($sampleMonitors, fn($m) => $m['status'] === 'Active')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($sampleMonitors, fn($m) => $m['status'] === 'Available')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">In Repair</div>
            <div class="stat-value"><?php echo count(array_filter($sampleMonitors, fn($m) => $m['status'] === 'In Repair')); ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-bar">
        <div class="filter-group">
            <label><i class="fas fa-expand"></i> Size:</label>
            <select>
                <option value="">All Sizes</option>
                <option value="24">24 inches</option>
                <option value="27">27 inches</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-circle-check"></i> Status:</label>
            <select>
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Available">Available</option>
                <option value="In Repair">In Repair</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" placeholder="Serial, brand...">
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand & Model</th>
                    <th>Display Info</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleMonitors as $monitor): ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-green);"><?php echo $monitor['serial']; ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo $monitor['monitorBrand']; ?></div>
                    </td>
                    <td>
                        <div class="spec-item">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span class="spec-value"><?php echo $monitor['monitorSize']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-desktop"></i>
                            <span class="spec-value"><?php echo $monitor['resolution']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-palette"></i>
                            <span class="spec-value"><?php echo $monitor['panelType']; ?> Panel</span>
                        </div>
                    </td>
                    <td><?php echo $monitor['yearAcquired']; ?></td>
                    <td>
                        <?php if ($monitor['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo $monitor['employeeName']; ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo $monitor['employeeId']; ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $monitor['status'])); ?>">
                            <?php echo $monitor['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All-in-One PCs Tab -->
<div class="tab-content" id="allinone-tab">
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Total All-in-One</div>
            <div class="stat-value"><?php echo count($sampleAllInOne); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo count(array_filter($sampleAllInOne, fn($a) => $a['status'] === 'Active')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo count(array_filter($sampleAllInOne, fn($a) => $a['status'] === 'Available')); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Average Year</div>
            <div class="stat-value"><?php echo round(array_sum(array_column($sampleAllInOne, 'yearAcquired')) / count($sampleAllInOne)); ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-bar">
        <div class="filter-group">
            <label><i class="fas fa-circle-check"></i> Status:</label>
            <select>
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Available">Available</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-search"></i> Search:</label>
            <input type="text" placeholder="Serial, brand, processor...">
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Brand & Model</th>
                    <th>Specifications</th>
                    <th>Screen Size</th>
                    <th>Year</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sampleAllInOne as $aio): ?>
                <tr>
                    <td>
                        <strong style="color: var(--primary-green);"><?php echo $aio['serial']; ?></strong>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo $aio['allinoneBrand']; ?></div>
                    </td>
                    <td>
                        <div class="spec-item">
                            <i class="fas fa-microchip"></i>
                            <span class="spec-value"><?php echo $aio['specificationProcessor']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-memory"></i>
                            <span class="spec-value"><?php echo $aio['specificationMemory']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-hdd"></i>
                            <span class="spec-value"><?php echo $aio['specificationStorage']; ?></span>
                        </div>
                    </td>
                    <td><?php echo $aio['screenSize']; ?></td>
                    <td><?php echo $aio['yearAcquired']; ?></td>
                    <td>
                        <?php if ($aio['employeeName']): ?>
                            <div style="font-weight: 600;"><?php echo $aio['employeeName']; ?></div>
                            <div style="font-size: 12px; color: var(--text-light);">ID: <?php echo $aio['employeeId']; ?></div>
                        <?php else: ?>
                            <span style="color: var(--text-light); font-style: italic;">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($aio['status']); ?>">
                            <?php echo $aio['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active state from all buttons
    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Set active button
    event.target.closest('.tab-btn').classList.add('active');
}

function openAddModal() {
    alert('Add Equipment modal would open here. This is a demo with sample data only.');
}

// Demo: Add some interactivity to action buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-icon')) {
        const btn = e.target.closest('.btn-icon');
        const title = btn.getAttribute('title');
        
        if (title === 'Delete') {
            if (confirm('Are you sure you want to delete this item? (Demo only)')) {
                alert('Item would be deleted. This is a demo with sample data only.');
            }
        } else {
            alert(`${title} action triggered. This is a demo with sample data only.`);
        }
    }
});
</script>