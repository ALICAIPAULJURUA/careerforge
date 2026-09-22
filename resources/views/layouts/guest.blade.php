<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CareerForge') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="min-h-full bg-surface-50 font-sans antialiased text-slate-700">
        <div class="min-h-screen flex">
            <!-- Brand panel — hidden on mobile -->
            <div class="hidden lg:flex lg:w-[44%] bg-ink-900 relative overflow-hidden">
                <div class="absolute inset-0">
                    <div class="absolute -top-24 -left-24 w-[520px] h-[520px] bg-honey-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-32 -right-32 w-[640px] h-[640px] bg-ink-600/40 rounded-full blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                </div>
                <div class="relative flex flex-col justify-between p-12 w-full max-w-md mx-auto">
                    <div>
                        <a href="/" class="inline-flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-white text-ink-900 grid place-items-center font-heading font-bold text-lg">C</span>
                            <span class="font-heading text-xl font-semibold tracking-tight text-white">CareerForge</span>
                        </a>
                    </div>
                    <div class="space-y-6">
                        <h1 class="font-heading text-4xl font-semibold tracking-tight text-white leading-[0.95]">
                            One profile.<br>
                            <span class="text-honey-50">Many resumes.</span>
                        </h1>
                        <p class="text-sm leading-relaxed text-slate-300 max-w-sm">
                            Build a single, complete career profile — then curate tailored resumes for each role without ever copying or losing source data.
                        </p>
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span class="w-8 h-px bg-honey-600"></span>
                            <span>Trusted by deliberate professionals</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">© {{ date('Y') }} CareerForge</p>
                </div>
            </div>

            <!-- Form panel -->
            <div class="flex-1 flex flex-col">
                <div class="lg:hidden flex items-center gap-3 px-6 py-6 border-b border-slate-200 bg-white">
                    <span class="w-9 h-9 rounded-xl bg-ink-900 text-white grid place-items-center font-heading font-bold">C</span>
                    <span class="font-heading text-lg font-semibold tracking-tight text-ink-900">CareerForge</span>
                </div>
                <div class="flex-1 flex flex-col justify-center px-6 py-10 sm:px-8">
                    <div class="w-full max-w-md mx-auto">
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-soft p-8 sm:p-9">
                            {{ $slot }}
                        </div>
                        <p class="mt-6 text-center text-xs text-slate-500">
                            Secure & private — your data stays yours.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
