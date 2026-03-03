/**
 * add_equipment_to_employee.js — v5
 * ─────────────────────────────────────────────────────────────────────────────
 * FIXED: SPA Tab Switch Skeleton Bug
 * Inline `display: none` added to body menus so they cannot flash unstyled
 * HTML when the SPA router removes the CSS file.
 * The cleanup tracker has also been accelerated to 50ms for instant DOM sweeping.
 * ─────────────────────────────────────────────────────────────────────────────
 */

/* ═══════════════════════════════════════════════════════════════════════════
   2. MODAL — appended to document.body
   ═══════════════════════════════════════════════════════════════════════════ */
(function () {
    var existing = document.getElementById('addEquipmentModal');
    if (existing && existing.parentElement !== document.body) {
        var stale = bootstrap.Modal.getInstance(existing);
        if (stale) stale.dispose();
        existing.remove();
        existing = null;
    }

    if (existing) return; 

    var el = document.createElement('div');
    el.innerHTML = `
    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-plus-circle"></i>
              <span id="aeModalTitle">Add Equipment</span>
            </h5>
            <button type="button" class="btn-close btn-close-white"
                    onclick="aeCloseModal()" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="ae-mode-tabs">
              <button class="ae-tab-btn active" id="aeTabExisting"
                      onclick="switchAddEquipmentMode('existing')">
                <i class="fas fa-link"></i> Assign Existing
              </button>
              <button class="ae-tab-btn" id="aeTabNew"
                      onclick="switchAddEquipmentMode('new')">
                <i class="fas fa-plus"></i> Add New Record
              </button>
            </div>
            <div id="aeExistingPane">
              <p class="ae-hint"><i class="fas fa-info-circle"></i>
                Select an unassigned item from the inventory to assign to this employee.
              </p>
              <div class="ae-search-wrap">
                <i class="fas fa-search ae-search-icon"></i>
                <input type="text" id="aeExistingSearch"
                       placeholder="Search by brand or serial…"
                       oninput="filterAeExistingList()">
              </div>
              <div id="aeExistingList" class="ae-existing-list">
                <div class="ae-empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading…</p></div>
              </div>
            </div>
            <div id="aeNewPane" style="display:none;">
              <p class="ae-hint"><i class="fas fa-info-circle"></i>
                Fill in the details — the record will be created and immediately assigned.
              </p>
              <div class="ae-form-wrap">
                <form id="aeNewForm" novalidate autocomplete="off">
                  <div id="aeNewFields"></div>
                </form>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="ae-btn-cancel" onclick="aeCloseModal()">Cancel</button>
            <button class="ae-btn-save" id="aeSaveBtn" onclick="saveAddEquipment()">
              <i class="fas fa-save"></i> Save &amp; Assign
            </button>
          </div>
        </div>
      </div>
    </div>`;

    document.body.appendChild(el.firstElementChild);

    // ── SPA GHOST CLEANUP TRACKER (Accelerated to 50ms) ───────────
    var aeModal = document.getElementById('addEquipmentModal');
    if (aeModal && aeModal.parentNode === document.body) {
        
        var aeSpaTracker = setInterval(function() {
            var rosterView = document.getElementById('roster-list-view');
            var profileView = document.getElementById('employee-profile-view');
            
            if (!rosterView && !profileView) {
                if (aeModal) aeModal.remove();
                
                var bodyMenu = document.getElementById('aeBodyMenu');
                if (bodyMenu) bodyMenu.remove();
                
                document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');

                clearInterval(aeSpaTracker);
            }
        }, 50); // Checks instantly instead of waiting 1 second
    }
}());


/* ═══════════════════════════════════════════════════════════════════════════
   3. CLOSE DROPDOWN ON OUTSIDE CLICK (Updated with inline hide)
   ═══════════════════════════════════════════════════════════════════════════ */
document.addEventListener('click', function (e) {
    var menu = document.getElementById('aeBodyMenu');
    var clickedInsideToggle = e.target.closest('.ae-dropdown');
    if (!clickedInsideToggle) {
        document.querySelectorAll('.ae-dropdown.open').forEach(function (dd) { dd.classList.remove('open'); });
        if (menu) {
            menu.classList.remove('show');
            menu.style.display = 'none'; // Prevents skeleton flash
        }
        _aeDdBtn = null;
    }
});


/* ═══════════════════════════════════════════════════════════════════════════
   4. PATCH renderEmployeeProfile
   ═══════════════════════════════════════════════════════════════════════════ */

(function () {
    function wrap(orig) {
        // Prevent double-wrapping if the SPA router loads this script twice
        if (orig._isAeWrapped) return;
        
        window.renderEmployeeProfile = function (data) {
            orig.call(this, data);
            var empId = data && data.employee ? data.employee.employeeId : null;
            if (empId) _aeInjectButtons(empId);
        };
        window.renderEmployeeProfile._isAeWrapped = true;
    }

    // --- NEW: Smart Polling Tracker ---
    // Instead of checking once, it checks every 100ms for up to 10 seconds.
    // This completely solves the ngrok/network latency race condition.
    var attempts = 0;
    var patchTimer = setInterval(function () {
        if (typeof window.renderEmployeeProfile === 'function') {
            wrap(window.renderEmployeeProfile);
            clearInterval(patchTimer);
        }
        
        attempts++;
        if (attempts > 100) { // Give up after 10 seconds
            clearInterval(patchTimer);
            console.warn('[AE] renderEmployeeProfile not found. Network too slow or missing roster.js');
        }
    }, 100);
}());


/* ═══════════════════════════════════════════════════════════════════════════
   5. STATE
   ═══════════════════════════════════════════════════════════════════════════ */
var _aeEmployeeId    = null;
var _aeEquipmentType = null;
var _aeMode          = 'existing';
var _aeSelectedId    = null;


/* ═══════════════════════════════════════════════════════════════════════════
   6. TYPE CONFIG
   ═══════════════════════════════════════════════════════════════════════════ */
var AE_TYPES = {
    systemunit: {
        label: 'System Unit', icon: 'fa-desktop',
        fields: function (y) {
            return '<div class="ae-row">'
                + _aeField('half','Category','systemUnitCategory','select',true,null,['Pre-Built','Custom Built'])
                + _aeField('half','Brand','systemUnitBrand','text',true,'e.g. ACER, HP')
                + _aeField('half','Serial Number','systemUnitSerial','text',true,'Serial #')
                + _aeField('half','Year Acquired','yearAcquired','number',true,null,null,{min:2000,max:2099,value:y})
                + _aeField('half','Processor','specificationProcessor','text',true,'e.g. i5-13th Gen')
                + _aeField('half','Memory (RAM)','specificationMemory','text',true,'e.g. 8GB DDR4')
                + _aeField('half','GPU','specificationGPU','text',true,'e.g. Integrated GPU')
                + _aeField('half','Storage','specificationStorage','text',true,'e.g. 256GB SSD')
                + '</div>';
        }
    },
    allinone: {
        label: 'All-in-One PC', icon: 'fa-computer',
        fields: function (y) {
            return '<div class="ae-row">'
                + _aeField('half','Brand','allinoneBrand','text',true)
                + _aeField('half','Serial Number','allinoneSerial','text',false)
                + _aeField('half','Year Acquired','yearAcquired','number',true,null,null,{min:2000,max:2099,value:y})
                + _aeField('half','Processor','specificationProcessor','text',true)
                + _aeField('half','Memory (RAM)','specificationMemory','text',true)
                + _aeField('half','GPU','specificationGPU','text',true)
                + _aeField('full','Storage','specificationStorage','text',true)
                + '</div>';
        }
    },
    monitor: {
        label: 'Monitor', icon: 'fa-tv',
        fields: function (y) {
            return '<div class="ae-row">'
                + _aeField('half','Brand','monitorBrand','text',true)
                + _aeField('half','Size','monitorSize','text',false,'e.g. 24 inches')
                + _aeField('half','Serial Number','monitorSerial','text',true)
                + _aeField('half','Year Acquired','yearAcquired','number',false,null,null,{min:2000,max:2099,value:y})
                + '</div>';
        }
    },
    printer: {
        label: 'Printer', icon: 'fa-print',
        fields: function (y) {
            return '<div class="ae-row">'
                + _aeField('half','Brand','printerBrand','text',true)
                + _aeField('half','Model','printerModel','text',true)
                + _aeField('half','Serial Number','printerSerial','text',false)
                + _aeField('half','Year Acquired','yearAcquired','number',false,null,null,{min:2000,max:2099,value:y})
                + '</div>';
        }
    },
    otherequipment: {
        label: 'Other Equipment', icon: 'fa-server',
        fields: function (y) {
            return '<div class="ae-row">'
                + _aeField('half','Equipment Type','equipmentType','text',true,'e.g. Laptop, Projector')
                + _aeField('half','Brand','brand','text',false)
                + _aeField('half','Model','model','text',false)
                + _aeField('half','Serial Number','serialNumber','text',false)
                + _aeField('half','Year Acquired','yearAcquired','number',false,null,null,{min:2000,max:2099,value:y})
                + '</div>';
        }
    },
    software: {
        label: 'License', icon: 'fa-key',
        fields: function () {
            return '<div class="ae-row">'
                + _aeField('half','Software Name','licenseSoftware','text',true,'e.g. Microsoft Office 365')
                + _aeField('half','License Details','licenseDetails','text',true,'e.g. Business Basic')
                + _aeField('half','License Type','licenseType','select',false,null,['Perpetual','Subscription'])
                + _aeField('half','Expiry Date','expiryDate','date',false)
                + _aeField('half','Associated Email','email','email',false)
                + '</div>';
        }
    }
};

/** Helper — builds one labelled field div */
function _aeField(size, label, name, type, required, placeholder, options, attrs) {
    var col = size === 'full' ? 'ae-col-full' : 'ae-col-half';
    var req = required ? ' <span class="ae-req">*</span>' : '';
    var html = '<div class="' + col + '"><label class="ae-field-label">' + label + req + '</label>';

    if (type === 'select') {
        html += '<select name="' + name + '" class="ae-select"' + (required ? ' required' : '') + '>';
        (options || []).forEach(function (o) { html += '<option value="' + o + '">' + o + '</option>'; });
        html += '</select>';
    } else {
        var extra = '';
        if (attrs) {
            if (attrs.min   !== undefined) extra += ' min="'   + attrs.min   + '"';
            if (attrs.max   !== undefined) extra += ' max="'   + attrs.max   + '"';
            if (attrs.value !== undefined) extra += ' value="' + attrs.value + '"';
        }
        if (placeholder) extra += ' placeholder="' + placeholder + '"';
        html += '<input type="' + type + '" name="' + name + '" class="ae-input"'
              + (required ? ' required' : '') + extra + '>';
    }
    return html + '</div>';
}


/* ═══════════════════════════════════════════════════════════════════════════
   7. INJECT BUTTONS
   ═══════════════════════════════════════════════════════════════════════════ */
function _aeInjectButtons(employeeId) {
    var sections = [
        { containerId: 'equipment-grid',              type: 'dropdown' },
        { containerId: 'printers-grid',               type: 'printer'  },
        { containerId: 'software-licenses-container', type: 'software' },
    ];

    sections.forEach(function (sec) {
        var container = document.getElementById(sec.containerId);
        if (!container) return;
        var wrapper   = container.closest('.data-table-container');
        if (!wrapper)  return;
        var hdr       = wrapper.querySelector('.table-header');
        if (!hdr)      return;

        hdr.querySelectorAll('.ae-add-btn-group').forEach(function (el) { el.remove(); });

        var uid  = 'aeDD_' + employeeId;
        var html = '';

        if (sec.type === 'dropdown') {
            html = '<div class="ae-add-btn-group">'
                 + '<div class="ae-dropdown" id="' + uid + '">'
                 + '<button class="ae-dropdown-toggle" onclick="aeToggleDd(\'' + uid + '\',event)">'
                 + '<i class="fas fa-plus"></i> Add <i class="fas fa-chevron-down ae-caret"></i>'
                 + '</button>'
                 + '</div></div>';
        } else {
            var cfg = AE_TYPES[sec.type] || {};
            html = '<div class="ae-add-btn-group">'
                 + '<button class="ae-add-single-btn" onclick="openAddEquipmentModal(' + employeeId + ',\'' + sec.type + '\')">'
                 + '<i class="fas fa-plus"></i> Add ' + cfg.label 
                 + '</button></div>';
        }

        hdr.insertAdjacentHTML('beforeend', html);
    });

    var stale = document.getElementById('aeBodyMenu');
    if (stale) stale.remove();

    var menu = document.createElement('div');
    menu.id        = 'aeBodyMenu';
    menu.className = 'ae-dropdown-menu';
    menu.style.display = 'none'; // Starts hidden at the DOM level
    menu.innerHTML =
          '<button class="ae-dropdown-item" onclick="aePickType(' + employeeId + ',\'systemunit\')"><i class="fas fa-desktop"></i> System Unit</button>'
        + '<button class="ae-dropdown-item" onclick="aePickType(' + employeeId + ',\'allinone\')"><i class="fas fa-computer"></i> All-in-One PC</button>'
        + '<button class="ae-dropdown-item" onclick="aePickType(' + employeeId + ',\'monitor\')"><i class="fas fa-tv"></i> Monitor</button>'
        + '<button class="ae-dropdown-item" onclick="aePickType(' + employeeId + ',\'otherequipment\')"><i class="fas fa-server"></i> Other Equipment</button>';
    document.body.appendChild(menu);
}


/* ═══════════════════════════════════════════════════════════════════════════
   8. DROPDOWN HELPERS
   ═══════════════════════════════════════════════════════════════════════════ */
var _aeDdBtn = null;

function _aePositionMenu(btn) {
    var menu  = document.getElementById('aeBodyMenu');
    if (!menu || !btn) return;
    var rect  = btn.getBoundingClientRect();
    var menuW = 220;
    var viewW = window.innerWidth;

    var left = rect.right - menuW;
    if (left < 8) left = Math.max(8, (viewW - menuW) / 2);
    if (left + menuW > viewW - 8) left = viewW - menuW - 8;

    menu.style.top  = (rect.bottom + 6) + 'px';
    menu.style.left = left + 'px';
}

function aeToggleDd(uid, e) {
    e.stopPropagation();
    var dd   = document.getElementById(uid);
    var menu = document.getElementById('aeBodyMenu');
    if (!dd || !menu) return;

    var isOpen = dd.classList.contains('open');

    // Close everything first
    document.querySelectorAll('.ae-dropdown.open').forEach(function (el) { el.classList.remove('open'); });
    menu.classList.remove('show');
    menu.style.display = 'none'; // Instant hide
    _aeDdBtn = null;

    if (!isOpen) {
        dd.classList.add('open');
        _aeDdBtn = dd.querySelector('.ae-dropdown-toggle');

        menu.style.position = 'fixed';
        menu.style.width    = '220px';
        menu.style.zIndex   = '99999';
        menu.style.display  = 'block'; // Make visible to CSS
        _aePositionMenu(_aeDdBtn);
        menu.classList.add('show');
    }
}

/* Close the menu on any scroll — updated with display: none */
document.addEventListener('scroll', function () {
    var menu = document.getElementById('aeBodyMenu');
    if (menu && menu.classList.contains('show')) {
        menu.classList.remove('show');
        menu.style.display = 'none'; // Instant hide
        document.querySelectorAll('.ae-dropdown.open').forEach(function (el) { el.classList.remove('open'); });
        _aeDdBtn = null;
    }
}, true);

function aePickType(empId, type) {
    var menu = document.getElementById('aeBodyMenu');
    if (menu) {
        menu.classList.remove('show');
        menu.style.display = 'none'; // Instant hide
    }
    document.querySelectorAll('.ae-dropdown.open').forEach(function (el) { el.classList.remove('open'); });
    _aeDdBtn = null;
    openAddEquipmentModal(empId, type);
}


/* ═══════════════════════════════════════════════════════════════════════════
   9. MODAL OPEN / CLOSE
   ═══════════════════════════════════════════════════════════════════════════ */
function openAddEquipmentModal(employeeId, equipmentType) {
    var cfg = AE_TYPES[equipmentType];
    if (!cfg) { showAlert('warning', 'Unknown equipment type: ' + equipmentType); return; }

    _aeEmployeeId    = employeeId;
    _aeEquipmentType = equipmentType;
    _aeSelectedId    = null;

    document.getElementById('aeModalTitle').textContent = 'Add ' + cfg.label;
    document.getElementById('aeNewFields').innerHTML = cfg.fields(new Date().getFullYear());
    document.getElementById('aeNewForm').querySelectorAll('.ae-invalid').forEach(function (el) { el.classList.remove('ae-invalid'); });

    switchAddEquipmentMode('existing');
    _aeLoadItems(equipmentType);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('addEquipmentModal')).show();
}

function aeCloseModal() {
    var el = document.getElementById('addEquipmentModal');
    if (!el) return;
    
    var m = bootstrap.Modal.getInstance(el);
    if (m) m.hide();

    setTimeout(function() {
        if (!document.querySelector('.modal.show')) {
            document.querySelectorAll('.modal-backdrop').forEach(function(bd) { bd.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    }, 400);
}


/* ═══════════════════════════════════════════════════════════════════════════
   10. TAB SWITCH
   ═══════════════════════════════════════════════════════════════════════════ */
function switchAddEquipmentMode(mode) {
    _aeMode = mode;
    document.getElementById('aeExistingPane').style.display = mode === 'existing' ? '' : 'none';
    document.getElementById('aeNewPane').style.display      = mode === 'new'      ? '' : 'none';
    document.getElementById('aeTabExisting').classList.toggle('active', mode === 'existing');
    document.getElementById('aeTabNew').classList.toggle('active',      mode === 'new');
}


/* ═══════════════════════════════════════════════════════════════════════════
   11. LOAD & RENDER UNASSIGNED ITEMS
   ═══════════════════════════════════════════════════════════════════════════ */
function _aeLoadItems(type) {
    var el = document.getElementById('aeExistingList');
    el.innerHTML = '<div class="ae-empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading…</p></div>';

    fetch(BASE_URL + 'ajax/get_unassigned_equipment.php?type=' + encodeURIComponent(type))
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) {
                el.innerHTML = '<div class="ae-empty-state"><i class="fas fa-exclamation-circle" style="opacity:1;color:var(--color-warning)"></i><p>' + (d.message || 'Failed to load.') + '</p></div>';
                return;
            }
            _aeRenderItems(d.items || [], type);
        })
        .catch(function () {
            el.innerHTML = '<div class="ae-empty-state"><i class="fas fa-times-circle" style="opacity:1;color:var(--color-danger)"></i><p>Error loading inventory.</p></div>';
        });
}

function _aeRenderItems(items, type) {
    var el  = document.getElementById('aeExistingList');
    var cfg = AE_TYPES[type] || { icon: 'fa-box' };

    if (!items.length) {
        el.innerHTML = '<div class="ae-empty-state"><i class="fas fa-box-open"></i><p>No unassigned items available.</p></div>';
        return;
    }

    var html = '';
    items.forEach(function (item) {
        var brand  = (item.brand  || 'Unknown').replace(/</g, '&lt;');
        var serial = (item.serial || 'N/A').replace(/</g, '&lt;');
        var sub    = serial;
        if (item.extra) sub += ' &nbsp;&bull;&nbsp; ' + String(item.extra).replace(/</g, '&lt;');
        if (item.model) brand += ' &mdash; ' + String(item.model).replace(/</g, '&lt;');
        var search = (brand + ' ' + serial).toLowerCase().replace(/"/g, '');

        html += '<div class="ae-existing-item" data-id="' + item.id + '" data-search="' + search + '" onclick="aeSelectItem(this,' + item.id + ')">'
              + '<div class="ae-eq-icon"><i class="fas ' + cfg.icon + '"></i></div>'
              + '<div class="ae-eq-info"><div class="ae-eq-brand">' + brand + '</div>'
              + '<div class="ae-eq-serial"><i class="fas fa-barcode" style="margin-right:4px;color:var(--primary-green)"></i>' + sub + '</div></div>'
              + '<i class="fas fa-check-circle ae-eq-check"></i>'
              + '</div>';
    });
    el.innerHTML = html;
}

function filterAeExistingList() {
    var q = (document.getElementById('aeExistingSearch').value || '').toLowerCase().trim();
    document.querySelectorAll('#aeExistingList .ae-existing-item').forEach(function (el) {
        el.style.display = (!q || el.dataset.search.includes(q)) ? '' : 'none';
    });
}

function aeSelectItem(el, id) {
    document.querySelectorAll('#aeExistingList .ae-existing-item').forEach(function (i) { i.classList.remove('ae-selected'); });
    el.classList.add('ae-selected');
    _aeSelectedId = id;
}


/* ═══════════════════════════════════════════════════════════════════════════
   12. SAVE & ASSIGN
   ═══════════════════════════════════════════════════════════════════════════ */
function saveAddEquipment() {
    var btn = document.getElementById('aeSaveBtn');
    var fd  = new FormData();
    fd.append('employee_id',    _aeEmployeeId);
    fd.append('equipment_type', _aeEquipmentType);
    fd.append('mode',           _aeMode);

    if (_aeMode === 'existing') {
        if (!_aeSelectedId) { showAlert('warning', 'Please select an item first.'); return; }
        fd.append('equipment_id', _aeSelectedId);
    } else {
        var form    = document.getElementById('aeNewForm');
        var invalid = false;
        form.querySelectorAll('.ae-input[required], .ae-select[required]').forEach(function (inp) {
            if (!inp.value.trim()) { inp.classList.add('ae-invalid'); invalid = true; }
            else                  { inp.classList.remove('ae-invalid'); }
        });
        if (invalid) { showAlert('warning', 'Please fill in all required fields.'); return; }
        new FormData(form).forEach(function (v, k) { fd.append(k, v); });
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    fetch(BASE_URL + 'ajax/add_equipment_to_employee.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save &amp; Assign';
            if (d.success) {
                aeCloseModal();
                showAlert('success', d.message || 'Equipment assigned successfully!');
                if (typeof viewEmployee === 'function' && _aeEmployeeId) viewEmployee(_aeEmployeeId);
            } else {
                showAlert('danger', d.message || 'Failed to assign equipment.');
            }
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save &amp; Assign';
            console.error('[AE]', err);
            showAlert('danger', 'An unexpected error occurred.');
        });
}