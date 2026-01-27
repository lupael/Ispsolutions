<div class="p-4">
    <div class="mb-4">
        <h5 class="font-semibold">{{ ucfirst($action) }} Customer: {{ $customer->username }}</h5>
    </div>

    @if($action === 'activate')
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            Are you sure you want to activate this customer?
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'activate']) }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes about this action..."></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Activate Customer</button>
            </div>
        </form>

    @elseif($action === 'suspend')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Are you sure you want to suspend this customer? They will lose access to services.
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'suspend']) }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                <select name="reason" id="reason" class="form-select" required>
                    <option value="">Select reason...</option>
                    <option value="non_payment">Non-payment</option>
                    <option value="abuse">Policy violation / Abuse</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="customer_request">Customer request</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="notes" class="form-label">Additional Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any additional details..."></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Suspend Customer</button>
            </div>
        </form>

    @elseif($action === 'recharge')
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Quick recharge for {{ $customer->username }}
        </div>
        <form id="quickActionForm" action="{{ route('panel.customers.quick-action.execute', ['customer' => $customer->id, 'action' => 'recharge']) }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="form-group mb-3">
                <label for="method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                <select name="method" id="method" class="form-select" required>
                    <option value="">Select method...</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="online">Online Payment</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Transaction reference or notes..."></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Process Recharge</button>
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
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
            // Close modal
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
