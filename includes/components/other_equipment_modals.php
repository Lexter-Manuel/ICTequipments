<!-- Add/Edit Equipment Modal -->
<div class="modal fade" id="otherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otherModalTitle">
                    <i class="fas fa-plus-circle"></i>
                    Add Other Equipment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="otherForm">
                    <input type="hidden" id="otherId">
                    
                    <!-- Equipment Information -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-info-circle"></i>
                            Equipment Information
                        </h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Equipment Type <span class="text-danger">*</span></label>
                                <div class="equipment-type-wrapper" style="position:relative;">
                                    <input type="text" class="form-control" id="otherType" required 
                                           placeholder="e.g. Projector, Switch, UPS" 
                                           autocomplete="off"
                                           oninput="onEquipmentTypeInput(this)"
                                           onfocus="onEquipmentTypeFocus(this)">
                                    <div id="typeDropdown" class="type-autocomplete-dropdown" style="display:none;"></div>
                                    <div id="typeSuggestionBanner" class="type-suggestion-banner" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otherSerial" required placeholder="Enter serial number">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Brand <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otherBrand" required placeholder="Enter brand name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otherModel" required placeholder="Enter model number">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Details / Specifications</label>
                            <textarea class="form-control" id="otherDetails" rows="3" placeholder="Enter technical specs or additional notes..."></textarea>
                        </div>
                    </div>

                    <!-- Location & Assignment -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Assignment
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label d-block">Assign To:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="assignType" id="typeLocation" value="location" checked onchange="toggleAssignmentType()">
                                <label class="btn btn-outline-primary" for="typeLocation"><i class="fas fa-building"></i> Location / Office</label>

                                <input type="radio" class="btn-check" name="assignType" id="typeEmployee" value="employee" onchange="toggleAssignmentType()">
                                <label class="btn btn-outline-primary" for="typeEmployee"><i class="fas fa-user"></i> Specific Employee</label>
                            </div>
                        </div>

                        <div id="locationContainer">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <label class="small text-muted">Division <span class="text-danger">*</span></label>
                                    <select class="form-select" id="locDivision">
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisionsData as $div): ?>
                                            <option value="<?= $div['location_id'] ?>"><?= $div['location_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Section</label>
                                    <select class="form-select" id="locSection" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Unit</label>
                                    <select class="form-select" id="locUnit" disabled>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="otherLocation" name="location_id"> 
                        </div>

                        <div id="employeeContainer" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otherEmployeeSearch" data-emp-search="otherEmployee" placeholder="Type to search employee..." autocomplete="off">
                                <input type="hidden" id="otherEmployee">
                                <small class="text-muted">Location will be cleared if assigned to an employee.</small>
                            </div>
                        </div>
                    </div>
                    <!-- Status & Year -->
                    <div class="form-section">
                        <h6 class="form-section-title">
                            <i class="fas fa-cog"></i>
                            Status & Acquisition
                        </h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="otherStatus">
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                    <option value="Disposed">Disposed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Year Acquired <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="otherYear" required min="2000" max="2030" placeholder="YYYY">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveOtherEquipment()">
                    <i class="fas fa-save"></i>
                    Save Equipment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewOtherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i>
                    Equipment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewOtherContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="button" class="btn btn-primary" onclick="editFromView()">
                    <i class="fas fa-edit"></i>
                    Edit Equipment
                </button>
            </div>
        </div>
    </div>
</div>