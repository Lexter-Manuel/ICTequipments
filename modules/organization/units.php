<?php
// modules/organization/units.php
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Fetch all units (location_type_id = 3) with parent information
$stmt = $db->query("
    SELECT 
        u.location_id as unitId,
        u.location_name as unitName,
        u.parent_location_id as parentId,
        u.created_at as createdAt,
        p.location_name as parentName,
        p.location_type_id as parentTypeId,
        p.parent_location_id as grandparentId,
        gp.location_name as grandparentName,
        gp.location_type_id as grandparentTypeId,
        COUNT(e.employeeId) as employee_count
    FROM location u
    LEFT JOIN location p ON u.parent_location_id = p.location_id
    LEFT JOIN location gp ON p.parent_location_id = gp.location_id
    LEFT JOIN tbl_employee e ON u.location_id = e.sectionId
    WHERE u.location_type_id = 3 AND u.is_deleted = '0'
    GROUP BY u.location_id
    ORDER BY 
        CASE 
            WHEN p.location_type_id = 1 THEN p.location_name
            WHEN gp.location_type_id = 1 THEN gp.location_name
            ELSE p.location_name
        END ASC,
        p.location_name ASC,
        u.location_name ASC
");
$units = $stmt->fetchAll();

// Organize units by their hierarchy
$unitsByHierarchy = [];
foreach ($units as $unit) {
    // Determine the division and section/parent
    if ($unit['parentTypeId'] == 1) {
        // Unit directly under Division
        $divisionName = $unit['parentName'];
        $sectionName = 'Direct Units';
        $divisionId = $unit['parentId'];
    } elseif ($unit['parentTypeId'] == 2) {
        // Unit under Section
        $divisionName = $unit['grandparentName'] ?: 'Unassigned';
        $sectionName = $unit['parentName'];
        $divisionId = $unit['grandparentId'];
    } else {
        // Orphaned unit
        $divisionName = 'Unassigned';
        $sectionName = $unit['parentName'] ?: 'Direct Units';
        $divisionId = null;
    }
    
    if (!isset($unitsByHierarchy[$divisionName])) {
        $unitsByHierarchy[$divisionName] = [
            'divisionId' => $divisionId,
            'sections' => []
        ];
    }
    
    if (!isset($unitsByHierarchy[$divisionName]['sections'][$sectionName])) {
        $unitsByHierarchy[$divisionName]['sections'][$sectionName] = [
            'parentId' => $unit['parentId'],
            'parentType' => $unit['parentTypeId'],
            'units' => []
        ];
    }
    
    $unitsByHierarchy[$divisionName]['sections'][$sectionName]['units'][] = $unit;
}

// Fetch divisions for dropdown
$divisions = $db->query("
    SELECT location_id as divisionId, location_name as divisionName 
    FROM location 
    WHERE location_type_id = 1 AND is_deleted = '0'
    ORDER BY location_name ASC
")->fetchAll();

// Fetch all sections for dropdown
$sections = $db->query("
    SELECT 
        s.location_id as sectionId, 
        s.location_name as sectionName,
        s.parent_location_id as divisionId,
        d.location_name as divisionName
    FROM location s
    LEFT JOIN location d ON s.parent_location_id = d.location_id
    WHERE s.location_type_id = 2 AND s.is_deleted = '0'
    ORDER BY d.location_name ASC, s.location_name ASC
")->fetchAll();
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.page-header h2 {
    font-family: 'Crimson Pro', serif;
    font-size: 28px;
    color: var(--text-dark);
    font-weight: 700;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.btn-secondary {
    background: white;
    color: var(--text-dark);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-light);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.division-group {
    margin-bottom: 40px;
}

.division-group-header {
    background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
    color: white;
    padding: 20px 24px;
    border-radius: 12px 12px 0 0;
    margin-bottom: 0;
}

.division-group-header h3 {
    font-family: 'Crimson Pro', serif;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
}

.section-subgroup {
    background: white;
    border-left: 1px solid var(--border-color);
    border-right: 1px solid var(--border-color);
}

.section-subgroup:last-child {
    border-radius: 0 0 12px 12px;
    border-bottom: 1px solid var(--border-color);
}

.section-header {
    background: rgba(45, 122, 79, 0.05);
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    color: var(--text-dark);
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header.direct-units {
    background: rgba(45, 122, 79, 0.08);
    font-style: italic;
    color: var(--text-medium);
}

.units-container {
    background: white;
}

.unit-item {
    padding: 18px 24px 18px 48px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s;
}

.unit-item:last-child {
    border-bottom: none;
}

.unit-item:hover {
    background: var(--bg-light);
}

.unit-info {
    flex: 1;
}

.unit-badge {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(45, 122, 79, 0.1);
    color: var(--primary-green);
    border-radius: 4px;
    font-weight: 700;
    font-size: 11px;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}

.unit-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.unit-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    color: var(--text-medium);
    font-size: 12px;
}

.unit-meta i {
    color: var(--primary-green);
}

.unit-actions {
    display: flex;
    gap: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Work Sans', sans-serif;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(45, 122, 79, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.alert {
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    border: 2px dashed var(--border-color);
}

.empty-state i {
    font-size: 64px;
    color: var(--text-light);
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--text-medium);
    margin-bottom: 24px;
}

.filter-container {
    margin-bottom: 24px;
    display: flex;
    gap: 12px;
}

.filter-container select {
    padding: 10px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    min-width: 200px;
}

.parent-type-toggle {
    margin-bottom: 20px;
}

.parent-type-toggle label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 14px;
}

.parent-type-buttons {
    display: flex;
    gap: 12px;
}

.parent-type-btn {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    font-weight: 600;
}

.parent-type-btn:hover {
    border-color: var(--primary-green);
    background: rgba(45, 122, 79, 0.05);
}

.parent-type-btn.active {
    border-color: var(--primary-green);
    background: var(--primary-green);
    color: white;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h2>Units Management</h2>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add Unit
    </button>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<!-- Filters -->
<?php if (count($divisions) > 0): ?>
<div class="filter-container">
    <select id="divisionFilter" onchange="filterUnits()">
        <option value="all">All Divisions</option>
        <?php foreach ($divisions as $div): ?>
        <option value="<?php echo htmlspecialchars($div['divisionName']); ?>">
            <?php echo htmlspecialchars($div['divisionName']); ?>
        </option>
        <?php endforeach; ?>
    </select>
    
    <select id="parentFilter" onchange="filterUnits()">
        <option value="all">All Parents (Sections & Direct)</option>
        <option value="direct">Direct Units Only</option>
        <?php 
        $groupedSections = [];
        foreach ($sections as $sec) {
            $divName = $sec['divisionName'] ?: 'Unassigned';
            if (!isset($groupedSections[$divName])) {
                $groupedSections[$divName] = [];
            }
            $groupedSections[$divName][] = $sec;
        }
        
        foreach ($groupedSections as $divName => $secs): 
        ?>
            <optgroup label="<?php echo htmlspecialchars($divName); ?>">
                <?php foreach ($secs as $sec): ?>
                <option value="<?php echo htmlspecialchars($sec['sectionName']); ?>">
                    <?php echo htmlspecialchars($sec['sectionName']); ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<!-- Units by Division and Parent -->
<?php if (count($unitsByHierarchy) > 0): ?>
    <?php foreach ($unitsByHierarchy as $divName => $divData): ?>
    <div class="division-group" data-division="<?php echo htmlspecialchars($divName); ?>">
        <div class="division-group-header">
            <h3><?php echo htmlspecialchars($divName); ?></h3>
        </div>
        
        <?php foreach ($divData['sections'] as $parentName => $parentData): ?>
        <div class="section-subgroup" data-parent="<?php echo htmlspecialchars($parentName); ?>">
            <div class="section-header <?php echo $parentName === 'Direct Units' ? 'direct-units' : ''; ?>">
                <?php if ($parentName === 'Direct Units'): ?>
                    <i class="fas fa-link"></i> <?php echo htmlspecialchars($parentName); ?> (directly under division)
                <?php else: ?>
                    <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($parentName); ?>
                <?php endif; ?>
                <span style="opacity: 0.7; font-weight: 400; font-size: 14px;"> (<?php echo count($parentData['units']); ?> unit<?php echo count($parentData['units']) != 1 ? 's' : ''; ?>)</span>
            </div>
            <div class="units-container">
                <?php foreach ($parentData['units'] as $unit): ?>
                <div class="unit-item">
                    <div class="unit-info">
                        <div class="unit-badge">UNIT</div>
                        <div class="unit-name">
                            <?php echo htmlspecialchars($unit['unitName']); ?>
                        </div>
                        <div class="unit-meta">
                            <span><i class="fas fa-users"></i> <?php echo $unit['employee_count']; ?> Employees</span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($unit['createdAt'])); ?></span>
                        </div>
                    </div>
                    <div class="unit-actions">
                        <button class="btn btn-sm btn-secondary" onclick="editUnit(<?php echo $unit['unitId']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUnit(<?php echo $unit['unitId']; ?>, '<?php echo htmlspecialchars($unit['unitName']); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
    <i class="fas fa-cubes"></i>
    <h3>No Units Found</h3>
    <p>Get started by adding your first unit</p>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add First Unit
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="unitModalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="unitForm" onsubmit="handleSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="unitId" name="unitId">
                    <input type="hidden" id="parentId" name="parentId">
                    
                    <div class="parent-type-toggle">
                        <label>Unit Location *</label>
                        <div class="parent-type-buttons">
                            <div class="parent-type-btn active" onclick="setParentType('division')" id="divisionTypeBtn">
                                <i class="fas fa-building"></i> Under Division
                            </div>
                            <div class="parent-type-btn" onclick="setParentType('section')" id="sectionTypeBtn">
                                <i class="fas fa-layer-group"></i> Under Section
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="divisionSelect">Division *</label>
                        <select class="form-control" id="divisionSelect" required onchange="handleDivisionChange()">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $div): ?>
                            <option value="<?php echo $div['divisionId']; ?>">
                                <?php echo htmlspecialchars($div['divisionName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="sectionGroup" style="display: none;">
                        <label for="sectionSelect">Section *</label>
                        <select class="form-control" id="sectionSelect">
                            <option value="">Select Division First</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="unitName">Unit Name *</label>
                        <textarea class="form-control" id="unitName" name="unitName" required maxlength="255" placeholder="e.g., ICT Unit, Property Unit"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="background-color: var(--primary-green); border-color: var(--primary-green);">
                        <i class="fas fa-save"></i>
                        Save Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editMode = false;
let currentParentType = 'division'; // 'division' or 'section'
const sectionsData = <?php echo json_encode($sections); ?>;

function openAddModal() {
    editMode = false;
    currentParentType = 'division';
    document.getElementById('unitModalTitle').textContent = 'Add Unit';
    document.getElementById('unitForm').reset();
    document.getElementById('unitId').value = '';
    document.getElementById('parentId').value = '';
    document.getElementById('sectionSelect').innerHTML = '<option value="">Select Division First</option>';
    document.getElementById('sectionGroup').style.display = 'none';
    document.getElementById('divisionTypeBtn').classList.add('active');
    document.getElementById('sectionTypeBtn').classList.remove('active');
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Unit';
    const modal = new bootstrap.Modal(document.getElementById('unitModal'));
    modal.show();
}

function setParentType(type) {
    currentParentType = type;
    
    if (type === 'division') {
        document.getElementById('divisionTypeBtn').classList.add('active');
        document.getElementById('sectionTypeBtn').classList.remove('active');
        document.getElementById('sectionGroup').style.display = 'none';
        document.getElementById('sectionSelect').removeAttribute('required');
    } else {
        document.getElementById('divisionTypeBtn').classList.remove('active');
        document.getElementById('sectionTypeBtn').classList.add('active');
        document.getElementById('sectionGroup').style.display = 'block';
        document.getElementById('sectionSelect').setAttribute('required', 'required');
        handleDivisionChange(); // Load sections for selected division
    }
}

function handleDivisionChange() {
    const divisionId = document.getElementById('divisionSelect').value;
    const sectionSelect = document.getElementById('sectionSelect');
    
    if (currentParentType === 'division') {
        // Set parentId to division
        document.getElementById('parentId').value = divisionId;
    } else {
        // Load sections for the division
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        
        if (divisionId) {
            const filteredSections = sectionsData.filter(s => s.divisionId == divisionId);
            
            if (filteredSections.length > 0) {
                filteredSections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.sectionId;
                    option.textContent = section.sectionName;
                    sectionSelect.appendChild(option);
                });
            } else {
                sectionSelect.innerHTML = '<option value="">No sections available</option>';
            }
        }
    }
}

async function editUnit(id) {
    editMode = true;
    document.getElementById('unitModalTitle').textContent = 'Edit Unit';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Unit';
    
    try {
        const response = await fetch(`../../ajax/get_unit.php?id=${id}`);
        const unit = await response.json();
        
        if (unit.success) {
            document.getElementById('unitId').value = unit.data.unitId;
            document.getElementById('unitName').value = unit.data.unitName;
            
            // Determine if unit is under division or section
            if (unit.data.parentTypeId == 1) {
                // Unit under Division
                setParentType('division');
                document.getElementById('divisionSelect').value = unit.data.parentId;
                document.getElementById('parentId').value = unit.data.parentId;
            } else if (unit.data.parentTypeId == 2) {
                // Unit under Section
                setParentType('section');
                document.getElementById('divisionSelect').value = unit.data.divisionId;
                handleDivisionChange();
                setTimeout(() => {
                    document.getElementById('sectionSelect').value = unit.data.parentId;
                    document.getElementById('parentId').value = unit.data.parentId;
                }, 100);
            }
            
            const modal = new bootstrap.Modal(document.getElementById('unitModal'));
            modal.show();
        } else {
            showAlert('Error loading unit data', 'error');
        }
    } catch (error) {
        showAlert('Failed to load unit data', 'error');
    }
}

async function deleteUnit(id, name) {
    if (!confirm(`Are you sure you want to delete unit "${name}"?\n\nThis will affect all employees assigned to this unit.`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('unitId', id);
        
        const response = await fetch('../../ajax/manage_unit.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => reloadCurrentPage(), 1500);
        } else {
            showAlert(result.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete unit', 'error');
    }
}

async function handleSubmit(event) {
    event.preventDefault();
    
    // Set the correct parentId based on parent type
    if (currentParentType === 'division') {
        document.getElementById('parentId').value = document.getElementById('divisionSelect').value;
    } else {
        document.getElementById('parentId').value = document.getElementById('sectionSelect').value;
    }
    
    const formData = new FormData(event.target);
    formData.append('action', editMode ? 'update' : 'create');
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const response = await fetch('../../ajax/manage_unit.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('unitModal')).hide();
            setTimeout(() => reloadCurrentPage(), 1500);
        } else {
            showAlert(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Unit' : '<i class="fas fa-save"></i> Save Unit';
        }
    } catch (error) {
        showAlert('Failed to save unit', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Unit' : '<i class="fas fa-save"></i> Save Unit';
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass}">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}

function filterUnits() {
    const divisionFilter = document.getElementById('divisionFilter').value;
    const parentFilter = document.getElementById('parentFilter').value;
    
    const divisionGroups = document.querySelectorAll('.division-group');
    
    divisionGroups.forEach(group => {
        const divName = group.dataset.division;
        const parentSubgroups = group.querySelectorAll('.section-subgroup');
        let hasVisibleParents = false;
        
        parentSubgroups.forEach(subgroup => {
            const parentName = subgroup.dataset.parent;
            const matchesDivision = divisionFilter === 'all' || divName === divisionFilter;
            
            let matchesParent = false;
            if (parentFilter === 'all') {
                matchesParent = true;
            } else if (parentFilter === 'direct') {
                matchesParent = parentName === 'Direct Units';
            } else {
                matchesParent = parentName === parentFilter;
            }
            
            if (matchesDivision && matchesParent) {
                subgroup.style.display = 'block';
                hasVisibleParents = true;
            } else {
                subgroup.style.display = 'none';
            }
        });
        
        group.style.display = hasVisibleParents ? 'block' : 'none';
    });
}
</script>