<?php
// modules/organization/divisions.php
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Fetch all divisions (location_type_id = 1) with section counts
$stmt = $db->query("
    SELECT 
        l.location_id as divisionId,
        l.location_name as divisionName,
        l.created_at as createdAt,
        COUNT(s.location_id) as section_count
    FROM location l
    LEFT JOIN location s ON l.location_id = s.parent_location_id AND s.location_type_id = 2 AND s.is_deleted = '0'
    WHERE l.location_type_id = 1 AND l.is_deleted = '0'
    GROUP BY l.location_id
    ORDER BY l.location_name ASC
");
$divisions = $stmt->fetchAll();
?>

<link rel="stylesheet" href="assets/css/divisions.css">

<!-- Page Header -->
<div class="page-header">
    <h2>Divisions Management</h2>
    <button class="btn btn-primary" onclick="DivisionsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add Division
    </button>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<!-- Divisions Grid -->
<?php if (count($divisions) > 0): ?>
<div class="divisions-grid">
    <?php foreach ($divisions as $division): ?>
    <div class="division-card">
        <div class="division-header">
            <div class="division-badge">DIVISION</div>
            <div class="division-actions">
                <button class="btn btn-sm btn-secondary" onclick="DivisionsManager.editDivision(<?php echo $division['divisionId']; ?>)">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="DivisionsManager.deleteDivision(<?php echo $division['divisionId']; ?>, '<?php echo htmlspecialchars($division['divisionName'], ENT_QUOTES); ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="division-name">
            <?php echo htmlspecialchars($division['divisionName'] ?: 'No name specified'); ?>
        </div>
        <div class="division-meta">
            <span><i class="fas fa-sitemap"></i> <?php echo $division['section_count']; ?> Sections</span>
            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($division['createdAt'])); ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <i class="fas fa-building"></i>
    <h3>No Divisions Found</h3>
    <p>Get started by adding your first division</p>
    <button class="btn btn-primary" onclick="DivisionsManager.openAddModal()">
        <i class="fas fa-plus"></i>
        Add First Division
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="divisionModal" tabindex="-1" aria-labelledby="divisionModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="divisionModalTitle">Add Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="divisionForm" onsubmit="DivisionsManager.handleSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="divisionId" name="divisionId">
                    
                    <div class="form-group">
                        <label for="divisionName">Division Name *</label>
                        <textarea class="form-control" id="divisionName" name="divisionName" required maxlength="255" placeholder="e.g., Engineering and Operation Division (EOD)"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="background-color: var(--primary-green); border-color: var(--primary-green);">
                        <i class="fas fa-save"></i>
                        Save Division
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Divisions Manager - Singleton pattern to prevent multiple initializations
var DivisionsManager = (function() {
    let modalInstance = null;
    let editMode = false;
    
    // Initialize modal once when tab is loaded
    function initModal() {
        var modalElement = document.getElementById('divisionModal');
        if (modalElement && !modalInstance) {
            modalInstance = new bootstrap.Modal(modalElement);
            
            // Cleanup on modal hidden
            modalElement.addEventListener('hidden.bs.modal', function() {
                document.getElementById('divisionForm').reset();
                editMode = false;
            });
        }
        return modalInstance;
    }
    
    function openAddModal() {
        editMode = false;
        document.getElementById('divisionModalTitle').textContent = 'Add Division';
        document.getElementById('divisionForm').reset();
        document.getElementById('divisionId').value = '';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Division';
        
        var modal = initModal();
        if (modal) modal.show();
    }
    
    async function editDivision(id) {
        editMode = true;
        document.getElementById('divisionModalTitle').textContent = 'Edit Division';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Division';
        
        try {
            var response = await fetch(`../../ajax/get_division.php?id=${id}`);
            var division = await response.json();
            
            if (division.success) {
                document.getElementById('divisionId').value = division.data.divisionId;
                document.getElementById('divisionName').value = division.data.divisionName;
                
                var modal = initModal();
                if (modal) modal.show();
            } else {
                showAlert('Error loading division data', 'error');
            }
        } catch (error) {
            showAlert('Failed to load division data', 'error');
        }
    }
    
    async function deleteDivision(id, name) {
        if (!confirm(`Are you sure you want to delete division "${name}"?\n\nThis will also affect all sections under this division.`)) {
            return;
        }
        
        try {
            var formData = new FormData();
            formData.append('action', 'delete');
            formData.append('divisionId', id);
            
            var response = await fetch('../../ajax/manage_division.php', {
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
            showAlert('Failed to delete division', 'error');
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
            var response = await fetch('../../ajax/manage_division.php', {
                method: 'POST',
                body: formData
            });
            
            var result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                if (modalInstance) modalInstance.hide();
                setTimeout(() => reloadCurrentPage(), 1500);
            } else {
                showAlert(result.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Division' : '<i class="fas fa-save"></i> Save Division';
            }
        } catch (error) {
            showAlert('Failed to save division', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Division' : '<i class="fas fa-save"></i> Save Division';
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
    
    // Cleanup function to destroy modal when tab is switched
    function cleanup() {
        if (modalInstance) {
            var modalElement = document.getElementById('divisionModal');
            if (modalElement) {
                // Hide modal if it's open
                modalInstance.hide();
                // Remove backdrop
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
                // Reset body class
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
            modalInstance = null;
        }
    }
    
    // Public API
    return {
        openAddModal,
        editDivision,
        deleteDivision,
        handleSubmit,
        cleanup
    };
})();

// Cleanup when tab is switched or page is unloaded
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        DivisionsManager.cleanup();
    }
});
</script>