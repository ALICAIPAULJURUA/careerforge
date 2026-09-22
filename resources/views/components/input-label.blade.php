@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ink-800 tracking-tight']) }}>
    {{ $value ?? $slot }}
</label>
