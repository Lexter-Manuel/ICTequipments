/**
 * maintenance-conductor.js
 * Handles performing maintenance with dynamic templates and real asset data.
 * Also provides the reusable openMaintenanceModal() used by schedule & roster pages.
 */

// Global State
var conductorScheduleId = null;
var currentAssetData = null;
var currentTemplateSignatories = {}; // Stores the template's signatory names for submission

// Equipment Type Map — shared across all pages
var EQUIPMENT_TYPE_MAP = {};
var _typeMapLoaded = false;

function ensureEquipmentTypeMap() {
    if (_typeMapLoaded) return Promise.resolve();
    return fetch(BASE_URL + 'ajax/get_equipment_types.php')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                res.data.forEach(function(t) {
                    var key = t.typeName.toLowerCase().replace(/[^a-z0-9]/g, '');
                    EQUIPMENT_TYPE_MAP[key] = t.typeId;
                });
                _typeMapLoaded = true;
            }
        })
        .catch(function(err) { console.error('Failed to load equipment type map:', err); });
}

// ============================================================
// REUSABLE: Open Maintenance Modal
// ============================================================
// opts: {
//   scheduleId   : number|null — if null, will look up / auto-create
//   equipmentId  : number      — needed when scheduleId is null
//   equipmentType: number|string — numeric type ID, or type name string ("System Unit")
//   typeName     : string      — display label for the type
//   brand        : string
//   serial       : string
//   owner        : string
//   location     : string
// }
function openMaintenanceModal(opts) {
    var modalEl = document.getElementById('maintenanceModal');
    if (!modalEl) {
        console.error('maintenanceModal element not found. Include maintenance_modal.php.');
        return;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    var container = document.getElementById('modal-maintenance-container');
    var loader    = document.getElementById('modal-maintenance-loader');
    loader.style.display = 'block';
    container.innerHTML = '';

    // Resolve type string → numeric ID if needed
    var resolveType;
    if (opts.equipmentType && isNaN(Number(opts.equipmentType))) {
        // It's a string like "System Unit"
        resolveType = ensureEquipmentTypeMap().then(function() {
            var key = opts.equipmentType.toLowerCase().replace(/[^a-z0-9]/g, '');
            var resolved = EQUIPMENT_TYPE_MAP[key];
            if (!resolved) {
                throw new Error('Unknown equipment type: ' + opts.equipmentType);
            }
            opts.typeName = opts.typeName || opts.equipmentType;
            opts.equipmentType = resolved;
        });
    } else {
        resolveType = Promise.resolve();
    }

    resolveType.then(function() {
        var typeId = opts.equipmentType;

        // Build fetch list: always need templates. If no scheduleId, also need assets.
        var fetches = [
            fetch(BASE_URL + 'ajax/manage_templates.php?action=list_by_type&type=' + typeId).then(function(r) { return r.json(); })
        ];
        if (!opts.scheduleId) {
            fetches.push(
                fetch(BASE_URL + 'ajax/get_maintenance_assets.php?type=' + typeId).then(function(r) { return r.json(); })
            );
        }

        return Promise.all(fetches);
    })
    .then(function(results) {
        loader.style.display = 'none';
        var tmplRes  = results[0];
        var assetRes = results[1]; // undefined when scheduleId was provided

        // Check templates
        if (!tmplRes.success || !tmplRes.data || tmplRes.data.length === 0) {
            container.innerHTML = '<div class="alert alert-warning m-4 text-center">'
                + 'No checklist templates found for <strong>' + (opts.typeName || 'this type') + '</strong>.'
                + '<br>Please create one in "Maintenance Templates" first.</div>';
            return;
        }

        // Resolve scheduleId if unknown
        var resolvedScheduleId = opts.scheduleId || null;
        var needsAutoCreate = false;

        if (!resolvedScheduleId && assetRes && assetRes.data) {
            var match = assetRes.data.find(function(a) { return a.equipmentId == opts.equipmentId; });
            if (match) {
                resolvedScheduleId = match.scheduleId;
            } else {
                needsAutoCreate = true;
            }
        } else if (!resolvedScheduleId) {
            needsAutoCreate = true;
        }

        // Build template options
        var optionsHtml = tmplRes.data.map(function(t) {
            return '<option value="' + t.templateId + '">' + t.templateName + ' (' + t.frequency + ')</option>';
        }).join('');

        // Render selection UI
        container.innerHTML = ''
            + '<div class="row justify-content-center p-4">'
            + '  <div class="col-md-8 text-center">'
            + '    <div class="bg-primary-xlight text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">'
            + '      <i class="fas fa-clipboard-list fa-2x"></i>'
            + '    </div>'
            + '    <h5 class="fw-bold">Maintenance Selection</h5>'
            + '    <p class="text-muted small mb-4">Select the checklist template to use for <strong>' + (opts.brand || 'this equipment') + '</strong>.</p>'
            + '    <div class="card bg-light border-0 p-3 mb-4 text-start">'
            + '      <div class="mb-3">'
            + '        <label class="form-label fw-bold small text-muted text-uppercase">1. Asset Details</label>'
            + '        <input type="text" class="form-control" value="' + (opts.brand || '') + ' — ' + (opts.serial || 'N/A') + '" readonly disabled>'
            + '      </div>'
            + '      <div class="mb-0">'
            + '        <label class="form-label fw-bold small text-muted text-uppercase">2. Select Checklist Template</label>'
            + '        <select class="form-select form-select-lg shadow-sm border-primary" id="modalTemplateSelect">'
            + '          ' + optionsHtml
            + '        </select>'
            + '      </div>'
            + '    </div>'
            + '    <div class="d-grid gap-2">'
            + '      <button class="btn btn-primary btn-lg" id="btnStartModalMaint">'
            + '        Start Inspection <i class="fas fa-arrow-right ms-2"></i>'
            + '      </button>'
            + '    </div>'
            + '  </div>'
            + '</div>';

        // Attach start handler
        document.getElementById('btnStartModalMaint').onclick = function() {
            var templateId = document.getElementById('modalTemplateSelect').value;

            // Set currentAssetData for the conductor
            currentAssetData = {
                owner:    opts.owner    || 'N/A',
                location: opts.location || 'N/A',
                serial:   opts.serial   || 'N/A',
                brand:    opts.brand    || 'N/A',
                type:     opts.typeName || 'Equipment'
            };

            if (needsAutoCreate) {
                container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Initializing schedule…</p></div>';
                fetch(BASE_URL + 'ajax/quick_add_schedule.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ equipmentId: opts.equipmentId, equipmentType: opts.equipmentType })
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        startMaintenanceSequence(res.scheduleId, templateId, 'modal-maintenance-container');
                    } else {
                        container.innerHTML = '<div class="alert alert-danger m-3">Failed to create schedule: ' + res.message + '</div>';
                    }
                });
            } else {
                startMaintenanceSequence(resolvedScheduleId, templateId, 'modal-maintenance-container');
            }
        };
    })
    .catch(function(err) {
        loader.style.display = 'none';
        container.innerHTML = '<div class="alert alert-danger m-3">Error: ' + err.message + '</div>';
    });
}

// ============================================================
// 1. Load Equipment Types into Dropdown
// ============================================================
function loadEquipmentTypes() {
    const select = document.getElementById('selectType');

    fetch(`${BASE_URL}ajax/get_equipment_types.php`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">— Select Type —</option>';
                res.data.forEach(type => {
                    const opt = document.createElement('option');
                    opt.value = type.typeId;
                    opt.textContent = type.typeName;
                    select.appendChild(opt);
                });
            } else {
                select.innerHTML = '<option value="">Error loading types</option>';
            }
        });
}

// ============================================================
// 2. Load Assets into Dropdown
// ============================================================
function loadMaintenanceAssets(typeSelectId, assetSelectId) {
    const type = document.getElementById(typeSelectId).value;
    const assetSelect = document.getElementById(assetSelectId);

    if (!type) {
        assetSelect.innerHTML = '<option value="">— Select Type First —</option>';
        assetSelect.disabled = true;
        return;
    }

    fetch(`${BASE_URL}ajax/get_maintenance_assets.php?type=${type}`)
        .then(r => r.json())
        .then(res => {
            assetSelect.innerHTML = '<option value="">— Select Asset —</option>';

            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.scheduleId;
                    opt.dataset.owner    = item.owner_name  || 'Unassigned';
                    opt.dataset.location = item.location_name || 'N/A';
                    opt.dataset.serial   = item.serial       || 'N/A';
                    opt.dataset.brand    = item.name         || 'Unknown';
                    opt.dataset.typename = item.type_name    || 'Equipment';
                    opt.textContent = `${item.name} (${item.serial}) — Due: ${item.nextDueDate}`;
                    assetSelect.appendChild(opt);
                });
                assetSelect.disabled = false;
            } else {
                assetSelect.innerHTML = '<option value="">No equipment due for maintenance</option>';
            }
        })
        .catch(err => console.error('Asset Load Error:', err));
}

// ============================================================
// 3. Load Templates into Dropdown
// ============================================================
function loadTemplateOptions(typeSelectId, templateSelectId) {
    const type = document.getElementById(typeSelectId).value;
    const templateSelect = document.getElementById(templateSelectId);

    if (!type) {
        templateSelect.innerHTML = '<option value="">— Select Type First —</option>';
        templateSelect.disabled = true;
        return;
    }

    fetch(`${BASE_URL}ajax/manage_templates.php?action=list_by_type&type=${type}`)
        .then(r => r.json())
        .then(res => {
            templateSelect.innerHTML = '<option value="">— Select Template —</option>';

            if (res.data && res.data.length > 0) {
                res.data.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.templateId;
                    opt.textContent = `${t.templateName} (${t.frequency})`;
                    templateSelect.appendChild(opt);
                });
                templateSelect.disabled = false;
                if (res.data.length === 1) templateSelect.value = res.data[0].templateId;
            } else {
                templateSelect.innerHTML = '<option value="">No templates found</option>';
            }
        });
}

// ============================================================
// 4. Start Maintenance Sequence
// ============================================================
function startMaintenanceSequence(scheduleId, templateId, containerId) {
    const container = document.getElementById(containerId);

    // Scenario A: perform-maintenance page — pull data from the dropdown
    const assetSelect = document.getElementById('selectAsset');
    if (assetSelect) {
        const selectedOption = assetSelect.options[assetSelect.selectedIndex];
        if (selectedOption) {
            currentAssetData = {
                owner:    selectedOption.dataset.owner,
                location: selectedOption.dataset.location,
                serial:   selectedOption.dataset.serial,
                brand:    selectedOption.dataset.brand,
                type:     selectedOption.dataset.typename
            };
        }
    } else {
        // Scenario B: roster modal — currentAssetData was set externally by roster.js
        if (!currentAssetData) {
            console.warn('Warning: currentAssetData is missing for maintenance modal.');
            currentAssetData = { owner: 'N/A', location: 'N/A', serial: 'N/A', brand: 'N/A', type: 'Equipment' };
        }
    }

    // Show loading state
    container.innerHTML = `
        <div class="mc-loading">
            <div class="spinner"></div>
            <p>Loading checklist…</p>
        </div>`;

    fetch(`${BASE_URL}ajax/manage_templates.php?action=get&id=${templateId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                container.innerHTML = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ${res.message}</div>`;
                return;
            }
            conductorScheduleId = scheduleId;

            // Capture signatories from the template for later submission
            try {
                currentTemplateSignatories = res.data.signatories_json
                    ? JSON.parse(res.data.signatories_json)
                    : {};
            } catch (e) {
                currentTemplateSignatories = {};
            }

            renderMaintenanceForm(res.data, container);
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error: ${err.message}</div>`;
        });
}

// ============================================================
// 5. Render the Checklist Form
// ============================================================
function renderMaintenanceForm(template, container) {
    let structure = {};
    let sigs = {};

    try {
        structure = JSON.parse(template.structure_json);
        if (template.signatories_json) sigs = JSON.parse(template.signatories_json);
    } catch (e) { console.error('JSON Parse Error', e); }

    const today = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });

    // Build checklist rows
    let checklistRows = '';
    let seqCounter = 0;
    if (structure.categories) {
        structure.categories.forEach((cat, catIdx) => {
            const itemCount = cat.items.length;
            cat.items.forEach((item, itemIdx) => {
                const uniqueName = `check_${catIdx}_${itemIdx}`;
                seqCounter++;
                checklistRows += `<tr>`;
                if (itemIdx === 0) {
                    checklistRows += `<td rowspan="${itemCount}" class="mc-category-cell">${cat.title}</td>`;
                }
                checklistRows += `
                    <td class="mc-desc-cell">
                        ${item.text}
                        <input type="hidden" name="tasks[${uniqueName}][desc]" value="${item.text}">
                        <input type="hidden" name="tasks[${uniqueName}][itemId]" value="${item.itemId || 0}">
                        <input type="hidden" name="tasks[${uniqueName}][categoryId]" value="${cat.categoryId || 0}">
                        <input type="hidden" name="tasks[${uniqueName}][categoryName]" value="${cat.title}">
                        <input type="hidden" name="tasks[${uniqueName}][seq]" value="${seqCounter}">
                    </td>
                    <td class="mc-radio-cell">
                        <input class="mc-radio" type="radio" name="tasks[${uniqueName}][status]" value="Yes" required>
                    </td>
                    <td class="mc-radio-cell">
                        <input class="mc-radio radio-no" type="radio" name="tasks[${uniqueName}][status]" value="No">
                    </td>
                    <td class="mc-radio-cell">
                        <input class="mc-radio radio-na" type="radio" name="tasks[${uniqueName}][status]" value="N/A">
                    </td>
                </tr>`;
            });
        });
    }

    // Determine cancel action context
    const isModal = !document.getElementById('selectAsset');
    const cancelAction = isModal
        ? `document.getElementById('maintenanceModal')?.querySelector('[data-bs-dismiss]')?.click()`
        : `reloadCurrentPage()`;

    const html = `
    <div class="mc-card">

        <!-- Banner Header -->
        <div class="mc-card-header">
            <div class="mc-header-inner">
                <div class="mc-header-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="mc-header-text">
                    <h4 class="mc-header-title">${template.templateName}</h4>
                    <p class="mc-header-subtitle">Preventive Maintenance Checklist</p>
                </div>
            </div>
        </div>

        <div class="mc-card-body">
            <form id="maintenanceForm">
                <input type="hidden" name="templateId" value="${template.templateId}">

                <!-- Row 1: Location + Date -->
                <div class="mc-info-grid mc-info-grid-2">
                    <div class="mc-field-group">
                        <span class="mc-field-label">Division / Section / Unit</span>
                        <span class="mc-field-value">${currentAssetData.location}</span>
                    </div>
                    <div class="mc-field-group">
                        <span class="mc-field-label">Date</span>
                        <span class="mc-field-value">${today}</span>
                    </div>
                </div>

                <!-- Row 2: Owner + Type + Serial -->
                <div class="mc-info-grid mc-info-grid-3" style="margin-bottom: var(--space-6);">
                    <div class="mc-field-group">
                        <span class="mc-field-label">Employee Name</span>
                        <span class="mc-field-value">${currentAssetData.owner}</span>
                    </div>
                    <div class="mc-field-group">
                        <span class="mc-field-label">ICT Equipment Type</span>
                        <span class="mc-field-value">${currentAssetData.type}</span>
                    </div>
                    <div class="mc-field-group">
                        <span class="mc-field-label">Property No. / Serial</span>
                        <span class="mc-field-value is-serial">${currentAssetData.serial}</span>
                    </div>
                </div>

                <div class="mc-section-divider"></div>

                <!-- Checklist Title -->
                <div class="mc-section-title">
                    <i class="fas fa-tasks"></i> Maintenance Checklist
                </div>

                <!-- Checklist Table -->
                <div class="mc-table-wrapper">
                    <div class="mc-table-scroll">
                    <table class="mc-table">
                        <thead>
                            <tr>
                                <th class="col-procedure">Procedure</th>
                                <th class="col-desc">Description</th>
                                <th class="col-radio">Yes</th>
                                <th class="col-radio">No</th>
                                <th class="col-radio">N/A</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${checklistRows}
                            <tr class="mc-remarks-row">
                                <td class="mc-remarks-label">Remarks</td>
                                <td colspan="4">
                                    <textarea
                                        class="mc-textarea"
                                        name="remarks"
                                        rows="3"
                                        placeholder="Observations, recommendations, or defects found…"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="mc-section-divider"></div>

                <!-- Overall Status Selection -->
                <div class="mc-section-title">
                    <i class="fas fa-heartbeat"></i> Overall Equipment Status
                </div>
                <div class="mc-info-grid mc-info-grid-2" style="margin-bottom: var(--space-6);">
                    <div class="mc-field-group">
                        <label class="mc-field-label" for="overallStatusSelect">Overall Status</label>
                        <select class="form-select" id="overallStatusSelect" name="overallStatus" required>
                            <option value="Operational" selected>Operational</option>
                            <option value="For Replacement">For Replacement</option>
                            <option value="Disposed">Disposed</option>
                        </select>
                    </div>
                    <div class="mc-field-group">
                        <label class="mc-field-label" for="conditionRatingSelect">Condition Rating</label>
                        <select class="form-select" id="conditionRatingSelect" name="conditionRating" required>
                            <option value="Excellent">Excellent</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select>
                    </div>
                </div>

                <div class="mc-section-divider"></div>

                <!-- Signatories -->
                <div class="mc-section-title">
                    <i class="fas fa-signature"></i> Signatories
                </div>

                <div class="mc-signatories-grid">
                    <div class="mc-signatory-card">
                        <div class="mc-signatory-header">Prepared / Conducted by</div>
                        <div class="mc-signatory-body">
                            <div class="mc-signatory-name">${(typeof CURRENT_USER !== 'undefined' && CURRENT_USER.name) || 'Current User'} (You)</div>
                            <div class="mc-signatory-line">${(typeof CURRENT_USER !== 'undefined' && CURRENT_USER.role) || 'ICT Staff'}</div>
                        </div>
                    </div>
                    <div class="mc-signatory-card">
                        <div class="mc-signatory-header">Checked by</div>
                        <div class="mc-signatory-body">
                            <div class="mc-signatory-name">${sigs.verifiedByName || 'N/A'}</div>
                            <div class="mc-signatory-line">${sigs.verifiedByTitle || 'Supervisor'}</div>
                        </div>
                    </div>
                    <div class="mc-signatory-card">
                        <div class="mc-signatory-header">Noted by</div>
                        <div class="mc-signatory-body">
                            <div class="mc-signatory-name">${sigs.notedByName || 'N/A'}</div>
                            <div class="mc-signatory-line">${sigs.notedByTitle || 'Head of Office'}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mc-actions">
                    <button type="button" class="mc-btn-cancel" onclick="${cancelAction}">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="mc-btn-submit" onclick="saveMaintenanceRecord()">
                        <i class="fas fa-check-circle"></i> Submit Record
                    </button>
                </div>

            </form>
        </div>
    </div>`;

    container.innerHTML = html;
    setupStatusConditionControls();
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function setupStatusConditionControls() {
    var overallSelect = document.getElementById('overallStatusSelect');
    var conditionSelect = document.getElementById('conditionRatingSelect');

    if (!overallSelect || !conditionSelect) return;

    function setOptionEnabled(value, enabled) {
        for (var i = 0; i < conditionSelect.options.length; i++) {
            if (conditionSelect.options[i].value === value) {
                conditionSelect.options[i].disabled = !enabled;
                break;
            }
        }
    }

    function applyRules() {
        var status = overallSelect.value;

        if (status === 'Operational') {
            conditionSelect.disabled = false;
            for (var i = 0; i < conditionSelect.options.length; i++) {
                conditionSelect.options[i].disabled = false;
            }
            return;
        }

        if (status === 'For Replacement') {
            conditionSelect.disabled = false;
            setOptionEnabled('Excellent', false);
            setOptionEnabled('Good', false);
            setOptionEnabled('Fair', true);
            setOptionEnabled('Poor', true);
            if (conditionSelect.options[conditionSelect.selectedIndex]?.disabled) {
                conditionSelect.value = 'Fair';
            }
            return;
        }

        if (status === 'Disposed') {
            setOptionEnabled('Excellent', false);
            setOptionEnabled('Good', false);
            setOptionEnabled('Fair', false);
            setOptionEnabled('Poor', true);
            conditionSelect.value = 'Poor';
            conditionSelect.disabled = true;
        }
    }

    overallSelect.addEventListener('change', applyRules);
    applyRules();
}

// ============================================================
// 6. Save Maintenance Record
// ============================================================
function saveMaintenanceRecord() {
    const form = document.getElementById('maintenanceForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Disable button to prevent double submit
    const submitBtn = form.querySelector('.mc-btn-submit');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';
    }

    const formData = new FormData(form);
    const rawTasks = {};

    for (const [key, value] of formData.entries()) {
        if (key.startsWith('tasks')) {
            const match = key.match(/tasks\[(.*?)\]\[(.*?)\]/);
            if (match) {
                const id    = match[1];
                const field = match[2];
                if (!rawTasks[id]) rawTasks[id] = {};
                rawTasks[id][field] = value;
            }
        }
    }

    const checklistData = Object.values(rawTasks);
    const overallStatus  = formData.get('overallStatus')  || 'Operational';
    const conditionRating = formData.get('conditionRating') || 'Good';

    const payload = {
        scheduleId:    conductorScheduleId,
        templateId:    formData.get('templateId') || null,
        equipmentId:   0,
        equipmentTypeId: 0,
        checklistData: checklistData,
        remarks:       formData.get('remarks'),
        overallStatus: overallStatus,
        conditionRating: conditionRating,
        signatories: {
            preparedBy: (typeof CURRENT_USER !== 'undefined' && CURRENT_USER.name) || 'Current User',
            checkedBy:  currentTemplateSignatories.verifiedByName || '',
            notedBy:    currentTemplateSignatories.notedByName    || ''
        }
    };

    fetch(`${BASE_URL}ajax/record_maintenance.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            // If FAB "Perform All" queue is active, advance to next item
            if (window._fabQueueActive && typeof fabOpenNext === 'function') {
                var modalEl = document.getElementById('maintenanceModal');
                if (modalEl) {
                    var bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                }
                var remaining = (fabMaintenanceQueue || []).length - (fabQueueIndex || 0);
                showAlert('success', 'Record saved! ' + (remaining > 0 ? remaining + ' equipment remaining…' : 'All done!'));
                setTimeout(function() { fabOpenNext(); }, 400);
                return;
            }
            alert('Maintenance record submitted successfully!');
            reloadCurrentPage();
        } else {
            alert('Error: ' + res.message);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Record';
            }
        }
    })
    .catch(err => {
        alert('Request failed: ' + err.message);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Record';
        }
    });
}