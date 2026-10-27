@php
    $user = Auth::user();
    $isGuru = $user && $user->role === 'guru';
    $layout = $isGuru ? 'premium-layout' : 'siswa-layout';
@endphp

<x-dynamic-component :component="$layout">
    <div class="space-y-6 max-w-6xl mx-auto pb-10">
        
        <!-- Cyber-Network Glassmorphism Header (Opsi C) -->
        <div class="relative bg-slate-950 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 rounded-3xl border border-emerald-500/20 shadow-2xl shadow-emerald-950/15 overflow-hidden backdrop-blur-xl">
            <!-- Subtle Cyber-Network Circuit & Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <svg class="absolute inset-0 w-full h-full pointer-events-none" style="opacity: 0.06;" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                <defs>
                    <pattern id="cyber-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.5" fill="#34d399" />
                        <path d="M 2 2 L 32 2 M 2 2 L 2 32" stroke="#34d399" stroke-width="0.5" stroke-dasharray="2 4" fill="none" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#cyber-grid)" />
            </svg>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <!-- User Avatar & Identity Node -->
                <div class="flex items-center gap-5">
                    <div class="relative shrink-0">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover ring-2 ring-emerald-400/40 shadow-lg shadow-emerald-950/50">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#008546] bg-gradient-to-br from-[#008546] to-emerald-700 text-white flex items-center justify-center font-extrabold text-2xl sm:text-3xl ring-2 ring-emerald-400/40 shadow-lg shadow-emerald-950/50">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <!-- Glowing Pulse Node -->
                        <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 ring-2 ring-slate-900"></span>
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                {{ $isGuru ? 'Instruktur TKJ' : 'Siswa TKJ' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-medium bg-white/10 text-slate-200 border border-white/15 backdrop-blur-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Akun Terverifikasi
                            </span>
                        </div>

                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white drop-shadow-sm">
                            {{ $user->name }}
                        </h1>
                        
                        <p class="text-xs sm:text-sm text-slate-300/90 font-medium flex items-center gap-2">
                            <i class="fa-regular fa-envelope text-slate-400 text-xs"></i>
                            {{ $user->email }}
                        </p>
                    </div>
                </div>

                <!-- Telemetry & Meta Nodes -->
                <div class="flex flex-wrap md:flex-col gap-2.5 text-xs text-slate-200 shrink-0">
                    <div class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md flex items-center gap-2.5">
                        <i class="fa-solid fa-school text-emerald-400 text-xs"></i>
                        <span class="font-medium">SMK Negeri 1 Kinali</span>
                    </div>
                    <div class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-md flex items-center gap-2.5">
                        <i class="fa-solid fa-calendar-check text-slate-400 text-xs"></i>
                        <span class="text-slate-300">Terdaftar: <strong class="text-white font-semibold">{{ $user->created_at ? $user->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y') : 'Aktif' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2-Column Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Status, Keamanan & Pengaturan Akun (col-span-4) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Card: Status & Hak Akses Akun -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-xs">
                    <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Status & Keamanan</h4>
                            <p class="text-xs text-slate-500">Ikhtisar hak akses sistem pembelajaran</p>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 flex items-center justify-center text-xs shrink-0">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="text-slate-500">Peran Sistem</span>
                            <span class="font-bold text-slate-900 capitalize">{{ $user->role ?? 'Siswa' }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="text-slate-500">Sesi Belajar</span>
                            <span class="inline-flex items-center gap-1.5 font-semibold text-[#008546]">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Terautentikasi
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="text-slate-500">Protokol Keamanan</span>
                            <span class="font-semibold text-slate-700">Enkripsi Bcrypt</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1">
                        <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-emerald-600 text-xs"></i>
                            Keamanan Lab Komputer
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Bila menggunakan PC lab SMK N 1 Kinali bersama, jangan lupa keluar (Logout) setelah selesai belajar agar sesi akun Anda aman.
                        </p>
                    </div>
                </div>

                <!-- Card: Zona Bahaya (Hapus Akun) -->
                <div class="bg-white rounded-2xl border border-rose-200 p-5 shadow-xs">
                    @include('profile.partials.delete-user-form')
                </div>

            </div>

            <!-- Right Column: Form Edit Profil & Kata Sandi (col-span-8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Form Update Profil -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-xs">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Form Update Password -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-xs">
                    @include('profile.partials.update-password-form')
                </div>

            </div>

        </div>

    </div>
</x-dynamic-component>
