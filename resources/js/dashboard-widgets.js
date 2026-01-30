/**
 * Dashboard Widgets JavaScript
 * Handles interactive elements on dashboard widgets without inline scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle remind customer buttons
    const remindButtons = document.querySelectorAll('[data-action="remind-customer"]');
    remindButtons.forEach(button => {
        button.addEventListener('click', function() {
            alert('Email reminder feature coming soon');
        });
    });

    // Handle confirm delete forms
    const confirmDeleteForms = document.querySelectorAll('[data-confirm-delete]');
    confirmDeleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this package?')) {
                e.preventDefault();
            }
        });
    });
});
