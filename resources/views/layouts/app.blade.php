<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Photobooth Studio')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf4f8', 100: '#fbe8f2', 200: '#f7d3e6', 300: '#f0b0d3',
                            400: '#e580b8', 500: '#d7529a', 600: '#c2337d', 700: '#a32463',
                            800: '#872152', 900: '#712046',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('photobooth.index') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-camera-retro text-white text-lg"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-brand-300 bg-clip-text text-transparent">PHOTOBOOTH<span class="text-brand-500">.IO</span></span>
                    <span class="text-[10px] block text-slate-400 font-semibold tracking-wider uppercase">Self-Photo Studio</span>
                </div>
            </a>
            
            <div class="flex items-center gap-3">
                <span class="text-xs px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Sistem Siap
                </span>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="border-t border-slate-800 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} Photobooth Studio. Ditenagai oleh Laravel & WebRTC Canvas.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>