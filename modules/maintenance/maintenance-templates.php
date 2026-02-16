<?php
// modules/inventory/maintenance-templates.php
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
    width: 50vw;
    max-width: calc(100vw - var(--sidebar-width) - 80px) !important;
    max-height: calc(100vh - 160px) !important;
}

.modal-xl .modal-dialog {
    max-width: calc(100vw - var(--sidebar-width) - 100px) !important;
}

.modal-fullscreen .modal-dialog {
    left: var(--sidebar-width) !important;
    top: 0 !important;
    transform: none !important;
    width: calc(100vw - var(--sidebar-width)) !important;
    max-width: calc(100vw - var(--sidebar-width)) !important;
    height: 100vh !important;
    max-height: 100vh !important;
    margin: 0 !important;
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
    }
    .modal-backdrop {
        left: 0 !important;
        width: 100vw !important;
    }
    .modal-fullscreen .modal-dialog {
        left: 0 !important;
    }
}

/* Template-specific styles */
.template-card {
    transition: all var(--transition-base);
    cursor: pointer;
}

.template-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.template-icon-box {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.template-badge {
    position: absolute;
    top: 12px;
    right: 12px;
}

.dropdown-toggle::after {
    display: none;
}

/* Template Builder/Preview Modal Styles */
.builder-paper {
    background: #fff;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-card);
    max-width: 900px;
    margin: 0 auto;
    padding: 40px;
    min-height: 800px;
}

.paper-header {
    text-align: center;
    border-bottom: 2px solid var(--text-dark);
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.paper-title {
    font-family: var(--font-display);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.paper-subtitle {
    font-size: 14px;
    color: var(--text-medium);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.paper-section {
    margin-bottom: 25px;
    border: 1px solid var(--text-dark);
}

.section-header {
    background: var(--bg-light);
    border-bottom: 1px solid var(--text-dark);
    padding: 8px 15px;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.checklist-row {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    padding: 10px 15px;
    align-items: center;
}

.checklist-row:last-child {
    border-bottom: none;
}

.signatory-box {
    border: 1px solid var(--text-dark);
    padding: 15px;
    margin-top: 30px;
}

.signatory-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    text-align: center;
    margin-top: 40px;
}

.sig-line {
    border-top: 1px solid var(--text-dark);
    margin-top: 30px;
    padding-top: 5px;
    font-weight: 600;
    font-size: 14px;
}

.sig-role {
    font-size: 12px;
    color: var(--text-medium);
    text-transform: uppercase;
}

.builder-controls {
    opacity: 0.5;
    transition: opacity var(--transition-fast);
}

.paper-section:hover .builder-controls {
    opacity: 1;
}

.template-stats {
    display: flex;
    gap: var(--space-4);
    padding-top: var(--space-3);
    border-top: 1px solid var(--border-color);
}

.template-stat {
    flex: 1;
}
</style>

<!-- Template View Modal -->
<div class="modal fade" id="templateViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalTitle">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content loaded by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editTemplate()">
                    <i class="fas fa-edit"></i> Edit Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template Builder Modal -->
<div class="modal fade" id="templateBuilderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-light">
            <div class="modal-header border-bottom bg-white">
                <div>
                    <h5 class="modal-title">Template Builder</h5>
                    <small class="text-muted">Create or edit maintenance checklist templates</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" onclick="previewTemplate()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="btn btn-success" onclick="saveTemplate()">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="overflow-y: auto;">
                <div class="builder-paper">
                    <div class="paper-header">
                        <div class="paper-title">ICT Preventive Maintenance</div>
                        <div class="paper-subtitle">Procedure Checklist</div>
                        
                        <div class="row mt-4 text-start small text-uppercase">
                            <div class="col-6">
                                <strong>Equipment Type:</strong> <span class="text-primary" contenteditable="true">[System Unit]</span>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Date:</strong> _________________
                            </div>
                        </div>
                    </div>

                    <div id="checklistCategories">
                        <div class="paper-section">
                            <div class="section-header">
                                <span contenteditable="true">I. Physical Inspection, Interiors and Cleaning</span>
                                <div class="builder-controls">
                                    <button class="btn btn-xs btn-link text-dark p-0 me-2" onclick="addItem(this)">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                    <button class="btn btn-xs btn-link text-danger p-0" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="checklist-items">
                                <div class="checklist-row">
                                    <div class="flex-grow-1" contenteditable="true">Dust removal performed</div>
                                    <div class="ms-3 text-muted small fst-italic">[Yes / No / N/A]</div>
                                    <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                                </div>
                                <div class="checklist-row">
                                    <div class="flex-grow-1" contenteditable="true">Parts are intact</div>
                                    <div class="ms-3 text-muted small fst-italic">[Yes / No / N/A]</div>
                                    <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                                </div>
                            </div>
                        </div>

                        <div class="paper-section">
                            <div class="section-header">
                                <span contenteditable="true">II. Hardware Performance Check</span>
                                <div class="builder-controls">
                                    <button class="btn btn-xs btn-link text-dark p-0 me-2" onclick="addItem(this)">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                    <button class="btn btn-xs btn-link text-danger p-0" onclick="removeSection(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="checklist-items">
                                <div class="checklist-row">
                                    <div class="flex-grow-1" contenteditable="true">Power Supply is working properly</div>
                                    <div class="ms-3 text-muted small fst-italic">[Yes / No / N/A]</div>
                                    <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-3 builder-controls mb-4" style="border: 2px dashed var(--border-color);">
                        <button class="btn btn-outline-primary btn-sm" onclick="addCategory()">
                            <i class="fas fa-folder-plus"></i> Add New Section
                        </button>
                    </div>

                    <div class="paper-section p-3" style="min-height: 100px;">
                        <strong class="d-block text-uppercase small mb-2">Remarks / Recommendations:</strong>
                        <div style="border-bottom: 1px solid var(--text-dark); margin-top: 20px;"></div>
                        <div style="border-bottom: 1px solid var(--text-dark); margin-top: 20px;"></div>
                    </div>

                    <div class="signatory-box">
                        <strong class="d-block text-uppercase small mb-3">Signatories</strong>
                        
                        <div class="alert alert-info py-2 px-3 small mb-3 builder-controls">
                            <i class="fas fa-info-circle"></i> "Prepared/Conducted By" will be auto-filled by the logged-in technician.
                        </div>

                        <div class="signatory-grid">
                            <div>
                                <div class="sig-role text-primary fw-bold">[Technician Name]</div>
                                <div class="sig-line">Prepared/Conducted By</div>
                                <div class="sig-role">ICT Staff</div>
                            </div>

                            <div class="position-relative p-2 border border-dashed rounded">
                                <div class="builder-controls position-absolute top-0 end-0 p-1">
                                    <button class="btn btn-xs btn-light border" onclick="editSignatory(this, 'Checked by')">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                                <div class="text-muted fst-italic">[Select from dropdown]</div>
                                <div class="sig-line">Checked by</div>
                                <div class="sig-role" contenteditable="true">Sr. Supply Officer</div>
                            </div>

                            <div class="position-relative p-2 border border-dashed rounded">
                                <div class="builder-controls position-absolute top-0 end-0 p-1">
                                    <button class="btn btn-xs btn-light border" onclick="editSignatory(this, 'Noted by')">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                                <div class="text-muted fst-italic">[Select from dropdown]</div>
                                <div class="sig-line">Noted by</div>
                                <div class="sig-role" contenteditable="true">Division Manager, AdFin</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Maintenance Templates</h1>
            <p class="page-subtitle">Manage preventive maintenance checklist forms</p>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="openBuilderModal()">
                <i class="fas fa-plus-circle"></i> Create New Template
            </button>
        </div>
    </div>
</div>

<div class="content-wrapper">
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-primary-xlight text-primary me-3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0" style="color: var(--primary-green);">4</div>
                            <small class="text-muted text-uppercase">Total Templates</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-success-xlight text-success me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0 text-success">4</div>
                            <small class="text-muted text-uppercase">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-info-xlight text-info me-3">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0 text-info">87</div>
                            <small class="text-muted text-uppercase">Times Used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-warning-xlight text-warning me-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0 text-warning">Feb 15</div>
                            <small class="text-muted text-uppercase">Last Updated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row g-4">
        
        <!-- Template 1: System Unit -->
        <div class="col-md-6 col-xl-4">
            <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate('system_unit')">
                <span class="template-badge badge bg-success">Active</span>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="template-icon-box bg-primary-xlight text-primary">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div class="dropdown" onclick="event.stopPropagation();">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewTemplate('system_unit')"><i class="fas fa-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editTemplateById('system_unit')"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="duplicateTemplate('system_unit')"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate('system_unit')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">System Unit Maintenance</h5>
                    <p class="text-muted small mb-3">Desktop PC & All-in-One Computers</p>
                    
                    <div class="template-stats">
                        <div class="template-stat">
                            <div class="small text-muted">Frequency</div>
                            <div class="fw-bold" style="color: var(--primary-green);">Semi-Annual</div>
                        </div>
                        <div class="template-stat text-end">
                            <div class="small text-muted">Used</div>
                            <div class="fw-bold" style="color: var(--primary-green);">45 times</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted"><i class="fas fa-list-check me-1"></i> 15 items</span>
                        <span class="text-muted">v2.1 • Updated Feb 15</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template 2: Laptop -->
        <div class="col-md-6 col-xl-4">
            <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate('laptop')">
                <span class="template-badge badge bg-success">Active</span>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="template-icon-box bg-primary-xlight text-primary">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="dropdown" onclick="event.stopPropagation();">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewTemplate('laptop')"><i class="fas fa-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editTemplateById('laptop')"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="duplicateTemplate('laptop')"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate('laptop')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Laptop Maintenance</h5>
                    <p class="text-muted small mb-3">Portable Computers & Notebooks</p>
                    
                    <div class="template-stats">
                        <div class="template-stat">
                            <div class="small text-muted">Frequency</div>
                            <div class="fw-bold" style="color: var(--primary-green);">Quarterly</div>
                        </div>
                        <div class="template-stat text-end">
                            <div class="small text-muted">Used</div>
                            <div class="fw-bold" style="color: var(--primary-green);">23 times</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted"><i class="fas fa-list-check me-1"></i> 15 items</span>
                        <span class="text-muted">v1.8 • Updated Jan 20</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template 3: Printer -->
        <div class="col-md-6 col-xl-4">
            <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate('printer')">
                <span class="template-badge badge bg-success">Active</span>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="template-icon-box bg-secondary-xlight text-secondary">
                            <i class="fas fa-print"></i>
                        </div>
                        <div class="dropdown" onclick="event.stopPropagation();">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewTemplate('printer')"><i class="fas fa-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editTemplateById('printer')"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="duplicateTemplate('printer')"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate('printer')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Printer Maintenance</h5>
                    <p class="text-muted small mb-3">LaserJet & InkJet Printers</p>
                    
                    <div class="template-stats">
                        <div class="template-stat">
                            <div class="small text-muted">Frequency</div>
                            <div class="fw-bold" style="color: var(--primary-green);">Monthly</div>
                        </div>
                        <div class="template-stat text-end">
                            <div class="small text-muted">Used</div>
                            <div class="fw-bold" style="color: var(--primary-green);">15 times</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted"><i class="fas fa-list-check me-1"></i> 8 items</span>
                        <span class="text-muted">v2.0 • Updated Feb 10</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template 4: Monitor -->
        <div class="col-md-6 col-xl-4">
            <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate('monitor')">
                <span class="template-badge badge bg-success">Active</span>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="template-icon-box bg-primary-xlight text-primary">
                            <i class="fas fa-tv"></i>
                        </div>
                        <div class="dropdown" onclick="event.stopPropagation();">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="viewTemplate('monitor')"><i class="fas fa-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" onclick="editTemplateById('monitor')"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="duplicateTemplate('monitor')"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate('monitor')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">Monitor Maintenance</h5>
                    <p class="text-muted small mb-3">LCD & LED Display Screens</p>
                    
                    <div class="template-stats">
                        <div class="template-stat">
                            <div class="small text-muted">Frequency</div>
                            <div class="fw-bold" style="color: var(--primary-green);">Semi-Annual</div>
                        </div>
                        <div class="template-stat text-end">
                            <div class="small text-muted">Used</div>
                            <div class="fw-bold" style="color: var(--primary-green);">4 times</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted"><i class="fas fa-list-check me-1"></i> 8 items</span>
                        <span class="text-muted">v1.0 • Updated Dec 05</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modal instances
let builderModal = null;
let viewModal = null;

// Template data
const templateData = {
    'system_unit': {
        name: 'System Unit Maintenance',
        description: 'Desktop PC & All-in-One Computers',
        frequency: 'Semi-Annual',
        items: 15,
        version: 'v2.1',
        sections: [
            {
                title: 'Physical Inspection, Interiors and Cleaning',
                items: ['Dust removal performed', 'Parts are intact', 'Fans are operating properly with minimal noise', 'No loose wires']
            },
            {
                title: 'Hardware Performance Check',
                items: ['Power Supply is working properly', 'CMOS Battery is in good condition', 'Unnecessary startup programs are disabled', 'Storage drive is in good health']
            },
            {
                title: 'Software',
                items: ['Windows OS is updated', 'Installed licensed programs are updated', 'Unnecessary programs are uninstalled', 'Virus scan performed; Antivirus is updated', 'Temporary files and recycled files are removed']
            },
            {
                title: 'Backup',
                items: ['Backup files are properly maintained']
            }
        ]
    },
    'laptop': {
        name: 'Laptop Maintenance',
        description: 'Portable Computers & Notebooks',
        frequency: 'Quarterly',
        items: 15,
        version: 'v1.8',
        sections: [
            {
                title: 'Physical Inspection, Interiors and Cleaning',
                items: ['Dust removal performed', 'Parts are intact', 'Keyboard and trackpad are clean', 'Screen is clean and undamaged']
            },
            {
                title: 'Hardware Performance Check',
                items: ['Power adapter is working properly', 'Battery health is good', 'Cooling fans are operating properly', 'Storage drive is in good health']
            },
            {
                title: 'Software',
                items: ['Windows OS is updated', 'Installed licensed programs are updated', 'Unnecessary programs are uninstalled', 'Virus scan performed; Antivirus is updated', 'Temporary files and recycled files are removed']
            },
            {
                title: 'Backup',
                items: ['Backup files are properly maintained']
            }
        ]
    },
    'printer': {
        name: 'Printer Maintenance',
        description: 'LaserJet & InkJet Printers',
        frequency: 'Monthly',
        items: 8,
        version: 'v2.0',
        sections: [
            {
                title: 'Physical Inspection, Interiors and Cleaning',
                items: ['Dust Removal', 'Parts are intact', 'Loose wires']
            },
            {
                title: 'Power Supply',
                items: ['Power Supply is working properly']
            },
            {
                title: 'Software',
                items: ['Printing function is operational', 'Ink and Toner levels are adequate', 'Waste Ink Pad is in good condition', 'Software/Firmware is updated']
            }
        ]
    },
    'monitor': {
        name: 'Monitor Maintenance',
        description: 'LCD & LED Display Screens',
        frequency: 'Semi-Annual',
        items: 8,
        version: 'v1.0',
        sections: [
            {
                title: 'Physical Inspection and Cleaning',
                items: ['Screen is clean and undamaged', 'Frame and stand are intact', 'Cables are properly connected']
            },
            {
                title: 'Display Performance Check',
                items: ['Display brightness is adequate', 'No dead pixels visible', 'Colors display correctly']
            },
            {
                title: 'Hardware Check',
                items: ['Power indicator works', 'OSD buttons are responsive']
            }
        ]
    }
};

function openBuilderModal() {
    if (!builderModal) {
        builderModal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
    }
    builderModal.show();
}

function viewTemplate(templateId) {
    const template = templateData[templateId];
    if (!template) return;
    
    const modalBody = document.getElementById('viewModalBody');
    document.getElementById('viewModalTitle').textContent = template.name + ' - Preview';
    
    let sectionsHtml = '';
    template.sections.forEach((section, idx) => {
        let itemsHtml = '';
        section.items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item}</td>
                    <td class="text-center">☐</td>
                    <td class="text-center">☐</td>
                    <td class="text-center">☐</td>
                </tr>
            `;
        });
        
        sectionsHtml += `
            <div class="mb-4">
                <h6 class="fw-bold text-uppercase mb-3">${idx + 1}. ${section.title}</h6>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center" style="width: 80px;">Yes</th>
                            <th class="text-center" style="width: 80px;">No</th>
                            <th class="text-center" style="width: 80px;">N/A</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
        `;
    });
    
    modalBody.innerHTML = `
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold mb-1">${template.name}</h4>
                    <p class="text-muted mb-0">${template.description}</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-info-xlight text-info border">${template.frequency}</span>
                    <div class="small text-muted mt-1">${template.items} checklist items</div>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        ${sectionsHtml}
        
        <div class="border rounded p-3 bg-light mb-4">
            <strong class="d-block mb-2">Remarks / Recommendations:</strong>
            <div style="min-height: 60px; border-bottom: 1px solid #999;"></div>
        </div>
        
        <div class="row g-4 text-center small">
            <div class="col-4">
                <div class="fw-bold">[Technician Name]</div>
                <div class="border-top border-dark mt-5 pt-2">Prepared/Conducted By</div>
                <div class="text-muted">ICT Staff</div>
            </div>
            <div class="col-4">
                <div class="fw-bold">[Supervisor Name]</div>
                <div class="border-top border-dark mt-5 pt-2">Checked by</div>
                <div class="text-muted">Sr. Supply Officer</div>
            </div>
            <div class="col-4">
                <div class="fw-bold">[Manager Name]</div>
                <div class="border-top border-dark mt-5 pt-2">Noted by</div>
                <div class="text-muted">Division Manager, AdFin</div>
            </div>
        </div>
    `;
    
    if (!viewModal) {
        viewModal = new bootstrap.Modal(document.getElementById('templateViewModal'));
    }
    viewModal.show();
}

function editTemplate() {
    if (viewModal) {
        viewModal.hide();
    }
    openBuilderModal();
}

function editTemplateById(templateId) {
    event.preventDefault();
    event.stopPropagation();
    alert('Opening template editor for: ' + templateData[templateId].name);
    openBuilderModal();
}

function duplicateTemplate(templateId) {
    event.preventDefault();
    event.stopPropagation();
    if (confirm('Create a copy of "' + templateData[templateId].name + '"?')) {
        alert('Template duplicated successfully!\n\nYou can now edit the copy.');
    }
}

function deleteTemplate(templateId) {
    event.preventDefault();
    event.stopPropagation();
    if (confirm('Delete "' + templateData[templateId].name + '"?\n\nThis action cannot be undone.')) {
        alert('Template deleted successfully!');
    }
}

// Builder functions
function addCategory() {
    const container = document.getElementById('checklistCategories');
    const newSection = document.createElement('div');
    newSection.className = 'paper-section';
    newSection.innerHTML = `
        <div class="section-header">
            <span contenteditable="true">New Section Header</span>
            <div class="builder-controls">
                <button class="btn btn-xs btn-link text-dark p-0 me-2" onclick="addItem(this)">
                    <i class="fas fa-plus"></i> Add Item
                </button>
                <button class="btn btn-xs btn-link text-danger p-0" onclick="removeSection(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="checklist-items">
            <div class="checklist-row">
                <div class="flex-grow-1" contenteditable="true">New inspection item...</div>
                <div class="ms-3 text-muted small fst-italic">[Yes / No / N/A]</div>
                <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
            </div>
        </div>
    `;
    container.appendChild(newSection);
}

function addItem(btn) {
    const itemsContainer = btn.closest('.section-header').nextElementSibling;
    const newItem = document.createElement('div');
    newItem.className = 'checklist-row';
    newItem.innerHTML = `
        <div class="flex-grow-1" contenteditable="true">New inspection item...</div>
        <div class="ms-3 text-muted small fst-italic">[Yes / No / N/A]</div>
        <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
    `;
    itemsContainer.appendChild(newItem);
}

function removeSection(btn) {
    if(confirm('Remove this entire section?')) {
        btn.closest('.paper-section').remove();
    }
}

function removeItem(btn) {
    btn.closest('.checklist-row').remove();
}

function editSignatory(btn, label) {
    const roleTitle = prompt("Enter the default title for '" + label + "' signatory:", "Section Head");
    if(roleTitle) {
        const container = btn.closest('.position-relative');
        container.querySelector('.sig-role').textContent = roleTitle;
    }
}

function previewTemplate() {
    alert('Preview functionality:\n\nThis would show a print-ready preview of the template without builder controls.');
}

function saveTemplate() {
    if (confirm('Save this template?\n\nThe template will be available for use in maintenance operations.')) {
        alert('Template saved successfully!\n\nYou can now use this template when performing maintenance.');
        if (builderModal) {
            builderModal.hide();
        }
    }
}
</script>