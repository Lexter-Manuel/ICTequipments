<!-- Edit Employee Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header edit-modal-header">
                <h5 class="modal-title" style="color: white;">
                    <i class="fas fa-user-edit"></i> Edit Employee
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" name="employeeId" id="edit_employeeId">
                    <input type="hidden" name="location_id" id="edit_locationId">
                    <input type="hidden" name="croppedImage" id="edit_croppedImage">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h6>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="firstName" id="edit_firstName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middleName" id="edit_middleName">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="lastName" id="edit_lastName" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Suffix</label>
                                        <input type="text" class="form-control" name="suffixName" id="edit_suffixName">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Sex <span class="text-danger">*</span></label>
                                        <select class="form-select" name="sex" id="edit_sex" required>
                                            <option value="">Select...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="birthDate" id="edit_birthDate" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Details -->
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-briefcase"></i> Employment Details</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Position <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="position" id="edit_position" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="employmentStatus" id="edit_employmentStatus" required>
                                            <option value="">Select...</option>
                                            <option value="Permanent">Permanent</option>
                                            <option value="Casual">Casual</option>
                                            <option value="Job Order">Job Order</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Location Assignment</h6>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Division <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_division" required>
                                            <option value="">Select Division</option>
                                            <?php foreach ($divisions as $div): ?>
                                                <option value="<?php echo $div['location_id']; ?>"><?php echo htmlspecialchars($div['location_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section</label>
                                        <select class="form-select" id="edit_section" disabled>
                                            <option value="">Select Division first</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Unit</label>
                                        <select class="form-select" id="edit_unit" disabled>
                                            <option value="">Select Section first</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-section">
                                <h6 class="form-section-title"><i class="fas fa-camera"></i> Employee Photo</h6>
                                    <div class="photo-upload-container">
                                        <div class="photo-upload-box" id="edit_photoUploadBox">
                                            <input type="file" id="edit_photoInput" name="photo" accept="image/*" hidden>
                                            <div class="upload-placeholder" id="edit_uploadPlaceholder">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click to upload photo</p>
                                                <small>JPG, PNG (Max 5MB)</small>
                                            </div>
                                            <div class="photo-preview" id="edit_photoPreview">
                                                <img id="edit_previewImage" src="" alt="Preview">
                                            </div>
                                        </div>
                                        <button type="button" class="btn-change-photo" id="edit_changePhotoBtn" style="display:none">
                                            <i class="fas fa-sync-alt"></i> Change Photo
                                        </button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveEmployee()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Crop Modal -->
<div class="modal fade" id="edit_cropModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-crop"></i> Crop Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="crop-container">
                    <img id="edit_imageToCrop" src="" alt="Crop">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="edit_cropButton">
                    <i class="fas fa-check"></i> Crop & Save
                </button>
            </div>
        </div>
    </div>
</div>