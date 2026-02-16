<?php
// modules/inventory/maintenance-schedule.php
?>
<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">

<style>
/* Modal positioning fix - centers within content area */
.modal {
    position: fixed !important;
    z-index: 1050;
}

.modal-dialog {
    position: fixed !important;
    margin: 0 !important;
    left: calc(var(--sidebar-width) + (100vw - var(--sidebar-width)) / 2) !important;
    top: 50% !important;
    transform: translate(-50%, -50%) !important;
    max-width: calc(100vw - var(--sidebar-width) - 80px) !important;
    max-height: calc(100vh - 160px) !important;
}

.modal-xl .modal-dialog {
    max-width: calc(100vw - var(--sidebar-width) - 100px) !important;
}

.modal-backdrop {
    position: fixed !important;
    left: var(--sidebar-width) !important;
    top: 0 !important;
    width: calc(100vw - var(--sidebar-width)) !important;
    height: 100vh !important;
}

.modal-content {
    max-height: calc(100vh - 160px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-body {
    overflow-y: auto;
    flex: 1;
}

@media (max-width: 1024px) {
    .modal-dialog {
        left: 50% !important;
        max-width: calc(100vw - 40px) !important;
    }
    .modal-backdrop {
        left: 0 !important;
        width: 100vw !important;
    }
}
</style>

<!-- Modal for Section Assets View -->
<div class="modal fade" id="sectionAssetsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSectionTitle">Engineering & Operation Division - All Assets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th>Equipment</th>
                                <th>Unit/Section</th>
                                <th>Owner</th>
                                <th>Next Due Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Content will be loaded here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Preventive Maintenance Schedule</h1>
            <p class="page-subtitle">Track upcoming and overdue maintenance tasks</p>
        </div>
        <div class="header-right">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" id="btnDetailed" onclick="switchView('detailed')">
                    <i class="fas fa-list"></i> Specific Equipment
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnSummary" onclick="switchView('summary')">
                    <i class="fas fa-th-large"></i> By Section Summary
                </button>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search serial, brand, or owner...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Sections</option>
                        <option>Administrative and Finance Division</option>
                        <option>Engineering and Operation Division</option>
                        <option>Office of the Department Manager</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Statuses</option>
                        <option class="text-danger fw-bold">Overdue</option>
                        <option class="text-warning fw-bold">Due Soon</option>
                        <option class="text-success fw-bold">Scheduled</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-light text-primary w-100 border"><i class="fas fa-file-export"></i> Export</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed View -->
    <div id="view-detailed" class="d-block">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Due Date</th>
                            <th>Equipment Detail</th>
                            <th>Location / Owner</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Overdue Items -->
                        <tr>
                            <td class="ps-4">
                                <div class="text-danger fw-bold">Feb 10, 2026</div>
                                <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> 6 Days Overdue</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Dell Optiplex 7080</div>
                                        <div class="small text-muted">SN: SU-2024-009</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Engineering Section</div>
                                <small class="text-muted">Construction Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Semi-Annual</span></td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(101, 'system_unit')">Perform Now</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-danger fw-bold">Feb 12, 2026</div>
                                <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> 4 Days Overdue</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Lenovo ThinkPad T14</div>
                                        <div class="small text-muted">SN: LP-2024-032</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Administrative Section</div>
                                <small class="text-muted">Property Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Quarterly</span></td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(201, 'laptop')">Perform Now</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-danger fw-bold">Feb 14, 2026</div>
                                <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> 2 Days Overdue</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">HP EliteDesk 800 G5</div>
                                        <div class="small text-muted">SN: SU-2023-156</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Finance Section</div>
                                <small class="text-muted">Accounting Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Semi-Annual</span></td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(102, 'system_unit')">Perform Now</button>
                            </td>
                        </tr>

                        <!-- Due Soon Items -->
                        <tr>
                            <td class="ps-4">
                                <div class="text-warning fw-bold">Feb 18, 2026</div>
                                <small class="text-muted">Due in 2 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Epson L3110</div>
                                        <div class="small text-muted">SN: PR-2023-112</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Administrative Section</div>
                                <small class="text-muted">Records Unit</small>
                            </td>
                            <td><span class="badge bg-warning-xlight text-warning border border-warning-subtle">Monthly</span></td>
                            <td><span class="badge bg-warning text-dark">Due Soon</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(301, 'printer')">Perform</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-warning fw-bold">Feb 20, 2026</div>
                                <small class="text-muted">Due in 4 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-tv"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Samsung S24C450</div>
                                        <div class="small text-muted">SN: MO-2024-078</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Engineering Section</div>
                                <small class="text-muted">Planning Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Semi-Annual</span></td>
                            <td><span class="badge bg-warning text-dark">Due Soon</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(401, 'monitor')">Perform</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-warning fw-bold">Feb 22, 2026</div>
                                <small class="text-muted">Due in 6 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">HP LaserJet Pro M404n</div>
                                        <div class="small text-muted">SN: PR-2024-089</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">ODM</div>
                                <small class="text-muted">ICT Unit</small>
                            </td>
                            <td><span class="badge bg-warning-xlight text-warning border border-warning-subtle">Monthly</span></td>
                            <td><span class="badge bg-warning text-dark">Due Soon</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary px-3 shadow-sm" onclick="performMaintenance(302, 'printer')">Perform</button>
                            </td>
                        </tr>

                        <!-- Scheduled Items -->
                        <tr>
                            <td class="ps-4">
                                <div class="text-success fw-bold">Mar 05, 2026</div>
                                <small class="text-muted">Due in 17 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Acer TravelMate P2</div>
                                        <div class="small text-muted">SN: LP-2024-055</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Finance Section</div>
                                <small class="text-muted">Cashier Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Quarterly</span></td>
                            <td><span class="badge bg-success">Scheduled</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary" disabled>Wait</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-success fw-bold">Mar 12, 2026</div>
                                <small class="text-muted">Due in 24 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Asus Vivo Mini PC</div>
                                        <div class="small text-muted">SN: SU-2025-011</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Operation Section</div>
                                <small class="text-muted">O&M Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Semi-Annual</span></td>
                            <td><span class="badge bg-success">Scheduled</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary" disabled>Wait</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-success fw-bold">Mar 20, 2026</div>
                                <small class="text-muted">Due in 32 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Canon PIXMA G3010</div>
                                        <div class="small text-muted">SN: PR-2023-145</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Engineering Section</div>
                                <small class="text-muted">Construction Unit</small>
                            </td>
                            <td><span class="badge bg-warning-xlight text-warning border border-warning-subtle">Monthly</span></td>
                            <td><span class="badge bg-success">Scheduled</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary" disabled>Wait</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="text-success fw-bold">Apr 08, 2026</div>
                                <small class="text-muted">Due in 51 Days</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Dell Latitude 3410</div>
                                        <div class="small text-muted">SN: LP-2024-067</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">Administrative Section</div>
                                <small class="text-muted">HR Unit</small>
                            </td>
                            <td><span class="badge bg-info-xlight text-info border border-info-subtle">Semi-Annual</span></td>
                            <td><span class="badge bg-success">Scheduled</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary" disabled>Wait</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing 10 of 127 records</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">...</a></li>
                            <li class="page-item"><a class="page-link" href="#">13</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary View -->
    <div id="view-summary" class="d-none">
        <div class="row g-4">
            <!-- Engineering and Operation Division -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">Engineering & Operation Division</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="small text-uppercase text-muted fw-bold mb-3">Engineering Section</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Construction Unit</span>
                                <span class="badge bg-danger rounded-pill">2 Overdue</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Planning Unit</span>
                                <span class="badge bg-warning text-dark rounded-pill">1 Due Soon</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">O&M Unit</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <h6 class="small text-uppercase text-muted fw-bold mb-3">Operation Section</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Field Operations</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Dispatch Unit</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-danger mb-0">2</div>
                                <small class="text-muted">Overdue</small>
                            </div>
                            <div class="col-4 text-center border-start border-end">
                                <div class="h5 fw-bold text-warning mb-0">1</div>
                                <small class="text-muted">Due Soon</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-success mb-0">45</div>
                                <small class="text-muted">Scheduled</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="viewSectionAssets('EOD')">View All Assets</button>
                    </div>
                </div>
            </div>

            <!-- Administrative and Finance Division -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">Administrative & Finance Division</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="small text-uppercase text-muted fw-bold mb-3">Administrative Section</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Property Unit</span>
                                <span class="badge bg-danger rounded-pill">1 Overdue</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Records Unit</span>
                                <span class="badge bg-warning text-dark rounded-pill">1 Due Soon</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">HR Unit</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <h6 class="small text-uppercase text-muted fw-bold mb-3">Finance Section</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Accounting Unit</span>
                                <span class="badge bg-danger rounded-pill">1 Overdue</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Cashier Unit</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-danger mb-0">2</div>
                                <small class="text-muted">Overdue</small>
                            </div>
                            <div class="col-4 text-center border-start border-end">
                                <div class="h5 fw-bold text-warning mb-0">1</div>
                                <small class="text-muted">Due Soon</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-success mb-0">38</div>
                                <small class="text-muted">Scheduled</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="viewSectionAssets('ADFIN')">View All Assets</button>
                    </div>
                </div>
            </div>

            <!-- Office of the Department Manager -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">Office of the Department Manager</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="small text-uppercase text-muted fw-bold mb-3">Department Units</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">ICT Unit</span>
                                <span class="badge bg-warning text-dark rounded-pill">1 Due Soon</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Legal Services</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted">Public Relations</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">BAC Unit</span>
                                <span class="badge bg-success rounded-pill">All Good</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-muted mb-0">0</div>
                                <small class="text-muted">Overdue</small>
                            </div>
                            <div class="col-4 text-center border-start border-end">
                                <div class="h5 fw-bold text-warning mb-0">1</div>
                                <small class="text-muted">Due Soon</small>
                            </div>
                            <div class="col-4 text-center">
                                <div class="h5 fw-bold text-success mb-0">18</div>
                                <small class="text-muted">Scheduled</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="viewSectionAssets('ODM')">View All Assets</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchView(view) {
    document.getElementById('view-detailed').className = view === 'detailed' ? 'd-block' : 'd-none';
    document.getElementById('view-summary').className  = view === 'summary' ? 'd-block' : 'd-none';
    
    document.getElementById('btnDetailed').classList.toggle('active', view === 'detailed');
    document.getElementById('btnSummary').classList.toggle('active', view === 'summary');
}

function performMaintenance(equipmentId, equipmentType) {
    // Store in sessionStorage for perform-maintenance.php to read
    sessionStorage.setItem('selectedEquipmentId', equipmentId);
    sessionStorage.setItem('selectedEquipmentType', equipmentType);
    
    // Redirect to perform maintenance page
    window.location.href = '?page=perform-maintenance&id=' + equipmentId + '&type=' + equipmentType;
}

// Mock data for section assets
const sectionAssetsData = {
    'EOD': [
        { name: 'Dell Optiplex 7080', serial: 'SU-2024-009', unit: 'Construction Unit', owner: 'Engr. Juan Dela Cruz', date: 'Feb 10, 2026', status: 'overdue', id: 101, type: 'system_unit' },
        { name: 'Samsung S24C450', serial: 'MO-2024-078', unit: 'Planning Unit', owner: 'Planning Staff', date: 'Feb 20, 2026', status: 'due', id: 401, type: 'monitor' },
        { name: 'Asus Vivo Mini PC', serial: 'SU-2025-011', unit: 'O&M Unit', owner: 'Pedro Reyes', date: 'Mar 12, 2026', status: 'scheduled', id: 103, type: 'system_unit' },
        { name: 'Canon PIXMA G3010', serial: 'PR-2023-145', unit: 'Construction Unit', owner: 'Construction Unit', date: 'Mar 20, 2026', status: 'scheduled', id: 303, type: 'printer' },
    ],
    'ADFIN': [
        { name: 'Lenovo ThinkPad T14', serial: 'LP-2024-032', unit: 'Property Unit', owner: 'Ana Garcia', date: 'Feb 12, 2026', status: 'overdue', id: 201, type: 'laptop' },
        { name: 'Epson L3110', serial: 'PR-2023-112', unit: 'Records Unit', owner: 'Records Unit', date: 'Feb 18, 2026', status: 'due', id: 301, type: 'printer' },
        { name: 'HP EliteDesk 800 G5', serial: 'SU-2023-156', unit: 'Accounting Unit', owner: 'Maria Santos', date: 'Feb 14, 2026', status: 'overdue', id: 102, type: 'system_unit' },
        { name: 'Acer TravelMate P2', serial: 'LP-2024-055', unit: 'Cashier Unit', owner: 'Jose Ramirez', date: 'Mar 05, 2026', status: 'scheduled', id: 202, type: 'laptop' },
        { name: 'Dell Latitude 3410', serial: 'LP-2024-067', unit: 'HR Unit', owner: 'Carmen Lopez', date: 'Apr 08, 2026', status: 'scheduled', id: 203, type: 'laptop' },
    ],
    'ODM': [
        { name: 'HP LaserJet Pro M404n', serial: 'PR-2024-089', unit: 'ICT Unit', owner: 'ICT Unit', date: 'Feb 22, 2026', status: 'due', id: 302, type: 'printer' },
        { name: 'Lenovo ThinkPad E14', serial: 'LP-2024-112', unit: 'Legal Services', owner: 'Legal Staff', date: 'May 10, 2026', status: 'scheduled', id: 204, type: 'laptop' },
        { name: 'Dell Vostro 3400', serial: 'LP-2025-023', unit: 'Public Relations', owner: 'PR Staff', date: 'Jun 15, 2026', status: 'scheduled', id: 205, type: 'laptop' },
    ]
};

function viewSectionAssets(division) {
    const divisionNames = {
        'EOD': 'Engineering & Operation Division',
        'ADFIN': 'Administrative & Finance Division',
        'ODM': 'Office of the Department Manager'
    };
    
    document.getElementById('modalSectionTitle').textContent = divisionNames[division] + ' - All Assets';
    
    const assets = sectionAssetsData[division] || [];
    const tbody = document.getElementById('modalTableBody');
    tbody.innerHTML = '';
    
    assets.forEach(asset => {
        const statusBadge = asset.status === 'overdue' ? '<span class="badge bg-danger">Overdue</span>' :
                           asset.status === 'due' ? '<span class="badge bg-warning text-dark">Due Soon</span>' :
                           '<span class="badge bg-success">Scheduled</span>';
        
        const icon = asset.type === 'system_unit' ? 'fa-desktop' :
                    asset.type === 'laptop' ? 'fa-laptop' :
                    asset.type === 'printer' ? 'fa-print' : 'fa-tv';
        
        const actionBtn = asset.status !== 'scheduled' 
            ? `<button class="btn btn-sm btn-primary" onclick="performMaintenance(${asset.id}, '${asset.type}')">Perform Now</button>`
            : `<button class="btn btn-sm btn-outline-secondary" disabled>Wait</button>`;
        
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas ${icon}"></i></div>
                        <div>
                            <div class="fw-medium">${asset.name}</div>
                            <small class="text-muted">${asset.serial}</small>
                        </div>
                    </div>
                </td>
                <td>${asset.unit}</td>
                <td>${asset.owner}</td>
                <td>${asset.date}</td>
                <td>${statusBadge}</td>
                <td class="text-end">${actionBtn}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('sectionAssetsModal'));
    modal.show();
}
</script>