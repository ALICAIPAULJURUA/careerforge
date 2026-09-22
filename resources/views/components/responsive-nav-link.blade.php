@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2.5 rounded-xl bg-ink-900 text-white text-sm font-medium'
            : 'block w-full px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-ink-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
