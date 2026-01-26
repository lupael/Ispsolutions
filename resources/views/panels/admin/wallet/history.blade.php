@extends('panels.layouts.app')

@section('title', 'Wallet Transaction History')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h1 class="text-3xl font-bold mb-4">Wallet Transaction History</h1>
        <p class="mb-6">User: {{ $user->name }} | Balance: {{ number_format($user->wallet_balance ?? 0, 2) }} {{ optional($user->billingProfile)->currency ?? config('app.currency', 'BDT') }}</p>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 text-xs rounded 
                                @if($transaction->type === 'credit') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium
                            @if($transaction->type === 'credit') text-green-600
                            @else text-red-600
                            @endif">
                            {{ $transaction->formatted_amount }}
                        </td>
                        <td class="px-6 py-4 text-sm">{{ number_format($transaction->balance_after, 2) }}</td>
                        <td class="px-6 py-4 text-sm">{{ $transaction->description }}</td>
                        <td class="px-6 py-4 text-sm">{{ $transaction->createdBy->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($transactions->hasPages())
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
