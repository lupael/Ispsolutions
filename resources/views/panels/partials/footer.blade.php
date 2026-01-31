<footer class="bg-white border-t border-gray-200 py-4 px-6">
    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
        <div>
            &copy; {{ date('Y') }} ISP Solution. All rights reserved.
        </div>
        <div class="flex space-x-4 mt-2 md:mt-0">
            <a href="{{ route('privacy-policy') }}" class="hover:text-blue-600">Privacy Policy</a>
            <a href="{{ route('terms-of-service') }}" class="hover:text-blue-600">Terms of Service</a>
            <a href="{{ route('support') }}" class="hover:text-blue-600">Support</a>
        </div>
    </div>
</footer>
