@props(['title', 'description' => null, 'action' => null])

<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-heading text-3xl font-semibold tracking-tight text-ink-900 leading-none">{{ $title }}</h1>
            @if($description)
                <p class="mt-3 text-sm leading-relaxed text-slate-600 max-w-2xl">{{ $description }}</p>
            @endif
            @if(isset($slot) && trim($slot) !== '')
                <div class="mt-3 text-sm text-slate-600">{{ $slot }}</div>
            @endif
        </div>
        @if($action)
            <div class="shrink-0">{{ $action }}</div>
        @endif
    </div>
</div>
