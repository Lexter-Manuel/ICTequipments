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

.divisions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.division-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px var(--shadow-soft);
    transition: all 0.3s;
    position: relative;
}

.division-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px var(--shadow-medium);
}

.division-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--border-color);
}

.division-badge {
    display: inline-block;
    padding: 6px 14px;
    background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
    color: white;
    border-radius: 6px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.division-actions {
    display: flex;
    gap: 8px;
}

.division-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 12px;
    line-height: 1.4;
}

.division-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    color: var(--text-medium);
    font-size: 13px;
}

.division-meta i {
    color: var(--primary-green);
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
</style>

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
const DivisionsManager = (function() {
    let modalInstance = null;
    let editMode = false;
    
    // Initialize modal once when tab is loaded
    function initModal() {
        const modalElement = document.getElementById('divisionModal');
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
        
        const modal = initModal();
        if (modal) modal.show();
    }
    
    async function editDivision(id) {
        editMode = true;
        document.getElementById('divisionModalTitle').textContent = 'Edit Division';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Division';
        
        try {
            const response = await fetch(`../../ajax/get_division.php?id=${id}`);
            const division = await response.json();
            
            if (division.success) {
                document.getElementById('divisionId').value = division.data.divisionId;
                document.getElementById('divisionName').value = division.data.divisionName;
                
                const modal = initModal();
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
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('divisionId', id);
            
            const response = await fetch('../../ajax/manage_division.php', {
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
            showAlert('Failed to delete division', 'error');
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
            const response = await fetch('../../ajax/manage_division.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
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
    
    // Cleanup function to destroy modal when tab is switched
    function cleanup() {
        if (modalInstance) {
            const modalElement = document.getElementById('divisionModal');
            if (modalElement) {
                // Hide modal if it's open
                modalInstance.hide();
                // Remove backdrop
                const backdrop = document.querySelector('.modal-backdrop');
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