<?php
// modules/organization/sections.php
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Fetch all sections (location_type_id = 2) with division information
$stmt = $db->query("
    SELECT 
        s.location_id as sectionId,
        s.location_name as sectionName,
        s.parent_location_id as divisionId,
        s.created_at as createdAt,
        d.location_name as divisionName,
        COUNT(e.employeeId) as employee_count
    FROM location s
    LEFT JOIN location d ON s.parent_location_id = d.location_id
    LEFT JOIN tbl_employee e ON s.location_id = e.sectionId
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

.division-group-header p {
    font-size: 14px;
    opacity: 0.9;
}

.sections-container {
    background: white;
    border: 1px solid var(--border-color);
    border-top: none;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}

.section-item {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s;
}

.section-item:last-child {
    border-bottom: none;
}

.section-item:hover {
    background: var(--bg-light);
}

.section-info {
    flex: 1;
}

.section-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(45, 122, 79, 0.1);
    color: var(--primary-green);
    border-radius: 6px;
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.section-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.section-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    color: var(--text-medium);
    font-size: 13px;
}

.section-meta i {
    color: var(--primary-green);
}

.section-actions {
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
}

.filter-container select {
    padding: 10px 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    min-width: 250px;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h2>Sections Management</h2>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add Section
    </button>
</div>

<!-- Alert Messages -->
<div id="alertContainer"></div>

<!-- Filter -->
<?php if (count($divisions) > 0): ?>
<div class="filter-container">
    <select id="divisionFilter" onchange="filterSections()">
        <option value="all">All Divisions</option>
        <?php foreach ($divisions as $div): ?>
        <option value="<?php echo htmlspecialchars($div['divisionName']); ?>">
            <?php echo htmlspecialchars($div['divisionName']); ?>
        </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<!-- Sections by Division -->
<?php if (count($sectionsByDivision) > 0): ?>
    <?php foreach ($sectionsByDivision as $divName => $divData): ?>
    <div class="division-group" data-division="<?php echo htmlspecialchars($divName); ?>">
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
                    <button class="btn btn-sm btn-secondary" onclick="editSection(<?php echo $section['sectionId']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSection(<?php echo $section['sectionId']; ?>, '<?php echo htmlspecialchars($section['sectionName']); ?>')">
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
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        Add First Section
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1" aria-labelledby="sectionModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="sectionModalTitle">Add Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sectionForm" onsubmit="handleSubmit(event)">
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
let editMode = false;

function openAddModal() {
    editMode = false;
    document.getElementById('sectionModalTitle').textContent = 'Add Section';
    document.getElementById('sectionForm').reset();
    document.getElementById('sectionId').value = '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Section';
    const modal = new bootstrap.Modal(document.getElementById('sectionModal'));
    modal.show();
}

async function editSection(id) {
    editMode = true;
    document.getElementById('sectionModalTitle').textContent = 'Edit Section';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Section';
    
    try {
        const response = await fetch(`../../ajax/get_section.php?id=${id}`);
        const section = await response.json();
        
        if (section.success) {
            document.getElementById('sectionId').value = section.data.sectionId;
            document.getElementById('sectionName').value = section.data.sectionName;
            document.getElementById('divisionId').value = section.data.divisionId;
            const modal = new bootstrap.Modal(document.getElementById('sectionModal'));
            modal.show();
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
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('sectionId', id);
        
        const response = await fetch('../../ajax/manage_section.php', {
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
        showAlert('Failed to delete section', 'error');
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
        const response = await fetch('../../ajax/manage_section.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('sectionModal')).hide();
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

function filterSections() {
    const filter = document.getElementById('divisionFilter').value;
    const groups = document.querySelectorAll('.division-group');
    
    groups.forEach(group => {
        if (filter === 'all' || group.dataset.division === filter) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    });
}
</script>