

var currentOtherId = null;
var locationManager;
var equipmentTypesCache = [];       // Cached list from registry
var typeDropdownActiveIdx = -1;     // Keyboard nav index

function filterOtherEquipment() {
    currentPage = 1;
    applyTableState();
}

function changePerPage() {
    perPage = parseInt(document.getElementById('perPageSelect').value);
    currentPage = 1;
    applyTableState();
}

function applyTableState() {
    // 1. Get filter values
    const searchTerm = document.getElementById('otherSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    // 2. Get all rows that have data attributes
    const allRows = Array.from(document.querySelectorAll('#otherTableBody tr[data-id]'));

    // 3. Filter Logic
    filteredRows = allRows.filter(row => {
        const serial = row.dataset.serial || '';
        const type = row.dataset.type || '';
        const brand = row.dataset.brand || '';
        const status = row.dataset.status || '';

        // Check if row matches search text
        const matchesSearch = serial.includes(searchTerm) || 
                              type.includes(searchTerm) || 
                              brand.includes(searchTerm);
        
        // Check if row matches status dropdown
        const matchesStatus = !statusFilter || status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    // 4. Pagination Logic
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    
    if (currentPage > totalPages) currentPage = totalPages;
    const start = (currentPage - 1) * perPage;
    const end = Math.min(start + perPage, total);

    // 5. Show/Hide Rows
    allRows.forEach(r => r.style.display = 'none'); // Hide all first
    
    filteredRows.forEach((row, idx) => {
        if (idx >= start && idx < end) {
            row.style.display = ''; // Show only current page rows
        }
    });

    // 6. Update UI Counts
    const countEl = document.getElementById('recordCount');
    if (countEl) {
        countEl.innerHTML = `Showing <strong>${total === 0 ? 0 : start + 1}–${end}</strong> of <strong>${total}</strong> records`;
    }

    renderPaginationControls('paginationControls', currentPage, totalPages, 'goToPage');
    updateRowCounters('otherTableBody', total === 0 ? 0 : start + 1);
}

function goToPage(page) {
    if (page < 1) return;
    currentPage = page;
    applyTableState();
}

// ==========================================
// CRUD OPERATIONS (AJAX)
// ==========================================

function openAddOtherEquipment() {
    currentOtherId = null;
    document.getElementById('otherForm').reset();
    document.getElementById('otherModalTitle').textContent = 'Add Equipment';
    
    if(locationManager) locationManager.reset();
    
    // Reset toggle to default
    if(document.getElementById('typeLocation')) {
        document.getElementById('typeLocation').checked = true;
        toggleAssignmentType();
    }

    // Reset type autocomplete state
    document.getElementById('typeDropdown').style.display = 'none';
    document.getElementById('typeSuggestionBanner').style.display = 'none';
    typeDropdownActiveIdx = -1;

    // Refresh type cache in case new types were added
    loadEquipmentTypesCache();

    new bootstrap.Modal(document.getElementById('otherModal')).show();
}


function editOtherEquipment(id) {
    fetch(`../ajax/manage_otherequipment.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentOtherId = id;
                var o = data.data;
                
                document.getElementById('otherModalTitle').textContent = 'Edit Equipment';
                document.getElementById('otherId').value = o.otherEquipmentId;
                document.getElementById('otherType').value = o.equipmentType;
                document.getElementById('otherBrand').value = o.brand;
                document.getElementById('otherModel').value = o.model;
                document.getElementById('otherSerial').value = o.serialNumber;
                document.getElementById('otherYear').value = o.yearAcquired;
                document.getElementById('otherStatus').value = o.status;
                document.getElementById('otherLocation').value = o.location_id || '';
                document.getElementById('otherEmployee').value = o.employeeId || '';
                document.getElementById('otherDetails').value = o.details;
                
                if (data.data.location_id) {
                    locationManager.setLocation(data.data.location_id);
                } else {
                    document.getElementById('typeLocation').checked = true;
                    toggleAssignmentType();
                    // Set hierarchy
                    setLocationHierarchy(data.data.location_id);
                }
                
                var modal = new bootstrap.Modal(document.getElementById('otherModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('Error loading equipment details: ' + error));
}

function saveOtherEquipment() {
    // 1. Basic Validation
    var typeInput = document.getElementById('otherType');
    if (!typeInput || !typeInput.value.trim()) {
        alert('Please enter equipment type');
        return;
    }

    // 2. Create FormData
    var formEl = document.getElementById('otherForm');
    var formData = new FormData(formEl);

    // 3. ACTION (Create/Update)
    formData.append('action', currentOtherId ? 'update' : 'create');
    if (currentOtherId) {
        formData.append('otherEquipmentId', currentOtherId);
    }

    // 4. MANUAL APPEND (Safety Net)
    // This ensures data is sent even if HTML 'name' attributes are missing
    if (!formData.get('type')) formData.append('type', document.getElementById('otherType').value);
    if (!formData.get('brand')) formData.append('brand', document.getElementById('otherBrand').value);
    if (!formData.get('model')) formData.append('model', document.getElementById('otherModel').value);
    if (!formData.get('serial')) formData.append('serial', document.getElementById('otherSerial').value);
    if (!formData.get('year')) formData.append('year', document.getElementById('otherYear').value);
    if (!formData.get('status')) formData.append('status', document.getElementById('otherStatus').value);
    if (!formData.get('details')) formData.append('details', document.getElementById('otherDetails').value);
    

    var locId = document.getElementById('otherLocation').value;
    var empId = document.getElementById('otherEmployee').value;
    

    if(document.getElementById('typeEmployee').checked && empId) {
         formData.set('location_id', ''); 
         formData.set('employee_id', empId);
    } else {
         formData.set('location_id', locId);
         formData.set('employee_id', ''); 
    }

    // 5. Send Request
    fetch('../ajax/manage_otherequipment.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('otherModal')).hide();
            // Refresh type cache so newly registered types appear immediately
            loadEquipmentTypesCache();
            reloadCurrentPage(); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error(error);
        alert('System Error: ' + error);
    });
}

function deleteOtherEquipment(id) {
    if (!confirm('Delete this equipment?')) return;

    let formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('../ajax/manage_otherequipment.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            reloadCurrentPage(); // Reload page to update table
        } else {
            alert(data.message);
        }
    });
}

// escapeHtml provided by shared utils.js

// ==========================================
// VIEW EQUIPMENT DETAILS
// ==========================================
function viewOtherEquipment(id) {
    var modalBody = document.getElementById('viewOtherContent');
    modalBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    var modal = new bootstrap.Modal(document.getElementById('viewOtherModal'));
    modal.show();

    fetch(`../ajax/manage_otherequipment.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var item = data.data;
                var statusBadge = getStatusBadge(item.status, item.employeeId);
                
                // Determine assignment display
                var assignmentHtml = '<span class="text-muted">Unassigned</span>';
                if (item.employeeId) {
                    assignmentHtml = `<i class="fas fa-user text-primary"></i> <strong>${escapeHtml(item.employeeName || 'Unknown Employee')}</strong>`;
                } else if (item.location_name) {
                    assignmentHtml = `<i class="fas fa-map-marker-alt text-danger"></i> <strong>${escapeHtml(item.location_name)}</strong>`;
                }

                var html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Equipment Type</h6>
                            <p class="fw-bold mb-0" style="font-size:16px">${escapeHtml(item.equipmentType)}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted text-uppercase mb-1" style="font-size:11px">Serial Number</h6>
                            <p class="fw-bold mb-0 text-primary" style="font-family:monospace; font-size:16px">${escapeHtml(item.serialNumber)}</p>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Brand & Model</label>
                            <div class="fw-bold">${escapeHtml(item.brand)} ${escapeHtml(item.model)}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Current Status</label>
                            <div>${statusBadge}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="small text-muted">Current Assignment</label>
                            <div class="p-2 bg-light border rounded">${assignmentHtml}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Year Acquired</label>
                            <div>${escapeHtml(item.yearAcquired)}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="small text-muted">Details / Specs</label>
                            <div class="p-2 border rounded bg-white small" style="min-height:60px">
                                ${item.details ? escapeHtml(item.details) : '<span class="text-muted font-italic">No details provided</span>'}
                            </div>
                        </div>
                    </div>
                `;
                modalBody.innerHTML = html;
                
                // Set the Edit button in the View modal to point to this item
                var editBtn = document.querySelector('#viewOtherModal .btn-primary');
                if(editBtn) editBtn.setAttribute('onclick', `editOtherEquipment(${item.otherEquipmentId}); bootstrap.Modal.getInstance(document.getElementById('viewOtherModal')).hide();`);
                
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error}</div>`;
        });
}

function getStatusBadge(status, hasEmployee) {
    var displayStatus = hasEmployee ? 'In Use' : status;
    var cls = 'secondary';
    
    switch(displayStatus) {
        case 'Available': cls = 'success'; break;
        case 'In Use': cls = 'primary'; break;
        case 'Under Maintenance': cls = 'warning'; break;
        case 'Disposed': cls = 'danger'; break;
    }
    return `<span class="badge bg-${cls}">${displayStatus}</span>`;
}

var divSelect = document.getElementById('locDivision');

if (divSelect) {
    loadDivisions();

    if (typeof LocationManager !== 'undefined') {
        locationManager = new LocationManager({
            divisionId: 'locDivision',
            sectionId: 'locSection',
            unitId: 'locUnit',
            locationIdInput: 'otherLocation'
        });
    } else {
        console.error('LocationManager not found. Ensure location_manager.js is included.');
    }

    divSelect.addEventListener('change', calculateFinalLocation);
    document.getElementById('locSection').addEventListener('change', calculateFinalLocation);
    document.getElementById('locUnit').addEventListener('change', calculateFinalLocation);
}

// Toggle between Employee and Location inputs
function toggleAssignmentType() {
    var isEmployee = document.getElementById('typeEmployee').checked;
    
    document.getElementById('locationContainer').style.display = isEmployee ? 'none' : 'block';
    document.getElementById('employeeContainer').style.display = isEmployee ? 'block' : 'none';
    
    if (isEmployee) {
        // If employee, clear location ID
        document.getElementById('otherLocation').value = '';
    } else {
        // If location, clear employee and recalculate location
        document.getElementById('otherEmployee').value = '';
        calculateFinalLocation();
    }
}

// 1. Load Divisions (Root Locations)
function loadDivisions() {
    fetch('../ajax/get_locations.php?type=1') // Type 1 = Division
        .then(res => res.json())
        .then(data => {
            var html = '<option value="">Select Division</option>';
            data.forEach(d => html += `<option value="${d.location_id}">${d.location_name}</option>`);
            document.getElementById('locDivision').innerHTML = html;
        });
}

// 2. Load Children (Sections AND Direct Units)
function loadLocationChildren(divisionId) {
    var sectionSelect = document.getElementById('locSection');
    var unitSelect = document.getElementById('locUnit');
    
    // Reset
    sectionSelect.innerHTML = '<option value="">--</option>';
    sectionSelect.disabled = true;
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;
    
    if (!divisionId) return;

    // Fetch all children of this division
    fetch(`../ajax/get_locations.php?parent=${divisionId}`)
        .then(res => res.json())
        .then(data => {
            // Separate Sections (Type 2) and Units (Type 3)
            var sections = data.filter(x => x.location_type_id == 2);
            var units = data.filter(x => x.location_type_id == 3);

            // Populate Sections
            if (sections.length > 0) {
                var html = '<option value="">Select Section</option>';
                sections.forEach(s => html += `<option value="${s.location_id}">${s.location_name}</option>`);
                sectionSelect.innerHTML = html;
                sectionSelect.disabled = false;
            }

            // Populate Direct Units (Units connected directly to Division)
            if (units.length > 0) {
                var html = '<option value="">Select Unit</option>';
                units.forEach(u => html += `<option value="${u.location_id}">${u.location_name}</option>`);
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
}

// 3. Load Units for a specific Section
function loadSectionUnits(sectionId) {
    var unitSelect = document.getElementById('locUnit');
    unitSelect.innerHTML = '<option value="">--</option>';
    unitSelect.disabled = true;

    if (!sectionId) {
        // If section deselected, reload direct units from division
        var divId = document.getElementById('locDivision').value;
        loadLocationChildren(divId); 
        return;
    }

    fetch(`../ajax/get_locations.php?parent=${sectionId}&type=3`) // Type 3 = Unit
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                var html = '<option value="">Select Unit</option>';
                data.forEach(u => html += `<option value="${u.location_id}">${u.location_name}</option>`);
                unitSelect.innerHTML = html;
                unitSelect.disabled = false;
            }
        });
}

// 4. Calculate Final Location ID (Prioritize Unit -> Section -> Division)
function calculateFinalLocation() {
    var divId = document.getElementById('locDivision').value;
    var secId = document.getElementById('locSection').value;
    var unitId = document.getElementById('locUnit').value;

    var final = '';
    if (unitId) final = unitId;
    else if (secId) final = secId;
    else if (divId) final = divId;

    document.getElementById('otherLocation').value = final;
}

// Helper: Pre-fill dropdowns when Editing
function setLocationHierarchy(locationId) {
    if (!locationId) return;

    fetch(`../ajax/get_location_path.php?id=${locationId}`)
        .then(res => res.json())
        .then(path => {
            // path = { division_id: 1, section_id: 5, unit_id: 10 }
            
            if (path.division_id) {
                document.getElementById('locDivision').value = path.division_id;
                
                // Trigger load of children
                loadLocationChildren(path.division_id);
                
                // Wait for AJAX to finish (simple timeout for now, or use async/await)
                setTimeout(() => {
                    if (path.section_id) {
                        document.getElementById('locSection').value = path.section_id;
                        loadSectionUnits(path.section_id);
                        
                        setTimeout(() => {
                            if (path.unit_id) document.getElementById('locUnit').value = path.unit_id;
                            calculateFinalLocation();
                        }, 200);
                    } else if (path.unit_id) {
                        // Direct unit under division
                        document.getElementById('locUnit').value = path.unit_id;
                        calculateFinalLocation();
                    }
                }, 200);
            }
        });
}

// ==============================================
// EQUIPMENT TYPE AUTOCOMPLETE / COMBOBOX
// ==============================================

/**
 * Load all equipment types from registry (cached)
 */
function loadEquipmentTypesCache() {
    return fetch('../ajax/get_equipment_types.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                equipmentTypesCache = data.data;
            }
            return equipmentTypesCache;
        })
        .catch(() => equipmentTypesCache);
}

// Load on page init
loadEquipmentTypesCache();

/**
 * Fuzzy scoring — returns a score (lower = better match). -1 = no match.
 */
function fuzzyScore(query, target) {
    var q = query.toLowerCase();
    var t = target.toLowerCase();

    // Exact match
    if (t === q) return 0;
    // Starts with
    if (t.startsWith(q)) return 1;
    // Contains
    if (t.includes(q)) return 2;
    // Check if query words appear in target
    var words = q.split(/\s+/);
    var allFound = words.every(w => t.includes(w));
    if (allFound) return 3;
    // Partial token overlap (e.g. "cctv" matches "CCTV System")
    var tWords = t.split(/[\s()\-\/]+/);
    var anyTokenMatch = words.some(w => tWords.some(tw => tw.startsWith(w) || w.startsWith(tw)));
    if (anyTokenMatch) return 4;

    return -1; // No match
}

/**
 * Highlight matched portion in text
 */
function highlightMatch(text, query) {
    if (!query) return escapeHtml(text);
    var idx = text.toLowerCase().indexOf(query.toLowerCase());
    if (idx === -1) return escapeHtml(text);
    var before = text.substring(0, idx);
    var match = text.substring(idx, idx + query.length);
    var after = text.substring(idx + query.length);
    return escapeHtml(before) + '<span class="type-match">' + escapeHtml(match) + '</span>' + escapeHtml(after);
}

/**
 * Called on every keystroke in the Equipment Type input
 */
function onEquipmentTypeInput(input) {
    var query = input.value.trim();
    var dropdown = document.getElementById('typeDropdown');
    var banner = document.getElementById('typeSuggestionBanner');
    
    typeDropdownActiveIdx = -1;
    banner.style.display = 'none';

    if (!query) {
        // Show all types when field has text cleared
        showTypeDropdown(equipmentTypesCache, '', dropdown);
        return;
    }

    // Filter and score
    var scored = equipmentTypesCache
        .map(t => ({ ...t, score: fuzzyScore(query, t.typeName) }))
        .filter(t => t.score >= 0)
        .sort((a, b) => a.score - b.score);

    showTypeDropdown(scored, query, dropdown);

    // Check for close match suggestion (when typed value isn't exact but similar)
    var exactMatch = scored.find(t => t.typeName.toLowerCase() === query.toLowerCase());
    if (!exactMatch && scored.length > 0 && scored[0].score <= 4) {
        showSuggestionBanner(query, scored[0].typeName, banner, input);
    }
}

/**
 * Show dropdown on focus (all types)
 */
function onEquipmentTypeFocus(input) {
    var query = input.value.trim();
    var dropdown = document.getElementById('typeDropdown');

    if (equipmentTypesCache.length === 0) {
        // Reload cache if empty
        loadEquipmentTypesCache().then(() => {
            var scored = equipmentTypesCache.map(t => ({
                ...t,
                score: query ? fuzzyScore(query, t.typeName) : 5
            })).filter(t => !query || t.score >= 0).sort((a, b) => a.score - b.score);
            showTypeDropdown(scored, query, dropdown);
        });
    } else {
        var scored = equipmentTypesCache.map(t => ({
            ...t,
            score: query ? fuzzyScore(query, t.typeName) : 5
        })).filter(t => !query || t.score >= 0).sort((a, b) => a.score - b.score);
        showTypeDropdown(scored, query, dropdown);
    }
}

/**
 * Render the dropdown
 */
function showTypeDropdown(items, query, dropdown) {
    if (items.length === 0 && !query) {
        dropdown.style.display = 'none';
        return;
    }

    var html = '';

    if (items.length === 0) {
        html += '<div class="type-no-match"><i class="fas fa-info-circle"></i> No existing type found</div>';
    } else {
        items.forEach((item, idx) => {
            var safeTypeName = item.typeName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            html += `<div class="type-item" data-index="${idx}" data-typename="${item.typeName.replace(/"/g, '&quot;')}"
                          onmousedown="selectEquipmentType('${safeTypeName}')"
                          onmouseenter="typeDropdownActiveIdx=${idx};highlightDropdownItem()">
                        <i class="fas fa-tag"></i>
                        <span>${highlightMatch(item.typeName, query)}</span>
                        <span class="type-context">${item.context || ''}</span>
                     </div>`;
        });
    }

    // "Add as new type" option when query doesn't exactly match any existing entry
    if (query && !items.find(t => t.typeName.toLowerCase() === query.toLowerCase())) {
        var safeQuery = query.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        html += `<div class="type-new-label" onmousedown="selectEquipmentType('${safeQuery}')">
                    <i class="fas fa-plus-circle"></i>
                    Add "<strong>${escapeHtml(query)}</strong>" as new type
                 </div>`;
    }

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

/**
 * Select an equipment type from dropdown
 */
function selectEquipmentType(typeName) {
    var input = document.getElementById('otherType');
    input.value = typeName;
    
    document.getElementById('typeDropdown').style.display = 'none';
    document.getElementById('typeSuggestionBanner').style.display = 'none';
    typeDropdownActiveIdx = -1;
}

/**
 * Show suggestion banner for close matches
 */
function showSuggestionBanner(typed, suggested, banner, input) {
    var safeSuggested = suggested.replace(/'/g, "\\'");
    banner.innerHTML = `
        <i class="fas fa-lightbulb"></i>
        <span>
            Did you mean 
            <span class="suggestion-use" onclick="selectEquipmentType('${safeSuggested}')">${escapeHtml(suggested)}</span>?
            This type already exists in the registry.
        </span>
    `;
    banner.style.display = 'flex';
}

/**
 * Keyboard navigation for dropdown
 */
function highlightDropdownItem() {
    var items = document.querySelectorAll('#typeDropdown .type-item');
    items.forEach((el, i) => el.classList.toggle('active', i === typeDropdownActiveIdx));
}

// Keyboard handler for arrow keys / enter / escape
document.addEventListener('keydown', function(e) {
    var dropdown = document.getElementById('typeDropdown');
    if (!dropdown || dropdown.style.display === 'none') return;
    
    var input = document.getElementById('otherType');
    if (document.activeElement !== input) return;

    var items = dropdown.querySelectorAll('.type-item');
    var maxIdx = items.length - 1;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        typeDropdownActiveIdx = Math.min(typeDropdownActiveIdx + 1, maxIdx);
        highlightDropdownItem();
        if (items[typeDropdownActiveIdx]) items[typeDropdownActiveIdx].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        typeDropdownActiveIdx = Math.max(typeDropdownActiveIdx - 1, 0);
        highlightDropdownItem();
        if (items[typeDropdownActiveIdx]) items[typeDropdownActiveIdx].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && typeDropdownActiveIdx >= 0 && items[typeDropdownActiveIdx]) {
        e.preventDefault();
        var selectedType = items[typeDropdownActiveIdx].getAttribute('data-typename') 
                           || items[typeDropdownActiveIdx].textContent.trim();
        selectEquipmentType(selectedType);
    } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
        typeDropdownActiveIdx = -1;
    }
});

// Close dropdown on click outside
document.addEventListener('click', function(e) {
    var wrapper = document.querySelector('.equipment-type-wrapper');
    var dropdown = document.getElementById('typeDropdown');
    if (wrapper && dropdown && !wrapper.contains(e.target)) {
        dropdown.style.display = 'none';
        typeDropdownActiveIdx = -1;
    }
});

