<!-- Reusable Maintenance Modal (included by any page that needs inline maintenance) -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: var(--radius-xl); overflow: hidden;">

            <!-- Styled Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="maintenanceModalLabel">
                    <i class="fas fa-clipboard-check"></i>
                    Preventive Maintenance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body: loading state + checklist container -->
            <div class="modal-body p-0">
                <div id="modal-maintenance-loader" class="mc-loading">
                    <div class="spinner"></div>
                    <p>Loading checklist…</p>
                </div>
                <div id="modal-maintenance-container"></div>
            </div>

        </div>
    </div>
</div>
