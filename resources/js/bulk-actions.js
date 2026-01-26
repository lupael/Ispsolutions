/**
 * Bulk Actions JavaScript (Feature 6.1)
 * Handles bulk operations on customers and other entities
 */

class BulkActionsManager {
    constructor(containerSelector = '[data-bulk-select-container]') {
        this.container = document.querySelector(containerSelector);
        if (!this.container) {
            console.warn('Bulk actions container not found');
            return;
        }

        this.selectAllCheckbox = this.container.querySelector('[data-bulk-select-all]');
        this.itemCheckboxes = this.container.querySelectorAll('[data-bulk-select-item]');
        this.bulkActionButton = document.querySelector('[data-bulk-action-button]');
        this.bulkActionSelect = document.querySelector('[data-bulk-action-select]');
        this.originalButtonText = null; // Store original button text once
        
        this.init();
    }

    init() {
        // Store original button text
        if (this.bulkActionButton) {
            this.originalButtonText = this.bulkActionButton.innerHTML;
        }

        // Select all functionality
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleAll(e.target.checked);
            });
        }

        // Individual checkbox change
        this.itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateUI();
            });
        });

        // Execute button click
        if (this.bulkActionButton) {
            this.bulkActionButton.addEventListener('click', () => {
                this.executeBulkAction();
            });
        }

        // Initial UI update
        this.updateUI();
    }

    toggleAll(checked) {
        this.itemCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateUI();
    }

    updateUI() {
        // Update select all state
        if (this.selectAllCheckbox) {
            const checkedCount = this.getSelectedCount();
            const totalCount = this.itemCheckboxes.length;
            
            this.selectAllCheckbox.checked = checkedCount === totalCount && totalCount > 0;
            this.selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        // Update action button state
        const selectedCount = this.getSelectedCount();
        if (this.bulkActionButton) {
            this.bulkActionButton.disabled = selectedCount === 0;
        }

        // Update selected count anywhere in the container
        const countBadges = document.querySelectorAll('[data-selected-count]');
        countBadges.forEach(badge => {
            badge.textContent = selectedCount;
        });

        // Update button text
        const actionText = this.bulkActionButton?.querySelector('[data-action-text]');
        if (actionText) {
            actionText.textContent = selectedCount > 0 
                ? `Apply to ${selectedCount} selected` 
                : 'Select items';
        }
    }

    getSelectedCount() {
        return Array.from(this.itemCheckboxes).filter(cb => cb.checked).length;
    }

    getSelectedIds() {
        return Array.from(this.itemCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));
    }

    async executeBulkAction() {
        const selectedIds = this.getSelectedIds();
        
        if (selectedIds.length === 0) {
            this.showNotification('error', 'Please select at least one item');
            return;
        }

        const action = this.bulkActionSelect?.value;
        if (!action) {
            this.showNotification('error', 'Please select an action');
            return;
        }

        // Get additional parameters based on action
        const params = await this.getActionParameters(action);
        if (params === null) {
            // User cancelled
            return;
        }

        // Confirm action
        if (!confirm(`Are you sure you want to ${action.replace('_', ' ')} ${selectedIds.length} customer(s)?`)) {
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        try {
            const response = await fetch('/panel/customers/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    customer_ids: selectedIds,
                    action: action,
                    ...params
                })
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                this.showNotification('success', data.message);
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification('error', data.message || 'An error occurred');
                this.setLoadingState(false);
            }
        } catch (error) {
            console.error('Bulk action error:', error);
            this.showNotification('error', 'An error occurred while executing the bulk action');
            this.setLoadingState(false);
        }
    }

    async getActionParameters(action) {
        switch (action) {
            case 'change_package':
                const packageId = prompt('Enter Package ID:');
                if (!packageId) return null;
                return { package_id: parseInt(packageId) };

            case 'change_operator':
                const operatorId = prompt('Enter Operator ID:');
                if (!operatorId) return null;
                return { operator_id: parseInt(operatorId) };

            case 'update_expiry':
                const expiryDate = prompt('Enter Expiry Date (YYYY-MM-DD):');
                if (!expiryDate) return null;
                return { expiry_date: expiryDate };

            case 'suspend':
            case 'activate':
                return {};

            default:
                return {};
        }
    }

    setLoadingState(loading) {
        if (this.bulkActionButton) {
            this.bulkActionButton.disabled = loading;
            
            if (loading) {
                this.bulkActionButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            } else {
                // Restore from stored original text
                this.bulkActionButton.innerHTML = this.originalButtonText || 'Apply Action';
            }
        }
    }

    showNotification(type, message) {
        // Simple notification - can be replaced with better UI library
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-bulk-select-container]')) {
        window.bulkActionsManager = new BulkActionsManager();
    }
});

// Export for use in other modules
export default BulkActionsManager;
