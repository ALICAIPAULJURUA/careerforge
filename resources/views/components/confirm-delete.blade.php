@props(['action', 'method' => 'DELETE', 'label' => 'Delete', 'confirmTitle' => 'Are you sure?', 'confirmText' => 'This action cannot be undone.'])

<div x-data="{ confirming: false }" class="inline-flex">
    <button x-show="!confirming" @click="confirming = true" type="button"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-rose-700 hover:text-rose-800 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-100 transition-colors']) }}>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        {{ $label }}
    </button>

    <div x-show="confirming" x-cloak x-transition class="flex items-center gap-2 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2">
        <span class="text-sm font-medium text-rose-900 hidden sm:inline">{{ $confirmTitle }}</span>
        <form method="POST" action="{{ $action }}" class="inline">
            @csrf
            @method($method)
            <button type="submit" class="px-3 py-1.5 bg-rose-700 text-white rounded-md text-sm font-medium hover:bg-rose-800">
                {{ __('Yes, delete') }}
            </button>
        </form>
        <button @click="confirming = false" type="button" class="px-3 py-1.5 bg-white border border-slate-200 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50">
            {{ __('Cancel') }}
        </button>
    </div>
</div>
