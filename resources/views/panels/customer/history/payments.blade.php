@extends('panels.layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Payment History</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Total Paid: <strong>{{ number_format($totalPaid, 2) }} BDT</strong></p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
                        <td class="px-6 py-4 text-sm"><span class="px-2 py-1 bg-green-100 text-green-800 rounded">{{ $payment->status }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No payment history</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($payments->hasPages())
            <div class="mt-4">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
