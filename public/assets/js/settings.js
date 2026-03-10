document.addEventListener('DOMContentLoaded', function() {

    // 1. Delegate Click Events for Tabs
    document.body.addEventListener('click', function(e) {
        // Find the closest button with our specific class
        const btn = e.target.closest('.settings-tab-btn');
        if (!btn) return;

        const targetName = btn.getAttribute('data-target');

        // Reset UI
        document.querySelectorAll('.ps-settings-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.remove('active'));
        
        // Activate target
        const targetPanel = document.getElementById('panel-' + targetName);
        if (targetPanel) {
            targetPanel.classList.add('active');
            btn.classList.add('active');
        }
    });

    // 2. Delegate Submit Events for Forms
    document.body.addEventListener('submit', function(e) {
        if (!e.target.classList.contains('settings-form')) return;
        
        e.preventDefault();
        const form = e.target;
        const group = form.getAttribute('data-group');
        
        // Execute your saveGroup logic here...
        console.log('Saving group:', group);
    });

    // 3. Delegate specific button clicks (Purge, Clear, Cache)
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('#btn-purge-logs')) {
            // execute purge logic
        }
        if (e.target.closest('#btn-clear-attempts')) {
            // execute clear attempts logic
        }
    });

});