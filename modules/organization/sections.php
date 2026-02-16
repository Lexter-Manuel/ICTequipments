<?php
// modules/organization/sections.php
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Fetch all sections (location_type_id = 2) with division information
$stmt = $db->query("
    SELECT 
        s.location_id as location_id,
        s.location_name as sectionName,
        s.parent_location_id as divisionId,
        s.created_at as createdAt,
        d.location_name as divisionName,
        COUNT(e.employeeId) as employee_count
    FROM location s
    LEFT JOIN location d ON s.parent_location_id = d.location_id
    LEFT JOIN tbl_employee e ON s.location_id = e.location_id
    WHERE s.location_type_id = 2 AND s.is_deleted = '0'
    GROUP BY s.location_id
    ORDER BY d.location_name ASC, s.location_name ASC
");
$sections = $stmt->fetchAll();

// Group sections by division
$sectionsByDivision = [];
foreach ($sections as $section) {
    $divName = $section['divisionName'] ?: 'Unassigned';
    if (!isset($sectionsByDivision[$divName])) {
        $sectionsByDivision[$divName] = [
            'divisionId' => $section['divisionId'],
            'sections' => []
        ];
    }
    $sectionsByDivision[$divName]['sections'][] = $section;
}

// Fetch divisions (location_type_id = 1) for dropdown
$divisions = $db->query("
    SELECT location_id as divisionId, location_name as divisionName 
    FROM location 
    WHERE location_type_id = 1 AND is_deleted = '0'
    ORDER BY location_name ASC
")->fetchAll();
?>
<link rel="stylesheet" href="assets/css/sections.css?v=<?php echo time(); ?>">

<!-- Page Header -->
<div class="page-header">
    <h2>Sections Management</h2>
    <button class="btn btn-primary" onclick="SectionsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add Section
    </button>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<!-- Filter -->
<?php if (count($divisions) > 0): ?>
<div class="filter-container">
    <select id="divisionFilter" onchange="SectionsManager.filterSections()">
        <option value="all">All Divisions</option>
        <?php foreach ($divisions as $div): ?>
        <option value="<?php echo $div['divisionId']; ?>">
            <?php echo htmlspecialchars($div['divisionName']); ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<!-- Sections by Division -->
<?php if (count($sectionsByDivision) > 0): ?>
    <?php foreach ($sectionsByDivision as $divName => $divData): ?>
    <div class="division-group" data-division="<?php echo htmlspecialchars($divName); ?>" data-division-id="<?php echo $divData['divisionId']; ?>">
        <div class="division-group-header">
            <h3><?php echo htmlspecialchars($divName); ?></h3>
            <p><?php echo count($divData['sections']); ?> Section(s)</p>
        </div>
        <div class="sections-container">
            <?php foreach ($divData['sections'] as $section): ?>
            <div class="section-item">
                <div class="section-info">
                    <div class="section-badge">SECTION</div>
                    <div class="section-name">
                        <?php echo htmlspecialchars($section['sectionName']); ?>
                    </div>
                    <div class="section-meta">
                        <span><i class="fas fa-users"></i> <?php echo $section['employee_count']; ?> Employees</span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($section['createdAt'])); ?></span>
                    </div>
                </div>
                <div class="section-actions">
                    <button class="btn btn-sm btn-secondary" onclick="SectionsManager.editSection(<?php echo $section['location_id']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="SectionsManager.deleteSection(<?php echo $section['location_id']; ?>, '<?php echo htmlspecialchars($section['sectionName']); ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
<div class="empty-state">
    <i class="fas fa-sitemap"></i>
    <h3>No Sections Found</h3>
    <p>Get started by adding your first section</p>
    <button class="btn btn-primary" onclick="SectionsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add First Section
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="sectionModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="sectionModalTitle">Add Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sectionForm" onsubmit="SectionsManager.handleSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="sectionId" name="sectionId">
                    
                    <div class="form-group">
                        <label for="divisionId">Division *</label>
                        <select class="form-control" id="divisionId" name="divisionId" required>
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $div): ?>
                            <option value="<?php echo $div['divisionId']; ?>">
                                <?php echo htmlspecialchars($div['divisionName']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sectionName">Section Name *</label>
                        <textarea class="form-control" id="sectionName" name="sectionName" required maxlength="255" placeholder="e.g., Administrative Section"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="background-color: var(--primary-green); border-color: var(--primary-green);">
                        <i class="fas fa-save"></i>
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Sections Manager - Singleton Pattern
var SectionsManager = (function() {
    let modalInstance = null;
    let editMode = false;
    
    function getModalInstance() {
        if (!modalInstance) {
            var modalElement = document.getElementById('sectionModal');
            if (modalElement) {
                modalInstance = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                
                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('sectionForm').reset();
                    editMode = false;
                });
            }
        }
        return modalInstance;
    }
    
    function openAddModal() {
        editMode = false;
        document.getElementById('sectionModalTitle').textContent = 'Add Section';
        document.getElementById('sectionForm').reset();
        document.getElementById('sectionId').value = '';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Section';
        
        var modal = getModalInstance();
        if (modal) modal.show();
    }
    
    async function editSection(id) {
        editMode = true;
        document.getElementById('sectionModalTitle').textContent = 'Edit Section';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Section';
        
        try {
            var response = await fetch(`../../ajax/get_section.php?id=${id}`);
            var section = await response.json();
            
            if (section.success) {
                document.getElementById('sectionId').value = section.data.sectionId;
                document.getElementById('sectionName').value = section.data.sectionName;
                document.getElementById('divisionId').value = section.data.divisionId;
                
                var modal = getModalInstance();
                if (modal) modal.show();
            } else {
                showAlert('Error loading section data', 'error');
            }
        } catch (error) {
            showAlert('Failed to load section data', 'error');
        }
    }
    
    async function deleteSection(id, name) {
        if (!confirm(`Are you sure you want to delete section "${name}"?\n\nThis will affect all employees assigned to this section.`)) {
            return;
        }
        
        try {
            var formData = new FormData();
            formData.append('action', 'delete');
            formData.append('sectionId', id);
            
            var response = await fetch('../../ajax/manage_section.php', {
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
            showAlert('Failed to delete section', 'error');
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
            var response = await fetch('../../ajax/manage_section.php', {
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
                submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Section' : '<i class="fas fa-save"></i> Save Section';
            }
        } catch (error) {
            showAlert('Failed to save section', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Section' : '<i class="fas fa-save"></i> Save Section';
        }
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
    
    function filterSections() {
        var filter = document.getElementById('divisionFilter').value;
        var groups = document.querySelectorAll('.division-group');
        
        groups.forEach(group => {
            if (filter === 'all' || group.dataset.divisionId === filter) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    }
    
    function destroy() {
        if (modalInstance) {
            try {
                var modalElement = document.getElementById('sectionModal');
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
        editSection,
        deleteSection,
        handleSubmit,
        filterSections,
        destroy
    };
})();

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        SectionsManager.destroy();
    }
});

window.addEventListener('beforeunload', function() {
    SectionsManager.destroy();
});
</script>