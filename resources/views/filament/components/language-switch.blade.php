@php
    $currentLocale = app()->getLocale();
@endphp

<div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-white/50 px-1 py-0.5 text-xs font-medium dark:border-gray-800 dark:bg-gray-900/60 shadow-xs">
    <a
        href="{{ route('filament.admin.locale.switch', ['locale' => 'en']) }}"
        @class([
            'rounded px-2 py-0.5 transition-colors',
            'bg-amber-500 font-semibold text-white dark:bg-amber-600 shadow-xs' => $currentLocale === 'en',
            'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $currentLocale !== 'en',
        ])
        title="English"
    >
        EN
    </a>
    <span class="text-gray-300 dark:text-gray-700 select-none">|</span>
    <a
        href="{{ route('filament.admin.locale.switch', ['locale' => 'bn']) }}"
        @class([
            'rounded px-2 py-0.5 transition-colors font-bangla',
            'bg-amber-500 font-semibold text-white dark:bg-amber-600 shadow-xs' => $currentLocale === 'bn',
            'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $currentLocale !== 'bn',
        ])
        title="বাংলা (Bangla)"
    >
        বাং
    </a>
</div>
