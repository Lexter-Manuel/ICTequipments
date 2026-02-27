/**
 * Employee Search Widget — Enhanced Autocomplete with Fuzzy Matching
 * Usage: empSearch.init(inputId, hiddenId)
 * Usage: empSearch.set(inputId, hiddenId, employeeId)  — populate in edit mode
 * Usage: empSearch.clear(inputId, hiddenId)            — reset both fields
 * Usage: empSearch.validate(inputId)                   — true if valid, shows error if not
 * Depends on: window.employeesData [{employeeId, fullName}]
 */
var empSearch = (function () {

    /**
     * Fuzzy score — lower = better.  -1 = no match.
     */
    function fuzzyScore(query, target) {
        var q = query.toLowerCase(), t = target.toLowerCase();
        if (t === q) return 0;                       // exact
        if (t.startsWith(q)) return 1;               // starts with
        if (t.indexOf(q) !== -1) return 2;           // contains
        // all query words found in target
        var words = q.split(/\s+/);
        if (words.every(function(w){ return t.indexOf(w) !== -1; })) return 3;
        // partial token overlap (first-name / last-name)
        var tWords = t.split(/[\s,.\-\/]+/);
        if (words.some(function(w){ return tWords.some(function(tw){ return tw.startsWith(w) || w.startsWith(tw); }); })) return 4;
        return -1;
    }

    /**
     * Highlight matched substring (first occurrence)
     */
    function highlight(text, query) {
        if (!query) return esc(text);
        var idx = text.toLowerCase().indexOf(query.toLowerCase());
        if (idx === -1) return esc(text);
        return esc(text.substring(0, idx))
             + '<span class="emp-match">' + esc(text.substring(idx, idx + query.length)) + '</span>'
             + esc(text.substring(idx + query.length));
    }

    function esc(s) {
        var d = document.createElement('span');
        d.textContent = s;
        return d.innerHTML;
    }

    function init(inputId, hiddenId) {
        var inp    = document.getElementById(inputId);
        var hidden = document.getElementById(hiddenId);
        if (!inp || !hidden || inp._empSearchDone) return;
        inp._empSearchDone = true;

        // ── Build DOM ──
        var wrap       = document.createElement('div');
        wrap.className = 'emp-autocomplete-wrapper';
        inp.parentNode.insertBefore(wrap, inp);
        wrap.appendChild(inp);

        var dropdown       = document.createElement('div');
        dropdown.className = 'emp-autocomplete-dropdown';
        dropdown.id        = inputId + '_dropdown';
        wrap.appendChild(dropdown);

        var banner       = document.createElement('div');
        banner.className = 'emp-suggestion-banner';
        banner.id        = inputId + '_banner';
        wrap.appendChild(banner);

        var errMsg           = document.createElement('small');
        errMsg.className     = 'text-danger';
        errMsg.style.display = 'none';
        wrap.appendChild(errMsg);

        var activeIdx = -1;

        // ── Helpers ──
        function showError(msg) {
            wrap.classList.add('emp-error');
            errMsg.textContent = msg;
            errMsg.style.display = 'block';
        }
        function clearError() {
            wrap.classList.remove('emp-error');
            errMsg.style.display = 'none';
        }

        function getData() {
            return (typeof employeesData !== 'undefined') ? employeesData : [];
        }

        // ── Render Dropdown ──
        function render(query) {
            dropdown.innerHTML = '';
            banner.style.display = 'none';
            activeIdx = -1;

            var q    = (query || '').trim();
            var data = getData();

            // Score & filter
            var scored;
            if (!q) {
                // Show all + Unassigned at top
                scored = data.map(function(e){ return { employeeId: e.employeeId, fullName: e.fullName, score: 5 }; });
            } else {
                scored = data
                    .map(function(e){ return { employeeId: e.employeeId, fullName: e.fullName, score: fuzzyScore(q, e.fullName) }; })
                    .filter(function(e){ return e.score >= 0; })
                    .sort(function(a, b){ return a.score - b.score || a.fullName.localeCompare(b.fullName); });
            }

            // Unassigned — always first
            addItem('', 'Unassigned', q, 0);

            if (q && scored.length === 0) {
                var none = document.createElement('div');
                none.className = 'emp-no-match';
                none.innerHTML = '<i class="fas fa-search"></i> No employee found for "<strong>' + esc(q) + '</strong>"';
                dropdown.appendChild(none);
            } else {
                scored.forEach(function(e, idx) {
                    addItem(e.employeeId, e.fullName, q, idx + 1);
                });
            }

            dropdown.style.display = 'block';

            // Suggestion banner for close matches
            if (q && scored.length > 0) {
                var exact = scored.find(function(e){ return e.fullName.toLowerCase() === q.toLowerCase(); });
                if (!exact && scored[0].score <= 4) {
                    showBanner(scored[0].fullName, scored[0].employeeId);
                }
            }
        }

        function addItem(empId, name, q, idx) {
            var item = document.createElement('div');
            item.className = 'emp-item';
            item.dataset.empId   = empId;
            item.dataset.empName = name;
            item.dataset.index   = idx;

            var icon = document.createElement('i');
            icon.className = empId === '' ? 'fas fa-user-slash' : 'fas fa-user';
            item.appendChild(icon);

            var span = document.createElement('span');
            span.className = 'emp-item-name';
            span.innerHTML = q ? highlight(name, q) : esc(name);
            item.appendChild(span);

            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
                select(empId, name);
            });
            item.addEventListener('mouseenter', function() {
                activeIdx = idx;
                highlightActive();
            });

            dropdown.appendChild(item);
        }

        function select(empId, name) {
            inp.value    = name;
            hidden.value = empId;
            clearError();
            dropdown.style.display = 'none';
            banner.style.display = 'none';
            activeIdx = -1;
        }

        function showBanner(name, empId) {
            banner.innerHTML = '<i class="fas fa-lightbulb"></i>'
                + '<span>Did you mean <span class="emp-suggestion-use">' + esc(name) + '</span>?</span>';
            banner.style.display = 'flex';
            banner.querySelector('.emp-suggestion-use').addEventListener('click', function() {
                select(empId, name);
            });
        }

        function highlightActive() {
            var items = dropdown.querySelectorAll('.emp-item');
            items.forEach(function(el, i) {
                el.classList.toggle('active', i === activeIdx);
            });
        }

        // ── Events ──
        inp.addEventListener('input', function() {
            hidden.value = '';
            clearError();
            render(this.value);
        });

        inp.addEventListener('focus', function() {
            render(this.value);
        });

        inp.addEventListener('blur', function() {
            setTimeout(function() {
                dropdown.style.display = 'none';
                banner.style.display = 'none';
                activeIdx = -1;
            }, 180);
        });

        inp.addEventListener('keydown', function(e) {
            if (dropdown.style.display === 'none') return;
            var items  = dropdown.querySelectorAll('.emp-item');
            var maxIdx = items.length - 1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, maxIdx);
                highlightActive();
                if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                highlightActive();
                if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && items[activeIdx]) {
                    select(items[activeIdx].dataset.empId, items[activeIdx].dataset.empName);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
                banner.style.display = 'none';
                activeIdx = -1;
            }
        });

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) {
                dropdown.style.display = 'none';
                banner.style.display = 'none';
                activeIdx = -1;
            }
        });

        // ── Validation fn ──
        inp._empValidate = function() {
            var text = inp.value.trim();
            if (text === '' || text === 'Unassigned' || hidden.value !== '') {
                clearError();
                return true;
            }
            showError('Invalid name — please select from the suggestions.');
            return false;
        };
    }

    function set(inputId, hiddenId, employeeId) {
        var inp    = document.getElementById(inputId);
        var hidden = document.getElementById(hiddenId);
        if (!inp || !hidden) return;
        hidden.value = employeeId || '';
        if (!employeeId) { inp.value = ''; return; }
        var data  = (typeof employeesData !== 'undefined') ? employeesData : [];
        var match = data.find(function(e) { return String(e.employeeId) === String(employeeId); });
        inp.value = match ? match.fullName : '';
    }

    function clear(inputId, hiddenId) {
        var inp    = document.getElementById(inputId);
        var hidden = document.getElementById(hiddenId);
        if (inp)    inp.value    = '';
        if (hidden) hidden.value = '';
    }

    function validate(inputId) {
        var inp = document.getElementById(inputId);
        if (!inp || !inp._empValidate) return true;
        return inp._empValidate();
    }

    return { init: init, set: set, clear: clear, validate: validate };
})();

/* Init all employee search fields on this page immediately after empSearch is defined. */
(function() {
    var fields = [
        ['suEmployeeSearch',      'suEmployee'],
        ['monEmployeeSearch',     'monEmployee'],
        ['aioEmployeeSearch',     'aioEmployee'],
        ['printerEmployeeSearch', 'printerEmployee'],
        ['otherEmployeeSearch',   'otherEmployee']
    ];
    fields.forEach(function(pair) {
        empSearch.init(pair[0], pair[1]);
    });
})();