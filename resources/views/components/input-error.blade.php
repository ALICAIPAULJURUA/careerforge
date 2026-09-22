@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-rose-700 bg-rose-50 border border-rose-100 rounded-lg px-3 py-2 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex gap-2"><span class="mt-0.5">•</span><span>{{ $message }}</span></li>
        @endforeach
    </ul>
@endif
