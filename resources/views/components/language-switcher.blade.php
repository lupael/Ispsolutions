@php
    $currentLocale = app()->getLocale();
    $availableLanguages = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'bn' => ['name' => 'বাংলা', 'flag' => '🇧🇩'],
    ];
@endphp

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center space-x-2 px-3 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <span class="text-lg">{{ $availableLanguages[$currentLocale]['flag'] }}</span>
        <span class="hidden sm:block text-sm font-medium">{{ $availableLanguages[$currentLocale]['name'] }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50"
         style="display: none;">
        <div class="py-1">
            @foreach($availableLanguages as $code => $language)
                <form method="POST" action="{{ route('language.switch') }}" class="block">
                    @csrf
                    <input type="hidden" name="language" value="{{ $code }}">
                    <button type="submit" 
                            class="flex items-center space-x-3 w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ $currentLocale === $code ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <span class="text-lg">{{ $language['flag'] }}</span>
                        <span>{{ $language['name'] }}</span>
                        @if($currentLocale === $code)
                            <svg class="w-4 h-4 ml-auto text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
