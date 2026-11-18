<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-Modul TKJ') }} — SMK Negeri 1 Kinali</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-[#f0f3ed]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8">
            <div class="text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                    <x-application-logo class="w-12 h-12" />
                    <div class="text-left">
                        <span class="block text-lg font-bold tracking-tight text-slate-900">E-Modul TKJ<span class="text-[#008546]">.</span></span>
                        <span class="block text-[10px] font-semibold uppercase tracking-widest text-slate-500">SMK Negeri 1 Kinali</span>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white border border-slate-200/90 shadow-sm overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-[11px] text-slate-500">
                &copy; {{ now()->year }} SMK Negeri 1 Kinali
            </p>
        </div>
    </body>
</html>
