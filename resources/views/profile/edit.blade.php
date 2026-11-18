@php
    $user = Auth::user();
    $isGuru = $user && $user->role === 'guru';
    $layout = $isGuru ? 'premium-layout' : 'siswa-layout';
@endphp

<x-dynamic-component :component="$layout">
    <div class="max-w-4xl mx-auto pb-12 space-y-6">

        <!-- Ringkasan Identitas Pengguna -->
        <header class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="flex items-center gap-4 min-w-0">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-[#008546] text-white flex items-center justify-center font-bold text-2xl shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md font-semibold bg-[#008546] text-white">
                                {{ $isGuru ? 'Pengajar TKJ' : 'Siswa TKJ' }}
                            </span>
                            @if(! $isGuru && $user->class_name)
                                <span class="text-slate-700 font-semibold">{{ $user->class_name }}</span>
                            @endif
                            @if(! $isGuru && $user->student_number)
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-slate-600 font-medium">NIS {{ $user->student_number }}</span>
                            @elseif($isGuru && $user->teacher_number)
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-slate-600 font-medium">NIP {{ $user->teacher_number }}</span>
                            @endif
                        </div>

                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 truncate">
                            {{ $user->name }}
                        </h1>

                        <p class="text-xs sm:text-sm text-slate-500 truncate">
                            {{ $user->email }}
                        </p>
                    </div>
                </div>

                <div class="flex sm:flex-col sm:items-end justify-between gap-1 text-xs text-slate-500 shrink-0">
                    <span class="font-semibold text-slate-800">SMK Negeri 1 Kinali</span>
                    <span>Terdaftar {{ $user->created_at ? $user->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y') : 'Aktif' }}</span>
                </div>
            </div>
        </header>

        <!-- Form Informasi Profil -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            @include('profile.partials.update-profile-information-form')
        </div>

        <!-- Form Perbarui Kata Sandi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            @include('profile.partials.update-password-form')
        </div>

        @if($isGuru)
            <div class="bg-white rounded-2xl border border-rose-200 p-6 sm:p-8 shadow-2xs">
                @include('profile.partials.delete-user-form')
            </div>
        @endif

    </div>
</x-dynamic-component>
