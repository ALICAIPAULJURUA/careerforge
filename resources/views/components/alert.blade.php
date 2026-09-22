@props(['type' => 'info', 'dismissible' => false])

@php
$styles = [
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-900',
    'error' => 'bg-rose-50 border-rose-200 text-rose-900',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-900',
    'info' => 'bg-sky-50 border-sky-200 text-sky-900',
    'neutral' => 'bg-slate-50 border-slate-200 text-slate-800',
];
$cls = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'border rounded-xl px-4 py-3.5 flex gap-3 text-sm leading-relaxed ' . $cls]) }} role="alert">
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" onclick="this.closest('[role=alert]').remove()" class="shrink-0 opacity-60 hover:opacity-100 -mr-1 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
