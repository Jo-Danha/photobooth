<!DOCTYPE html>
<html lang="id" data-default-lang="<?php echo e($boothSetting->ui_language ?? 'id'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(($boothSetting && $boothSetting->favicon_path) ? asset($boothSetting->favicon_path) : asset('favicon.svg')); ?>">
    <title><?php echo $__env->yieldContent('title', ($boothAppName ?? 'Photobooth Studio')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const brandPalette = <?php echo json_encode($brandPalette, 15, 512) ?>;
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: brandPalette
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
    <script src="<?php echo e(asset('js/i18n.js')); ?>"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Cegah halaman melebar kesamping (mobile/tablet) */
        html, body { max-width: 100vw; overflow-x: hidden; overscroll-behavior-x: none; }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">
    <?php if(!($hideChrome ?? false)): ?>
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-2 min-w-0 flex-wrap max-sm:h-auto max-sm:py-2">
            <a href="<?php echo e(route('photobooth.index')); ?>" id="boothLogo" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-camera-retro text-white text-lg"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white via-slate-200 to-brand-300 bg-clip-text text-transparent"><?php echo e($boothAppName ?? 'PHOTOBOOTH.IO'); ?></span>
                    <span class="text-[10px] block text-slate-400 font-semibold tracking-wider uppercase">Self-Photo Studio</span>
                </div>
            </a>
            
            <div class="flex items-center gap-3">
                <span class="text-xs px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Sistem Siap
                </span>
                <button type="button" onclick="window.toggleBoothLang()" title="Ganti Bahasa" data-lang-label class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 flex items-center justify-center text-xs font-bold">EN</button>
                <?php if(isset($boothSetting) && !$boothSetting->is_lock_mode): ?>
                <a href="<?php echo e(route('admin.login')); ?>" title="Admin" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 flex items-center justify-center">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <main class="flex-grow">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if(!($hideChrome ?? false)): ?>
    <footer class="border-t border-slate-800 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4">
            <?php
                $footerDefault = '© ' . date('Y') . ' ' . ($boothAppName ?? 'Photobooth Studio') . '. Ditenagai oleh Laravel & WebRTC Canvas.';
            ?>
            <p><?php echo nl2br(e($boothSetting->footer_text ?: $footerDefault)); ?></p>
        </div>
    </footer>
    <?php endif; ?>

    <?php echo $__env->yieldContent('scripts'); ?>

    <?php if(isset($boothSetting) && $boothSetting->is_lock_mode && !request()->routeIs('admin.*')): ?>
    <script>
        (function () {
            const adminLogin = "<?php echo e(route('admin.login')); ?>";

            // 1. Paksa layar penuh (fullscreen) saat interaksi pertama
            function enterFullscreen() {
                const el = document.documentElement;
                if (!document.fullscreenElement && el.requestFullscreen) {
                    el.requestFullscreen().catch(() => {});
                }
            }
            ['click', 'touchstart', 'keydown'].forEach(evt =>
                document.addEventListener(evt, enterFullscreen, { once: false })
            );

            // 2. Saat user berpindah tab / minimise, tampilkan overlay "Kembali"
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(2,6,23,.97);color:#fff;display:none;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-family:sans-serif;';
            overlay.innerHTML = '<div style="font-size:48px;margin-bottom:16px;">📸</div><div style="font-size:20px;font-weight:700;">Silakan kembali ke Photobooth</div><div style="font-size:13px;color:#94a3b8;margin-top:8px;">Ketuk di mana saja untuk melanjutkan</div>';
            overlay.addEventListener('click', () => { enterFullscreen(); overlay.style.display = 'none'; });
            document.body.appendChild(overlay);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    overlay.style.display = 'flex';
                }
            });

            // 3. Blokir klik kanan & kombinasi buka tab/devtools
            document.addEventListener('contextmenu', e => e.preventDefault());
            document.addEventListener('keydown', e => {
                const k = e.key.toLowerCase();
                if (e.ctrlKey && ['t', 'n', 'w'].includes(k)) e.preventDefault();
                if (e.ctrlKey && e.shiftKey && ['i', 'j', 'c'].includes(k)) e.preventDefault();
                if (k === 'f12') e.preventDefault();
            });

            // 4. Cara keluar kiosk (khusus admin): tahan logo 2.5 detik
            const logo = document.getElementById('boothLogo');
            if (logo) {
                let pressTimer = null;
                const start = () => { pressTimer = setTimeout(() => { window.location.href = adminLogin; }, 2500); };
                const cancel = () => { clearTimeout(pressTimer); };
                logo.addEventListener('mousedown', start);
                logo.addEventListener('touchstart', start);
                logo.addEventListener('mouseup', cancel);
                logo.addEventListener('mouseleave', cancel);
                logo.addEventListener('touchend', cancel);
            }
        })();
    </script>
    <?php endif; ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\photobooth\resources\views/layouts/app.blade.php ENDPATH**/ ?>