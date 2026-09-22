@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center gap-2 font-medium tracking-tight rounded-lg transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$sizes = [
    'sm' => 'px-3.5 py-2 text-sm',
    'md' => 'px-5 py-2.5 text-sm',
    'lg' => 'px-6 py-3 text-sm',
    'icon' => 'p-2.5 text-sm',
];

$variants = [
    'primary' => 'bg-ink-800 text-white border border-ink-800 shadow-sm hover:bg-ink-900 hover:border-ink-900 focus-visible:ring-honey-600',
    'secondary' => 'bg-white text-ink-700 border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-slate-300 focus-visible:ring-ink-200',
    'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-ink-800 border border-transparent focus-visible:ring-ink-200',
    'danger' => 'bg-rose-700 text-white border border-rose-700 shadow-sm hover:bg-rose-800 focus-visible:ring-rose-500',
    'subtle' => 'bg-slate-100 text-ink-700 hover:bg-slate-200 border border-transparent focus-visible:ring-ink-200',
    'honey' => 'bg-honey-600 text-white border border-honey-600 shadow-sm hover:bg-honey-700 focus-visible:ring-honey-600',
];

$classes = $base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
