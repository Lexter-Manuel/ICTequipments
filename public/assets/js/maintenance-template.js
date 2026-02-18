// 1. Force Load Logic (Fixes the infinite loading spinner)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadTemplates);
} else {
    loadTemplates(); // Run immediately if already loaded
}

// 2. Main Loader Function
function loadTemplates() {
    const container = document.getElementById('templatesContainer');
    
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
                        <div class="mb-3 text-muted" style="opacity: 0.5;">
                            <i class="fas fa-folder-open fa-3x"></i>
                        </div>
                        <h5 class="text-muted">No templates found</h5>
                        <p class="small text-muted">Create a new template to get started.</p>
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
    // Determine Icon & Color based on Type
    let iconClass = 'fa-cogs';
    let bgClass = 'bg-primary-xlight text-primary';
    const type = (t.targetTypeId || '').toLowerCase();

    if (type.includes('system')) { iconClass = 'fa-desktop'; }
    else if (type.includes('printer')) { iconClass = 'fa-print'; bgClass = 'bg-secondary-xlight text-secondary'; }
    else if (type.includes('laptop')) { iconClass = 'fa-laptop'; }
    else if (type.includes('monitor')) { iconClass = 'fa-tv'; }

    // Count Items (Use count from DB if available, else 0)
    let itemCount = t.item_count || 0;

    // Format Date
    const dateCreated = t.createdAt ? new Date(t.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : 'N/A';

    return `
    <div class="col-md-6 col-xl-4">
        <div class="card template-card h-100 border-0 shadow-sm position-relative" onclick="viewTemplate(${t.templateId})">
            <span class="template-badge badge bg-success">Active</span>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="template-icon-box ${bgClass}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="viewTemplate(${t.templateId})"><i class="fas fa-eye me-2"></i> Edit</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate(${t.templateId})"><i class="fas fa-trash me-2"></i> Delete</a></li>
                        </ul>
                    </div>
                </div>
                <h5 class="fw-bold mb-1">${t.templateName}</h5>
                <p class="text-muted small mb-3">Target: ${t.targetTypeId ? t.targetTypeId.replace('_', ' ').toUpperCase() : 'N/A'}</p>
                
                <div class="template-stats">
                    <div class="template-stat">
                        <div class="small text-muted">Frequency</div>
                        <div class="fw-bold" style="color: var(--primary-green);">${t.frequency}</div>
                    </div>
                    <div class="template-stat text-end">
                        <div class="small text-muted">Questions</div>
                        <div class="fw-bold" style="color: var(--primary-green);">${itemCount} items</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light border-top">
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-muted"><i class="fas fa-list-check me-1"></i> ${itemCount} items</span>
                    <span class="text-muted">Created ${dateCreated}</span>
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
    
    const modal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
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
    const container = document.getElementById('checklistCategories');
    const newSection = document.createElement('div');
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
                <div class="ms-3 text-muted small fst-italic">[Pass / Fail / N/A]</div>
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
        <div class="ms-3 text-muted small fst-italic">[Pass / Fail / N/A]</div>
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
    const nameEl = currentSignatoryContainer.querySelector('[id*="Name"]');
    const titleEl = currentSignatoryContainer.querySelector('[id*="Title"]');
    
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
    const newName = document.getElementById('inputSigName').value.trim();
    const newTitle = document.getElementById('inputSigTitle').value.trim();

    // Update the DOM elements in the builder
    const nameEl = currentSignatoryContainer.querySelector('[id*="Name"]');
    const titleEl = currentSignatoryContainer.querySelector('[id*="Title"]');

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
    const modalEl = document.getElementById('templateViewModal');
    const modalBody = document.getElementById('viewModalBody');
    
    // Initialize Modal if needed
    if (!previewModalInstance) {
        previewModalInstance = new bootstrap.Modal(modalEl);
    }
    
    // Show Loading State
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading template preview...</p>
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
    let structure = {};
    try {
        structure = JSON.parse(t.structure_json);
    } catch(e) { console.error("JSON Parse Error", e); }

    // 2. Build the Main Table Rows
    let tableRows = '';
    
    if(structure.categories) {
        structure.categories.forEach((cat) => {
            const itemCount = cat.items.length;
            
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
    let sigs = { verifiedByTitle: 'Division/Section Head', notedByTitle: 'Head of Office', verifiedByName: '', notedByName: '' };
    try {
        if(t.signatories_json) {
            const parsedSigs = JSON.parse(t.signatories_json);
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
                        <div class="flex-grow-1 border-bottom border-dark ps-2">${t.targetTypeId.replace('_',' ')}</div>
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
                    <small class="d-block text-muted mb-4">Prepared/Conducted by:</small>
                    <div class="text-center">
                        <div class="fw-bold text-uppercase">[Technician Name]</div>
                        <div class="border-top border-dark mt-1 pt-1 mx-2"></div>
                        <small class="text-uppercase" style="font-size: 11px;">ICT Staff</small>
                    </div>
                </div>
                <div class="col-4">
                    <small class="d-block text-muted mb-4">Checked by:</small>
                    <div class="text-center">
                        <div class="fw-bold text-uppercase text-primary">${sigs.verifiedByName || '&nbsp;'}</div>
                        <div class="border-top border-dark mt-1 pt-1 mx-2"></div>
                        <small class="text-uppercase text-primary" style="font-size: 11px;">${sigs.verifiedByTitle}</small>
                    </div>
                </div>
                <div class="col-4">
                    <small class="d-block text-muted mb-4">Noted by:</small>
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
    const titleEl = document.querySelector('.paper-title');
    const typeSelect = document.getElementById('globalTypeSelect');
    const freqSelect = document.getElementById('globalFreqSelect');

    let templateData = {
        id: currentTemplateId,
        title: titleEl.innerText.trim(),
        equipmentType: typeSelect.value,
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
        let headerSpan = section.querySelector('.section-header span');
        let category = {
            order: index + 1,
            title: headerSpan ? headerSpan.innerText.trim() : 'Untitled',
            items: []
        };
        section.querySelectorAll('.checklist-row').forEach((row, rIndex) => {
            let itemText = row.querySelector('.flex-grow-1');
            if(itemText) category.items.push({ order: rIndex + 1, text: itemText.innerText.trim() });
        });
        templateData.categories.push(category);
    });

    // DETERMINE ACTION: Create or Update
    const action = currentTemplateId ? 'update' : 'create';

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
    const viewModalEl = document.getElementById('templateViewModal');
    const viewModal = bootstrap.Modal.getInstance(viewModalEl);
    if (viewModal) viewModal.hide();

    // Open Builder
    const builderModal = new bootstrap.Modal(document.getElementById('templateBuilderModal'));
    builderModal.show();

    // Show Loading state in builder
    document.querySelector('.paper-title').innerText = "Loading...";
    document.getElementById('checklistCategories').innerHTML = "";

    // Fetch Data
    fetch(`../ajax/manage_templates.php?action=get&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success) { alert(data.message); return; }
            
            const t = data.data;
            currentTemplateId = t.templateId; // SET GLOBAL ID

            // A. Populate Header
            document.querySelector('.paper-title').innerText = t.templateName;
            document.getElementById('globalTypeSelect').value = t.targetTypeId;
            document.getElementById('globalFreqSelect').value = t.frequency;

            // B. Populate Signatories
            try {
                const sigs = JSON.parse(t.signatories_json);
                if(sigs) {
                    document.getElementById('sigVerifiedName').innerText = sigs.verifiedByName || "[Select Supervisor Name]";
                    document.getElementById('sigVerifiedTitle').innerText = sigs.verifiedByTitle || "Division / Section Head";
                    document.getElementById('sigNotedName').innerText = sigs.notedByName || "[Select Head of Office]";
                    document.getElementById('sigNotedTitle').innerText = sigs.notedByTitle || "Head of Office";
                }
            } catch(e) { console.error("Sig Parse Error", e); }

            // C. Re-Draw Categories & Items
            const container = document.getElementById('checklistCategories');
            container.innerHTML = ""; // Clear again to be safe

            // Parse structure (it comes as string from DB)
            const structure = JSON.parse(t.structure_json);
            
            if(structure.categories) {
                structure.categories.forEach(cat => {
                    // Create Section HTML
                    const sectionId = 'sec_' + Date.now() + Math.random(); 
                    let itemsHtml = '';
                    
                    cat.items.forEach(item => {
                        itemsHtml += `
                            <div class="checklist-row">
                                <div class="flex-grow-1" contenteditable="true">${item.text}</div>
                                <div class="ms-3 text-muted small fst-italic">[Pass / Fail / NA]</div>
                                <button class="btn btn-link btn-sm text-danger ms-2 builder-controls" onclick="removeItem(this)">&times;</button>
                            </div>`;
                    });

                    const sectionHtml = `
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
    const dropdown = new bootstrap.Dropdown(btn);
    dropdown.addEventListener('shown.bs.dropdown', function () {
        const menu = btn.nextElementSibling;
        const rect = btn.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
    })
}