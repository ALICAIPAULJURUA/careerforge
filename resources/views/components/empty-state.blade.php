@props(['title' => 'Nothing here yet', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-6 border border-dashed border-slate-200 rounded-2xl bg-slate-50/50']) }}>
    @if($icon)
        <div class="mx-auto w-12 h-12 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 mb-4">
            {{ $icon }}
        </div>
    @endif
    <h3 class="font-heading text-base font-semibold text-ink-900">{{ $title }}</h3>
    @if($description)
        <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">{{ $description }}</p>
    @endif
    @if(isset($slot) && trim($slot) !== '')
        <div class="mt-6 flex justify-center">{{ $slot }}</div>
    @endif
</div>
