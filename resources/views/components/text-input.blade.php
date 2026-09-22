@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 rounded-lg shadow-sm focus:border-ink-300 focus:ring-4 focus:ring-ink-100 focus:ring-offset-0 disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed transition-colors duration-150']) !!}>
