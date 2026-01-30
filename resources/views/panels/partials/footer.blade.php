<footer class="bg-white border-t border-gray-200 py-4 px-6">
    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
        <div>
            &copy; {{ date('Y') }} ISP Solution. All rights reserved.
        </div>
        <div class="flex space-x-4 mt-2 md:mt-0">
            {{-- TODO: Create Privacy Policy page and route --}}
            <span role="link" aria-disabled="true" class="text-gray-400 cursor-not-allowed" title="Coming soon">Privacy Policy</span>
            {{-- TODO: Create Terms of Service page and route --}}
            <span role="link" aria-disabled="true" class="text-gray-400 cursor-not-allowed" title="Coming soon">Terms of Service</span>
            <a href="{{ route('panel.tickets.create') }}" class="hover:text-blue-600">Support</a>
        </div>
    </div>
</footer>
