/**
 * Bulk Selection Handler
 * Handles "Select All" checkbox with indeterminate state
 * Used across customer list pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle "Select All" checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('[data-bulk-select-item]');
    
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        // Select/deselect all items when "Select All" is toggled
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
        
        // Update "Select All" state based on individual checkboxes
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }
});
