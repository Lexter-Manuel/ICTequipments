// 1. Force Load Logic (Fixes the infinite loading spinner)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadTemplates);
} else {
    loadTemplates(); // Run immediately if already loaded
}

// 2. Main Loader Function
function loadTemplates() {
    var container = document.getElementById('templatesContainer');
    
    // Safety check: stop if we aren't on the templates page
    if (!container) return;

    // Use the correct path relative to your dashboard
    fetch('../ajax/manage_templates.php?action=list')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<div class="col-12 text-danger">Error: ${data.message}</div>`;
                return;
            }

            if (data.data.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="mb-3 " style="opacity: 0.5;">
                            <i class="fas fa-folder-open fa-3x"></i>
                        </div>
                        <h5 class="">No templates found</h5>
                        <p class="small ">Create a new template to get started.</p>
                    </div>`;
                return;
            }

            // Clear spinner
            container.innerHTML = '';

            // Render cards
            data.data.forEach(template => {
                // THIS WAS MISSING: Calling the function below
                container.insertAdjacentHTML('beforeend', createTemplateCard(template));
            });
        })
        .catch(error => {
            console.error('Template Load Error:', error);
            container.innerHTML = `
                <div class="col-12 text-center text-danger py-5">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                    <strong>Failed to load templates.</strong><br>
                    <small>${error.message}</small>
                </div>`;
        });
}

// 3. The Function That Was Missing (Card Generator)
function createTemplateCard(t) {
    // Determine Icon & Color based on Type(s)
    var typeIds = (t.targetTypeId || '').split(',').map(s => s.trim());
    var typeNames = t.targetTypeNames || [];

    // Pick primary icon from first type
    var iconClass = 'fa-cogs';
    var bgClass = 'bg-primary-xlight text-primary';
    var firstId = typeIds[0] || '';

    if (firstId === '1')      { iconClass = 'fa-desktop'; }
    else if (firstId === '2') { iconClass = 'fa-desktop'; }
    else if (firstId === '3') { iconClass = 'fa-tv'; bgClass = 'bg-info-xlight text-info'; }
    else if (firstId === '4') { iconClass = 'fa-print'; bgClass = 'bg-secondary-xlight text-secondary'; }
    else if (firstId === '5') { iconClass = 'fa-laptop'; }

    // Count Items (Use count from DB if available, else 0)
    var itemCount = t.item_count || 0;

    // Format Date
    var dateCreated = t.createdAt ? new Date(t.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A';

    // Build type badges
    var typeBadges = '';
    if (typeNames.length > 0) {
        typeBadges = typeNames.map(name => `<span class="badge bg-light text-dark border me-1" style="font-size:10px;">${name}</span>`).join('');
    } else {
        typeBadges = '<span class="badge bg-light text-muted border" style="font-size:10px;">No type assigned</span>';
    }

    return `
    <div class="col-md-6 col-xl-4">
        <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate(${t.templateId})">
            <span class="template-badge badge bg-success">Active</span>
            <div class="card-body">
<div class="d-flex justify-content-between align-items-start mb-3">
                <div class="template-icon-box ${bgClass}">
                    <i class="fas ${iconClass}"></i>
                </div>
                
                <div class="dropdown" onclick="event.stopPropagation()">
                    <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-edit" href="#" onclick="editTemplate(${t.templateId})">
                                <i class="fas fa-edit me-2"></i> Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteTemplate(${t.templateId})">
                                <i class="fas fa-trash me-2"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
                <h5 class="fw-bold mb-1">${t.templateName}</h5>
                <div class="mb-3">${typeBadges}</div>
                
                <div class="template-stats">
                    <div class="template-stat">
                        <div class="small ">Frequency</div>
                        <div class="fw-bold" style="color: var(--primary-green);">${t.frequency}</div>
                    </div>
                    <div class="template-stat text-end">
                        <div class="small ">Questions</div>
                        <div class="fw-bold" style="color: var(--primary-green);">${itemCount} items</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light border-top">
                <div class="d-flex justify-content-between align-items-center small">
                    <span class=""><i class="fas fa-list-check me-1"></i> ${itemCount} items</span>
                    <span class="">Created ${dateCreated}</span>
                </div>
            </div>
        </div>
    </div>
    `;
}

function openCreateTemplateModal() {
    currentTemplateId = null; // RESET ID
    document.querySelector('.paper-title').innerText = "ICT Preventive Maintenance";
    document.getElementById('checklistCategories').innerHTML = ""; // Clear canvas
    
    // Reset Signatories
    document.getElementById('sigVerifiedName').innerText = "[Select Supervisor Name]";
    document.getElementById('sigVerifiedTitle').innerText = "Division / Section Head";

    // Reset equipment type multi-select
    selectAllTypes(false);
    
    var modal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
    modal.show();
}

// 4. Template Builder Functions (For the Modal)
var builderModal = null;

function openBuilderModal() {
    if (!builderModal) {
        builderModal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
    }
    builderModal.show();
}

function addCategory() {
    var container = document.getElementById('checklistCategories');
    var newSection = document.createElement('div');
    newSection.className = 'paper-section';
    newSection.innerHTML = `
        <div class="section-header">
            <span contenteditable="true">New Section Header</span>
            <div class="builder-controls">
                <button class="btn btn-xs btn-link text-dark p-0 me-2" onclick="addItem(this)"><i class="fas fa-plus"></i> Add Item</button>
                <button class="btn btn-xs btn-link text-danger p-0" onclick="removeSection(this)"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="checklist-items">
            <div class="checklist-row">
                <div class="flex-grow-1" contenteditable="true">New inspection item...</div>
                <div class="ms-3  small fst-italic">[Yes / No / N/A]</div>
                <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
            </div>
        </div>
    `;
    container.appendChild(newSection);
}

function addItem(btn) {
    var itemsContainer = btn.closest('.section-header').nextElementSibling;
    var newItem = document.createElement('div');
    newItem.className = 'checklist-row';
    newItem.innerHTML = `
        <div class="flex-grow-1" contenteditable="true">New inspection item...</div>
        <div class="ms-3  small fst-italic">[Pass / No / N/A]</div>
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

// Global State
var currentTemplateId = null; // null = create mode, number = edit mode

// Global variable to track which element we are editing
var currentSignatoryContainer = null;
var signatoryModalInstance = null;

// 1. OPEN THE MODAL
function editSignatory(btn) {
    // Save reference to the container (the parent div of the clicked button)
    currentSignatoryContainer = btn.closest('.position-relative');
    
    // Find the current values
    var nameEl = currentSignatoryContainer.querySelector('[id*="Name"]');
    var titleEl = currentSignatoryContainer.querySelector('[id*="Title"]');
    
    // Populate the modal inputs
    document.getElementById('inputSigName').value = nameEl.innerText;
    document.getElementById('inputSigTitle').value = titleEl.innerText;
    
    // Show the modal
    if (!signatoryModalInstance) {
        signatoryModalInstance = new bootstrap.Modal(document.getElementById('signatoryModal'));
    }
    signatoryModalInstance.show();
    
    // Auto-focus the name field
    setTimeout(() => document.getElementById('inputSigName').focus(), 500);
}

// 2. APPLY CHANGES (Called when "Apply Changes" is clicked)
function applySignatoryChanges() {
    if (!currentSignatoryContainer) return;

    // Get new values
    var newName = document.getElementById('inputSigName').value.trim();
    var newTitle = document.getElementById('inputSigTitle').value.trim();

    // Update the DOM elements in the builder
    var nameEl = currentSignatoryContainer.querySelector('[id*="Name"]');
    var titleEl = currentSignatoryContainer.querySelector('[id*="Title"]');

    nameEl.innerText = newName || "[Name Placeholder]";
    titleEl.innerText = newTitle || "Position Title";

    // Visual Feedback (Flash green)
    nameEl.classList.add('text-success');
    setTimeout(() => nameEl.classList.remove('text-success'), 500);

    // Close Modal
    signatoryModalInstance.hide();
}

var previewModalInstance = null;

function viewTemplate(id) {
    var modalEl = document.getElementById('templateViewModal');
    var modalBody = document.getElementById('viewModalBody');
    
    // Initialize Modal if needed
    if (!previewModalInstance) {
        previewModalInstance = new bootstrap.Modal(modalEl);
    }
    
    // Show Loading State
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 ">Loading template preview...</p>
        </div>`;
    
    previewModalInstance.show();

    // Fetch Data
    fetch(`../ajax/manage_templates.php?action=get&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                renderPreview(data.data, modalBody);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(err => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading preview: ${err.message}</div>`;
        });
}

function renderPreview(t, container) {
    // 1. Parse Data
    var structure = {};
    try {
        structure = JSON.parse(t.structure_json);
    } catch(e) { console.error("JSON Parse Error", e); }

    // 2. Build the Main Table Rows
    var tableRows = '';
    
    if(structure.categories) {
        structure.categories.forEach((cat) => {
            var itemCount = cat.items.length;
            
            // Loop through items in this category
            cat.items.forEach((item, index) => {
                tableRows += '<tr>';
                
                // Column 1: Maintenance Procedure (Category)
                // Only render this cell for the FIRST item in the category, with rowspan
                if (index === 0) {
                    tableRows += `<td rowspan="${itemCount}" class="align-middle fw-bold bg-light" style="width: 25%;">${cat.title}</td>`;
                }
                
                // Column 2: Description (Item)
                tableRows += `<td style="width: 51%;">${item.text}</td>`;
                
                // Column 3, 4, 5: Checkboxes (Empty for preview)
                tableRows += `<td class="text-center" style="width: 8%;"></td>`; // Yes
                tableRows += `<td class="text-center" style="width: 8%;"></td>`; // No
                tableRows += `<td class="text-center" style="width: 8%;"></td>`; // N/A
                
                tableRows += '</tr>';
            });
        });
    }

    // 3. Parse Signatories
    var sigs = { verifiedByTitle: 'Division/Section Head', notedByTitle: 'Head of Office', verifiedByName: '', notedByName: '' };
    try {
        if(t.signatories_json) {
            var parsedSigs = JSON.parse(t.signatories_json);
            if(parsedSigs.verifiedByTitle) sigs = Object.assign(sigs, parsedSigs);
        }
    } catch(e) {}

    // 4. Render the Full Paper Layout
    container.innerHTML = `
        <div class="builder-paper mx-auto p-4" style="background: white; max-width: 850px; font-family: 'Times New Roman', serif;">
            
            <div class="text-center mb-4">
                <h5 class="fw-bold text-uppercase mb-1" style="color: #000; letter-spacing: 1px;">ICT PREVENTIVE MAINTENANCE</h5>
                <p class="text-uppercase fw-bold m-0" style="font-size: 14px; color: #444;">Procedure Checklist</p>
            </div>

            <div class="row g-3 mb-4 small text-uppercase fw-bold" style="color: #000;">
                <div class="col-6">
                    <div class="d-flex align-items-end mb-2">
                        <span style="width: 130px;">Division/Unit:</span>
                        <div class="flex-grow-1 border-bottom border-dark"></div>
                    </div>
                    <div class="d-flex align-items-end mb-2">
                        <span style="width: 130px;">Employee Name:</span>
                        <div class="flex-grow-1 border-bottom border-dark"></div>
                    </div>
                    <div class="d-flex align-items-end">
                        <span style="width: 130px;">Designation:</span>
                        <div class="flex-grow-1 border-bottom border-dark"></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-end mb-2">
                        <span style="width: 140px;">Date:</span>
                        <div class="flex-grow-1 border-bottom border-dark"></div>
                    </div>
                    <div class="d-flex align-items-end mb-2">
                        <span style="width: 140px;">Equipment Type:</span>
                        <div class="flex-grow-1 border-bottom border-dark ps-2">${(t.targetTypeNames || []).join(', ') || formatTypeIds(t.targetTypeId)}</div>
                    </div>
                    <div class="d-flex align-items-end">
                        <span style="width: 140px;">Property No:</span>
                        <div class="flex-grow-1 border-bottom border-dark"></div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered border-dark table-sm w-100 mb-4" style="font-size: 13px; color: #000;">
                <thead style="background-color: #f0f0f0;">
                    <tr class="text-center align-middle">
                        <th class="py-2">Maintenance Procedure</th>
                        <th class="py-2">Description</th>
                        <th>Yes</th>
                        <th>No</th>
                        <th>N/A</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>

            <div class="border border-dark p-2 mb-4" style="min-height: 80px;">
                <strong class="d-block small text-uppercase mb-2">Remarks / Recommendations:</strong>
                <div class="border-bottom border-dark border-secondary mt-4 mx-2"></div>
                <div class="border-bottom border-dark border-secondary mt-4 mx-2 mb-2"></div>
            </div>

            <div class="row pt-4">
                <div class="col-4">
                    <small class="d-block  mb-4">Prepared/Conducted by:</small>
                    <div class="text-center">
                        <div class="fw-bold text-uppercase">[Technician Name]</div>
                        <div class="border-top border-dark mt-1 pt-1 mx-2"></div>
                        <small class="text-uppercase" style="font-size: 11px;">ICT Staff</small>
                    </div>
                </div>
                <div class="col-4">
                    <small class="d-block  mb-4">Checked by:</small>
                    <div class="text-center">
                        <div class="fw-bold text-uppercase text-primary">${sigs.verifiedByName || '&nbsp;'}</div>
                        <div class="border-top border-dark mt-1 pt-1 mx-2"></div>
                        <small class="text-uppercase text-primary" style="font-size: 11px;">${sigs.verifiedByTitle}</small>
                    </div>
                </div>
                <div class="col-4">
                    <small class="d-block  mb-4">Noted by:</small>
                    <div class="text-center">
                        <div class="fw-bold text-uppercase text-primary">${sigs.notedByName || '&nbsp;'}</div>
                        <div class="border-top border-dark mt-1 pt-1 mx-2"></div>
                        <small class="text-uppercase text-primary" style="font-size: 11px;">${sigs.notedByTitle}</small>
                    </div>
                </div>
            </div>

        </div>
    `;
}

// 4. SAVE (Handles both Create and Update)
function saveTemplate() {
    // ... (Get Elements logic same as before) ...
    var titleEl = document.querySelector('.paper-title');
    var freqSelect = document.getElementById('globalFreqSelect');

    // Collect selected equipment types from multi-select checkboxes
    var selectedTypes = [];
    document.querySelectorAll('#typeMultiSelectMenu input[type="checkbox"]:checked').forEach(function(cb) {
        selectedTypes.push(cb.value);
    });

    if (selectedTypes.length === 0) {
        alert('Please select at least one equipment type.');
        return;
    }

    var templateData = {
        id: currentTemplateId,
        title: titleEl.innerText.trim(),
        equipmentType: selectedTypes.join(','),
        frequency: freqSelect.value,
        categories: [],
        signatories: {
            // DIRECTLY SCRAPE THE TEXT
            verifiedByName: document.getElementById('sigVerifiedName').innerText.trim(),
            verifiedByTitle: document.getElementById('sigVerifiedTitle').innerText.trim(),
            notedByName: document.getElementById('sigNotedName').innerText.trim(),
            notedByTitle: document.getElementById('sigNotedTitle').innerText.trim()
        }
    };

    // Scrape Categories (Same as before)
    document.querySelectorAll('.paper-section').forEach((section, index) => {
        var headerSpan = section.querySelector('.section-header span');
        var category = {
            order: index + 1,
            title: headerSpan ? headerSpan.innerText.trim() : 'Untitled',
            items: []
        };
        section.querySelectorAll('.checklist-row').forEach((row, rIndex) => {
            var itemText = row.querySelector('.flex-grow-1');
            if(itemText) category.items.push({ order: rIndex + 1, text: itemText.innerText.trim() });
        });
        templateData.categories.push(category);
    });

    // DETERMINE ACTION: Create or Update
    var action = currentTemplateId ? 'update' : 'create';

    fetch(`../ajax/manage_templates.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(templateData)
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert("Saved successfully!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    });
}
// 2. SWITCH TO EDIT MODE
function editTemplate(id) {
    // If called from Preview Modal, close it first
    var viewModalEl = document.getElementById('templateViewModal');
    var viewModal = bootstrap.Modal.getInstance(viewModalEl);
    if (viewModal) viewModal.hide();

    // Open Builder
    var builderModal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
    builderModal.show();

    // Show Loading state in builder
    document.querySelector('.paper-title').innerText = "Loading...";
    document.getElementById('checklistCategories').innerHTML = "";

    // Fetch Data
    fetch(`../ajax/manage_templates.php?action=get&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success) { alert(data.message); return; }
            
            var t = data.data;
            currentTemplateId = t.templateId; // SET GLOBAL ID

            // A. Populate Header
            document.querySelector('.paper-title').innerText = t.templateName;
            setSelectedEquipmentTypes(t.targetTypeId);
            document.getElementById('globalFreqSelect').value = t.frequency;

            // B. Populate Signatories
            try {
                var sigs = JSON.parse(t.signatories_json);
                if(sigs) {
                    document.getElementById('sigVerifiedName').innerText = sigs.verifiedByName || "[Select Supervisor Name]";
                    document.getElementById('sigVerifiedTitle').innerText = sigs.verifiedByTitle || "Division / Section Head";
                    document.getElementById('sigNotedName').innerText = sigs.notedByName || "[Select Head of Office]";
                    document.getElementById('sigNotedTitle').innerText = sigs.notedByTitle || "Head of Office";
                }
            } catch(e) { console.error("Sig Parse Error", e); }

            // C. Re-Draw Categories & Items
            var container = document.getElementById('checklistCategories');
            container.innerHTML = ""; // Clear again to be safe

            // Parse structure (it comes as string from DB)
            var structure = JSON.parse(t.structure_json);
            
            if(structure.categories) {
                structure.categories.forEach(cat => {
                    // Create Section HTML
                    var sectionId = 'sec_' + Date.now() + Math.random(); 
                    var itemsHtml = '';
                    
                    cat.items.forEach(item => {
                        itemsHtml += `
                            <div class="checklist-row">
                                <div class="flex-grow-1" contenteditable="true">${item.text}</div>
                                <div class="ms-3  small fst-italic">[Yes / No / NA]</div>
                                <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                            </div>`;
                    });

                    var sectionHtml = `
                        <div class="paper-section">
                            <div class="section-header">
                                <span contenteditable="true">${cat.title}</span>
                                <div class="builder-controls">
                                    <button class="btn btn-xs btn-link text-dark p-0 me-2" onclick="addItem(this)"><i class="fas fa-plus"></i> Add Item</button>
                                    <button class="btn btn-xs btn-link text-danger p-0" onclick="removeSection(this)"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="checklist-items">${itemsHtml}</div>
                        </div>`;
                    
                    container.insertAdjacentHTML('beforeend', sectionHtml);
                });
            }
        });
}

function OpenDropdown(btn) {
    var dropdown = new bootstrap.Dropdown(btn);
    dropdown.show();
}

function deleteTemplate(id) {
    if (confirm("Are you sure you want to delete this template? This action cannot be undone.")) {
        fetch(`${BASE_URL}ajax/manage_templates.php?action=delete&id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert("Template deleted successfully.");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            });
    }
}

// ========================================
// MULTI-SELECT EQUIPMENT TYPES
// ========================================
var equipmentTypeCache = []; // [{typeId, typeName}, ...]

function loadBuilderEquipmentTypes() {
    var menu = document.getElementById('typeMultiSelectMenu');
    if (!menu) return;

    fetch(`${BASE_URL}ajax/get_equipment_types.php`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                equipmentTypeCache = res.data;
                renderTypeCheckboxes();
            } else {
                menu.innerHTML = '<div class="text-danger small p-2">Error loading types</div>';
            }
        })
        .catch(err => {
            console.error(err);
            menu.innerHTML = '<div class="text-danger small p-2">Connection Error</div>';
        });
}

function renderTypeCheckboxes(selectedIds) {
    var menu = document.getElementById('typeMultiSelectMenu');
    if (!menu) return;

    var selected = [];
    if (selectedIds) {
        selected = String(selectedIds).split(',').map(s => s.trim());
    }

    var html = '';
    equipmentTypeCache.forEach(function(type) {
        var checked = selected.includes(String(type.typeId)) ? 'checked' : '';
        html += `
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" value="${type.typeId}" id="typeChk_${type.typeId}" ${checked} onchange="updateTypeLabel()">
                <label class="form-check-label small" for="typeChk_${type.typeId}">${type.typeName}</label>
            </div>`;
    });

    html += `<hr class="my-1"><div class="d-flex gap-1">
        <button class="btn btn-outline-primary btn-sm flex-fill py-0" style="font-size:11px;" onclick="selectAllTypes(true)">All</button>
        <button class="btn btn-outline-secondary btn-sm flex-fill py-0" style="font-size:11px;" onclick="selectAllTypes(false)">None</button>
    </div>`;

    menu.innerHTML = html;
    updateTypeLabel();
}

function selectAllTypes(selectAll) {
    document.querySelectorAll('#typeMultiSelectMenu input[type="checkbox"]').forEach(function(cb) {
        cb.checked = selectAll;
    });
    updateTypeLabel();
}

function updateTypeLabel() {
    var label = document.getElementById('typeMultiSelectLabel');
    var checked = document.querySelectorAll('#typeMultiSelectMenu input[type="checkbox"]:checked');
    if (checked.length === 0) {
        label.textContent = 'Select Types...';
    } else if (checked.length === equipmentTypeCache.length) {
        label.textContent = 'All Equipment Types';
    } else if (checked.length <= 2) {
        var names = [];
        checked.forEach(function(cb) {
            var t = equipmentTypeCache.find(x => String(x.typeId) === cb.value);
            if (t) names.push(t.typeName);
        });
        label.textContent = names.join(', ');
    } else {
        label.textContent = checked.length + ' types selected';
    }
}

function setSelectedEquipmentTypes(typeIdStr) {
    var ids = String(typeIdStr || '').split(',').map(s => s.trim());
    document.querySelectorAll('#typeMultiSelectMenu input[type="checkbox"]').forEach(function(cb) {
        cb.checked = ids.includes(cb.value);
    });
    updateTypeLabel();
}

function getSelectedEquipmentTypes() {
    var ids = [];
    document.querySelectorAll('#typeMultiSelectMenu input[type="checkbox"]:checked').forEach(function(cb) {
        ids.push(cb.value);
    });
    return ids.join(',');
}

/** Resolve comma-separated type IDs to display names using the cache */
function formatTypeIds(typeIdStr) {
    var ids = String(typeIdStr || '').split(',').map(s => s.trim());
    var names = ids.map(function(id) {
        var t = equipmentTypeCache.find(x => String(x.typeId) === id);
        return t ? t.typeName : id;
    });
    return names.join(', ');
}

loadBuilderEquipmentTypes();