/**
 * equipment-unified.js — Unified Equipment Table & By-Location View
 * Replaces the multi-tab layout with a single filterable table + location grouping.
 * Depends on: utils.js (escapeHtml, renderPaginationControls, updateRowCounters), equipment.js (CRUD functions)
 */
var EqUnified = (function() {
    'use strict';

    // State
    var currentView = 'all';
    var currentPage = 1;
    var perPage = (typeof defaultPerPage !== 'undefined') ? defaultPerPage : 25;
    var filteredData = [];
    var locationTreeCache = {};

    // Computer type names for the "Computers" view filter
    var computerTypes = ['System Unit', 'Monitor', 'All-in-One'];

    // Type icon map
    var typeIcons = {
        'System Unit': 'fa-tower-broadcast',
        'Monitor': 'fa-tv',
        'All-in-One': 'fa-computer',
        'Printer': 'fa-print'
    };

    function init() {
        // Set per-page select to match default
        var ppSel = document.getElementById('unifiedPerPage');
        if (ppSel) {
            for (var i = 0; i < ppSel.options.length; i++) {
                if (parseInt(ppSel.options[i].value) === perPage) {
                    ppSel.selectedIndex = i;
                    break;
                }
            }
        }
        applyFilters();
    }

    // ─── VIEW SWITCHING ────────────────────────────────
    function switchView(view) {
        currentView = view;
        currentPage = 1;

        var tableView = document.getElementById('tableView');
        var locationView = document.getElementById('locationView');
        var typeGroup = document.getElementById('typeFilterGroup');
        var statusGroup = document.getElementById('statusFilterGroup');
        var searchGroup = document.getElementById('searchGroup');
        var addGroup = document.getElementById('addBtnGroup');
        var subtitle = document.getElementById('viewSubtitle');
        var tableTitle = document.getElementById('tableTitle');

        if (view === 'location') {
            tableView.style.display = 'none';
            locationView.style.display = 'block';
            typeGroup.style.display = 'none';
            statusGroup.style.display = 'none';
            searchGroup.style.display = 'none';
            addGroup.style.display = 'none';
            subtitle.textContent = 'Equipment grouped by organizational location';
            tableTitle.textContent = 'By Location';
        } else {
            tableView.style.display = '';
            locationView.style.display = 'none';
            typeGroup.style.display = '';
            statusGroup.style.display = '';
            searchGroup.style.display = '';
            addGroup.style.display = '';

            // Reset type filter based on view preset
            var typeFilter = document.getElementById('typeFilter');
            if (view === 'computers') {
                typeFilter.value = '';
                subtitle.textContent = 'System Units, Monitors, and All-in-One PCs';
                tableTitle.textContent = 'Computer Equipment';
            } else if (view === 'printers') {
                typeFilter.value = 'Printer';
                subtitle.textContent = 'Printer inventory';
                tableTitle.textContent = 'Printer Inventory';
            } else if (view === 'other') {
                typeFilter.value = '';
                subtitle.textContent = 'Peripherals, networking, and miscellaneous equipment';
                tableTitle.textContent = 'Other Equipment';
            } else {
                typeFilter.value = '';
                subtitle.textContent = 'All equipment across all categories';
                tableTitle.textContent = 'Equipment List';
            }

            applyFilters();
        }
    }

    // ─── FILTERING & RENDERING ─────────────────────────
    function applyFilters() {
        currentPage = 1;
        var typeVal = (document.getElementById('typeFilter').value || '').toLowerCase();
        var statusVal = (document.getElementById('statusFilter').value || '').toLowerCase();
        var searchVal = (document.getElementById('unifiedSearch').value || '').toLowerCase().trim();

        filteredData = (typeof allEquipmentData !== 'undefined' ? allEquipmentData : []).filter(function(eq) {
            // View-based pre-filter
            if (currentView === 'computers' && computerTypes.indexOf(eq.typeName) === -1) return false;
            if (currentView === 'printers' && eq.typeName !== 'Printer') return false;
            if (currentView === 'other' && (computerTypes.indexOf(eq.typeName) !== -1 || eq.typeName === 'Printer')) return false;

            // Type filter
            if (typeVal && eq.typeName.toLowerCase() !== typeVal) return false;

            // Status filter
            if (statusVal && eq.status.toLowerCase() !== statusVal) return false;

            // Search
            if (searchVal) {
                var haystack = [
                    eq.serial, eq.brand, eq.model, eq.typeName,
                    eq.employeeName, eq.location_name, eq.year,
                    specsString(eq.specs)
                ].join(' ').toLowerCase();
                if (haystack.indexOf(searchVal) === -1) return false;
            }

            return true;
        });

        updateStats();
        renderTable();
    }

    function specsString(specs) {
        if (!specs) return '';
        var parts = [];
        for (var k in specs) {
            if (specs.hasOwnProperty(k)) parts.push(specs[k]);
        }
        return parts.join(' ');
    }

    function updateStats() {
        var data = filteredData;
        document.getElementById('statTotal').textContent = data.length;
        document.getElementById('statActive').textContent = data.filter(function(r) { return r.employee_id; }).length;
        document.getElementById('statAvailable').textContent = data.filter(function(r) {
            return !r.employee_id && r.status === 'Available';
        }).length;
        document.getElementById('statMaint').textContent = data.filter(function(r) {
            return r.status === 'Under Maintenance';
        }).length;
    }

    function renderTable() {
        var tbody = document.getElementById('unifiedTableBody');
        var total = filteredData.length;
        var totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        var start = (currentPage - 1) * perPage;
        var end = Math.min(start + perPage, total);
        var pageData = filteredData.slice(start, end);

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="empty-state"><i class="fas fa-inbox"></i><p>No equipment found matching your filters</p></td></tr>';
        } else {
            var html = '';
            for (var i = 0; i < pageData.length; i++) {
                html += buildRow(pageData[i], start + i + 1);
            }
            tbody.innerHTML = html;
        }

        // Record count
        var countEl = document.getElementById('unifiedRecordCount');
        if (countEl) {
            countEl.innerHTML = 'Showing <strong>' + (total === 0 ? 0 : start + 1) + '&ndash;' + end + '</strong> of <strong>' + total + '</strong> equipment';
        }

        // Pagination
        if (typeof renderPaginationControls === 'function') {
            renderPaginationControls('unifiedPagination', currentPage, totalPages, 'EqUnified.goToPage');
        }
    }

    function buildRow(eq, num) {
        var icon = typeIcons[eq.typeName] || 'fa-server';
        var statusClass = eq.status.toLowerCase().replace(/\s+/g, '-');

        // Specs column — show relevant specs
        var specsHtml = '—';
        if (eq.specs) {
            var specParts = [];
            if (eq.specs.Processor) specParts.push('<div class="spec-item"><i class="fas fa-microchip"></i><span class="spec-value">' + escapeHtml(eq.specs.Processor) + '</span></div>');
            if (eq.specs.Memory) specParts.push('<div class="spec-item"><i class="fas fa-memory"></i><span class="spec-value">' + escapeHtml(eq.specs.Memory) + '</span></div>');
            if (eq.specs.Storage) specParts.push('<div class="spec-item"><i class="fas fa-hdd"></i><span class="spec-value">' + escapeHtml(eq.specs.Storage) + '</span></div>');
            if (eq.specs['Monitor Size']) specParts.push('<div class="spec-item"><i class="fas fa-expand"></i><span class="spec-value">' + escapeHtml(eq.specs['Monitor Size']) + '</span></div>');
            if (specParts.length > 0) specsHtml = specParts.join('');
        }

        // Location
        var locHtml = eq.location_name
            ? '<div class="location-badge"><i class="fas fa-map-marker-alt"></i> ' + escapeHtml(eq.location_name) + '</div>'
            : '<span style="color:var(--text-light);font-style:italic">\u2014</span>';

        // Assigned To
        var empHtml = eq.employeeName
            ? '<div style="font-weight:600;color:var(--text-dark)">' + escapeHtml(eq.employeeName) + '</div>'
            : '<span style="color:var(--text-light);font-style:italic">Unassigned</span>';

        // Maintenance
        var maintHtml = eq.lastMaint
            ? '<div class="maintenance-info"><i class="fas fa-tools"></i>' + escapeHtml(formatDate(eq.lastMaint)) + '</div>'
            : '<span class="text-muted"><i class="fas fa-clock"></i> No record</span>';

        // Actions — route to correct edit/delete based on type
        var actionHtml = buildActions(eq);

        return '<tr>' +
            '<td>' + num + '</td>' +
            '<td><div class="eq-type-badge"><i class="fas ' + icon + '"></i> ' + escapeHtml(eq.typeName) + '</div></td>' +
            '<td><span class="serial-number">' + escapeHtml(eq.serial || 'N/A') + '</span></td>' +
            '<td><div style="font-weight:600;color:var(--text-dark)">' + escapeHtml(eq.brand || '') + '</div>' +
                (eq.model ? '<div style="font-size:12px;color:var(--text-light)">' + escapeHtml(eq.model) + '</div>' : '') + '</td>' +
            '<td>' + specsHtml + '</td>' +
            '<td>' + escapeHtml(eq.year || 'N/A') + '</td>' +
            '<td>' + locHtml + '</td>' +
            '<td>' + empHtml + '</td>' +
            '<td>' + maintHtml + '</td>' +
            '<td><span class="status-badge status-' + statusClass + '">' + escapeHtml(eq.status) + '</span></td>' +
            '<td>' + actionHtml + '</td>' +
            '</tr>';
    }

    function buildActions(eq) {
        var editFn, deleteFn;
        switch (eq.typeName) {
            case 'System Unit':
                editFn = 'editSystemUnit(' + eq.id + ')';
                deleteFn = 'deleteSystemUnit(' + eq.id + ')';
                break;
            case 'Monitor':
                editFn = 'editMonitor(' + eq.id + ')';
                deleteFn = 'deleteMonitor(' + eq.id + ')';
                break;
            case 'All-in-One':
                editFn = 'editAllInOne(' + eq.id + ')';
                deleteFn = 'deleteAllInOne(' + eq.id + ')';
                break;
            case 'Printer':
                editFn = 'editPrinter(' + eq.id + ')';
                deleteFn = 'deletePrinter(' + eq.id + ')';
                break;
            default:
                editFn = 'editOtherEquipment(' + eq.id + ')';
                deleteFn = 'deleteOtherEquipment(' + eq.id + ')';
                break;
        }
        return '<div class="action-buttons">' +
            '<button class="btn-icon btn-view" title="View" onclick="EqUnified.viewEquipment(' + eq.id + ', \'' + escapeHtml(eq.typeName).replace(/'/g, "\\'") + '\')"><i class="fas fa-eye"></i></button>' +
            '<button class="btn-icon" title="Edit" onclick="' + editFn + '"><i class="fas fa-edit"></i></button>' +
            '<button class="btn-icon btn-danger" title="Delete" onclick="' + deleteFn + '"><i class="fas fa-trash"></i></button>' +
            '</div>';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    function goToPage(page) {
        var totalPages = Math.max(1, Math.ceil(filteredData.length / perPage));
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
    }

    function changePerPage() {
        perPage = parseInt(document.getElementById('unifiedPerPage').value);
        currentPage = 1;
        renderTable();
    }

    // ─── BY LOCATION VIEW ──────────────────────────────
    function loadLocationTree() {
        var divId = document.getElementById('locDivisionSelect').value;
        var content = document.getElementById('locationContent');

        if (!divId) {
            content.innerHTML = '<div class="loc-empty-state"><i class="fas fa-map-marked-alt"></i><h3>Select a Division</h3><p>Choose a division above to see equipment grouped by section and unit.</p></div>';
            return;
        }

        content.innerHTML = '<div class="loc-loading"><i class="fas fa-spinner fa-spin"></i> Loading location tree...</div>';

        fetch('../ajax/get_location_equipment.php?division_id=' + encodeURIComponent(divId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    content.innerHTML = '<div class="loc-empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error</h3><p>' + escapeHtml(data.message) + '</p></div>';
                    return;
                }
                locationTreeCache = data;
                renderLocationTree(data);
            })
            .catch(function(err) {
                content.innerHTML = '<div class="loc-empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading data</h3><p>' + escapeHtml(err.message || String(err)) + '</p></div>';
            });
    }

    function renderLocationTree(data) {
        var content = document.getElementById('locationContent');
        var sections = data.sections || [];

        if (sections.length === 0) {
            content.innerHTML = '<div class="loc-empty-state"><i class="fas fa-folder-open"></i><h3>No sections found</h3><p>This division has no sections or units with equipment.</p></div>';
            return;
        }

        var html = '';
        sections.forEach(function(section) {
            var sectionTotal = 0;

            // Count equipment in section itself
            if (section.equipment && section.equipment.length > 0) {
                sectionTotal += section.equipment.length;
            }

            // Count equipment in units
            (section.units || []).forEach(function(unit) {
                sectionTotal += (unit.equipment || []).length;
            });

            html += '<div class="loc-section" data-section-name="' + escapeHtml(section.name).toLowerCase() + '">';
            html += '<div class="loc-section-header" onclick="EqUnified.toggleSection(this)">';
            html += '<div class="loc-section-title">';
            html += '<i class="fas fa-chevron-right loc-chevron"></i>';
            html += '<i class="fas fa-sitemap"></i> ' + escapeHtml(section.name);
            html += '<span class="loc-count-badge">' + sectionTotal + ' equipment</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="loc-section-body" style="display:none;">';

            // Equipment directly under the section (if any)
            if (section.equipment && section.equipment.length > 0) {
                html += buildLocationEquipmentTable(section.equipment, section.name);
            }

            // Units under this section
            (section.units || []).forEach(function(unit) {
                var unitEquip = unit.equipment || [];
                    html += '<div class="loc-unit" data-unit-name="' + escapeHtml(unit.name).toLowerCase() + '">';
                html += '<div class="loc-unit-header">';
                html += '<div class="loc-unit-title">';
                html += '<i class="fas fa-folder"></i> ' + escapeHtml(unit.name);
                html += '<span class="loc-count-badge">' + unitEquip.length + '</span>';
                html += '</div>';
                html += '</div>';
                if (unitEquip.length > 0) {
                    html += buildLocationEquipmentTable(unitEquip, unit.name);
                } else {
                    html += '<div class="loc-no-equip"><i class="fas fa-info-circle"></i> No equipment in this unit</div>';
                }
                html += '</div>';
            });

            html += '</div></div>';
        });

        content.innerHTML = html;
    }

    function buildLocationEquipmentTable(equipment, locationName) {
        var html = '<div class="loc-equip-table"><table><thead><tr>' +
            '<th>#</th><th>Type</th><th>Serial</th><th>Brand / Model</th>' +
            '<th>Assigned To</th><th>Last Maintenance</th><th>Actions</th>' +
            '</tr></thead><tbody>';

        equipment.forEach(function(eq, idx) {
            var icon = typeIcons[eq.typeName] || 'fa-server';
            var maintHtml = eq.lastMaint
                ? '<span class="text-success"><i class="fas fa-check-circle"></i> ' + escapeHtml(formatDate(eq.lastMaint)) + '</span>'
                : '<span class="text-muted"><i class="fas fa-clock"></i> None</span>';

            html += '<tr>' +
                '<td>' + (idx + 1) + '</td>' +
                '<td><i class="fas ' + icon + '" style="color:var(--primary-green);margin-right:4px"></i>' + escapeHtml(eq.typeName) + '</td>' +
                '<td><span class="serial-number">' + escapeHtml(eq.serial || 'N/A') + '</span></td>' +
                '<td><strong>' + escapeHtml(eq.brand || '') + '</strong>' + (eq.model ? ' <span style="color:var(--text-light)">' + escapeHtml(eq.model) + '</span>' : '') + '</td>' +
                '<td>' + (eq.employeeName ? escapeHtml(eq.employeeName) : '<span class="text-muted">Unassigned</span>') + '</td>' +
                '<td>' + maintHtml + '</td>' +
                '<td>' + buildActions(eq) + '</td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function toggleSection(headerEl) {
        var body = headerEl.nextElementSibling;
        var chevron = headerEl.querySelector('.loc-chevron');
        if (body.style.display === 'none') {
            body.style.display = 'block';
            chevron.classList.remove('fa-chevron-right');
            chevron.classList.add('fa-chevron-down');
            headerEl.closest('.loc-section').classList.add('loc-section-open');
        } else {
            body.style.display = 'none';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-right');
            headerEl.closest('.loc-section').classList.remove('loc-section-open');
        }
    }

    function filterLocationResults() {
        var term = (document.getElementById('locSearch').value || '').toLowerCase().trim();
        var sections = document.querySelectorAll('.loc-section');
        sections.forEach(function(sec) {
            var sectionName = sec.dataset.sectionName || '';
            var units = sec.querySelectorAll('.loc-unit');
            var sectionMatch = sectionName.indexOf(term) !== -1;
            var anyUnitMatch = false;

            units.forEach(function(u) {
                var unitName = u.dataset.unitName || '';
                var unitMatch = unitName.indexOf(term) !== -1;
                if (unitMatch || sectionMatch || !term) {
                    u.style.display = '';
                    anyUnitMatch = true;
                } else {
                    u.style.display = 'none';
                }
            });

            sec.style.display = (sectionMatch || anyUnitMatch || !term) ? '' : 'none';

            // Auto-expand matching sections
            if (term && (sectionMatch || anyUnitMatch)) {
                var body = sec.querySelector('.loc-section-body');
                var chevron = sec.querySelector('.loc-chevron');
                if (body) body.style.display = 'block';
                if (chevron) {
                    chevron.classList.remove('fa-chevron-right');
                    chevron.classList.add('fa-chevron-down');
                }
                sec.classList.add('loc-section-open');
            }
        });
    }

    // ─── VIEW EQUIPMENT DETAIL ──────────────────────────
    function viewEquipment(id, typeName) {
        var contentEl = document.getElementById('viewEquipmentContent');
        var titleEl = document.getElementById('viewEquipmentModalTitle');
        var subtitleEl = document.getElementById('viewEquipmentSubtitle');
        var iconEl = document.getElementById('viewEquipmentIcon');
        var editBtn = document.getElementById('viewEquipmentEditBtn');
        contentEl.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';
        titleEl.textContent = typeName + ' Details';
        subtitleEl.textContent = '';

        // Set type icon
        var iconClass = typeIcons[typeName] || 'fa-server';
        iconEl.innerHTML = '<i class="fas ' + iconClass + '"></i>';

        // Determine which endpoint & param to use
        var endpoint, param;
        switch (typeName) {
            case 'System Unit': endpoint = 'manage_systemunit.php'; param = 'systemunit_id'; break;
            case 'Monitor':     endpoint = 'manage_monitor.php';    param = 'monitor_id'; break;
            case 'All-in-One':  endpoint = 'manage_allinone.php';   param = 'allinone_id'; break;
            case 'Printer':     endpoint = 'manage_printer.php';    param = 'printer_id'; break;
            default:            endpoint = 'manage_otherequipment.php'; param = 'id'; break;
        }

        // Wire Edit button
        editBtn.onclick = function() {
            bootstrap.Modal.getInstance(document.getElementById('viewEquipmentModal')).hide();
            switch (typeName) {
                case 'System Unit': editSystemUnit(id); break;
                case 'Monitor':     editMonitor(id); break;
                case 'All-in-One':  editAllInOne(id); break;
                case 'Printer':     editPrinter(id); break;
                default:            editOtherEquipment(id); break;
            }
        };

        new bootstrap.Modal(document.getElementById('viewEquipmentModal')).show();

        fetch('../ajax/' + endpoint + '?action=get&' + param + '=' + encodeURIComponent(id))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) { contentEl.innerHTML = '<div class="p-4 text-danger"><i class="fas fa-exclamation-triangle"></i> ' + escapeHtml(data.message) + '</div>'; return; }
                var d = data.data;
                var brand = d.systemUnitBrand || d.monitorBrand || d.allinoneBrand || d.printerBrand || d.brand || '';
                var serial = d.systemUnitSerial || d.monitorSerial || d.allinoneSerial || d.printerSerial || d.serialNumber || '';
                subtitleEl.textContent = brand + (serial ? ' · ' + serial : '');
                contentEl.innerHTML = buildViewDetailHtml(d, typeName);
            })
            .catch(function(err) {
                contentEl.innerHTML = '<div class="p-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading details</div>';
            });
    }

    function buildViewDetailHtml(d, typeName) {
        var brand = d.systemUnitBrand || d.monitorBrand || d.allinoneBrand || d.printerBrand || d.brand || '';
        var model = d.printerModel || d.model || '';
        var serial = d.systemUnitSerial || d.monitorSerial || d.allinoneSerial || d.printerSerial || d.serialNumber || '';
        var year = d.yearAcquired || '';
        var status = d.status || 'N/A';
        var statusClass = status.toLowerCase().replace(/\s+/g, '-');

        var html = '';

        // ── Status Banner ──
        html += '<div class="view-status-banner status-bg-' + statusClass + '">';
        html += '<span class="status-badge status-' + statusClass + '" style="font-size:13px;padding:5px 14px;">' + escapeHtml(status) + '</span>';
        if (d.employeeName) {
            html += '<span class="view-assigned-to"><i class="fas fa-user"></i> ' + escapeHtml(d.employeeName) + '</span>';
        } else if (d.location_name) {
            html += '<span class="view-assigned-to"><i class="fas fa-map-marker-alt"></i> ' + escapeHtml(d.location_name) + '</span>';
        } else {
            html += '<span class="view-assigned-to text-muted"><i class="fas fa-minus-circle"></i> Unassigned</span>';
        }
        html += '</div>';

        // ── Info Cards Row ──
        html += '<div class="view-cards-row">';
        if (brand) {
            html += '<div class="view-info-card"><div class="view-info-card-label">Brand</div><div class="view-info-card-value">' + escapeHtml(brand) + '</div></div>';
        }
        if (model) {
            html += '<div class="view-info-card"><div class="view-info-card-label">Model</div><div class="view-info-card-value">' + escapeHtml(model) + '</div></div>';
        }
        if (serial) {
            html += '<div class="view-info-card"><div class="view-info-card-label">Serial Number</div><div class="view-info-card-value"><code>' + escapeHtml(serial) + '</code></div></div>';
        }
        if (year) {
            html += '<div class="view-info-card"><div class="view-info-card-label">Year Acquired</div><div class="view-info-card-value">' + escapeHtml(String(year)) + '</div></div>';
        }
        html += '</div>';

        // ── Specifications Section (type-specific) ──
        var specs = [];
        if (d.systemUnitCategory) specs.push({icon: 'fa-layer-group', label: 'Category', value: d.systemUnitCategory});
        if (d.specificationProcessor) specs.push({icon: 'fa-microchip', label: 'Processor', value: d.specificationProcessor});
        if (d.specificationMemory) specs.push({icon: 'fa-memory', label: 'Memory', value: d.specificationMemory});
        if (d.specificationGPU || d.specificationGpu) specs.push({icon: 'fa-display', label: 'GPU', value: d.specificationGPU || d.specificationGpu});
        if (d.specificationStorage) specs.push({icon: 'fa-hdd', label: 'Storage', value: d.specificationStorage});
        if (d.monitorSize) specs.push({icon: 'fa-expand', label: 'Screen Size', value: d.monitorSize});
        if (d.details) specs.push({icon: 'fa-info-circle', label: 'Details', value: d.details});

        if (specs.length > 0) {
            html += '<div class="view-section">';
            html += '<div class="view-section-title"><i class="fas fa-cogs"></i> Specifications</div>';
            html += '<div class="view-specs-grid">';
            for (var i = 0; i < specs.length; i++) {
                html += '<div class="view-spec-item">';
                html += '<i class="fas ' + specs[i].icon + '"></i>';
                html += '<div><div class="view-spec-label">' + escapeHtml(specs[i].label) + '</div>';
                html += '<div class="view-spec-value">' + escapeHtml(specs[i].value) + '</div></div>';
                html += '</div>';
            }
            html += '</div></div>';
        }

        // ── Maintenance Section ──
        var hasLastMaint = d.maintenanceDate;
        var hasNextMaint = d.nextMaintenanceDate;
        if (hasLastMaint || hasNextMaint) {
            html += '<div class="view-section">';
            html += '<div class="view-section-title"><i class="fas fa-tools"></i> Maintenance</div>';
            html += '<div class="view-maint-row">';
            if (hasLastMaint) {
                html += '<div class="view-maint-card">';
                html += '<div class="view-maint-icon last"><i class="fas fa-check-circle"></i></div>';
                html += '<div><div class="view-maint-label">Last Maintenance</div>';
                html += '<div class="view-maint-date">' + escapeHtml(formatDate(d.maintenanceDate)) + '</div></div>';
                html += '</div>';
            }
            if (hasNextMaint) {
                var isOverdue = new Date(d.nextMaintenanceDate) < new Date();
                html += '<div class="view-maint-card' + (isOverdue ? ' overdue' : '') + '">';
                html += '<div class="view-maint-icon next' + (isOverdue ? ' overdue' : '') + '"><i class="fas ' + (isOverdue ? 'fa-exclamation-triangle' : 'fa-calendar-check') + '"></i></div>';
                html += '<div><div class="view-maint-label">' + (isOverdue ? 'Overdue' : 'Next Scheduled') + '</div>';
                html += '<div class="view-maint-date">' + escapeHtml(formatDate(d.nextMaintenanceDate)) + '</div></div>';
                html += '</div>';
            }
            html += '</div></div>';
        } else {
            html += '<div class="view-section">';
            html += '<div class="view-section-title"><i class="fas fa-tools"></i> Maintenance</div>';
            html += '<div class="view-maint-empty"><i class="fas fa-clock"></i> No maintenance dates recorded</div>';
            html += '</div>';
        }

        // ── Assignment Section ──
        html += '<div class="view-section">';
        html += '<div class="view-section-title"><i class="fas fa-link"></i> Assignment</div>';
        if (d.employeeName) {
            html += '<div class="view-assignment"><i class="fas fa-user-check text-primary"></i> <strong>' + escapeHtml(d.employeeName) + '</strong></div>';
        }
        if (d.location_name) {
            html += '<div class="view-assignment"><i class="fas fa-map-marker-alt text-danger"></i> ' + escapeHtml(d.location_name) + '</div>';
        }
        if (!d.employeeName && !d.location_name) {
            html += '<div class="view-assignment text-muted"><i class="fas fa-minus-circle"></i> Not assigned to any employee or location</div>';
        }
        html += '</div>';

        return html;
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 0);
    }

    // Public API
    return {
        switchView: switchView,
        applyFilters: applyFilters,
        goToPage: goToPage,
        changePerPage: changePerPage,
        loadLocationTree: loadLocationTree,
        toggleSection: toggleSection,
        filterLocationResults: filterLocationResults,
        viewEquipment: viewEquipment
    };
})();
