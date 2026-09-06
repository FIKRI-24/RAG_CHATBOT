<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Chatbot Pintar SMK N 1 Kinali</title>

    <!-- Scripts & Styles (bundled via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .login-wrapper {
            width: 100%;
            max-width: 380px !important;
            margin: 0 auto;
        }
    </style>
</head>
<body class="antialiased gradient-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Decorative background blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-300/20 blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-emerald-300/20 blur-[100px] pointer-events-none"></div>

    <div class="login-wrapper relative z-10">
        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-white shadow-sm border border-gray-100 mb-3 text-[#008546]">
                <i class="fa-solid fa-robot text-xl"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">RAG Chatbot Pintar</h1>
            <p class="text-sm text-gray-500 mt-1">SMK N 1 Kinali</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-2xl p-6 sm:p-8">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-gray-800 tracking-tight">Selamat Datang</h2>
                <p class="text-sm text-gray-500 mt-0.5">Silakan masuk ke akun Anda.</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fa-regular fa-envelope text-gray-400 text-sm"></i>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                               class="block w-full pl-10 pr-3 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-[#008546]/30 focus:border-[#008546] transition-all sm:text-sm" 
                               placeholder="nama@smkn1kinali.sch.id">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#008546] hover:text-[#006e39] transition-colors">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400 text-sm"></i>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password" 
                               class="block w-full pl-10 pr-3 py-2.5 bg-gray-50/50 border border-gray-200 text-gray-900 rounded-xl focus:ring-2 focus:ring-[#008546]/30 focus:border-[#008546] transition-all sm:text-sm" 
                               placeholder="••••••••">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center mt-2">
                    <input id="remember_me" name="remember" type="checkbox" 
                           class="h-4 w-4 text-[#008546] focus:ring-[#008546] border-gray-300 rounded cursor-pointer transition-colors">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-600 cursor-pointer">
                        Ingat saya
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-[#008546] hover:bg-[#00703c] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#008546] transition-all transform active:scale-[0.98]">
                        Log In
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer info -->
        <div class="text-center mt-8">
            <p class="text-xs text-gray-500">
                Belum punya akun? Hubungi Administrator Sekolah.
            </p>
        </div>
    </div>

</body>
</html>
