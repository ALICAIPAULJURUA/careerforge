<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-700 border border-rose-700 rounded-lg font-medium text-sm text-white shadow-sm hover:bg-rose-800 hover:border-rose-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 active:bg-rose-900 disabled:opacity-50 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
