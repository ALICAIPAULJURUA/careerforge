<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CareerForge') }} — {{ $title ?? '' }}</title>

        <!-- Typography — Fraunces for headings, Plus Jakarta Sans for body -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="min-h-full bg-surface-50 font-sans antialiased text-slate-700 selection:bg-honey-100 selection:text-ink-900">
        <div class="min-h-screen flex flex-col">
            <livewire:layout.navigation />

            @if (isset($header))
                <header class="bg-white border-b border-slate-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-7">
                        <div class="font-heading text-[22px] font-semibold tracking-tight text-ink-900 leading-tight">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endif

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
                    {{ $slot }}
                </div>
            </main>

            <footer class="mt-auto border-t border-slate-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row justify-between gap-3 text-xs text-slate-500">
                    <span class="font-medium tracking-wide">CareerForge — one profile, many resumes.</span>
                    <span>Built for the deliberate job-seeker.</span>
                </div>
            </footer>
        </div>
    </body>
</html>
