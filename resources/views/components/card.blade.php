@props(['padding' => 'p-6', 'hover' => false])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-2xl shadow-card ' . $padding . ($hover ? ' hover:shadow-soft transition-shadow duration-150' : '')]) }}>
    {{ $slot }}
</div>
