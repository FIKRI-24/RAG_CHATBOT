@php
    $user = Auth::user();
    $isGuru = $user && $user->role === 'guru';
    $layout = $isGuru ? 'premium-layout' : 'siswa-layout';
@endphp

<x-dynamic-component :component="$layout">
    <div class="space-y-6 max-w-6xl mx-auto pb-10">
        
        <!-- Profile Overview Hero Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#008546] text-white p-6 sm:p-8 rounded-3xl shadow-lg relative overflow-hidden">
            <!-- Background Glow Decor -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <!-- User Avatar & Identity -->
                <div class="flex items-center gap-4 sm:gap-5">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover shadow-xl shadow-emerald-500/20 border-2 border-white/20 shrink-0">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-[#008546] to-emerald-400 text-white flex items-center justify-center font-extrabold text-2xl sm:text-3xl shadow-xl shadow-emerald-500/20 border-2 border-white/20 shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            @if($isGuru)
                                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                    <i class="fa-solid fa-chalkboard-user"></i> Pengajar TKJ
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                                    <i class="fa-solid fa-graduation-cap"></i> Siswa TKJ
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-medium bg-white/10 text-emerald-300 border border-white/10">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> Akun Aktif
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
                            {{ $user->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 flex items-center gap-2 mt-1">
                            <i class="fa-solid fa-envelope text-emerald-400"></i>
                            <span>{{ $user->email }}</span>
                        </p>
                    </div>
                </div>

                <!-- Meta Badges -->
                <div class="flex flex-col sm:flex-row md:flex-col lg:flex-row gap-2.5 text-xs text-slate-300 shrink-0">
                    <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/10 flex items-center gap-2">
                        <i class="fa-solid fa-school text-emerald-400"></i>
                        <span>SMK Negeri 1 Kinali</span>
                    </div>
                    <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/10 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-emerald-400"></i>
                        <span>Terdaftar: {{ $user->created_at ? $user->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y') : 'Aktif' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2-Column Balanced Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Ringkasan Akun & Keamanan (col-span-4) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Card: Status & Hak Akses Akun -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-sm border border-emerald-100">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Status & Keamanan</h4>
                            <p class="text-[11px] text-slate-400">Ikhtisar hak akses sistem</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 font-medium">Peran / Role:</span>
                            <span class="font-bold text-slate-800 capitalize">{{ $user->role ?? 'Siswa' }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 font-medium">Verifikasi Email:</span>
                            @if($user->hasVerifiedEmail())
                                <span class="font-bold text-emerald-600 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i> Terverifikasi
                                </span>
                            @else
                                <span class="font-bold text-amber-600 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation"></i> Belum
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 font-medium">Keamanan Sandi:</span>
                            <span class="font-bold text-emerald-600 flex items-center gap-1">
                                <i class="fa-solid fa-lock"></i> Bcrypt Hash
                            </span>
                        </div>
                    </div>

                    <!-- Tips Penggunaan Akun -->
                    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/60 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#008546]">
                            <i class="fa-solid fa-lightbulb"></i>
                            <span>Tips Akun Laboratorium</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Jika menggunakan komputer lab bersama di SMK N 1 Kinali, selalu klik tombol <strong>Keluar (Logout)</strong> sebelum meninggalkan meja kerja.
                        </p>
                    </div>
                </div>

                <!-- Card: Zona Bahaya (Hapus Akun) -->
                <div class="bg-white rounded-3xl border-2 border-rose-100 shadow-sm p-6">
                    @include('profile.partials.delete-user-form')
                </div>

            </div>

            <!-- Right Column: Form Edit Profil & Kata Sandi (col-span-8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Form Update Profil -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Form Update Password -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-8">
                    @include('profile.partials.update-password-form')
                </div>

            </div>

        </div>

    </div>
</x-dynamic-component>
