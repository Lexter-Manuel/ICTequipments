<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">

<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Maintenance History</h1>
            <p class="page-subtitle">Archive of completed maintenance activities</p>
        </div>
        <div class="header-right">
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-outline-primary active" id="btnHistoryDetailed" onclick="switchHistoryView('detailed')">
                    <i class="fas fa-list"></i> Specific Equipment
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnHistorySummary" onclick="switchHistoryView('summary')">
                    <i class="fas fa-building"></i> Per Section Summary
                </button>
            </div>
            
            <button class="btn btn-success" onclick="exportReport()">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </div>
    </div>
</div>

<div class="content-wrapper">

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted text-uppercase fw-bold">Date Range</label>
                    <select class="form-select form-select-sm">
                        <option>Last 7 Days</option>
                        <option>This Month</option>
                        <option selected>Last 3 Months</option>
                        <option>This Year</option>
                        <option>All Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted text-uppercase fw-bold">Division</label>
                    <select class="form-select form-select-sm">
                        <option value="">All Divisions</option>
                        <option>Administrative & Finance</option>
                        <option>Engineering & Operation</option>
                        <option>Office of the Dept. Manager</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted text-uppercase fw-bold">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search serial, technician, or remarks...">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed View -->
    <div id="history-detailed" class="d-block">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Date Completed</th>
                            <th>Equipment</th>
                            <th>Location</th>
                            <th>Technician</th>
                            <th>Condition</th>
                            <th class="text-end pe-4">Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 15, 2026</div>
                                <small class="text-muted">10:45 AM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Asus Vivo Mini PC</div>
                                        <small class="text-muted">SN: SU-2025-011</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Operation Section</div>
                                <small class="text-muted">O&M Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Lexter Manuel</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Excellent</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(1)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 14, 2026</div>
                                <small class="text-muted">2:30 PM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">HP LaserJet Pro M404n</div>
                                        <small class="text-muted">SN: PR-2024-089</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">ODM</div>
                                <small class="text-muted">ICT Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Demi Xavier</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Good</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(2)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 13, 2026</div>
                                <small class="text-muted">9:15 AM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Dell Latitude 3410</div>
                                        <small class="text-muted">SN: LP-2024-067</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Administrative Section</div>
                                <small class="text-muted">HR Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Lexter Manuel</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Excellent</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(3)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 12, 2026</div>
                                <small class="text-muted">3:20 PM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-tv"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">LG 24MK430H Monitor</div>
                                        <small class="text-muted">SN: MO-2023-134</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Finance Section</div>
                                <small class="text-muted">Accounting Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Demi Xavier</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Good</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(4)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 10, 2026</div>
                                <small class="text-muted">11:00 AM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">HP ProDesk 400 G6</div>
                                        <small class="text-muted">SN: SU-2023-089</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Engineering Section</div>
                                <small class="text-muted">Planning Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Lexter Manuel</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-warning-xlight text-warning border border-warning-subtle">Fair</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(5)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 08, 2026</div>
                                <small class="text-muted">1:45 PM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Canon PIXMA G3010</div>
                                        <small class="text-muted">SN: PR-2023-145</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Engineering Section</div>
                                <small class="text-muted">Construction Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Demi Xavier</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Good</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(6)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 06, 2026</div>
                                <small class="text-muted">10:15 AM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-laptop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Lenovo ThinkPad E14</div>
                                        <small class="text-muted">SN: LP-2024-112</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">ODM</div>
                                <small class="text-muted">Legal Services</small>
                            </td>
                            <td>
                                <div class="fw-medium">Lexter Manuel</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Excellent</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(7)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 05, 2026</div>
                                <small class="text-muted">3:00 PM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Dell OptiPlex 5070</div>
                                        <small class="text-muted">SN: SU-2024-045</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Administrative Section</div>
                                <small class="text-muted">Records Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Demi Xavier</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Good</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(8)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 03, 2026</div>
                                <small class="text-muted">9:30 AM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary-xlight text-secondary rounded p-2 me-2"><i class="fas fa-print"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Epson L3150</div>
                                        <small class="text-muted">SN: PR-2024-056</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Finance Section</div>
                                <small class="text-muted">Cashier Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Lexter Manuel</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-warning-xlight text-warning border border-warning-subtle">Fair</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(9)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Feb 01, 2026</div>
                                <small class="text-muted">2:10 PM</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-xlight text-primary rounded p-2 me-2"><i class="fas fa-desktop"></i></div>
                                    <div>
                                        <div class="fw-medium text-dark">Acer Veriton M200</div>
                                        <small class="text-muted">SN: SU-2023-178</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-medium">Administrative Section</div>
                                <small class="text-muted">Property Unit</small>
                            </td>
                            <td>
                                <div class="fw-medium">Demi Xavier</div>
                                <small class="text-muted">CMT</small>
                            </td>
                            <td><span class="badge bg-success-xlight text-success border border-success-subtle">Excellent</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="viewReport(10)"><i class="fas fa-file-pdf text-danger"></i> View</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing 10 of 87 completed maintenance records</small>
                    <nav>
                        <ul class="pagination pagination-sm justify-content-end mb-0">
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">...</a></li>
                            <li class="page-item"><a class="page-link" href="#">9</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary View -->
    <div id="history-summary" class="d-none">
        <div class="row g-4">
            
            <!-- Engineering & Operation Division Card -->
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Engineering & Operation Division</h6>
                        <span class="badge bg-success">94% Compliance</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-primary mb-0">48</div>
                                <small class="text-muted small text-uppercase">Total Assets</small>
                            </div>
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-success mb-0">45</div>
                                <small class="text-muted small text-uppercase">Maintained</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 fw-bold text-danger mb-0">3</div>
                                <small class="text-muted small text-uppercase">Pending</small>
                            </div>
                        </div>
                        
                        <h6 class="small text-muted fw-bold text-uppercase mb-2">Breakdown by Section</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><strong>Engineering Section</strong></span>
                                <span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Construction Unit</span>
                                <span class="text-warning"><i class="fas fa-clock"></i> 19/21</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Planning Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 14/14</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>O&M Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 7/7</span>
                            </li>
                            
                            <li class="list-group-item d-flex justify-content-between px-0 border-top">
                                <span><strong>Operation Section</strong></span>
                                <span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Field Operations</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 3/3</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Dispatch Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 2/2</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <button class="btn btn-sm btn-outline-primary w-100">View Detailed Report</button>
                    </div>
                </div>
            </div>

            <!-- Administrative & Finance Division Card -->
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Administrative & Finance Division</h6>
                        <span class="badge bg-success">95% Compliance</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-primary mb-0">41</div>
                                <small class="text-muted small text-uppercase">Total Assets</small>
                            </div>
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-success mb-0">39</div>
                                <small class="text-muted small text-uppercase">Maintained</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 fw-bold text-danger mb-0">2</div>
                                <small class="text-muted small text-uppercase">Pending</small>
                            </div>
                        </div>
                        
                        <h6 class="small text-muted fw-bold text-uppercase mb-2">Breakdown by Section</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><strong>Administrative Section</strong></span>
                                <span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Property Unit</span>
                                <span class="text-warning"><i class="fas fa-clock"></i> 11/12</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Records Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 9/9</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>HR Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 8/8</span>
                            </li>
                            
                            <li class="list-group-item d-flex justify-content-between px-0 border-top">
                                <span><strong>Finance Section</strong></span>
                                <span></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Accounting Unit</span>
                                <span class="text-warning"><i class="fas fa-clock"></i> 6/7</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 ps-3">
                                <span>Cashier Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 5/5</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <button class="btn btn-sm btn-outline-primary w-100">View Detailed Report</button>
                    </div>
                </div>
            </div>

            <!-- Office of the Department Manager Card -->
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Office of the Department Manager</h6>
                        <span class="badge bg-success">95% Compliance</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-primary mb-0">19</div>
                                <small class="text-muted small text-uppercase">Total Assets</small>
                            </div>
                            <div class="col-4 border-end">
                                <div class="h4 fw-bold text-success mb-0">18</div>
                                <small class="text-muted small text-uppercase">Maintained</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 fw-bold text-danger mb-0">1</div>
                                <small class="text-muted small text-uppercase">Pending</small>
                            </div>
                        </div>
                        
                        <h6 class="small text-muted fw-bold text-uppercase mb-2">Breakdown by Unit</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>ICT Unit</span>
                                <span class="text-warning"><i class="fas fa-clock"></i> 7/8</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Legal Services</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 4/4</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Public Relations</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 4/4</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>BAC Unit</span>
                                <span class="text-success"><i class="fas fa-check-circle"></i> 3/3</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <button class="btn btn-sm btn-outline-primary w-100">View Detailed Report</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function switchHistoryView(view) {
    document.getElementById('history-detailed').className = view === 'detailed' ? 'd-block' : 'd-none';
    document.getElementById('history-summary').className  = view === 'summary' ? 'd-block' : 'd-none';
    
    document.getElementById('btnHistoryDetailed').classList.toggle('active', view === 'detailed');
    document.getElementById('btnHistorySummary').classList.toggle('active', view === 'summary');
}

function exportReport() {
    alert('Exporting maintenance history report...\n\nThis will generate an Excel file with:\n- Complete maintenance records\n- Technician summaries\n- Equipment condition trends\n- Compliance statistics');
}

function viewReport(recordId) {
    alert('Opening maintenance report #' + recordId + '...\n\nThis will display:\n- Detailed checklist results\n- Before/after photos\n- Technician notes\n- Signatory information');
}
</script>