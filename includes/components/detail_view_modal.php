<!-- Reusable Detail View Modal for Maintenance Schedule & History -->
<div class="modal fade" id="maintenanceDetailModal" tabindex="-1" aria-labelledby="maintenanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: var(--radius-xl); overflow: hidden;">

            <!-- Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-green), var(--primary-green-dark, #1a6b3c)); color: #fff; border-bottom: none;">
                <h5 class="modal-title" id="maintenanceDetailModalLabel">
                    <i class="fas fa-info-circle"></i>
                    <span id="detailModalTitleText">Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-0">
                <div id="detail-modal-loader" style="text-align:center; padding:60px; display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p style="margin-top:10px; color:var(--text-light);">Loading details…</p>
                </div>
                <div id="detail-modal-content"></div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid var(--border-color, #e5e7eb); justify-content: flex-end;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="detailModalActionBtn" style="display:none;">
                    <i class="fas fa-file-pdf"></i> View Report
                </button>
            </div>

        </div>
    </div>
</div>

<style>
/* ─── Detail Modal Styles ─── */
.detail-section {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}
.detail-section:last-child { border-bottom: none; }
.detail-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-light, #6b7280);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-section-title i { color: var(--primary-green, #22c55e); }

.detail-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.detail-info-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }

.detail-field {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.detail-field-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-light, #6b7280);
}
.detail-field-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-dark, #1f2937);
}

/* Status badges in detail view */
.detail-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.detail-status-badge.badge-overdue   { background: #fef2f2; color: #dc2626; }
.detail-status-badge.badge-due-soon  { background: #fffbeb; color: #d97706; }
.detail-status-badge.badge-scheduled { background: #f0fdf4; color: #16a34a; }
.detail-status-badge.badge-operational  { background: #f0fdf4; color: #16a34a; }
.detail-status-badge.badge-replacement  { background: #fef2f2; color: #dc2626; }
.detail-status-badge.badge-disposed     { background: #f3f4f6; color: #6b7280; }

/* Condition badges */
.detail-cond-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.detail-cond-badge.cond-excellent { background: #f0fdf4; color: #16a34a; }
.detail-cond-badge.cond-good      { background: #eff6ff; color: #2563eb; }
.detail-cond-badge.cond-fair      { background: #fffbeb; color: #d97706; }
.detail-cond-badge.cond-poor      { background: #fef2f2; color: #dc2626; }

/* Checklist table in detail view */
.detail-checklist-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.detail-checklist-table th {
    background: var(--bg-subtle, #f9fafb);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-light, #6b7280);
    padding: 8px 12px;
    text-align: left;
    border-bottom: 2px solid var(--border-color, #e5e7eb);
}
.detail-checklist-table td {
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    vertical-align: middle;
}
.detail-checklist-table tr:last-child td { border-bottom: none; }
.detail-checklist-table .cat-row td {
    background: var(--bg-subtle, #f9fafb);
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--primary-green, #16a34a);
    padding: 6px 12px;
}

.detail-response {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
.detail-response.resp-yes { background: #dcfce7; color: #16a34a; }
.detail-response.resp-no  { background: #fee2e2; color: #dc2626; }
.detail-response.resp-na  { background: #f3f4f6; color: #6b7280; }
.detail-response.resp-ok  { background: #dcfce7; color: #16a34a; }
.detail-response.resp-fail { background: #fee2e2; color: #dc2626; }
.detail-response.resp-warning { background: #fef3c7; color: #d97706; }
.detail-response.resp-minor { background: #fef3c7; color: #d97706; }

/* Signatories row */
.detail-signatories {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.detail-signatory {
    text-align: center;
    padding: 12px;
    background: var(--bg-subtle, #f9fafb);
    border-radius: var(--radius-md, 8px);
}
.detail-signatory-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-light, #6b7280);
    margin-bottom: 6px;
}
.detail-signatory-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-dark, #1f2937);
}

/* Remarks */
.detail-remarks {
    background: var(--bg-subtle, #f9fafb);
    padding: 12px 16px;
    border-radius: var(--radius-md, 8px);
    font-size: 0.85rem;
    color: var(--text-dark, #1f2937);
    white-space: pre-wrap;
    line-height: 1.5;
}
.detail-remarks.empty {
    font-style: italic;
    color: var(--text-light, #9ca3af);
}

/* Recent history mini-table */
.detail-recent-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}
.detail-recent-table th {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-light, #6b7280);
    padding: 6px 8px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    text-align: left;
}
.detail-recent-table td {
    padding: 6px 8px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    color: var(--text-dark, #1f2937);
}
.detail-recent-table tr:last-child td { border-bottom: none; }

.detail-empty {
    text-align: center;
    padding: 30px;
    color: var(--text-light, #9ca3af);
    font-style: italic;
}

@media (max-width: 576px) {
    .detail-info-grid,
    .detail-info-grid.cols-3 { grid-template-columns: 1fr; }
    .detail-signatories { grid-template-columns: 1fr; }
}
</style>
