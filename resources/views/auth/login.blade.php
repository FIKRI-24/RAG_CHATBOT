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
            <!-- Background Foto Gedung SMK N 1 Kinali (Full Card) -->
            <div class="login-story-bg" style="position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; margin: 0 !important; padding: 0 !important; z-index: 0 !important; overflow: hidden !important; pointer-events: none !important;" aria-hidden="true">
                <img src="{{ asset('images/smk-kinali.jpg') }}" alt="Gedung Bengkel Praktik TJKT SMK Negeri 1 Kinali" class="login-story-bg-img" style="position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; object-fit: cover !important; object-position: center 38% !important; display: block !important;">
                <div class="login-story-bg-overlay" style="position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important;"></div>
            </div>
            <a class="login-brand" href="{{ route('login') }}" aria-label="E-Modul TKJ SMK Negeri 1 Kinali dan UPGRISBA, halaman masuk" style="display: inline-flex !important; align-items: center !important; gap: 16px !important; text-decoration: none !important;">
                <div class="login-brand-logos" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 12px !important;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Kinali" class="brand-logo-img" title="SMK Negeri 1 Kinali" style="height: 56px !important; width: auto !important; max-width: 60px !important; object-fit: contain !important; filter: drop-shadow(0 3px 8px rgba(0, 0, 0, 0.5)) !important; display: block !important;">
                    <img src="{{ asset('images/logo-upgrisba.png') }}" alt="Logo UPGRISBA" class="brand-logo-img" title="Universitas PGRI Sumatera Barat (UPGRISBA)" style="height: 56px !important; width: auto !important; max-width: 60px !important; object-fit: contain !important; filter: drop-shadow(0 3px 8px rgba(0, 0, 0, 0.5)) !important; display: block !important;">
                </div>
                <div class="login-brand-text">
                    <strong style="font-size: 20px; font-weight: 700; letter-spacing: -0.5px; color: #fff; display: block; line-height: 1.2; text-shadow: 0 2px 8px rgba(0, 20, 10, 0.7);">E-Modul TKJ<span class="brand-dot" style="color: #c5ef9b;">.</span></strong>
                    <small style="display: block; margin-top: 4px; color: #c2dccb; font-size: 10px; font-weight: 600; letter-spacing: 1.8px; text-shadow: 0 1px 4px rgba(0, 20, 10, 0.6);">SMK NEGERI 1 KINALI &bull; UPGRISBA</small>
                </div>
            </a>
            <div class="login-story-content">
                <div class="login-eyebrow"><span></span> RUANG TUMBUH, RUANG BELAJAR</div>
                <h1 id="story-title">Langkah kecil.<br><span>Pemahaman besar.</span></h1>
                <p class="login-story-description">Pelajari modul TKJ, asah kemampuan lewat latihan, dan temukan bantuan dari asisten AI saat dibutuhkan.</p>
                <div class="login-building-tag">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    <span>Bengkel Praktik TJKT &mdash; SMK Negeri 1 Kinali</span>
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