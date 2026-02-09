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
    LEFT JOIN tbl_employee e ON u.location_id = e.location_id
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
        $parentIdForFilter = $unit['parentId']; // For "direct" filter
    } elseif ($unit['parentTypeId'] == 2) {
        // Unit under Section
        $divisionName = $unit['grandparentName'] ?: 'Unassigned';
        $sectionName = $unit['parentName'];
        $divisionId = $unit['grandparentId'];
        $parentIdForFilter = $unit['parentId']; // Section ID
    } else {
        // Orphaned unit (no parent)
        $divisionName = 'Unassigned';
        $sectionName = 'Direct Units';
        $divisionId = null;
        $parentIdForFilter = null;
    }
    
    if (!isset($unitsByHierarchy[$divisionName])) {
        $unitsByHierarchy[$divisionName] = [
            'divisionId' => $divisionId,
            'sections' => []
        ];
    }
    
    if (!isset($unitsByHierarchy[$divisionName]['sections'][$sectionName])) {
        $unitsByHierarchy[$divisionName]['sections'][$sectionName] = [
            'parentId' => $parentIdForFilter,
            'parentType' => $unit['parentTypeId'],
            'isDirect' => ($unit['parentTypeId'] == 1),
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
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    background: var(--bg-light);
    padding: 4px;
    border-radius: 8px;
}

.parent-type-toggle button {
    flex: 1;
    padding: 10px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--text-medium);
}

.parent-type-toggle button.active {
    background: white;
    color: var(--primary-green);
    box-shadow: 0 2px 4px var(--shadow-soft);
}

.hierarchy-info {
    background: rgba(45, 122, 79, 0.05);
    border-left: 3px solid var(--primary-green);
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    color: var(--text-medium);
}

.hierarchy-info i {
    color: var(--primary-green);
    margin-right: 8px;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h2>Units Management</h2>
    <button class="btn btn-primary" onclick="UnitsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add Unit
    </button>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<!-- Filters -->
<?php if (count($divisions) > 0): ?>
<div class="filter-container">
    <select id="divisionFilter" onchange="UnitsManager.filterUnits()">
        <option value="all">All Divisions</option>
        <?php foreach ($divisions as $div): ?>
        <option value="<?php echo $div['divisionId']; ?>">
            <?php echo htmlspecialchars($div['divisionName']); ?>
        </option>
        <?php endforeach; ?>
    </select>
    
    <select id="parentFilter" onchange="UnitsManager.filterUnits()">
        <option value="all">All Parents (Divisions & Sections)</option>
        <option value="direct">Direct Units Only</option>
        <?php foreach ($sections as $sec): ?>
        <option value="<?php echo $sec['sectionId']; ?>">
            <?php echo htmlspecialchars($sec['divisionName']); ?> > <?php echo htmlspecialchars($sec['sectionName']); ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<!-- Units by Hierarchy -->
<?php if (count($unitsByHierarchy) > 0): ?>
    <?php foreach ($unitsByHierarchy as $divName => $divData): ?>
    <div class="division-group" data-division="<?php echo htmlspecialchars($divName); ?>" data-division-id="<?php echo $divData['divisionId']; ?>">
        <div class="division-group-header">
            <h3><?php echo htmlspecialchars($divName); ?></h3>
        </div>
        
        <?php foreach ($divData['sections'] as $sectionName => $sectionData): ?>
        <div class="section-subgroup" 
             data-parent="<?php echo htmlspecialchars($sectionName); ?>" 
             data-parent-id="<?php echo $sectionData['parentId']; ?>"
             data-is-direct="<?php echo $sectionData['isDirect'] ? '1' : '0'; ?>">
            <div class="section-header <?php echo $sectionName === 'Direct Units' ? 'direct-units' : ''; ?>">
                <i class="fas fa-<?php echo $sectionName === 'Direct Units' ? 'layer-group' : 'sitemap'; ?>"></i>
                <?php echo htmlspecialchars($sectionName); ?>
                <span style="margin-left: auto; font-weight: normal; font-size: 13px;">
                    <?php echo count($sectionData['units']); ?> Unit(s)
                </span>
            </div>
            <div class="units-container">
                <?php foreach ($sectionData['units'] as $unit): ?>
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
                        <button class="btn btn-sm btn-secondary" onclick="UnitsManager.editUnit(<?php echo $unit['unitId']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="UnitsManager.deleteUnit(<?php echo $unit['unitId']; ?>, '<?php echo htmlspecialchars($unit['unitName']); ?>')">
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
    <i class="fas fa-layer-group"></i>
    <h3>No Units Found</h3>
    <p>Get started by adding your first unit</p>
    <button class="btn btn-primary" onclick="UnitsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add First Unit
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="unitModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="unitModalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="unitForm" onsubmit="UnitsManager.handleSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="unitId" name="unitId">
                    <input type="hidden" id="parentId" name="parentId">
                    
                    <div class="hierarchy-info">
                        <i class="fas fa-info-circle"></i>
                        Units can be placed directly under a <strong>Division</strong> or under a <strong>Section</strong> within a division.
                    </div>
                    
                    <div class="form-group">
                        <label>Parent Type *</label>
                        <div class="parent-type-toggle">
                            <button type="button" id="divisionTypeBtn" class="active" onclick="setParentType('division')">
                                <i class="fas fa-building"></i> Division
                            </button>
                            <button type="button" id="sectionTypeBtn" onclick="setParentType('section')">
                                <i class="fas fa-sitemap"></i> Section
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="divisionSelect">Division *</label>
                        <select class="form-control" id="divisionSelect" name="divisionSelect" required onchange="handleDivisionChange()">
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
                        <select class="form-control" id="sectionSelect" name="sectionSelect" onchange="document.getElementById('parentId').value = this.value">
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="unitName">Unit Name *</label>
                        <textarea class="form-control" id="unitName" name="unitName" required maxlength="255" placeholder="e.g., ICT Unit"></textarea>
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
// Global data arrays for form population
const divisionsData = <?php echo json_encode($divisions); ?>;
const sectionsData = <?php echo json_encode($sections); ?>;

// Helper functions for parent type selection
function setParentType(type) {
    const divisionBtn = document.getElementById('divisionTypeBtn');
    const sectionBtn = document.getElementById('sectionTypeBtn');
    const divisionSelect = document.getElementById('divisionSelect');
    const sectionGroup = document.getElementById('sectionGroup');
    
    if (type === 'division') {
        divisionBtn.classList.add('active');
        sectionBtn.classList.remove('active');
        divisionSelect.parentElement.style.display = 'block';
        sectionGroup.style.display = 'none';
        document.getElementById('parentId').value = '';
    } else if (type === 'section') {
        divisionBtn.classList.remove('active');
        sectionBtn.classList.add('active');
        divisionSelect.parentElement.style.display = 'block';
        sectionGroup.style.display = 'block';
        document.getElementById('parentId').value = '';
    }
}

function handleDivisionChange() {
    const divisionId = document.getElementById('divisionSelect').value;
    const sectionSelect = document.getElementById('sectionSelect');
    const sectionBtn = document.getElementById('sectionTypeBtn');
    
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    document.getElementById('parentId').value = '';
    
    if (divisionId) {
        // Populate sections for selected division with hierarchy
        sectionsData.forEach(sec => {
            if (String(sec.divisionId) === divisionId) {
                const option = document.createElement('option');
                option.value = sec.sectionId;
                option.textContent = `${sec.divisionName} > ${sec.sectionName}`;
                sectionSelect.appendChild(option);
            }
        });
        
        // Set parent to this division if in division mode
        const divisionBtn = document.getElementById('divisionTypeBtn');
        if (divisionBtn.classList.contains('active')) {
            document.getElementById('parentId').value = divisionId;
        }
    }
}

// Units Manager - Singleton Pattern
const UnitsManager = (function() {
    let modalInstance = null;
    let editMode = false;
    
    function getModalInstance() {
        if (!modalInstance) {
            const modalElement = document.getElementById('unitModal');
            if (modalElement) {
                modalInstance = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                
                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('unitForm').reset();
                    editMode = false;
                });
            }
        }
        return modalInstance;
    }
    
    function openAddModal() {
        editMode = false;
        document.getElementById('unitModalTitle').textContent = 'Add Unit';
        document.getElementById('unitForm').reset();
        document.getElementById('unitId').value = '';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Unit';
        
        // Reset parent type to 'division' by default
        setParentType('division');
        
        const modal = getModalInstance();
        if (modal) modal.show();
    }
    
    async function editUnit(id) {
        editMode = true;
        document.getElementById('unitModalTitle').textContent = 'Edit Unit';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Unit';
        
        try {
            const response = await fetch(`../../ajax/get_unit.php?id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                const unit = result.data;
                document.getElementById('unitId').value = unit.unitId;
                document.getElementById('unitName').value = unit.unitName;
                document.getElementById('parentId').value = unit.parentId;
                
                // Determine parent type and set accordingly
                const parentTypeId = unit.parentTypeId;
                if (parentTypeId === 1) {
                    // Direct under division
                    setParentType('division');
                    document.getElementById('divisionSelect').value = unit.parentId;
                } else if (parentTypeId === 2) {
                    // Under section
                    setParentType('section');
                    // Find the division ID for this section
                    const section = sectionsData.find(s => s.sectionId === unit.parentId);
                    if (section) {
                        document.getElementById('divisionSelect').value = section.divisionId;
                        handleDivisionChange();
                        setTimeout(() => {
                            document.getElementById('sectionSelect').value = unit.parentId;
                        }, 100);
                    }
                }
                
                const modal = getModalInstance();
                if (modal) modal.show();
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
                const modal = getModalInstance();
                if (modal) modal.hide();
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
    
    function handleParentTypeChange() {
        // This function is no longer used - parent type is managed via setParentType()
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
        const groups = document.querySelectorAll('.division-group');
        
        let hasVisibleGroups = false;
        
        groups.forEach(group => {
            const divisionMatch = divisionFilter === 'all' || group.dataset.divisionId === divisionFilter;
            const subgroups = group.querySelectorAll('.section-subgroup');
            let hasVisibleSubgroups = false;
            
            subgroups.forEach(subgroup => {
                let parentMatch = false;
                
                if (parentFilter === 'all') {
                    parentMatch = true;
                } else if (parentFilter === 'direct') {
                    parentMatch = subgroup.dataset.isDirect === '1';
                } else {
                    parentMatch = subgroup.dataset.parentId === parentFilter;
                }
                
                if (parentMatch) {
                    subgroup.style.display = 'block';
                    hasVisibleSubgroups = true;
                } else {
                    subgroup.style.display = 'none';
                }
            });
            
            if (divisionMatch && hasVisibleSubgroups) {
                group.style.display = 'block';
                hasVisibleGroups = true;
            } else {
                group.style.display = 'none';
            }
        });
        
        // Show/hide empty state if no results
        const emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = hasVisibleGroups ? 'none' : 'block';
        }
    }
    
    function destroy() {
        if (modalInstance) {
            try {
                const modalElement = document.getElementById('unitModal');
                if (modalElement) {
                    modalInstance.hide();
                }
                modalInstance.dispose();
            } catch(e) {}
            modalInstance = null;
        }
        
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    
    return {
        openAddModal,
        editUnit,
        deleteUnit,
        handleSubmit,
        filterUnits,
        destroy
    };
})();

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        UnitsManager.destroy();
    }
});

window.addEventListener('beforeunload', function() {
    UnitsManager.destroy();
});
</script>