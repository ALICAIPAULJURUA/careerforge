<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-ink-800 border border-ink-800 rounded-lg font-medium text-sm text-white tracking-tight shadow-sm hover:bg-ink-900 hover:border-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-honey-600 focus-visible:ring-offset-2 active:bg-ink-900 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150']) }}>
    {{ $slot }}
</button>
