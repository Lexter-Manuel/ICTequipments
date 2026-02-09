/**
 * Software License Management - JavaScript
 */

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filterLicenseType');
    const filterStatus = document.getElementById('filterStatus');
    const searchInput = document.getElementById('searchLicense');
    const table = document.getElementById('softwareTable');
    
    // Apply filters
    function applyFilters() {
        const typeValue = filterType.value.toLowerCase();
        const statusValue = filterStatus.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const type = row.dataset.type.toLowerCase();
            const status = row.dataset.status.toLowerCase();
            const text = row.textContent.toLowerCase();
            
            const typeMatch = !typeValue || type === typeValue;
            const statusMatch = !statusValue || status === statusValue;
            const searchMatch = !searchValue || text.includes(searchValue);
            
            if (typeMatch && statusMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        updateVisibleCount();
    }
    
    // Update visible count
    function updateVisibleCount() {
        const rows = table.querySelectorAll('tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        
        // You can add a count display here if needed
        console.log(`Showing ${visibleRows.length} of ${rows.length} licenses`);
    }
    
    // Event listeners
    if (filterType) filterType.addEventListener('change', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (searchInput) {
        searchInput.addEventListener('input', debounce(applyFilters, 300));
    }
});

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Failed to copy:', err);
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Show password (demo)
function showPassword(id) {
    alert(`This would show the password for license ID: ${id}\n\nIn production, this would:\n- Verify user permissions\n- Log the access\n- Display the actual password\n\nDemo only - no real passwords stored.`);
}

// View license details
function viewLicense(id) {
    alert(`View detailed information for license ID: ${id}\n\nThis would open a modal with:\n- Full license information\n- Purchase history\n- Usage statistics\n- Renewal information\n\nDemo only.`);
}

// Edit license
function editLicense(id) {
    alert(`Edit license ID: ${id}\n\nThis would open an edit modal with:\n- All license fields\n- Ability to update information\n- Assignment to employees\n- Set expiry dates\n\nDemo only.`);
}

// Delete license
function deleteLicense(id) {
    if (confirm('Are you sure you want to delete this license?\n\nThis action cannot be undone.\n\n(Demo only - no actual deletion)')) {
        showNotification('License deleted successfully', 'success');
        // In production, this would call an API to delete the license
    }
}

// Open add license modal
function openAddLicenseModal() {
    alert('Add New License\n\nThis would open a modal with:\n- Software name\n- License type (Subscription/Perpetual)\n- License details\n- Expiry date\n- Credentials\n- Assignment options\n\nDemo only.');
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#2563eb'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Export functionality (demo)
function exportLicenseReport() {
    alert('Export License Report\n\nThis would generate a report in:\n- PDF format\n- Excel/CSV format\n- Including all license details\n- Filtered by current view\n\nDemo only.');
}

// Highlight expiring licenses
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const expiryCountdown = row.querySelector('.expiry-countdown');
        if (expiryCountdown) {
            const text = expiryCountdown.textContent;
            
            // Add pulsing animation to expiring soon
            if (expiryCountdown.classList.contains('warning')) {
                row.style.background = 'rgba(245, 158, 11, 0.03)';
            }
            
            // Add stronger highlight to expired
            if (expiryCountdown.classList.contains('danger')) {
                row.style.background = 'rgba(239, 68, 68, 0.03)';
            }
        }
    });
});

// Sort table functionality
function sortTable(columnIndex) {
    const table = document.getElementById('softwareTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    let ascending = true;
    const header = table.querySelectorAll('th')[columnIndex];
    
    if (header.classList.contains('sort-asc')) {
        ascending = false;
        header.classList.remove('sort-asc');
        header.classList.add('sort-desc');
    } else {
        // Remove all sort classes
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add('sort-asc');
    }
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        if (ascending) {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// Auto-check for expiring licenses on load
document.addEventListener('DOMContentLoaded', function() {
    checkExpiringLicenses();
});

function checkExpiringLicenses() {
    const rows = document.querySelectorAll('tbody tr');
    let expiringSoonCount = 0;
    let expiredCount = 0;
    
    rows.forEach(row => {
        const status = row.dataset.status;
        if (status === 'Expiring Soon') expiringSoonCount++;
        if (status === 'Expired') expiredCount++;
    });
    
    if (expiredCount > 0) {
        console.warn(`⚠️ ${expiredCount} license(s) have expired!`);
    }
    
    if (expiringSoonCount > 0) {
        console.warn(`⏰ ${expiringSoonCount} license(s) expiring soon!`);
    }
}