<?php

?>
<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/perform_maintenance.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/css/maintenance-checklist.css?v=<?php echo time(); ?>">

<div class="content-wrapper page-content">
    <!-- Selection Stage -->
    <div class="pm-selection-wrapper" id="selection-stage">
        <div class="pm-selection-card">

            <!-- Banner -->
            <div class="pm-card-banner">
                <div class="pm-banner-content">
                    <div class="pm-banner-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="pm-banner-text">
                        <h5>Start New Inspection</h5>
                        <p>Choose an equipment type, template, and asset to proceed.</p>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <div class="pm-card-body">
                <form onsubmit="event.preventDefault(); startSelection();">

                    <!-- Step 1 -->
                    <div class="pm-field-group">
                        <label class="pm-step-label">
                            <span class="pm-step-badge">1</span>
                            Equipment Type
                        </label>
                        <select class="pm-form-select" id="selectType">
                            <option value="">Loading types…</option>
                        </select>
                    </div>

                    <!-- Step 2 -->
                    <div class="pm-field-group">
                        <label class="pm-step-label">
                            <span class="pm-step-badge">2</span>
                            Checklist Template
                        </label>
                        <select class="pm-form-select" id="selectTemplate" disabled>
                            <option value="">— Select type first —</option>
                        </select>
                    </div>

                    <!-- Step 3 -->
                    <div class="pm-field-group">
                        <label class="pm-step-label">
                            <span class="pm-step-badge">3</span>
                            Select Equipment
                        </label>
                        <select class="pm-form-select" id="selectAsset" disabled>
                            <option value="">— Select type first —</option>
                        </select>
                    </div>

                    <div class="pm-divider"></div>

                    <button type="submit" class="pm-btn-start">
                        Begin Maintenance Inspection
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <!-- <div class="pm-info-strip">
                        <i class="fas fa-info-circle"></i>
                        <span>Only assets currently due for maintenance are shown in the asset list.</span>
                    </div> -->

                </form>
            </div>

        </div>
    </div>

    <!-- Checklist Stage (hidden initially) -->
    <div id="checklist-stage" class="d-none">
        <button class="pm-back-btn" onclick="location.reload()">
            <i class="fas fa-arrow-left"></i>
            Back to Selection
        </button>
        <div id="maintenance-form-container"></div>
    </div>

</div>

<script>
    (function() {
        loadEquipmentTypes();

        const typeSelect = document.getElementById('selectType');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                loadMaintenanceAssets('selectType', 'selectAsset');
                loadTemplateOptions('selectType', 'selectTemplate');
            });
        }
    })();

    function startSelection() {
        const scheduleId = document.getElementById('selectAsset').value;
        const templateId = document.getElementById('selectTemplate').value;

        if (!scheduleId) { alert('Please select an asset.'); return; }
        if (!templateId) { alert('Please select a checklist template.'); return; }

        document.getElementById('selection-stage').classList.add('d-none');
        document.getElementById('checklist-stage').classList.remove('d-none');

        startMaintenanceSequence(scheduleId, templateId, 'maintenance-form-container');
    }
</script>