<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kuis Objektif | SMK N 1 Kinali</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden" x-data="{ menuOpen: false }">
    <div x-show="menuOpen" @click="menuOpen = false" class="fixed inset-0 bg-slate-900/50 z-30 md:hidden" style="display:none"></div>
    <aside class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform md:translate-x-0 transition-transform" :class="menuOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center overflow-hidden shrink-0"><img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Kinali" class="w-10 h-10 object-contain"></div>
                <div><p class="font-bold">E-Modul TKJ</p><p class="text-[10px] text-emerald-600 font-semibold">SMK N 1 Kinali</p></div>
            </div>
            <button type="button" @click="menuOpen = false" aria-label="Tutup menu" class="md:hidden p-2"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <nav class="flex-1 p-4 space-y-2 text-sm font-semibold">
            <a href="{{ route('siswa.dashboard') }}" class="flex gap-3 p-3 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-robot text-emerald-700"></i>Tanya Jawab AI (Chatbot)</a>
            <a href="{{ route('siswa.modules.index') }}" class="flex gap-3 p-3 rounded-xl bg-emerald-50 text-emerald-800"><i class="fa-solid fa-book-bookmark"></i>Katalog E-Modul</a>
            <a href="{{ route('siswa.petunjuk') }}" class="flex gap-3 p-3 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-circle-question"></i>Petunjuk Siswa</a>
            <a href="{{ route('profile.edit') }}" class="flex gap-3 p-3 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-user-gear"></i>Profil Saya</a>
        </nav>
        <div class="p-4 border-t border-slate-100 flex items-center justify-between gap-2">
            <p class="font-semibold text-sm truncate">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" aria-label="Keluar" class="p-2 text-slate-500 hover:text-rose-700"><i class="fa-solid fa-right-from-bracket"></i></button></form>
        </div>
    </aside>
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <header class="h-16 shrink-0 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center gap-3">
            <button type="button" @click="menuOpen = true" :aria-expanded="menuOpen" aria-label="Buka menu" class="md:hidden p-2"><i class="fa-solid fa-bars"></i></button>
            <p class="font-semibold text-sm">Kuis Objektif</p>
        </header>
        <div class="flex-1 overflow-y-auto">{{ $slot }}</div>
    </main>
</body>
</html>
