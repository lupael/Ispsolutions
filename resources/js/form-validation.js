/**
 * Form Validation Utility
 * Provides client-side validation for ISP Solution forms
 */

class FormValidator {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;

        this.init();
    }

    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.form.classList.add('was-validated');
        }, false);

        // Real-time validation for input fields
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    this.validateField(field);
                }
            });
        });
    }

    validateForm() {
        let isValid = true;
        const fields = this.form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required.';
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }
        }

        // Min length validation
        if (field.hasAttribute('minlength') && value) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (value.length < minLength) {
                isValid = false;
                errorMessage = `Minimum ${minLength} characters required.`;
            }
        }

        // Max length validation
        if (field.hasAttribute('maxlength') && value) {
            const maxLength = parseInt(field.getAttribute('maxlength'));
            if (value.length > maxLength) {
                isValid = false;
                errorMessage = `Maximum ${maxLength} characters allowed.`;
            }
        }

        // Number validation
        if (field.type === 'number' && value) {
            const numValue = parseFloat(value);
            
            if (field.hasAttribute('min')) {
                const min = parseFloat(field.getAttribute('min'));
                if (numValue < min) {
                    isValid = false;
                    errorMessage = `Value must be at least ${min}.`;
                }
            }

            if (field.hasAttribute('max')) {
                const max = parseFloat(field.getAttribute('max'));
                if (numValue > max) {
                    isValid = false;
                    errorMessage = `Value must not exceed ${max}.`;
                }
            }
        }

        // URL validation
        if (field.type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                isValid = false;
                errorMessage = 'Please enter a valid URL.';
            }
        }

        // Password confirmation
        if (field.name === 'password_confirmation') {
            const passwordField = this.form.querySelector('input[name="password"]');
            if (passwordField && value !== passwordField.value) {
                isValid = false;
                errorMessage = 'Passwords do not match.';
            }
        }

        // IP Address validation
        if (field.hasAttribute('data-validate') && field.getAttribute('data-validate') === 'ip') {
            const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (value && !ipPattern.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid IP address.';
            }
        }

        // MAC Address validation
        if (field.hasAttribute('data-validate') && field.getAttribute('data-validate') === 'mac') {
            const macPattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
            if (value && !macPattern.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid MAC address (e.g., 00:11:22:33:44:55).';
            }
        }

        this.updateFieldState(field, isValid, errorMessage);
        return isValid;
    }

    updateFieldState(field, isValid, errorMessage) {
        const feedbackElement = field.parentElement.querySelector('.invalid-feedback');

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedbackElement) {
                feedbackElement.textContent = '';
            }
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            if (feedbackElement) {
                feedbackElement.textContent = errorMessage;
            } else {
                // Create feedback element if it doesn't exist
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errorMessage;
                field.parentElement.appendChild(feedback);
            }
        }
    }
}

// Bulk selection utilities
class BulkSelector {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) {
            if (typeof console !== 'undefined' && console.warn) {
                console.warn(`BulkSelector: Container element '${containerSelector}' not found`);
            }
            return;
        }

        this.selectAllCheckbox = this.container.querySelector('[data-bulk-select-all]');
        this.itemCheckboxes = this.container.querySelectorAll('[data-bulk-select-item]');
        this.bulkActionButton = document.querySelector('[data-bulk-action-button]');

        this.init();
    }

    init() {
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', () => {
                this.toggleAll(this.selectAllCheckbox.checked);
            });
        }

        this.itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectAllState();
                this.updateBulkActionButton();
            });
        });

        this.updateBulkActionButton();
    }

    toggleAll(checked) {
        this.itemCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateBulkActionButton();
    }

    updateSelectAllState() {
        if (!this.selectAllCheckbox) return;

        const checkedCount = Array.from(this.itemCheckboxes).filter(cb => cb.checked).length;
        this.selectAllCheckbox.checked = checkedCount === this.itemCheckboxes.length;
        this.selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < this.itemCheckboxes.length;
    }

    updateBulkActionButton() {
        if (!this.bulkActionButton) return;

        const checkedCount = Array.from(this.itemCheckboxes).filter(cb => cb.checked).length;
        this.bulkActionButton.disabled = checkedCount === 0;
        
        const countBadge = this.bulkActionButton.querySelector('[data-selected-count]');
        if (countBadge) {
            countBadge.textContent = checkedCount;
        }
    }

    getSelectedIds() {
        return Array.from(this.itemCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize form validators
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new FormValidator(`#${form.id}`);
    });

    // Initialize bulk selectors
    if (document.querySelector('[data-bulk-select-container]')) {
        new BulkSelector('[data-bulk-select-container]');
    }

    // Password toggle
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-password-toggle');
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }
            }
        });
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm-delete]').forEach(form => {
        form.addEventListener('submit', (e) => {
            const message = form.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FormValidator, BulkSelector };
}
