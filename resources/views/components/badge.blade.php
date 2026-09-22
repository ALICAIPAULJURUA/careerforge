@props(['variant' => 'neutral'])

@php
$variants = [
    'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
    'accent' => 'bg-honey-50 text-ink-800 border-honey-100',
    'success' => 'bg-emerald-50 text-emerald-800 border-emerald-100',
    'warning' => 'bg-amber-50 text-amber-800 border-amber-100',
    'danger' => 'bg-rose-50 text-rose-800 border-rose-100',
    'ink' => 'bg-ink-800 text-white border-ink-800',
];
$cls = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium tracking-wide border ' . $cls]) }}>
    {{ $slot }}
</span>
