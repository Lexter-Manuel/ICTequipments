<?php

$isWidget = isset($isWidget) ? $isWidget : false;
?>

<?php if (!$isWidget): ?>
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title"><i class="fas fa-tools"></i> ICT Preventive Maintenance</h1>
            <p class="page-subtitle">Procedure Checklist</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <div class="text-center">
            <h5 class="mb-0 fw-bold text-uppercase" style="color: var(--primary-green);">ICT Preventive Maintenance</h5>
            <p class="mb-0 text-muted small">Procedure Checklist</p>
        </div>
    </div>
    
    <div class="card-body p-4">
        <form id="maintenanceForm">
            
            <!-- Header Information -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Division/Section/Unit</label>
                    <input type="text" class="form-control" id="divisionUnit" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Date</label>
                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Employee Name</label>
                    <input type="text" class="form-control" id="employeeName" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">ICT Equipment Type</label>
                    <input type="text" class="form-control" id="equipmentTypeName" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Property No.</label>
                    <input type="text" class="form-control" id="propertyNo" readonly>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Designation</label>
                    <input type="text" class="form-control" id="designation" placeholder="e.g., Engineer II, Administrative Officer">
                </div>
            </div>

            <hr class="my-4">

            <!-- Maintenance Checklist Table for System Units/Laptops -->
            <div id="checklistComputer">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Maintenance Procedure</th>
                                <th style="width: 50%;">Description</th>
                                <th style="width: 8%;" class="text-center">Yes</th>
                                <th style="width: 8%;" class="text-center">No</th>
                                <th style="width: 9%;" class="text-center">N/A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Physical Inspection, Interiors and Cleaning -->
                            <tr>
                                <td rowspan="4" class="align-middle fw-bold">Physical Inspection, Interiors and Cleaning</td>
                                <td>Dust removal performed</td>
                                <td class="text-center"><input type="radio" name="pc_dust" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pc_dust" value="No"></td>
                                <td class="text-center"><input type="radio" name="pc_dust" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Parts are intact</td>
                                <td class="text-center"><input type="radio" name="pc_parts" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pc_parts" value="No"></td>
                                <td class="text-center"><input type="radio" name="pc_parts" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Fans are operating properly with minimal noise</td>
                                <td class="text-center"><input type="radio" name="pc_fans" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pc_fans" value="No"></td>
                                <td class="text-center"><input type="radio" name="pc_fans" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>No loose wires</td>
                                <td class="text-center"><input type="radio" name="pc_wires" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pc_wires" value="No"></td>
                                <td class="text-center"><input type="radio" name="pc_wires" value="N/A"></td>
                            </tr>

                            <!-- Hardware Performance Check -->
                            <tr>
                                <td rowspan="4" class="align-middle fw-bold">Hardware Performance Check</td>
                                <td>Power Supply is working properly</td>
                                <td class="text-center"><input type="radio" name="hw_power" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="hw_power" value="No"></td>
                                <td class="text-center"><input type="radio" name="hw_power" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>CMOS Battery is in good condition</td>
                                <td class="text-center"><input type="radio" name="hw_battery" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="hw_battery" value="No"></td>
                                <td class="text-center"><input type="radio" name="hw_battery" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Unnecessary startup programs are disabled</td>
                                <td class="text-center"><input type="radio" name="hw_startup" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="hw_startup" value="No"></td>
                                <td class="text-center"><input type="radio" name="hw_startup" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Storage drive is in good health</td>
                                <td class="text-center"><input type="radio" name="hw_storage" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="hw_storage" value="No"></td>
                                <td class="text-center"><input type="radio" name="hw_storage" value="N/A"></td>
                            </tr>

                            <!-- Software -->
                            <tr>
                                <td rowspan="5" class="align-middle fw-bold">Software</td>
                                <td>Windows OS is updated</td>
                                <td class="text-center"><input type="radio" name="sw_os" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="sw_os" value="No"></td>
                                <td class="text-center"><input type="radio" name="sw_os" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Installed licensed programs are updated</td>
                                <td class="text-center"><input type="radio" name="sw_licensed" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="sw_licensed" value="No"></td>
                                <td class="text-center"><input type="radio" name="sw_licensed" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Unnecessary programs are uninstalled</td>
                                <td class="text-center"><input type="radio" name="sw_unnecessary" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="sw_unnecessary" value="No"></td>
                                <td class="text-center"><input type="radio" name="sw_unnecessary" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Virus scan performed; Antivirus is updated</td>
                                <td class="text-center"><input type="radio" name="sw_antivirus" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="sw_antivirus" value="No"></td>
                                <td class="text-center"><input type="radio" name="sw_antivirus" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Temporary files and recycled files are removed</td>
                                <td class="text-center"><input type="radio" name="sw_cleanup" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="sw_cleanup" value="No"></td>
                                <td class="text-center"><input type="radio" name="sw_cleanup" value="N/A"></td>
                            </tr>

                            <!-- Backup -->
                            <tr>
                                <td class="align-middle fw-bold">Backup</td>
                                <td>Backup files are properly maintained</td>
                                <td class="text-center"><input type="radio" name="backup" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="backup" value="No"></td>
                                <td class="text-center"><input type="radio" name="backup" value="N/A"></td>
                            </tr>

                            <!-- Remarks -->
                            <tr>
                                <td class="fw-bold">Remarks/Recommendations</td>
                                <td colspan="4">
                                    <textarea class="form-control" rows="3" placeholder="Enter any remarks or recommendations..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Maintenance Checklist Table for Printers/Scanners -->
            <div id="checklistPrinter" style="display: none;">
                <p class="text-muted small mb-3"><em>(For additional ICT equipment of user ex. Printers, Scanners, Etc.)</em></p>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">ICT Equipment Type</label>
                        <input type="text" class="form-control" placeholder="e.g., Printer, Scanner">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Property No.</label>
                        <input type="text" class="form-control" placeholder="Property Number">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Maintenance Procedure</th>
                                <th style="width: 50%;">Description</th>
                                <th style="width: 8%;" class="text-center">Yes</th>
                                <th style="width: 8%;" class="text-center">No</th>
                                <th style="width: 9%;" class="text-center">N/A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Physical Inspection -->
                            <tr>
                                <td rowspan="3" class="align-middle fw-bold">Physical Inspection, Interiors and Cleaning</td>
                                <td>Dust Removal</td>
                                <td class="text-center"><input type="radio" name="pr_dust" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_dust" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_dust" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Parts are intact</td>
                                <td class="text-center"><input type="radio" name="pr_parts" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_parts" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_parts" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Loose wires</td>
                                <td class="text-center"><input type="radio" name="pr_wires" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_wires" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_wires" value="N/A"></td>
                            </tr>
                            <tr>
                                <td class="align-middle fw-bold">Power Supply</td>
                                <td>Power Supply is working properly</td>
                                <td class="text-center"><input type="radio" name="pr_power" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_power" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_power" value="N/A"></td>
                            </tr>

                            <!-- Software -->
                            <tr>
                                <td rowspan="4" class="align-middle fw-bold">Software</td>
                                <td>Printing /Scanning function is operational</td>
                                <td class="text-center"><input type="radio" name="pr_function" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_function" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_function" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Ink and Toner levels are adequate</td>
                                <td class="text-center"><input type="radio" name="pr_ink" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_ink" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_ink" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Waste Ink Pad is in good condition</td>
                                <td class="text-center"><input type="radio" name="pr_waste" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_waste" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_waste" value="N/A"></td>
                            </tr>
                            <tr>
                                <td>Software/Firmware is updated</td>
                                <td class="text-center"><input type="radio" name="pr_software" value="Yes"></td>
                                <td class="text-center"><input type="radio" name="pr_software" value="No"></td>
                                <td class="text-center"><input type="radio" name="pr_software" value="N/A"></td>
                            </tr>

                            <!-- Remarks -->
                            <tr>
                                <td class="fw-bold">Remarks/Recommendations</td>
                                <td colspan="4">
                                    <textarea class="form-control" rows="3" placeholder="Enter any remarks or recommendations..."></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="my-4">

            <!-- Signatories Section -->
            <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Prepared/Conducted by:</label>
                        <?php 
                            $conductorName = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? 'Unknown User';
                        ?>
                        <input type="text" class="form-control mb-2" value="<?php echo htmlspecialchars($conductorName); ?>" readonly>
                        <p class="text-center mb-0 small text-muted border-top pt-2">ICT Staff</p>
                    </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Checked by:</label>
                    <select class="form-control mb-2">
                        <option value="">-- Select --</option>
                        <option>MARIBETH O. CRUZ</option>
                        <option>ROBERTO M. SANTOS</option>
                    </select>
                    <p class="text-center mb-0 small text-muted border-top pt-2">Sr. Supply Officer</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Noted by:</label>
                    <select class="form-control mb-2">
                        <option value="">-- Select --</option>
                        <option>ATTY JUNE I. BARIOGA</option>
                        <option>ENGR. MARIA REYES</option>
                        <option>MR. JUAN DELA CRUZ</option>
                    </select>
                    <p class="text-center mb-0 small text-muted border-top pt-2">Division Manager, AdFin</p>
                </div>
            </div>

            <hr class="my-4">

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <div>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Complete all checklist items before submission.</small>
                </div>
                <div class="d-flex gap-2">
                    <?php if($isWidget): ?>
                        <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                            <i class="fas fa-save me-2"></i> Save Draft
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="submitMaintenance()">
                            <i class="fas fa-check-circle me-2"></i> Submit Record
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-success px-4" onclick="submitMaintenance()">
                            <i class="fas fa-check-circle me-2"></i> Save Record
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
function saveDraft() {
    alert('Draft saved successfully!\n\nYou can continue this maintenance inspection later.');
}

function submitMaintenance() {
    if (confirm('Submit maintenance record?\n\nThis will mark the equipment as maintained and update the schedule.')) {
        alert('Maintenance record submitted successfully!\n\nThe next maintenance due date has been updated.');
        
        setTimeout(() => {
            navigateToPage('maintenance-history');
        }, 1500);
    }
}
</script>