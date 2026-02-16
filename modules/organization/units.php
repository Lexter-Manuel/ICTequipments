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

<link rel="stylesheet" href="assets/css/units.css?v=<?php echo time(); ?>">

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
var divisionsData = <?php echo json_encode($divisions); ?>;
var sectionsData = <?php echo json_encode($sections); ?>;

// Helper functions for parent type selection
function setParentType(type) {
    var divisionBtn = document.getElementById('divisionTypeBtn');
    var sectionBtn = document.getElementById('sectionTypeBtn');
    var divisionSelect = document.getElementById('divisionSelect');
    var sectionGroup = document.getElementById('sectionGroup');
    
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
    var divisionId = document.getElementById('divisionSelect').value;
    var sectionSelect = document.getElementById('sectionSelect');
    var sectionBtn = document.getElementById('sectionTypeBtn');
    
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    document.getElementById('parentId').value = '';
    
    if (divisionId) {
        // Populate sections for selected division with hierarchy
        sectionsData.forEach(sec => {
            if (String(sec.divisionId) === divisionId) {
                var option = document.createElement('option');
                option.value = sec.sectionId;
                option.textContent = `${sec.divisionName} > ${sec.sectionName}`;
                sectionSelect.appendChild(option);
            }
        });
        
        // Set parent to this division if in division mode
        var divisionBtn = document.getElementById('divisionTypeBtn');
        if (divisionBtn.classList.contains('active')) {
            document.getElementById('parentId').value = divisionId;
        }
    }
}

// Units Manager - Singleton Pattern
var UnitsManager = (function() {
    let modalInstance = null;
    let editMode = false;
    
    function getModalInstance() {
        if (!modalInstance) {
            var modalElement = document.getElementById('unitModal');
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
        
        var modal = getModalInstance();
        if (modal) modal.show();
    }
    
    async function editUnit(id) {
        editMode = true;
        document.getElementById('unitModalTitle').textContent = 'Edit Unit';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Unit';
        
        try {
            var response = await fetch(`../../ajax/get_unit.php?id=${id}`);
            var result = await response.json();
            
            if (result.success) {
                var unit = result.data;
                document.getElementById('unitId').value = unit.unitId;
                document.getElementById('unitName').value = unit.unitName;
                document.getElementById('parentId').value = unit.parentId;
                
                // Determine parent type and set accordingly
                var parentTypeId = unit.parentTypeId;
                if (parentTypeId === 1) {
                    // Direct under division
                    setParentType('division');
                    document.getElementById('divisionSelect').value = unit.parentId;
                } else if (parentTypeId === 2) {
                    // Under section
                    setParentType('section');
                    // Find the division ID for this section
                    var section = sectionsData.find(s => s.sectionId === unit.parentId);
                    if (section) {
                        document.getElementById('divisionSelect').value = section.divisionId;
                        handleDivisionChange();
                        setTimeout(() => {
                            document.getElementById('sectionSelect').value = unit.parentId;
                        }, 100);
                    }
                }
                
                var modal = getModalInstance();
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
            var formData = new FormData();
            formData.append('action', 'delete');
            formData.append('unitId', id);
            
            var response = await fetch('../../ajax/manage_unit.php', {
                method: 'POST',
                body: formData
            });
            
            var result = await response.json();
            
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
        
        var formData = new FormData(event.target);
        formData.append('action', editMode ? 'update' : 'create');
        
        var submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        try {
            var response = await fetch('../../ajax/manage_unit.php', {
                method: 'POST',
                body: formData
            });
            
            var result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                var modal = getModalInstance();
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
        var alertContainer = document.getElementById('alertContainer');
        var alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
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
        var divisionFilter = document.getElementById('divisionFilter').value;
        var parentFilter = document.getElementById('parentFilter').value;
        var groups = document.querySelectorAll('.division-group');
        
        let hasVisibleGroups = false;
        
        groups.forEach(group => {
            var divisionMatch = divisionFilter === 'all' || group.dataset.divisionId === divisionFilter;
            var subgroups = group.querySelectorAll('.section-subgroup');
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
        var emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = hasVisibleGroups ? 'none' : 'block';
        }
    }
    
    function destroy() {
        if (modalInstance) {
            try {
                var modalElement = document.getElementById('unitModal');
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