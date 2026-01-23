@extends('panels.layouts.app')

@section('title', 'Record Subscription Payment')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Record Subscription Payment</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Record a payment for client subscription</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="#" method="POST" class="space-y-6" onsubmit="event.preventDefault(); alert('Payment recording functionality will be implemented soon.');">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Client *</label>
                    <select name="client_id" id="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" onchange="updateInvoices(this.value)">
                        <option value="">Choose a client...</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-invoices="{{ $customer->invoices->toJson() }}">
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="invoice_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice *</label>
                    <select name="invoice_id" id="invoice_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select invoice...</option>
                    </select>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount *</label>
                    <input type="number" name="amount" id="amount" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date *</label>
                    <input type="date" name="payment_date" id="payment_date" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="check">Check</option>
                        <option value="card">Card</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>

                <div>
                    <label for="transaction_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction ID</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('panel.sales-manager.subscriptions.bills') }}" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
function updateInvoices(clientId) {
    const clientSelect = document.getElementById('client_id');
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');
    
    // Clear existing options
    invoiceSelect.innerHTML = '<option value="">Select invoice...</option>';
    amountInput.value = '';
    
    if (!clientId) return;
    
    // Get selected option
    const selectedOption = clientSelect.options[clientSelect.selectedIndex];
    const invoices = JSON.parse(selectedOption.dataset.invoices || '[]');
    
    // Populate invoices
    invoices.forEach(invoice => {
        const option = document.createElement('option');
        option.value = invoice.id;
        option.textContent = `Invoice #${invoice.id} - $${invoice.total_amount} (Due: ${invoice.due_date})`;
        option.dataset.amount = invoice.total_amount;
        invoiceSelect.appendChild(option);
    });
    
    // Auto-populate amount when invoice is selected
    invoiceSelect.addEventListener('change', function() {
        const selectedInvoice = this.options[this.selectedIndex];
        if (selectedInvoice.dataset.amount) {
            amountInput.value = selectedInvoice.dataset.amount;
        }
    });
}
</script>
@endpush
@endsection
