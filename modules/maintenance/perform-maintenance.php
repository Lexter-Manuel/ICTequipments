<?php
// modules/inventory/perform-maintenance.php
?>
<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/maintenance.css?v=<?php echo time(); ?>">

<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Perform Maintenance</h1>
            <p class="page-subtitle">Select equipment to begin inspection</p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    
    <!-- Equipment Selection Stage -->
    <div class="row justify-content-center" id="selection-stage">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary-xlight text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-clipboard-check fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Start New Inspection</h5>
                        <p class="text-muted small">Select the equipment type and asset ID to load the correct checklist.</p>
                    </div>

                    <form onsubmit="event.preventDefault(); startMaintenance();">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Equipment Type</label>
                            <select class="form-select form-select-lg" id="selectType" onchange="loadAssets()">
                                <option value="">-- Select Type --</option>
                                <option value="system_unit">System Unit / Desktop PC</option>
                                <option value="laptop">Laptop</option>
                                <option value="printer">Printer</option>
                                <option value="monitor">Monitor</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Select Asset</label>
                            <select class="form-select form-select-lg" id="selectAsset" disabled>
                                <option value="">-- Select Equipment Type First --</option>
                            </select>
                            <small class="text-muted">Only equipment due for maintenance will appear</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg shadow-sm" id="btnStart" disabled>
                            Start Maintenance <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Checklist Stage -->
    <div id="checklist-stage" class="d-none">
        <div class="mb-3">
            <button class="btn btn-link text-muted text-decoration-none px-0" onclick="location.reload()">
                <i class="fas fa-arrow-left"></i> Back to Selection
            </button>
        </div>

        <div class="row g-4">
            <!-- Equipment Info Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="card-title mb-0 text-uppercase text-muted small fw-bold">Asset Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4 pt-2">
                            <div class="avatar-circle bg-light text-primary mx-auto mb-3" style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                                <i class="fas fa-desktop fa-2x" id="equipmentIcon"></i>
                            </div>
                            <h5 class="mb-1 fw-bold" id="equipmentName">Dell Optiplex 7080</h5>
                            <span class="badge bg-secondary-xlight text-secondary border" id="equipmentType">System Unit</span>
                        </div>
                        
                        <div class="px-2">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Serial Number</span>
                                <span class="fw-bold font-monospace text-dark" id="equipmentSerial">SU-2024-009</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Division</span>
                                <span class="fw-medium text-end" id="equipmentDivision">
                                    Engineering & Operation Division
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Section / Unit</span>
                                <span class="fw-medium text-end" id="equipmentSection"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Assigned To</span>
                                <span class="fw-medium" id="equipmentOwner">Engr. Juan Dela Cruz</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Last Maintenance</span>
                                <span class="fw-medium" id="lastMaintenance">Aug 15, 2025</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Frequency</span>
                                <span class="badge bg-info-xlight text-info" id="maintenanceFreq">Semi-Annual</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist Form -->
            <div class="col-lg-8">
                <?php 
                    // This flag tells the include to hide its own headers
                    $isWidget = true; 
                    include '../../includes/components/maintenance-checklist.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<script>
// Mock data for different equipment types
const equipmentData = {
    'system_unit': [
        { id: 101, name: 'Dell Optiplex 7080', serial: 'SU-2024-009', division: 'Engineering & Operation', section: 'Engineering Section', unit: 'Construction Unit', owner: 'Engr. Juan Dela Cruz', lastMaint: 'Aug 15, 2025', freq: 'Semi-Annual', designation: 'Engineer II' },
        { id: 102, name: 'HP EliteDesk 800 G5', serial: 'SU-2023-156', division: 'Administrative & Finance', section: 'Finance Section', unit: 'Accounting Unit', owner: 'Maria Santos', lastMaint: 'Aug 10, 2025', freq: 'Semi-Annual', designation: 'Accountant III' },
        { id: 103, name: 'Asus Vivo Mini PC', serial: 'SU-2025-011', division: 'Engineering & Operation', section: 'Operation Section', unit: 'O&M Unit', owner: 'Pedro Reyes', lastMaint: 'Sep 12, 2025', freq: 'Semi-Annual', designation: 'Engineer I' }
    ],
    'laptop': [
        { id: 201, name: 'Lenovo ThinkPad T14', serial: 'LP-2024-032', division: 'Administrative & Finance', section: 'Administrative Section', unit: 'Property Unit', owner: 'Ana Garcia', lastMaint: 'Nov 12, 2025', freq: 'Quarterly', designation: 'Administrative Officer IV' },
        { id: 202, name: 'Acer TravelMate P2', serial: 'LP-2024-055', division: 'Administrative & Finance', section: 'Finance Section', unit: 'Cashier Unit', owner: 'Jose Ramirez', lastMaint: 'Dec 05, 2025', freq: 'Quarterly', designation: 'Cashier II' },
        { id: 203, name: 'Dell Latitude 3410', serial: 'LP-2024-067', division: 'Administrative & Finance', section: 'Administrative Section', unit: 'HR Unit', owner: 'Carmen Lopez', lastMaint: 'Oct 08, 2025', freq: 'Semi-Annual', designation: 'Human Resource Officer III' }
    ],
    'printer': [
        { id: 301, name: 'Epson L3110', serial: 'PR-2023-112', division: 'Administrative & Finance', section: 'Administrative Section', unit: 'Records Unit', owner: 'Records Unit', lastMaint: 'Jan 18, 2026', freq: 'Monthly', designation: 'N/A' },
        { id: 302, name: 'HP LaserJet Pro M404n', serial: 'PR-2024-089', division: 'Office of the Dept. Manager', section: 'ICT Unit', unit: '', owner: 'ICT Unit', lastMaint: 'Jan 22, 2026', freq: 'Monthly', designation: 'N/A' },
        { id: 303, name: 'Canon PIXMA G3010', serial: 'PR-2023-145', division: 'Engineering & Operation', section: 'Engineering Section', unit: 'Construction Unit', owner: 'Construction Unit', lastMaint: 'Feb 20, 2026', freq: 'Monthly', designation: 'N/A' }
    ],
    'monitor': [
        { id: 401, name: 'Samsung S24C450', serial: 'MO-2024-078', division: 'Engineering & Operation', section: 'Engineering Section', unit: 'Planning Unit', owner: 'Planning Unit', lastMaint: 'Aug 20, 2025', freq: 'Semi-Annual', designation: 'N/A' },
        { id: 402, name: 'LG 24MK430H Monitor', serial: 'MO-2023-134', division: 'Administrative & Finance', section: 'Finance Section', unit: 'Accounting Unit', owner: 'Accounting Unit', lastMaint: 'Aug 12, 2025', freq: 'Semi-Annual', designation: 'N/A' }
    ]
};

// Check if we came from schedule page with pre-selected equipment
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const equipId = urlParams.get('id');
    const equipType = urlParams.get('type');
    
    if (equipId && equipType) {
        // Auto-load the equipment
        document.getElementById('selectType').value = equipType;
        loadAssets();
        
        setTimeout(() => {
            document.getElementById('selectAsset').value = equipId;
            document.getElementById('btnStart').disabled = false;
            
            // Auto-start maintenance
            setTimeout(() => {
                startMaintenance();
            }, 500);
        }, 100);
    }
});

function loadAssets() {
    const type = document.getElementById('selectType').value;
    const assetSelect = document.getElementById('selectAsset');
    const btnStart = document.getElementById('btnStart');
    
    if (!type) {
        assetSelect.disabled = true;
        assetSelect.innerHTML = '<option value="">-- Select Equipment Type First --</option>';
        btnStart.disabled = true;
        return;
    }
    
    const equipment = equipmentData[type] || [];
    assetSelect.disabled = false;
    assetSelect.innerHTML = '<option value="">-- Select Asset --</option>';
    
    equipment.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.serial})`;
        option.dataset.equipment = JSON.stringify(item);
        assetSelect.appendChild(option);
    });
    
    assetSelect.addEventListener('change', function() {
        btnStart.disabled = !this.value;
    });
}

function startMaintenance() {
    const type = document.getElementById('selectType').value;
    const assetSelect = document.getElementById('selectAsset');
    const assetId = assetSelect.value;

    if (!type || !assetId) {
        alert('Please select both an Equipment Type and a specific Asset.');
        return;
    }
    
    // Get selected equipment data
    const selectedOption = assetSelect.options[assetSelect.selectedIndex];
    const equipment = JSON.parse(selectedOption.dataset.equipment);
    
    // Update equipment info display
    document.getElementById('equipmentName').textContent = equipment.name;
    document.getElementById('equipmentSerial').textContent = equipment.serial;
    document.getElementById('equipmentDivision').textContent = equipment.division;
    document.getElementById('equipmentSection').innerHTML = equipment.section + (equipment.unit ? '<br><small class="text-muted">' + equipment.unit + '</small>' : '');
    document.getElementById('equipmentOwner').textContent = equipment.owner;
    document.getElementById('lastMaintenance').textContent = equipment.lastMaint;
    document.getElementById('maintenanceFreq').textContent = equipment.freq;
    
    // Update checklist form fields
    document.getElementById('divisionUnit').value = equipment.division + ' / ' + equipment.section + (equipment.unit ? ' / ' + equipment.unit : '');
    document.getElementById('employeeName').value = equipment.owner;
    document.getElementById('propertyNo').value = equipment.serial;
    
    // Update icon based on type
    const iconMap = {
        'system_unit': 'fa-desktop',
        'laptop': 'fa-laptop',
        'printer': 'fa-print',
        'monitor': 'fa-tv'
    };
    const typeNames = {
        'system_unit': 'System Unit',
        'laptop': 'Laptop',
        'printer': 'Printer',
        'monitor': 'Monitor'
    };
    
    document.getElementById('equipmentIcon').className = 'fas ' + iconMap[type] + ' fa-2x';
    document.getElementById('equipmentType').textContent = typeNames[type];
    document.getElementById('equipmentTypeName').value = typeNames[type];
    
    // Show/hide appropriate checklist based on equipment type
    if (type === 'printer') {
        document.getElementById('checklistComputer').style.display = 'none';
        document.getElementById('checklistPrinter').style.display = 'block';
    } else {
        document.getElementById('checklistComputer').style.display = 'block';
        document.getElementById('checklistPrinter').style.display = 'none';
    }
    
    // Hide selection, show checklist
    document.getElementById('selection-stage').classList.add('d-none');
    document.getElementById('checklist-stage').classList.remove('d-none');
    
    // Scroll to top smoothly
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>