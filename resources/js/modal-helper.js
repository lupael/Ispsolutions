/**
 * Enhanced Modal System
 * Provides AJAX-powered modals with loading states
 */

class EnhancedModal {
    constructor(modalId) {
        this.modalId = modalId;
        this.modal = document.getElementById(modalId);
        this.modalBody = this.modal?.querySelector('.modal-body');
        this.modalTitle = this.modal?.querySelector('.modal-title');
    }

    /**
     * Show modal with AJAX content
     */
    showWithContent(url, title = null) {
        if (!this.modal) {
            console.error(`Modal ${this.modalId} not found`);
            return;
        }

        // Set title if provided
        if (title && this.modalTitle) {
            this.modalTitle.textContent = title;
        }

        // Show loading state
        this.showLoading();

        // Show modal
        const bootstrapModal = new bootstrap.Modal(this.modal);
        bootstrapModal.show();

        // Fetch content
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            this.modalBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading modal content:', error);
            this.showError('Failed to load content. Please try again.');
        });
    }

    /**
     * Show loading state
     */
    showLoading() {
        if (this.modalBody) {
            this.modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
            `;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (this.modalBody) {
            this.modalBody.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }

    /**
     * Set content directly
     */
    setContent(html, title = null) {
        if (title && this.modalTitle) {
            this.modalTitle.textContent = title;
        }
        if (this.modalBody) {
            this.modalBody.innerHTML = html;
        }
    }

    /**
     * Show modal
     */
    show() {
        if (this.modal) {
            const bootstrapModal = new bootstrap.Modal(this.modal);
            bootstrapModal.show();
        }
    }

    /**
     * Hide modal
     */
    hide() {
        if (this.modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(this.modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }
}

// Global modal instances
window.modalInstances = {
    fup: null,
    billingProfile: null,
    quickAction: null
};

/**
 * Initialize modal instances
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal instances
    if (document.getElementById('fupModal')) {
        window.modalInstances.fup = new EnhancedModal('fupModal');
    }
    if (document.getElementById('billingProfileModal')) {
        window.modalInstances.billingProfile = new EnhancedModal('billingProfileModal');
    }
    if (document.getElementById('quickActionModal')) {
        window.modalInstances.quickAction = new EnhancedModal('quickActionModal');
    }
});

/**
 * Global helper functions for backward compatibility
 */
window.showFupModal = function(packageId) {
    const url = `/panel/packages/${packageId}/fup`;
    window.modalInstances.fup?.showWithContent(url, 'Fair Usage Policy');
};

window.showBillingProfileModal = function(profileId) {
    const url = `/panel/billing-profiles/${profileId}`;
    window.modalInstances.billingProfile?.showWithContent(url, 'Billing Profile Details');
};

window.showQuickActionModal = function(action, customerId) {
    const url = `/panel/customers/${customerId}/quick-action/${action}`;
    const titles = {
        'activate': 'Activate Customer',
        'suspend': 'Suspend Customer',
        'recharge': 'Quick Recharge'
    };
    window.modalInstances.quickAction?.showWithContent(url, titles[action] || 'Quick Action');
};
