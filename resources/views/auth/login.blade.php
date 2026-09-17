@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#008546">
    <title>Masuk — E-Modul TKJ | SMK Negeri 1 Kinali</title>
    @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js'])
</head>
<body class="login-page" x-data="{ feature: 'modul', showPassword: false, submitting: false, capsLock: false }" @pageshow.window="submitting = false">
    <a class="login-skip" href="#login-form">Langsung ke form masuk</a>
    <main class="login-shell">
        <section class="login-story" aria-labelledby="story-title">
            <a class="login-brand" href="{{ route('login') }}" aria-label="E-Modul TKJ SMK Negeri 1 Kinali, halaman masuk">
                <span class="login-brand-icon"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i></span>
                <span><strong>E-Modul TKJ<span class="brand-dot">.</span></strong><small>SMK NEGERI 1 KINALI</small></span>
            </a>
            <div class="login-story-content">
                <div class="login-eyebrow"><span></span> RUANG TUMBUH, RUANG BELAJAR</div>
                <h1 id="story-title">Langkah kecil.<br><span>Pemahaman besar.</span></h1>
                <p class="login-story-description">Pelajari modul TKJ, asah kemampuan lewat latihan, dan temukan bantuan dari asisten AI saat dibutuhkan.</p>
                <div class="learning-scene" aria-hidden="true">
                    <div class="scene-orbit orbit-one"></div><div class="scene-orbit orbit-two"></div>
                    <span class="scene-spark spark-one">✦</span><span class="scene-spark spark-two">✦</span>
                    <div class="scene-note note-top"><span class="note-icon"><i class="fa-solid fa-check"></i></span><div>Selangkah lebih paham<small>Mulai dari rasa ingin tahu.</small></div></div>
                    <div class="scene-book">
                        <div class="book-heading"><span><i class="fa-solid fa-layer-group"></i> E-MODUL TKJ</span><i class="fa-solid fa-ellipsis"></i></div>
                        <svg viewBox="0 0 320 148" class="book-illustration" fill="none">
                            <path d="M160 127C125 107 88 101 42 108V29C86 18 125 29 160 48C195 29 234 18 278 29V108C232 101 195 107 160 127Z" fill="#E3F2DD"/>
                            <path d="M160 117C130 98 98 94 57 99V17C98 14 130 27 160 47V117Z" fill="#FAFFF7"/>
                            <path d="M160 117C190 98 222 94 263 99V17C222 14 190 27 160 47V117Z" fill="#C7E8AC"/>
                            <path d="M160 47V117" stroke="#83B779" stroke-width="2"/>
                            <path d="M77 42C96 43 112 49 135 61M77 58C96 59 112 65 135 77M77 74C91 75 103 79 116 85" stroke="#B4C8A8" stroke-width="4" stroke-linecap="round"/>
                            <rect x="190" y="42" width="47" height="32" rx="8" fill="#008546"/>
                            <path d="M202 54L210 59L202 64M217 65H225" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M189 87L236 81" stroke="#85B46D" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                        <div class="book-caption"><span>Kenali. Pelajari. Kuasai.</span><span class="book-arrow">↗</span></div>
                    </div>
                    <div class="scene-note note-bottom"><span class="note-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span><div>Teman berpikir kamu<small>Asisten AI berbasis materi.</small></div></div>
                </div>
                <div class="login-feature-buttons" aria-label="Jelajahi fitur belajar" x-cloak>
                    <button type="button" @click="feature = 'modul'" :aria-pressed="feature === 'modul'" :class="{ 'is-active': feature === 'modul' }"><i class="fa-solid fa-book-open" aria-hidden="true"></i> E-Modul</button>
                    <button type="button" @click="feature = 'chat'" :aria-pressed="feature === 'chat'" :class="{ 'is-active': feature === 'chat' }"><i class="fa-regular fa-comments" aria-hidden="true"></i> Tanya AI</button>
                    <button type="button" @click="feature = 'quiz'" :aria-pressed="feature === 'quiz'" :class="{ 'is-active': feature === 'quiz' }"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Latihan</button>
                </div>
                <p class="login-feature-description" aria-live="polite" x-text="feature === 'modul' ? 'Materi dari guru, bisa dipelajari sesuai ritmemu.' : (feature === 'chat' ? 'Tanyakan materi yang belum dipahami kepada asisten AI.' : 'Uji pemahaman dan belajar lagi dari setiap jawaban.')">Materi dari guru, bisa dipelajari sesuai ritmemu.</p>
            </div>
            <div class="login-story-footer"><span>TEKNIK KOMPUTER & JARINGAN</span><span>Belajar hari ini, siap untuk esok <span aria-hidden="true">↗</span></span></div>
        </section>
        <section class="login-access" aria-labelledby="login-title">
            <div class="login-access-top"><span>E-MODUL INTERAKTIF</span><a href="#login-help">Butuh bantuan? <i class="fa-regular fa-circle-question" aria-hidden="true"></i></a></div>
            <div class="login-form-wrap">
                <div class="login-welcome-icon" aria-hidden="true"><i class="fa-solid fa-arrow-right-to-bracket"></i><span>✦</span></div>
                <p class="login-kicker">SELAMAT DATANG KEMBALI</p>
                <h2 id="login-title">Siap belajar<br> hal baru?</h2>
                <p class="login-intro">Masuk ke akunmu dan lanjutkan perjalanan belajarmu.</p>
                @if(session('status'))
                    <div class="login-status" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
                @endif
                <form id="login-form" method="POST" action="{{ route('login') }}" @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }" :aria-busy="submitting">
                    @csrf
                    <div class="login-field">
                        <label for="email">Alamat email</label>
                        <div class="login-input-wrap {{ $errors->has('email') ? 'has-error' : '' }}">
                            <i class="fa-regular fa-envelope field-icon" aria-hidden="true"></i>
                            <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}" required autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="255" placeholder="nama@email.com" @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                        </div>
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="login-field-error" />
                    </div>
                    <div class="login-field">
                        <label for="password">Password</label>
                        <div class="login-input-wrap {{ $errors->has('password') ? 'has-error' : '' }}">
                            <i class="fa-solid fa-lock field-icon" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" maxlength="1024" placeholder="Masukkan passwordmu" @keydown="capsLock = $event.getModifierState('CapsLock')" @keyup="capsLock = $event.getModifierState('CapsLock')" @blur="capsLock = false" aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}" @if($errors->has('password')) aria-invalid="true" @endif>
                            <button type="button" class="login-password-toggle" x-cloak @click="showPassword = !showPassword" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :aria-pressed="showPassword" aria-controls="password"><i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i></button>
                        </div>
                        <p id="password-hint" class="login-caps-hint" x-cloak x-show="capsLock" role="status">Caps Lock aktif. Periksa huruf besar pada passwordmu.</p>
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="login-field-error" />
                    </div>
                    <div class="login-form-options">
                        <label class="login-remember" for="remember_me"><input id="remember_me" name="remember" type="checkbox" @checked(old('remember'))><span>Ingat saya</span></label>
                        @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Lupa password?</a>@endif
                    </div>
                    <button type="submit" class="login-submit" :disabled="submitting"><span x-text="submitting ? 'Sedang masuk…' : 'Masuk ke ruang belajar'">Masuk ke ruang belajar</span><i class="fa-solid" :class="submitting ? 'fa-circle-notch fa-spin' : 'fa-arrow-right'" aria-hidden="true"></i></button>
                </form>
                <div class="login-account-help" id="login-help" tabindex="-1"><span class="help-icon"><i class="fa-solid fa-user-group" aria-hidden="true"></i></span><p><strong>Belum punya akun?</strong><br>Hubungi guru atau administrator sekolah untuk mendapatkan akun belajar.</p></div>
                @if(config('security.registration_enabled'))
                    <p class="login-register">Pendaftaran mandiri tersedia. <a href="{{ route('register') }}">Daftar akun siswa <span aria-hidden="true">↗</span></a></p>
                @endif
            </div>
            <footer class="login-access-footer"><span>© {{ now()->year }} SMK Negeri 1 Kinali</span><span><i class="fa-solid fa-seedling" aria-hidden="true"></i> Tumbuh bersama pengetahuan.</span></footer>
        </section>
    </main>
</body>
</html>
