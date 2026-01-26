/**
 * Form Validation Utility
 * Provides client-side validation for ISP Solution forms
 * Feature 2.1: Added real-time duplicate validation
 */

class FormValidator {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;

        this.debounceTimers = {};
        this.validationEndpoints = {
            mobile: '/api/validate/mobile',
            username: '/api/validate/username',
            email: '/api/validate/email',
            national_id: '/api/validate/national-id',
            ip_address: '/api/validate/static-ip'
        };

        this.init();
    }

    init() {
        // Get exclude ID for edit forms
        this.excludeId = this.form.dataset.excludeId || null;

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

            // Real-time duplicate validation for specific fields
            if (this.validationEndpoints[field.name]) {
                this.attachDuplicateValidation(field);
            }
        });
    }

    /**
     * Attach duplicate validation to a field (Feature 2.1)
     */
    attachDuplicateValidation(field) {
        const fieldName = field.name;
        
        // Validation on blur
        field.addEventListener('blur', () => {
            const value = field.value.trim();
            if (value) {
                this.checkDuplicate(field, fieldName, value);
            }
        });

        // Validation on input with debounce
        field.addEventListener('input', () => {
            this.debounce(() => {
                const value = field.value.trim();
                if (value) {
                    this.checkDuplicate(field, fieldName, value);
                }
            }, fieldName, 800);
        });
    }

    /**
     * Check for duplicates via API (Feature 2.1)
     */
    async checkDuplicate(field, fieldName, value) {
        const feedbackEl = this.getOrCreateFeedback(field);
        
        // Show loading state
        this.setLoadingState(field, feedbackEl);

        try {
            const endpoint = this.validationEndpoints[fieldName];
            const params = new URLSearchParams({
                [fieldName]: value
            });
            
            if (this.excludeId) {
                params.append('exclude_id', this.excludeId);
            }

            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.exists) {
                this.updateFieldState(field, false, data.message);
            } else {
                // Only show success indicator for duplicate check
                field.classList.remove('is-invalid', 'border-red-500');
                field.classList.add('border-green-500');
                feedbackEl.className = 'validation-feedback text-sm mt-1 text-green-600';
                feedbackEl.innerHTML = `<i class="fas fa-check-circle me-1"></i>${data.message}`;
            }
        } catch (error) {
            console.error('Duplicate validation error:', error);
            // Clear validation state on error
            field.classList.remove('is-invalid', 'is-valid', 'border-green-500', 'border-red-500');
        }
    }

    /**
     * Set loading state (Feature 2.1)
     */
    setLoadingState(field, feedbackEl) {
        field.classList.remove('is-valid', 'is-invalid', 'border-green-500', 'border-red-500');
        feedbackEl.className = 'validation-feedback text-sm mt-1 text-gray-500';
        feedbackEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Checking...';
    }

    /**
     * Get or create feedback element (Feature 2.1)
     */
    getOrCreateFeedback(field) {
        let feedback = field.parentElement.querySelector('.validation-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'validation-feedback text-sm mt-1';
            field.parentNode.insertBefore(feedback, field.nextSibling);
        }
        return feedback;
    }

    /**
     * Debounce function (Feature 2.1)
     */
    debounce(func, key, delay = 500) {
        clearTimeout(this.debounceTimers[key]);
        this.debounceTimers[key] = setTimeout(func, delay);
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

    // Feature 8.2: Prevent Duplicate Form Submissions
    preventDuplicateSubmissions();
});

/**
 * Prevent Duplicate Form Submissions (Feature 8.2)
 * Disables submit buttons after first click and shows loading state
 */
function preventDuplicateSubmissions() {
    document.querySelectorAll('form').forEach(form => {
        // Skip forms that explicitly opt-out or are handled via AJAX-specific logic
        if (form.hasAttribute('data-no-submit-protection') || form.hasAttribute('data-ajax-form')) {
            return;
        }

        let isSubmitting = false;
        const originalSubmitText = new Map();

        // Store original button text
        form.querySelectorAll('button[type="submit"]').forEach(btn => {
            originalSubmitText.set(btn, btn.innerHTML);
        });

        form.addEventListener('submit', function(e) {
            // Check if another listener already prevented submission
            if (e.defaultPrevented) {
                return;
            }

            // If already submitting, prevent duplicate submission
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            // Check if form is valid (HTML5 validation)
            if (!form.checkValidity()) {
                // Let browser handle validation display
                return true;
            }

            // Mark as submitting
            isSubmitting = true;

            // Disable all submit buttons and show loading state
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = true;
                
                // Add loading spinner
                const hasIcon = btn.querySelector('i');
                if (hasIcon) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                } else {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + btn.textContent;
                }
                
                // Add visual feedback
                btn.classList.add('opacity-75', 'cursor-wait');
            });

            // Re-enable after 10 seconds as a safety mechanism
            // (in case form submission fails or redirects fail)
            setTimeout(() => {
                if (isSubmitting) {
                    isSubmitting = false;
                    form.querySelectorAll('button[type="submit"]').forEach(btn => {
                        btn.disabled = false;
                        btn.innerHTML = originalSubmitText.get(btn) || btn.innerHTML;
                        btn.classList.remove('opacity-75', 'cursor-wait');
                    });
                }
            }, 10000);
        });

        // Handle form reset
        form.addEventListener('reset', function() {
            isSubmitting = false;
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = originalSubmitText.get(btn) || btn.innerHTML;
                btn.classList.remove('opacity-75', 'cursor-wait');
            });
        });
    });

    // Handle AJAX forms separately
    document.querySelectorAll('form[data-ajax-form]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn || submitBtn.disabled) {
                return;
            }

            // Disable button
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            // Get form data
            const formData = new FormData(form);
            const url = form.action;
            const method = form.method || 'POST';

            // Submit via fetch
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert(data.message || 'Success!');
                    
                    // Optionally redirect or reset form
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        form.reset();
                    }
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                alert('An error occurred while submitting the form');
            })
            .finally(() => {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FormValidator, BulkSelector, preventDuplicateSubmissions };
}
