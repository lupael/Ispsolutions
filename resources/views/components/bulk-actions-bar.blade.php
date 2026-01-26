@props([
    'actions' => ['suspend', 'activate', 'change_package', 'change_operator', 'update_expiry']
])

<div class="bulk-actions-bar bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 sticky top-0 z-10" style="display: none;" id="bulkActionsBar">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                <span data-selected-count>0</span> selected
            </span>
            
            <select 
                data-bulk-action-select 
                class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Select Action...</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}">{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                data-bulk-action-button 
                class="text-sm px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                disabled
            >
                <span data-action-text>Apply Action</span>
            </button>
            
            <button 
                type="button" 
                onclick="document.querySelectorAll('[data-bulk-select-item]').forEach(cb => { if (cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); } }); if (window.bulkActionsManager) window.bulkActionsManager.updateUI();"
                class="text-sm px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors"
            >
                Clear Selection
            </button>
        </div>
    </div>
</div>

<script>
// Show/hide bulk actions bar based on selection
document.addEventListener('DOMContentLoaded', function() {
    const bar = document.getElementById('bulkActionsBar');
    if (bar) {
        const checkboxes = document.querySelectorAll('[data-bulk-select-item]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const selectedCount = Array.from(checkboxes).filter(c => c.checked).length;
                bar.style.display = selectedCount > 0 ? 'block' : 'none';
            });
        });
    }
});
</script>
