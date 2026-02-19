<?php
require_once '../../config/database.php';
$db = getDB();
$totalTemplates = $db->query("SELECT COUNT(*) FROM tbl_maintenance_template")->fetchColumn();
$activeTemplates = $db->query("SELECT COUNT(*) FROM tbl_maintenance_template WHERE isActive = 1")->fetchColumn();

?>
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-templates.css?v=<?php echo time(); ?>">

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
            
            <div class="modal-header border-bottom bg-white py-2">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h5 class="modal-title m-0">Template Builder</h5>
                        <small class="" style="font-size: 11px;">Editing Mode</small>
                    </div>
                    
                    <div class="d-flex align-items-center ms-4">
                        <label class="small  me-2 fw-bold">FREQUENCY:</label>
                        <select class="form-select form-select-sm" id="globalFreqSelect" style="width: 140px; border-color: var(--primary-green);">
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Semi-Annual" selected>Semi-Annual</option>
                            <option value="Annual">Annual</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="previewTemplate()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="btn btn-success btn-sm" onclick="saveTemplate()">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="overflow-y: auto;">
                <div class="builder-paper">
                    <div class="paper-header">
                        <div class="paper-title" contenteditable="true">ICT Preventive Maintenance</div>
                        <div class="paper-subtitle" contenteditable="true">Procedure Checklist</div>
                        
                        <div class="row mt-4 text-start small text-uppercase align-items-center">
                            <div class="col-6 d-flex align-items-center">
                                <strong class="me-2">Equipment Type:</strong> 
                                
                                <div class="dropdown d-inline-block" id="typeMultiSelectDropdown">
                                    <button class="btn btn-sm bg-light fw-bold text-primary border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="cursor: pointer;">
                                        <span id="typeMultiSelectLabel">Loading types...</span>
                                    </button>
                                    <div class="dropdown-menu p-2" style="min-width: 220px;" id="typeMultiSelectMenu">
                                        <!-- Populated by JS -->
                                    </div>
                                </div>
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
                                    <div class="ms-3  small fst-italic">[Yes / No / N/A]</div>
                                    <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                                </div>
                                <div class="checklist-row">
                                    <div class="flex-grow-1" contenteditable="true">Parts are intact</div>
                                    <div class="ms-3  small fst-italic">[Yes / No / N/A]</div>
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
                                    <div class="ms-3  small fst-italic">[Yes / No / N/A]</div>
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

                            <div class="position-relative p-2 border border-dashed rounded builder-hover">
                                <div class=" small mb-1 fst-italic user-select-none" style="opacity:0.5">Click text to edit</div>
                                
                                <div class="fw-bold text-primary" contenteditable="true" id="sigVerifiedName" style="outline: none;">
                                    [Select Supervisor Name]
                                </div>
                                
                                <div class="sig-line">Checked By</div>
                                
                                <div class="sig-role small " contenteditable="true" id="sigVerifiedTitle" style="outline: none;">
                                    Division / Section Head
                                </div>
                            </div>

                            <div class="position-relative p-2 border border-dashed rounded builder-hover">
                                <div class=" small mb-1 fst-italic user-select-none" style="opacity:0.5">Click text to edit</div>
                                
                                <div class="fw-bold text-primary" contenteditable="true" id="sigNotedName" style="outline: none;">
                                    [Select Head of Office]
                                </div>
                                
                                <div class="sig-line">Noted By</div>
                                
                                <div class="sig-role small " contenteditable="true" id="sigNotedTitle" style="outline: none;">
                                    Head of Office
                                </div>
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
            <h2>
                <i class="fas fa-file-alt me-2"></i>Maintenance Templates
            </h2>
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
                            <div class="h4 fw-bold mb-0" style="color: var(--primary-green);"><?php echo $totalTemplates ?? 0; ?></div>
                            <small class=" text-uppercase">Total Templates</small>
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
                            <div class="h4 fw-bold mb-0 text-success"><?php echo $activeTemplates ?? 0; ?></div>
                            <small class=" text-uppercase">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-info-xlight text-info me-3">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0 text-info"></div>
                            <small class=" text-uppercase">Times Used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="template-icon-box bg-warning-xlight text-warning me-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="h4 fw-bold mb-0 text-warning"></div>
                            <small class=" text-uppercase">Last Updated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
    <div class="row g-4" id="templatesContainer">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 ">Loading templates...</p>
        </div>
    </div>
</div>

<script src="assets/js/maintenance-template.js?v=<?php echo time(); ?>"></script>