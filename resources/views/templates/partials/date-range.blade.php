@props(['start', 'end', 'isCurrent' => false])
<span class="text-sm text-gray-600">
    {{ $start ? \Carbon\Carbon::parse($start)->format('M Y') : '' }}
    –
    @if($isCurrent)
        Present
    @elseif($end)
        {{ \Carbon\Carbon::parse($end)->format('M Y') }}
    @else
        —
    @endif
</span>
