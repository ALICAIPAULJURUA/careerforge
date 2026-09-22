@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-ink-900 text-white text-sm font-medium tracking-tight shadow-sm'
            : 'inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-sm font-medium text-slate-600 hover:text-ink-900 hover:bg-slate-100 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
