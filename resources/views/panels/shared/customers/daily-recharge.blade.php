@extends('panels.layouts.app')

@section('title', 'Daily Recharge - ' . $customer->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Recharge for {{ $customer->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('panel.admin.customers.show', $customer) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <!-- Customer Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Customer</span>
                                    <span class="info-box-number">{{ $customer->name }}</span>
                                    <span class="text-sm">{{ $customer->username }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Balance</span>
                                    <span class="info-box-number">{{ number_format($customer->wallet_balance ?? 0, 2) }}</span>
                                    <span class="text-sm">BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recharge Form -->
                    <form action="{{ route('panel.admin.customers.daily-recharge.process', $customer) }}" method="POST" id="rechargeForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="package_id">Select Package <span class="text-danger">*</span></label>
                                    <select name="package_id" id="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                                        <option value="">-- Select Package --</option>
                                        @foreach($dailyPackages as $package)
                                            <option value="{{ $package->id }}" 
                                                    data-price="{{ $package->daily_rate ?? $package->price }}"
                                                    {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                                {{ $package->name }} - {{ number_format($package->daily_rate ?? $package->price, 2) }} BDT/day
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('package_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="days">Number of Days <span class="text-danger">*</span></label>
                                    <input type="number" name="days" id="days" 
                                           class="form-control @error('days') is-invalid @enderror" 
                                           value="{{ old('days', 1) }}" 
                                           min="1" max="30" required>
                                    @error('days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Enter number of days (1-30)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">-- Select Method --</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                        <option value="wallet" {{ old('payment_method') == 'wallet' ? 'selected' : '' }}>Wallet Balance</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Amount</label>
                                    <div class="input-group">
                                        <input type="text" id="total_amount" class="form-control" readonly value="0.00">
                                        <div class="input-group-append">
                                            <span class="input-group-text">BDT</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Notes (Optional)</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-bolt"></i> Process Recharge
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recharge History -->
            @if($rechargeHistory->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Recent Recharge History</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Package</th>
                                <th>Days</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rechargeHistory as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $transaction->notes ?? 'N/A' }}</td>
                                <td>{{ $transaction->payment_data['days'] ?? 1 }}</td>
                                <td>{{ number_format($transaction->amount, 2) }} BDT</td>
                                <td>{{ ucfirst($transaction->payment_method ?? 'N/A') }}</td>
                                <td>
                                    <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('package_id');
    const daysInput = document.getElementById('days');
    const totalAmountInput = document.getElementById('total_amount');

    function calculateTotal() {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        const pricePerDay = parseFloat(selectedOption.dataset.price) || 0;
        const days = parseInt(daysInput.value) || 0;
        const total = pricePerDay * days;
        
        totalAmountInput.value = total.toFixed(2);
    }

    packageSelect.addEventListener('change', calculateTotal);
    daysInput.addEventListener('input', calculateTotal);
    
    // Calculate on page load if values are present
    calculateTotal();
});
</script>
@endpush
@endsection
