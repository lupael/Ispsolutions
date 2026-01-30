<div class="p-4">
    <div class="mb-4">
        <h5 class="font-semibold">{{ ucfirst($action) }} Customer: {{ $customer->username }}</h5>
    </div>

    @if($action === 'activate')
        <div class="p-4 rounded-md mb-4 bg-green-50 border border-green-200 text-green-800">
            <i class="fas fa-check-circle mr-2"></i>
            Are you sure you want to activate this customer?
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'activate']) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                <textarea name="notes" id="notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Add any notes about this action..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">Activate Customer</button>
            </div>
        </form>

    @elseif($action === 'suspend')
        <div class="p-4 rounded-md mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Are you sure you want to suspend this customer? They will lose access to services.
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'suspend']) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <select name="reason" id="reason" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select reason...</option>
                    <option value="non_payment">Non-payment</option>
                    <option value="abuse">Policy violation / Abuse</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="customer_request">Customer request</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                <textarea name="notes" id="notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Add any additional details..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-700">Suspend Customer</button>
            </div>
        </form>

    @elseif($action === 'recharge')
        <div class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>
            Quick recharge for {{ $customer->username }}
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'recharge']) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="mb-3">
                <label for="method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                <select name="method" id="method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select method...</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="online">Online Payment</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                <textarea name="notes" id="notes" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="2" placeholder="Transaction reference or notes..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Process Recharge</button>
            </div>
        </form>
    @endif
</div>

<script nonce="{{ csp_nonce() }}">
document.getElementById('quickActionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            // Close modal - Note: Bootstrap modals require JavaScript framework
            bootstrap.Modal.getInstance(document.getElementById('quickActionModal'))?.hide();
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert(data.message || 'An error occurred');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Try Again';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Try Again';
    });
});
</script>
