import './bootstrap';
import Alpine from 'alpinejs';
import AnalyticsManager from './analytics';
import ApexCharts from 'apexcharts';
import './bulk-actions';
import './form-validation';

// Customer Details Editor Alpine Component
// This must be defined BEFORE Alpine.start() to be available for use
window.customerDetailsEditor = function(customerId) {
    return {
        customerId: customerId,
        sections: {
            general: { isDirty: false },
            credentials: { isDirty: false },
            address: { isDirty: false },
            network: { isDirty: false },
            mac: { isDirty: false },
            comments: { isDirty: false },
        },
        
        markDirty(section) {
            this.sections[section].isDirty = true;
        },
        
        async saveSection(section) {
            const formId = section === 'general' ? 'general-info-form' : `${section}-form`;
            const form = document.getElementById(formId);
            if (!form) {
                console.error(`Form not found: ${formId}`);
                return;
            }
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const updateUrl = form.dataset.updateUrl;
                if (!updateUrl) {
                    console.error('Update URL not found on form');
                    this.showNotification('Configuration error', 'error');
                    return;
                }
                
                const response = await fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    this.sections[section].isDirty = false;
                    this.showNotification('Changes saved successfully', 'success');
                } else {
                    this.showNotification('Failed to save changes', 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showNotification('An error occurred while saving', 'error');
            }
        },
        
        showNotification(message, type) {
            // Simple notification - can be enhanced with a better notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        },
        
        checkUnsavedChanges() {
            return Object.values(this.sections).some(section => section.isDirty);
        },
        
        init() {
            // Warn on page leave if there are unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.checkUnsavedChanges()) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Would you like to save before leaving?';
                    return e.returnValue;
                }
            });
        }
    }
};

// Start Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Make ApexCharts available globally
window.ApexCharts = ApexCharts;

// Initialize Analytics Manager globally
window.analyticsManager = new AnalyticsManager();

// Metronic Core JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drawer functionality
    initDrawers();

    // Initialize menu functionality
    initMenus();

    // Initialize sticky headers
    initStickyHeaders();

    // Initialize modal functionality
    initModals();
});

// Drawer functionality
function initDrawers() {
    const drawers = document.querySelectorAll('[data-kt-drawer]');

    drawers.forEach(drawer => {
        const toggles = document.querySelectorAll(`[data-kt-drawer-toggle="#${drawer.id}"]`);

        toggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleDrawer(drawer);
            });
        });

        // Close drawer when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (!drawer.classList.contains('hidden') && 
                !drawer.contains(e.target) && 
                !Array.from(toggles).some(toggle => toggle.contains(e.target))) {
                closeDrawer(drawer);
            }
        });
    });
}

function toggleDrawer(drawer) {
    const isHidden = drawer.classList.contains('hidden');
    if (isHidden) {
        openDrawer(drawer);
    } else {
        closeDrawer(drawer);
    }
}

function openDrawer(drawer) {
    drawer.classList.remove('hidden');
    drawer.classList.add('flex');
    document.body.classList.add('overflow-hidden'); // Prevent background scroll on mobile
}

function closeDrawer(drawer) {
    drawer.classList.add('hidden');
    drawer.classList.remove('flex');
    document.body.classList.remove('overflow-hidden'); // Restore scroll
}

// Menu functionality
function initMenus() {
    const menus = document.querySelectorAll('[data-kt-menu="true"]');

    menus.forEach(menu => {
        const items = menu.querySelectorAll('[data-kt-menu-item-toggle="dropdown"]');

        items.forEach(item => {
            const trigger = item.querySelector('[data-kt-menu-item-trigger="click"], [data-kt-menu-item-trigger="click|lg:hover"]');
            const dropdown = item.querySelector('.kt-menu-dropdown');

            if (trigger && dropdown) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdown.classList.toggle('hidden');
                });
            }
        });
    });
}

// Sticky header functionality
function initStickyHeaders() {
    const stickyElements = document.querySelectorAll('[data-kt-sticky="true"]');

    stickyElements.forEach(element => {
        const stickyClass = element.getAttribute('data-kt-sticky-class') || 'kt-sticky';
        const offset = parseInt(element.getAttribute('data-kt-sticky-offset')) || 0;

        window.addEventListener('scroll', function() {
            if (window.scrollY > offset) {
                element.classList.add(...stickyClass.split(' '));
            } else {
                element.classList.remove(...stickyClass.split(' '));
            }
        });
    });
}

// Modal functionality
function initModals() {
    const modalToggles = document.querySelectorAll('[data-kt-modal-toggle]');

    modalToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-kt-modal-toggle');
            const modal = document.querySelector(modalId);

            if (modal) {
                modal.classList.toggle('hidden');
                modal.classList.toggle('flex');
            }
        });
    });
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.kt-modal');

    modals.forEach(modal => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});

// Export functions for use in other modules
window.MetronicCore = {
    initDrawers,
    initMenus,
    initStickyHeaders,
    initModals
};
