@extends('panels.layouts.app')

@section('title', 'Adjust Wallet Balance')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h1 class="text-3xl font-bold mb-6">Adjust Wallet Balance</h1>
        <p class="mb-4">User: {{ $user->name }}</p>

        @php
            $currency = optional($user->billingProfile)->currency ?? config('app.currency', 'BDT');
        @endphp
        <div class="mb-6 p-4 bg-blue-50 rounded">
            <p class="text-sm">Current Balance</p>
            <p class="text-2xl font-bold">{{ number_format($user->wallet_balance ?? 0, 2) }} {{ $currency }}</p>
        </div>

        <form action="{{ route('panel.admin.wallet.adjust', $user) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Amount *</label>
                    <input type="number" name="amount" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300">
                    <p class="text-sm text-gray-500">Positive to add, negative to deduct</p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Description *</label>
                    <textarea name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ url()->previous() }}" class="px-4 py-2 border rounded-md">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Adjust Balance</button>
            </div>
        </form>
    </div>
</div>
@endsection
