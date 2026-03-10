
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

    // State for batch scheduling
    var batchScheduleLocationId = null;
    var batchScheduleLocationName = '';
    var batchScheduleStats = null;

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
            html += '<div class="loc-section-header">';
            html += '<div class="loc-section-title" onclick="EqUnified.toggleSection(this.parentElement)">';
            html += '<i class="fas fa-chevron-right loc-chevron"></i>';
            html += '<i class="fas fa-sitemap"></i> ' + escapeHtml(section.name);
            html += '<span class="loc-count-badge">' + sectionTotal + ' equipment</span>';
            var secStats = section.schedule_stats || {};
            if (secStats.unscheduled > 0) {
                html += '<span class="loc-count-badge" style="background:var(--color-warning);color:#000;">' + secStats.unscheduled + ' unscheduled</span>';
            }
            html += '</div>';
            html += '<button class="btn btn-sm btn-outline-success loc-schedule-btn" onclick="event.stopPropagation(); EqUnified.openBatchSchedule(' + section.location_id + ', \'' + escapeHtml(section.name).replace(/'/g, "\\'") + '\')" title="Schedule maintenance for this section">';
            html += '<i class="fas fa-calendar-check"></i> Schedule';
            html += '</button>';
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
                var unitStats = unit.schedule_stats || {};
                if (unitStats.unscheduled > 0) {
                    html += '<span class="loc-count-badge" style="background:var(--color-warning);color:#000;">' + unitStats.unscheduled + ' unscheduled</span>';
                }
                html += '</div>';
                if (unitEquip.length > 0) {
                    html += '<button class="btn btn-sm btn-outline-success loc-schedule-btn" onclick="event.stopPropagation(); EqUnified.openBatchSchedule(' + unit.location_id + ', \'' + escapeHtml(unit.name).replace(/'/g, "\\'") + '\')" title="Schedule maintenance for this unit">';
                    html += '<i class="fas fa-calendar-check"></i> Schedule';
                    html += '</button>';
                }
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

            // Also check equipment rows within the section's own table (not inside units)
            var sectionTables = sec.querySelectorAll(':scope > .loc-section-body > .loc-equip-table');
            var anySecRowMatch = filterEquipmentRows(sectionTables, term);

            units.forEach(function(u) {
                var unitName = u.dataset.unitName || '';
                var unitMatch = unitName.indexOf(term) !== -1;

                // Check equipment rows within this unit
                var unitTables = u.querySelectorAll('.loc-equip-table');
                var anyRowMatch = filterEquipmentRows(unitTables, term);

                if (unitMatch || anyRowMatch || sectionMatch || !term) {
                    u.style.display = '';
                    anyUnitMatch = true;
                } else {
                    u.style.display = 'none';
                }
            });

            sec.style.display = (sectionMatch || anyUnitMatch || anySecRowMatch || !term) ? '' : 'none';

            // Auto-expand matching sections
            if (term && (sectionMatch || anyUnitMatch || anySecRowMatch)) {
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

    function filterEquipmentRows(tables, term) {
        var anyMatch = false;
        tables.forEach(function(table) {
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                if (!term) {
                    row.style.display = '';
                    anyMatch = true;
                    return;
                }
                var text = (row.textContent || '').toLowerCase();
                if (text.indexOf(term) !== -1) {
                    row.style.display = '';
                    anyMatch = true;
                } else {
                    row.style.display = 'none';
                }
            });
        });
        return anyMatch;
    }

    // ─── BATCH SCHEDULE FOR LOCATION ────────────────
    function openBatchSchedule(locationId, locationName) {
        batchScheduleLocationId = locationId;
        batchScheduleLocationName = locationName;
        batchScheduleStats = null;

        // Show config step, hide result
        document.getElementById('locBatchConfig').style.display = 'block';
        document.getElementById('locBatchResult').style.display = 'none';
        document.getElementById('locBatchConfirmBtn').style.display = 'inline-flex';
        document.getElementById('locBatchCancelBtn').textContent = 'Cancel';

        document.getElementById('locBatchTitle').innerHTML = '<i class="fas fa-calendar-check me-2"></i> Schedule Maintenance — ' + escapeHtml(locationName);

        // Set default start date to 7 days from now
        var defaultDate = new Date();
        defaultDate.setDate(defaultDate.getDate() + 7);
        document.getElementById('locBatchStartDate').value = defaultDate.toISOString().split('T')[0];

        // Load stats for this location
        var infoEl = document.getElementById('locBatchInfo');
        infoEl.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading stats…</div>';

        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('locBatchScheduleModal'));
        modal.show();

        fetch('../ajax/get_location_equipment.php?location_stats=' + encodeURIComponent(locationId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    infoEl.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + escapeHtml(data.message) + '</div>';
                    return;
                }
                batchScheduleStats = data;
                infoEl.innerHTML = '<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">'
                    + '<div>'
                    + '<div style="font-size:var(--text-base); font-weight:600; color:var(--text-dark);"><i class="fas fa-map-marker-alt" style="color:var(--primary-green);"></i> ' + escapeHtml(locationName) + '</div>'
                    + '</div>'
                    + '<div style="display:flex; gap:12px; font-size:var(--text-sm);">'
                    + '<span><strong>' + data.total + '</strong> total</span>'
                    + '<span style="color:var(--color-success);"><strong>' + data.scheduled + '</strong> scheduled</span>'
                    + '<span style="color:var(--color-warning);"><strong>' + data.unscheduled + '</strong> unscheduled</span>'
                    + '</div>'
                    + '</div>';
            })
            .catch(function(err) {
                infoEl.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading stats</div>';
            });
    }

    function executeBatchSchedule() {
        if (!batchScheduleLocationId) return;

        var startDate = document.getElementById('locBatchStartDate').value;
        var frequency = document.getElementById('locBatchFrequency').value;

        if (!startDate) {
            if (typeof Alerts !== 'undefined') {
                Alerts.warning('Please select a start date.');
            } else {
                alert('Please select a start date.');
            }
            return;
        }

        // Switch to result view
        document.getElementById('locBatchConfig').style.display = 'none';
        document.getElementById('locBatchResult').style.display = 'block';
        document.getElementById('locBatchConfirmBtn').style.display = 'none';
        document.getElementById('locBatchResultContent').innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Creating schedules…</div>';

        fetch('../ajax/batch_initialize_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                locationId: batchScheduleLocationId,
                startDate: startDate,
                frequency: frequency
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(j) {
            if (!j.success) {
                document.getElementById('locBatchResultContent').innerHTML = '<div class="text-center py-4">'
                    + '<div style="font-size:48px; color:var(--color-danger); margin-bottom:12px;"><i class="fas fa-times-circle"></i></div>'
                    + '<h5 style="color:var(--text-dark);">Failed</h5>'
                    + '<p style="color:var(--text-light);">' + escapeHtml(j.message) + '</p>'
                    + '</div>';
                return;
            }

            document.getElementById('locBatchResultContent').innerHTML = '<div class="text-center py-4">'
                + '<div style="font-size:48px; color:var(--color-success); margin-bottom:12px;"><i class="fas fa-check-circle"></i></div>'
                + '<h5 style="color:var(--text-dark);">Schedules Created!</h5>'
                + '<p style="color:var(--text-light); margin-bottom:16px;">' + escapeHtml(j.message) + '</p>'
                + '<div style="display:flex; justify-content:center; gap:24px; font-size:var(--text-sm);">'
                + '<div><span style="font-size:24px; font-weight:700; color:var(--color-success);">' + j.created + '</span><br>Created</div>'
                + '<div><span style="font-size:24px; font-weight:700; color:var(--text-light);">' + j.skipped + '</span><br>Skipped</div>'
                + '<div><span style="font-size:24px; font-weight:700; color:var(--primary-green);">' + j.total + '</span><br>Total</div>'
                + '</div>'
                + '</div>';

            document.getElementById('locBatchCancelBtn').textContent = 'Close';

            // Refresh the location tree to update schedule counts
            loadLocationTree();
        })
        .catch(function(e) {
            document.getElementById('locBatchResultContent').innerHTML = '<div class="text-center py-4 text-danger">'
                + '<i class="fas fa-exclamation-triangle"></i> Network error: ' + escapeHtml(e.message || String(e))
                + '</div>';
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

        // Use unified equipment endpoint
        var endpoint = 'manage_equipment.php';
        var param = 'equipment_id';

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
                var brand = d.brand || '';
                var serial = d.serial_number || '';
                subtitleEl.textContent = brand + (serial ? ' · ' + serial : '');
                contentEl.innerHTML = buildViewDetailHtml(d, typeName);
                loadAssignmentHistory(d.equipment_id || id);
            })
            .catch(function(err) {
                contentEl.innerHTML = '<div class="p-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading details</div>';
            });
    }

    function buildViewDetailHtml(d, typeName) {
        var brand = d.brand || '';
        var model = d.model || '';
        var serial = d.serial_number || '';
        var year = d.year_acquired || '';
        var status = d.status || 'N/A';
        var statusClass = status.toLowerCase().replace(/\s+/g, '-');

        var html = '';

        // ── Status Banner ──
        html += '<div class="view-status-banner status-bg-' + statusClass + '">';
        html += '<span class="status-badge status-' + statusClass + '" style="font-size:13px;padding:5px 14px;">' + escapeHtml(status) + '</span>';
        if (d.employeeName) {
            html += '<span class="view-assigned-to"><i class="fas fa-user"></i> ' + escapeHtml(d.employeeName) + '</span>';
        } else if (d.locationName || d.location_name) {
            html += '<span class="view-assigned-to"><i class="fas fa-map-marker-alt"></i> ' + escapeHtml(d.locationName || d.location_name) + '</span>';
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

        // ── Specifications Section (from unified specs object) ──
        var specs = [];
        var sp = d.specs || {};
        var specIconMap = {
            'Category': 'fa-layer-group', 'Processor': 'fa-microchip', 'Memory': 'fa-memory',
            'GPU': 'fa-display', 'Storage': 'fa-hdd', 'Monitor Size': 'fa-expand',
            'Printer Type': 'fa-print', 'Connectivity': 'fa-wifi', 'IP Address': 'fa-network-wired',
            'Resolution': 'fa-desktop', 'Details': 'fa-info-circle'
        };
        for (var specKey in sp) {
            if (sp.hasOwnProperty(specKey) && sp[specKey] && specKey !== 'Maintenance Date' && specKey !== 'Next Maintenance Date') {
                specs.push({icon: specIconMap[specKey] || 'fa-cog', label: specKey, value: sp[specKey]});
            }
        }

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
        var hasLastMaint = d.maintenanceDate || (d.specs && d.specs['Maintenance Date']);
        var hasNextMaint = d.nextMaintenanceDate || (d.specs && d.specs['Next Maintenance Date']);
        if (hasLastMaint || hasNextMaint) {
            html += '<div class="view-section">';
            html += '<div class="view-section-title"><i class="fas fa-tools"></i> Maintenance</div>';
            html += '<div class="view-maint-row">';
            if (hasLastMaint) {
                var lastMaintDate = d.maintenanceDate || d.specs['Maintenance Date'];
                html += '<div class="view-maint-card">';
                html += '<div class="view-maint-icon last"><i class="fas fa-check-circle"></i></div>';
                html += '<div><div class="view-maint-label">Last Maintenance</div>';
                html += '<div class="view-maint-date">' + escapeHtml(formatDate(lastMaintDate)) + '</div></div>';
                html += '</div>';
            }
            if (hasNextMaint) {
                var nextMaintDate = d.nextMaintenanceDate || d.specs['Next Maintenance Date'];
                var isOverdue = new Date(nextMaintDate) < new Date();
                html += '<div class="view-maint-card' + (isOverdue ? ' overdue' : '') + '">';
                html += '<div class="view-maint-icon next' + (isOverdue ? ' overdue' : '') + '"><i class="fas ' + (isOverdue ? 'fa-exclamation-triangle' : 'fa-calendar-check') + '"></i></div>';
                html += '<div><div class="view-maint-label">' + (isOverdue ? 'Overdue' : 'Next Scheduled') + '</div>';
                html += '<div class="view-maint-date">' + escapeHtml(formatDate(nextMaintDate)) + '</div></div>';
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
        if (d.locationName || d.location_name) {
            html += '<div class="view-assignment"><i class="fas fa-map-marker-alt text-danger"></i> ' + escapeHtml(d.locationName || d.location_name) + '</div>';
        }
        if (!d.employeeName && !d.locationName && !d.location_name) {
            html += '<div class="view-assignment text-muted"><i class="fas fa-minus-circle"></i> Not assigned to any employee or location</div>';
        }
        html += '</div>';

        // ── Assignment History Section ──
        var eqId = d.equipment_id || '';
        html += '<div class="view-section">';
        html += '<div class="view-section-title" style="cursor:pointer;user-select:none;" onclick="EqUnified.toggleHistorySection(this)">';
        html += '<i class="fas fa-history"></i> Assignment History';
        html += '<i class="fas fa-chevron-down" style="margin-left:auto;font-size:0.7rem;transition:transform .2s;"></i>';
        html += '</div>';
        html += '<div id="assignmentHistoryContent" data-equipment-id="' + eqId + '">';
        html += '<div class="text-center py-3"><i class="fas fa-spinner fa-spin text-muted"></i> Loading history…</div>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Toggle the assignment history collapsible section
     */
    function toggleHistorySection(titleEl) {
        var container = titleEl.nextElementSibling;
        var chevron = titleEl.querySelector('.fa-chevron-down, .fa-chevron-up');
        if (container.style.display === 'none') {
            container.style.display = '';
            if (chevron) { chevron.classList.remove('fa-chevron-up'); chevron.classList.add('fa-chevron-down'); }
        } else {
            container.style.display = 'none';
            if (chevron) { chevron.classList.remove('fa-chevron-down'); chevron.classList.add('fa-chevron-up'); }
        }
    }

    /**
     * Fetch and render assignment history after the detail modal opens
     */
    function loadAssignmentHistory(equipmentId) {
        var container = document.getElementById('assignmentHistoryContent');
        if (!container || !equipmentId) return;

        fetch('../ajax/get_assignment_history.php?equipment_id=' + encodeURIComponent(equipmentId))
            .then(function(r) { 
                if (!r.ok) throw new Error('HTTP Status ' + r.status);
                return r.json(); 
            })
            .then(function(resp) {
                // Check for server-reported query errors
                if (resp.success === false) {
                    container.innerHTML = '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-triangle"></i> ' + escapeHtml(resp.message) + '</div>';
                    return;
                }

                // Check for legitimate empty history
                if (!resp.data || resp.data.length === 0) {
                    container.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-inbox"></i> No assignment history recorded yet.</div>';
                    return;
                }

                // Render Table securely
                var rows = resp.data;
                var html = '<table class="table table-sm table-hover mb-0" style="font-size:0.82rem;">';
                html += '<thead><tr>'
                    + '<th style="width:35%;">Employee</th>'
                    + '<th style="width:20%;">Action</th>'
                    + '<th style="width:30%;">Date</th>'
                    + '<th style="width:15%;">By</th>'
                    + '</tr></thead><tbody>';

                for (var i = 0; i < rows.length; i++) {
                    var r = rows[i];
                    var actionBadge = '';
                    if (r.action === 'assigned') {
                        actionBadge = '<span class="badge bg-success bg-opacity-10 text-white" style="font-size:0.75rem;"><i class="fas fa-arrow-right"></i> Assigned</span>';
                    } else if (r.action === 'unassigned') {
                        actionBadge = '<span class="badge bg-secondary bg-opacity-10" style="font-size:0.75rem; color: var(-border-color)"><i class="fas fa-user-minus"></i> Unassigned</span>';
                    } else {
                        actionBadge = '<span class="badge bg-info bg-opacity-10 text-info" style="font-size:0.75rem;"><i class="fas fa-exchange-alt"></i> Transferred</span>';
                    }

                    var dateStr = r.assigned_at ? escapeHtml(formatDate(r.assigned_at)) : (r.unassigned_at ? escapeHtml(formatDate(r.unassigned_at)) : escapeHtml(formatDate(r.created_at)));

                    html += '<tr>'
                        + '<td><i class="fas fa-user-circle text-muted me-1"></i>' + escapeHtml(r.employeeName) + '</td>'
                        + '<td>' + actionBadge + '</td>'
                        + '<td>' + dateStr + '</td>'
                        + '<td>' + (r.performedByName ? escapeHtml(r.performedByName) : '—') + '</td>'
                        + '</tr>';
                }
                html += '</tbody></table>';
                container.innerHTML = html;
            })
            .catch(function(err) {
                container.innerHTML = '<div class="text-center py-3 text-danger"><i class="fas fa-wifi"></i> Request failed: ' + escapeHtml(err.message) + '</div>';
            });
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
        viewEquipment: viewEquipment,
        toggleHistorySection: toggleHistorySection,
        openBatchSchedule: openBatchSchedule,
        executeBatchSchedule: executeBatchSchedule
    };
})();
