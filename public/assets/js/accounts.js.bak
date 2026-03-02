/* accounts.js — NIA UPRIIS ICT Inventory */

/* ---- Toggle Form ---- */
function toggleForm() {
    var container = document.getElementById('accountFormContainer');
    var isActive = container.classList.contains('active');

    if (isActive) {
        container.classList.remove('active');
        resetForm();
    } else {
        // Reset to "Add" mode
        resetForm();
        container.classList.add('active');
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function resetForm() {
    var form = document.getElementById('accountForm');
    form.reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('accountId').value = '';
    document.getElementById('formTitleText').textContent = 'Add New Admin';
    document.getElementById('formSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Add Admin';
    document.getElementById('passwordSectionTitle').textContent = 'Set Password';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('confirmRequired').style.display = 'inline';
    document.getElementById('password').required = true;
    document.getElementById('confirmPassword').required = true;
    document.getElementById('editPasswordHint').style.display = 'none';
    document.getElementById('passwordMatchMsg').style.display = 'none';
    document.getElementById('strengthFill').className = 'strength-fill';
    document.getElementById('strengthLabel').textContent = '';
}

/* ---- Edit Account ---- */
function editAccount(acc) {
    resetForm();
    var container = document.getElementById('accountFormContainer');

    document.getElementById('formAction').value = 'edit';
    document.getElementById('accountId').value = acc.id;
    document.getElementById('formTitleText').textContent = 'Edit Admin Account';
    document.getElementById('formSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Save Changes';
    document.getElementById('passwordSectionTitle').textContent = 'Change Password';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('confirmRequired').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('confirmPassword').required = false;
    document.getElementById('editPasswordHint').style.display = 'block';

    document.getElementById('userName').value = acc.user_name;
    document.getElementById('email').value = acc.email;
    document.getElementById('status').value = acc.status;

    container.classList.add('active');
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ---- Toggle / Unlock / Delete via AJAX ---- */
function toggleStatus(id, currentStatus, name) {
    var newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    var action = newStatus === 'Active' ? 'activate' : 'deactivate';
    var icon = newStatus === 'Active' ? 'check-circle' : 'ban';
    var isActivate = newStatus === 'Active';

    showConfirm(
        isActivate ? 'Activate Account' : 'Deactivate Account',
        `${isActivate ? 'Activate' : 'Deactivate'} account for <strong>${name}</strong>?`,
        icon,
        isActivate ? 'success' : 'warning',
        isActivate,
        () => postAction({ action, account_id: id })
    );
}

function unlockAccount(id, name) {
    showConfirm(
        'Unlock Account',
        `Unlock the account for <strong>${name}</strong>? This will reset their failed login attempts.`,
        'unlock',
        'warning',
        true,
        () => postAction({ action: 'unlock', account_id: id })
    );
}

function deleteAccount(id, name) {
    showConfirm(
        'Delete Account',
        `Permanently delete the account for <strong>${name}</strong>? This cannot be undone.`,
        'trash-alt',
        'danger',
        false,
        () => postAction({ action: 'delete', account_id: id })
    );
}

function postAction(data) {
    var formData = new FormData();
    for (var [key, value] of Object.entries(data)) {
        formData.append(key, value);
    }
    fetch(`${BASE_URL}ajax/process_account.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(result => {
        alert(result.message);
        reloadCurrentPage();
    })
    .catch(err => {
        alert('Error: ' + err);
        reloadCurrentPage();
    });
}

/* ---- Confirm Modal ---- */
var confirmCallback = null;

function showConfirm(title, message, icon, type, isGreen, onConfirm) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').innerHTML = message;
    document.getElementById('confirmIcon').innerHTML = `<i class="fas fa-${icon}"></i>`;
    document.getElementById('confirmIcon').className = `confirm-icon ${type}`;

    var okBtn = document.getElementById('confirmOkBtn');
    okBtn.className = isGreen ? 'btn-confirm-ok confirm-green' : 'btn-confirm-ok';

    confirmCallback = onConfirm;
    document.getElementById('confirmModal').classList.add('active');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
}

document.getElementById('confirmOkBtn').addEventListener('click', function () {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

// Close modal on backdrop click
document.getElementById('confirmModal').addEventListener('click', function (e) {
    if (e.target === this) closeConfirmModal();
});

/* ---- Table Search & Filter ---- */
function filterTable() {
    var query = document.getElementById('searchInput').value.toLowerCase();
    var statusFilter = document.getElementById('statusFilter').value;
    var rows = document.querySelectorAll('#accountsTable tbody tr[data-status]');
    var counter = 1;

    rows.forEach(row => {
        var text = row.textContent.toLowerCase();
        var status = row.dataset.status;
        var matchSearch = text.includes(query);
        var matchStatus = !statusFilter || status === statusFilter;
        var visible = matchSearch && matchStatus;
        row.style.display = visible ? '' : 'none';
        var counterCell = row.querySelector('td.row-counter');
        if (counterCell && visible) {
            counterCell.textContent = counter++;
        }
    });
}

/* ---- Password Strength ---- */
document.getElementById('password').addEventListener('input', function () {
    var val = this.value;
    var fill = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');

    if (!val) {
        fill.className = 'strength-fill';
        fill.style.width = '0';
        label.textContent = '';
        return;
    }

    var score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    if (score <= 1) {
        fill.className = 'strength-fill weak';
        label.textContent = 'Weak password';
        label.style.color = '#ef4444';
    } else if (score <= 2) {
        fill.className = 'strength-fill fair';
        label.textContent = 'Fair password';
        label.style.color = '#f59e0b';
    } else {
        fill.className = 'strength-fill strong';
        label.textContent = 'Strong password';
        label.style.color = 'var(--primary-green)';
    }

    checkPasswordMatch();
});

document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
    var pw = document.getElementById('password').value;
    var cpw = document.getElementById('confirmPassword').value;
    var msg = document.getElementById('passwordMatchMsg');
    if (cpw && pw !== cpw) {
        msg.style.display = 'block';
    } else {
        msg.style.display = 'none';
    }
}

/* ---- Toggle Password Visibility ---- */
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId === 'password' ? 'passwordIcon' : 'confirmPasswordIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

/* ---- Form Submit Validation ---- */
document.getElementById('accountForm').addEventListener('submit', function (e) {
    e.preventDefault();

    var pw = document.getElementById('password').value;
    var cpw = document.getElementById('confirmPassword').value;
    var action = document.getElementById('formAction').value;

    if (action === 'add' && !pw) {
        alert('Password is required when creating a new account.');
        return;
    }

    if (pw && pw !== cpw) {
        alert('Passwords do not match. Please re-enter.');
        return;
    }

    if (pw && pw.length < 8) {
        alert('Password must be at least 8 characters long.');
        return;
    }

    var formData = new FormData(this);
    fetch(`${BASE_URL}ajax/process_account.php`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(result => {
        alert(result.message);
        if (result.success) {
            reloadCurrentPage();
        }
    })
    .catch(err => alert('Error: ' + err));
});