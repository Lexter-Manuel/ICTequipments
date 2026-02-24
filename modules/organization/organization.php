<?php
// modules/organization/organization.php
// Merged Organization page: Divisions, Sections, Units in one tabbed view
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// ── Divisions data ──
$divStmt = $db->query("
    SELECT 
        l.location_id   AS divisionId,
        l.location_name AS divisionName,
        l.created_at    AS createdAt,
        COUNT(s.location_id) AS section_count
    FROM location l
    LEFT JOIN location s ON l.location_id = s.parent_location_id AND s.location_type_id = 2 AND s.is_deleted = '0'
    WHERE l.location_type_id = 1 AND l.is_deleted = '0'
    GROUP BY l.location_id
    ORDER BY l.location_name ASC
");
$divisions = $divStmt->fetchAll();

// ── Sections data ──
$secStmt = $db->query("
    SELECT 
        s.location_id        AS location_id,
        s.location_name      AS sectionName,
        s.parent_location_id AS divisionId,
        s.created_at         AS createdAt,
        d.location_name      AS divisionName,
        COUNT(e.employeeId)  AS employee_count
    FROM location s
    LEFT JOIN location d ON s.parent_location_id = d.location_id
    LEFT JOIN tbl_employee e ON s.location_id = e.location_id
    WHERE s.location_type_id = 2 AND s.is_deleted = '0'
    GROUP BY s.location_id
    ORDER BY d.location_name ASC, s.location_name ASC
");
$sections = $secStmt->fetchAll();

$sectionsByDivision = [];
foreach ($sections as $section) {
    $divName = $section['divisionName'] ?: 'Unassigned';
    if (!isset($sectionsByDivision[$divName])) {
        $sectionsByDivision[$divName] = [
            'divisionId' => $section['divisionId'],
            'sections'   => []
        ];
    }
    $sectionsByDivision[$divName]['sections'][] = $section;
}

// ── Divisions list for dropdowns ──
$divDropdown = $db->query("
    SELECT location_id AS divisionId, location_name AS divisionName
    FROM location WHERE location_type_id = 1 AND is_deleted = '0'
    ORDER BY location_name ASC
")->fetchAll();

// ── Units data ──
$unitStmt = $db->query("
    SELECT 
        u.location_id        AS unitId,
        u.location_name      AS unitName,
        u.parent_location_id AS parentId,
        u.created_at         AS createdAt,
        p.location_name      AS parentName,
        p.location_type_id   AS parentTypeId,
        p.parent_location_id AS grandparentId,
        gp.location_name     AS grandparentName,
        gp.location_type_id  AS grandparentTypeId,
        COUNT(e.employeeId)  AS employee_count
    FROM location u
    LEFT JOIN location p  ON u.parent_location_id = p.location_id
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
$units = $unitStmt->fetchAll();

$unitsByHierarchy = [];
foreach ($units as $unit) {
    if ($unit['parentTypeId'] == 1) {
        $divisionName = $unit['parentName'];
        $sectionName  = 'Direct Units';
        $divisionId   = $unit['parentId'];
        $parentIdForFilter = $unit['parentId'];
    } elseif ($unit['parentTypeId'] == 2) {
        $divisionName = $unit['grandparentName'] ?: 'Unassigned';
        $sectionName  = $unit['parentName'];
        $divisionId   = $unit['grandparentId'];
        $parentIdForFilter = $unit['parentId'];
    } else {
        $divisionName = 'Unassigned';
        $sectionName  = 'Direct Units';
        $divisionId   = null;
        $parentIdForFilter = null;
    }
    if (!isset($unitsByHierarchy[$divisionName])) {
        $unitsByHierarchy[$divisionName] = ['divisionId' => $divisionId, 'sections' => []];
    }
    if (!isset($unitsByHierarchy[$divisionName]['sections'][$sectionName])) {
        $unitsByHierarchy[$divisionName]['sections'][$sectionName] = [
            'parentId'   => $parentIdForFilter,
            'parentType' => $unit['parentTypeId'],
            'isDirect'   => ($unit['parentTypeId'] == 1),
            'units'      => []
        ];
    }
    $unitsByHierarchy[$divisionName]['sections'][$sectionName]['units'][] = $unit;
}

// Sections for unit dropdown
$secDropdown = $db->query("
    SELECT 
        s.location_id        AS sectionId,
        s.location_name      AS sectionName,
        s.parent_location_id AS divisionId,
        d.location_name      AS divisionName
    FROM location s
    LEFT JOIN location d ON s.parent_location_id = d.location_id
    WHERE s.location_type_id = 2 AND s.is_deleted = '0'
    ORDER BY d.location_name ASC, s.location_name ASC
")->fetchAll();
?>

<link rel="stylesheet" href="assets/css/organization.css?v=<?php echo time(); ?>">

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-sitemap"></i> Organization Management</h2>
</div>

<!-- Tabs -->
<ul class="org-tabs" id="orgTabs">
    <li class="org-tab active" data-tab="divisions">
        <i class="fas fa-building"></i> Divisions
        <span class="org-tab-count"><?php echo count($divisions); ?></span>
    </li>
    <li class="org-tab" data-tab="sections">
        <i class="fas fa-sitemap"></i> Sections
        <span class="org-tab-count"><?php echo count($sections); ?></span>
    </li>
    <li class="org-tab" data-tab="units">
        <i class="fas fa-th-large"></i> Units
        <span class="org-tab-count"><?php echo count($units); ?></span>
    </li>
</ul>

<!-- Alert Messages (shared) -->
<div id="alertContainer"></div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 1 — DIVISIONS
     ═══════════════════════════════════════════════════════════ -->
<div class="org-tab-panel active" id="panel-divisions">
    <div class="panel-toolbar">
        <button class="btn btn-primary" onclick="DivisionsManager.openAddModal()">
            <i class="fas fa-plus"></i> Add Division
        </button>
    </div>

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
            <div class="division-name"><?php echo htmlspecialchars($division['divisionName'] ?: 'No name specified'); ?></div>
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
            <i class="fas fa-plus"></i> Add First Division
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 2 — SECTIONS
     ═══════════════════════════════════════════════════════════ -->
<div class="org-tab-panel" id="panel-sections">
    <div class="panel-toolbar">
        <?php if (count($divDropdown) > 0): ?>
        <select id="sectionDivisionFilter" onchange="SectionsManager.filterSections()">
            <option value="all">All Divisions</option>
            <?php foreach ($divDropdown as $div): ?>
            <option value="<?php echo $div['divisionId']; ?>"><?php echo htmlspecialchars($div['divisionName']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="SectionsManager.openAddModal()">
            <i class="fas fa-plus"></i> Add Section
        </button>
    </div>

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
                        <div class="section-name"><?php echo htmlspecialchars($section['sectionName']); ?></div>
                        <div class="section-meta">
                            <span><i class="fas fa-users"></i> <?php echo $section['employee_count']; ?> Employees</span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($section['createdAt'])); ?></span>
                        </div>
                    </div>
                    <div class="section-actions">
                        <button class="btn btn-sm btn-secondary" onclick="SectionsManager.editSection(<?php echo $section['location_id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="SectionsManager.deleteSection(<?php echo $section['location_id']; ?>, '<?php echo htmlspecialchars($section['sectionName'], ENT_QUOTES); ?>')">
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
            <i class="fas fa-plus"></i> Add First Section
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 3 — UNITS
     ═══════════════════════════════════════════════════════════ -->
<div class="org-tab-panel" id="panel-units">
    <div class="panel-toolbar">
        <?php if (count($divDropdown) > 0): ?>
        <select id="unitDivisionFilter" onchange="UnitsManager.handleDivisionFilterChange()">
            <option value="all">All Divisions</option>
            <?php foreach ($divDropdown as $div): ?>
            <option value="<?php echo $div['divisionId']; ?>"><?php echo htmlspecialchars($div['divisionName']); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="unitParentFilter" onchange="UnitsManager.filterUnits()">
            <option value="all">All Parents</option>
            <option value="direct">Direct Units Only</option>
            <?php foreach ($secDropdown as $sec): ?>
            <option value="<?php echo $sec['sectionId']; ?>" data-division-id="<?php echo $sec['divisionId']; ?>"><?php echo htmlspecialchars($sec['sectionName']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="UnitsManager.openAddModal()">
            <i class="fas fa-plus"></i> Add Unit
        </button>
    </div>

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
                    <span style="margin-left:auto;font-weight:normal;font-size:13px;">
                        <?php echo count($sectionData['units']); ?> Unit(s)
                    </span>
                </div>
                <div class="units-container">
                    <?php foreach ($sectionData['units'] as $unit): ?>
                    <div class="unit-item">
                        <div class="unit-info">
                            <div class="unit-badge">UNIT</div>
                            <div class="unit-name"><?php echo htmlspecialchars($unit['unitName']); ?></div>
                            <div class="unit-meta">
                                <span><i class="fas fa-users"></i> <?php echo $unit['employee_count']; ?> Employees</span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($unit['createdAt'])); ?></span>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn btn-sm btn-secondary" onclick="UnitsManager.editUnit(<?php echo $unit['unitId']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="UnitsManager.deleteUnit(<?php echo $unit['unitId']; ?>, '<?php echo htmlspecialchars($unit['unitName'], ENT_QUOTES); ?>')">
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
            <i class="fas fa-plus"></i> Add First Unit
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODALS
     ═══════════════════════════════════════════════════════════ -->

<!-- Division Modal -->
<div class="modal fade" id="divisionModal" tabindex="-1" aria-labelledby="divisionModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="divisionModalTitle">Add Division</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                    <button type="submit" class="btn btn-primary" id="divSubmitBtn" style="background-color:var(--primary-green);border-color:var(--primary-green);">
                        <i class="fas fa-save"></i> Save Division
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Section Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1" aria-labelledby="sectionModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="sectionModalTitle">Add Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sectionForm" onsubmit="SectionsManager.handleSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="sectionId" name="sectionId">
                    <div class="form-group">
                        <label for="secDivisionId">Division *</label>
                        <select class="form-control" id="secDivisionId" name="divisionId" required>
                            <option value="">Select Division</option>
                            <?php foreach ($divDropdown as $div): ?>
                            <option value="<?php echo $div['divisionId']; ?>"><?php echo htmlspecialchars($div['divisionName']); ?></option>
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
                    <button type="submit" class="btn btn-primary" id="secSubmitBtn" style="background-color:var(--primary-green);border-color:var(--primary-green);">
                        <i class="fas fa-save"></i> Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="unitModalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <?php foreach ($divDropdown as $div): ?>
                            <option value="<?php echo $div['divisionId']; ?>"><?php echo htmlspecialchars($div['divisionName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="sectionGroup" style="display:none;">
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
                    <button type="submit" class="btn btn-primary" id="unitSubmitBtn" style="background-color:var(--primary-green);border-color:var(--primary-green);">
                        <i class="fas fa-save"></i> Save Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
// ── AJAX path ──
var AJAX_PATH = '../ajax/';

// ── Data for unit form dropdowns ──
var divisionsData = <?php echo json_encode($divDropdown); ?>;
var sectionsData  = <?php echo json_encode($secDropdown); ?>;

// ══════════════════════════════════════════════════════════════
// Tab switching
// ══════════════════════════════════════════════════════════════
(function() {
    var tabs = document.querySelectorAll('#orgTabs .org-tab');
    var panels = document.querySelectorAll('.org-tab-panel');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.dataset.tab;

            tabs.forEach(function(t) { t.classList.remove('active'); });
            panels.forEach(function(p) { p.classList.remove('active'); });

            this.classList.add('active');
            document.getElementById('panel-' + target).classList.add('active');
        });
    });
})();

// ══════════════════════════════════════════════════════════════
// Divisions Manager
// ══════════════════════════════════════════════════════════════
var DivisionsManager = (function() {
    let modalInstance = null;
    let editMode = false;

    function initModal() {
        var el = document.getElementById('divisionModal');
        if (el && !modalInstance) {
            modalInstance = new bootstrap.Modal(el);
            el.addEventListener('hidden.bs.modal', function() {
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
        document.getElementById('divSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Save Division';
        var m = initModal(); if (m) m.show();
    }

    async function editDivision(id) {
        editMode = true;
        document.getElementById('divisionModalTitle').textContent = 'Edit Division';
        document.getElementById('divSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Division';
        try {
            var r = await fetch(`${AJAX_PATH}get_division.php?id=${id}`);
            var d = await r.json();
            if (d.success) {
                document.getElementById('divisionId').value = d.data.divisionId;
                document.getElementById('divisionName').value = d.data.divisionName;
                var m = initModal(); if (m) m.show();
            } else { showAlert('Error loading division data', 'error'); }
        } catch(e) { showAlert('Failed to load division data', 'error'); }
    }

    async function deleteDivision(id, name) {
        if (!confirm(`Are you sure you want to delete division "${name}"?\n\nThis will also affect all sections under this division.`)) return;
        try {
            var fd = new FormData(); fd.append('action','delete'); fd.append('divisionId', id);
            var r = await fetch(`${AJAX_PATH}manage_division.php`, { method:'POST', body:fd });
            var res = await r.json();
            if (res.success) { showAlert(res.message,'success'); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); }
        } catch(e) { showAlert('Failed to delete division','error'); }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        var fd = new FormData(event.target);
        fd.append('action', editMode ? 'update' : 'create');
        var btn = document.getElementById('divSubmitBtn');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        try {
            var r = await fetch(`${AJAX_PATH}manage_division.php`, { method:'POST', body:fd });
            var res = await r.json();
            if (res.success) { showAlert(res.message,'success'); if (modalInstance) modalInstance.hide(); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); btn.disabled = false; btn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Division' : '<i class="fas fa-save"></i> Save Division'; }
        } catch(e) { showAlert('Failed to save division','error'); btn.disabled = false; btn.innerHTML = editMode ? '<i class="fas fa-save"></i> Update Division' : '<i class="fas fa-save"></i> Save Division'; }
    }

    function cleanup() {
        if (modalInstance) { try { modalInstance.hide(); } catch(e){} modalInstance = null; }
        document.querySelectorAll('.modal-backdrop').forEach(el=>el.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
    }

    return { openAddModal, editDivision, deleteDivision, handleSubmit, cleanup };
})();

// ══════════════════════════════════════════════════════════════
// Sections Manager
// ══════════════════════════════════════════════════════════════
var SectionsManager = (function() {
    let modalInstance = null;
    let editMode = false;

    function getModal() {
        if (!modalInstance) {
            var el = document.getElementById('sectionModal');
            if (el) { modalInstance = new bootstrap.Modal(el, {backdrop:'static',keyboard:false}); el.addEventListener('hidden.bs.modal',function(){ document.getElementById('sectionForm').reset(); editMode=false; }); }
        }
        return modalInstance;
    }

    function openAddModal() {
        editMode = false;
        document.getElementById('sectionModalTitle').textContent='Add Section';
        document.getElementById('sectionForm').reset();
        document.getElementById('sectionId').value='';
        document.getElementById('secSubmitBtn').innerHTML='<i class="fas fa-save"></i> Save Section';
        var m=getModal(); if(m) m.show();
    }

    async function editSection(id) {
        editMode = true;
        document.getElementById('sectionModalTitle').textContent='Edit Section';
        document.getElementById('secSubmitBtn').innerHTML='<i class="fas fa-save"></i> Update Section';
        try {
            var r = await fetch(`${AJAX_PATH}get_section.php?id=${id}`);
            var s = await r.json();
            if (s.success) {
                document.getElementById('sectionId').value = s.data.sectionId;
                document.getElementById('sectionName').value = s.data.sectionName;
                document.getElementById('secDivisionId').value = s.data.divisionId;
                var m=getModal(); if(m) m.show();
            } else { showAlert('Error loading section data','error'); }
        } catch(e) { showAlert('Failed to load section data','error'); }
    }

    async function deleteSection(id, name) {
        if (!confirm(`Are you sure you want to delete section "${name}"?\n\nThis will affect all employees assigned to this section.`)) return;
        try {
            var fd=new FormData(); fd.append('action','delete'); fd.append('sectionId',id);
            var r=await fetch(`${AJAX_PATH}manage_section.php`,{method:'POST',body:fd});
            var res=await r.json();
            if(res.success){ showAlert(res.message,'success'); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); }
        } catch(e){ showAlert('Failed to delete section','error'); }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        var fd=new FormData(event.target);
        fd.append('action', editMode ? 'update' : 'create');
        var btn=document.getElementById('secSubmitBtn');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';
        try {
            var r=await fetch(`${AJAX_PATH}manage_section.php`,{method:'POST',body:fd});
            var res=await r.json();
            if(res.success){ showAlert(res.message,'success'); var m=getModal(); if(m) m.hide(); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); btn.disabled=false; btn.innerHTML=editMode?'<i class="fas fa-save"></i> Update Section':'<i class="fas fa-save"></i> Save Section'; }
        } catch(e){ showAlert('Failed to save section','error'); btn.disabled=false; btn.innerHTML=editMode?'<i class="fas fa-save"></i> Update Section':'<i class="fas fa-save"></i> Save Section'; }
    }

    function filterSections() {
        var filter = document.getElementById('sectionDivisionFilter').value;
        var groups = document.querySelectorAll('#panel-sections .division-group');
        groups.forEach(function(g){
            g.style.display = (filter==='all' || g.dataset.divisionId===filter) ? 'block' : 'none';
        });
    }

    function destroy() {
        if(modalInstance){ try{ modalInstance.hide(); modalInstance.dispose(); }catch(e){} modalInstance=null; }
        document.querySelectorAll('.modal-backdrop').forEach(el=>el.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
    }

    return { openAddModal, editSection, deleteSection, handleSubmit, filterSections, destroy };
})();

// ══════════════════════════════════════════════════════════════
// Unit helpers
// ══════════════════════════════════════════════════════════════
function setParentType(type) {
    var dBtn = document.getElementById('divisionTypeBtn');
    var sBtn = document.getElementById('sectionTypeBtn');
    var sGrp = document.getElementById('sectionGroup');

    if (type === 'division') {
        dBtn.classList.add('active'); sBtn.classList.remove('active');
        sGrp.style.display = 'none';
        document.getElementById('parentId').value = '';
    } else {
        dBtn.classList.remove('active'); sBtn.classList.add('active');
        sGrp.style.display = 'block';
        document.getElementById('parentId').value = '';
    }
}

function handleDivisionChange() {
    var divId = document.getElementById('divisionSelect').value;
    var secSel = document.getElementById('sectionSelect');
    secSel.innerHTML = '<option value="">Select Section</option>';
    document.getElementById('parentId').value = '';

    if (divId) {
        sectionsData.forEach(function(sec){
            if (String(sec.divisionId) === divId) {
                var o = document.createElement('option');
                o.value = sec.sectionId; o.textContent = sec.sectionName;
                secSel.appendChild(o);
            }
        });
        if (document.getElementById('divisionTypeBtn').classList.contains('active')) {
            document.getElementById('parentId').value = divId;
        }
    }
}

// ══════════════════════════════════════════════════════════════
// Units Manager
// ══════════════════════════════════════════════════════════════
var UnitsManager = (function() {
    let modalInstance = null;
    let editMode = false;

    function getModal() {
        if (!modalInstance) {
            var el = document.getElementById('unitModal');
            if (el) {
                modalInstance = new bootstrap.Modal(el, {backdrop:'static',keyboard:false});
                el.addEventListener('hidden.bs.modal', function(){
                    document.getElementById('unitForm').reset();
                    document.getElementById('unitId').value='';
                    editMode=false;
                    setParentType('division');
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
        document.getElementById('unitSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Save Unit';
        setParentType('division');
        var m = getModal(); if(m) m.show();
    }

    async function editUnit(id) {
        editMode = true;
        document.getElementById('unitModalTitle').textContent = 'Edit Unit';
        document.getElementById('unitSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Unit';
        try {
            var r = await fetch(`${AJAX_PATH}get_unit.php?id=${id}`);
            if (!r.ok) throw new Error('HTTP ' + r.status);
            var ct = r.headers.get('content-type');
            if (!ct || !ct.includes('application/json')) throw new Error('Non-JSON response');
            var result = await r.json();
            if (result.success) {
                var u = result.data;
                document.getElementById('unitId').value = u.unitId;
                document.getElementById('unitName').value = u.unitName;
                document.getElementById('parentId').value = u.parentId;
                var ptId = parseInt(u.parentTypeId);
                if (ptId === 1) {
                    setParentType('division');
                    document.getElementById('divisionSelect').value = u.parentId;
                } else if (ptId === 2) {
                    setParentType('section');
                    var sec = sectionsData.find(function(s){ return parseInt(s.sectionId)===parseInt(u.parentId); });
                    if (sec) {
                        document.getElementById('divisionSelect').value = sec.divisionId;
                        handleDivisionChange();
                        setTimeout(function(){ document.getElementById('sectionSelect').value = u.parentId; }, 100);
                    }
                }
                var m = getModal(); if(m) m.show();
            } else { showAlert(result.message || 'Error loading unit data','error'); }
        } catch(e) { console.error(e); showAlert('Failed to load unit data','error'); }
    }

    async function deleteUnit(id, name) {
        if (!confirm(`Are you sure you want to delete unit "${name}"?\n\nThis will affect all employees assigned to this unit.`)) return;
        try {
            var fd=new FormData(); fd.append('action','delete'); fd.append('unitId',id);
            var r=await fetch(`${AJAX_PATH}manage_unit.php`,{method:'POST',body:fd});
            var res=await r.json();
            if(res.success){ showAlert(res.message,'success'); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); }
        } catch(e){ showAlert('Failed to delete unit','error'); }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        var parentId = document.getElementById('parentId').value;
        var divSel = document.getElementById('divisionSelect').value;
        var secSel = document.getElementById('sectionSelect').value;
        var dBtn = document.getElementById('divisionTypeBtn');

        if (dBtn.classList.contains('active')) {
            if (divSel) document.getElementById('parentId').value = divSel;
        } else {
            if (secSel) document.getElementById('parentId').value = secSel;
        }
        parentId = document.getElementById('parentId').value;
        if (!parentId) { showAlert('Please select a parent location','error'); return; }

        var fd = new FormData(event.target);
        fd.append('action', editMode ? 'update' : 'create');
        var btn = document.getElementById('unitSubmitBtn');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';
        try {
            var r=await fetch(`${AJAX_PATH}manage_unit.php`,{method:'POST',body:fd});
            var ct=r.headers.get('content-type');
            var txt=await r.text();
            if(!ct||!ct.includes('application/json')) throw new Error('Server error: '+txt.substring(0,200));
            var res=JSON.parse(txt);
            if(res.success){ showAlert(res.message,'success'); var m=getModal(); if(m) m.hide(); setTimeout(()=>reloadCurrentPage(),1500); }
            else { showAlert(res.message,'error'); btn.disabled=false; btn.innerHTML=editMode?'<i class="fas fa-save"></i> Update Unit':'<i class="fas fa-save"></i> Save Unit'; }
        } catch(e){ console.error(e); showAlert(e.message||'Failed to save unit','error'); btn.disabled=false; btn.innerHTML=editMode?'<i class="fas fa-save"></i> Update Unit':'<i class="fas fa-save"></i> Save Unit'; }
    }

    function filterUnits() {
        var divFilter = document.getElementById('unitDivisionFilter').value;
        var parentFilter = document.getElementById('unitParentFilter').value;
        var groups = document.querySelectorAll('#panel-units .division-group');
        groups.forEach(function(group){
            var divMatch = divFilter==='all' || group.dataset.divisionId===divFilter;
            var subs = group.querySelectorAll('.section-subgroup');
            var hasVis = false;
            subs.forEach(function(sub){
                var pMatch = parentFilter==='all' ? true : parentFilter==='direct' ? sub.dataset.isDirect==='1' : sub.dataset.parentId===parentFilter;
                sub.style.display = pMatch ? 'block' : 'none';
                if(pMatch) hasVis=true;
            });
            group.style.display = (divMatch && hasVis) ? 'block' : 'none';
        });
    }

    function handleDivisionFilterChange() {
        var divFilter = document.getElementById('unitDivisionFilter').value;
        var pf = document.getElementById('unitParentFilter');
        pf.value = 'all';
        pf.querySelectorAll('option').forEach(function(o){
            if(o.value==='all'||o.value==='direct') { o.style.display='block'; }
            else { var did=o.getAttribute('data-division-id'); o.style.display=(divFilter==='all'||did===divFilter)?'block':'none'; }
        });
        filterUnits();
    }

    function destroy() {
        if(modalInstance){ try{ modalInstance.hide(); modalInstance.dispose(); }catch(e){} modalInstance=null; }
        document.querySelectorAll('.modal-backdrop').forEach(el=>el.remove());
        document.body.classList.remove('modal-open'); document.body.style.overflow=''; document.body.style.paddingRight='';
    }

    return { openAddModal, editUnit, deleteUnit, handleSubmit, filterUnits, handleDivisionFilterChange, destroy };
})();

// ── Shared alert helper ──
function showAlert(message, type) {
    var c = document.getElementById('alertContainer');
    var cls = type==='success' ? 'alert-success' : 'alert-error';
    var ico = type==='success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    c.innerHTML = '<div class="alert '+cls+'"><i class="fas '+ico+'"></i> <span>'+message+'</span></div>';
    setTimeout(function(){ c.innerHTML=''; }, 5000);
}

// ── Cleanup on visibility / unload ──
document.addEventListener('visibilitychange', function() {
    if (document.hidden) { DivisionsManager.cleanup(); SectionsManager.destroy(); UnitsManager.destroy(); }
});
window.addEventListener('beforeunload', function() { DivisionsManager.cleanup(); SectionsManager.destroy(); UnitsManager.destroy(); });
</script>
