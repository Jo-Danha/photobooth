<?php $__env->startSection('title', 'Photobooth Studio Live - Ambil Foto & Edit'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    @keyframes cameraFlash {
        0% { opacity: 0; }
        50% { opacity: 1; }
        100% { opacity: 0; }
    }
    .flash-active {
        animation: cameraFlash 0.25s ease-out;
    }
    .shape-btn.active {
        border-color: var(--brand, #c2337d);
        background: rgba(194,51,125,0.12);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<?php $__env->stopSection(); ?>

<?php
    $isEvent = $session->payment_method === 'FREE_EVENT_MODE';
?>

<?php $__env->startSection('content'); ?>
<!-- Top Bar: Timer Sesi -->
<div class="bg-slate-900 border-b border-slate-800 sticky top-16 z-40 px-4 py-2.5">
    <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-400 hidden sm:inline">Paket:</span>
            <span class="text-xs font-bold px-2.5 py-1 rounded bg-brand-950 text-brand-300 border border-brand-800/60">
                <?php echo e($session->package_name); ?>

            </span>
        </div>

        <div class="flex items-center gap-2">
            <?php if($setting->bg_music_path): ?>
            <button type="button" id="btnToggleMusic" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 flex items-center justify-center" title="Musik Latar">
                <i class="fa-solid fa-volume-high"></i>
            </button>
            <?php endif; ?>
            <span class="text-xs text-slate-400 font-medium">Sisa Waktu:</span>
            <div id="sessionTimerBadge" class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-950 border border-emerald-700/60 text-emerald-400 font-mono font-bold text-sm">
                <i class="fa-regular fa-clock animate-pulse"></i>
                <span id="sessionTimerText">00:00</span>
            </div>
        </div>
    </div>

    <div class="w-full bg-slate-800 h-1 mt-2 rounded-full overflow-hidden">
        <div id="sessionProgressBar" class="bg-gradient-to-r from-emerald-500 via-brand-500 to-amber-500 h-full w-full transition-all duration-1000"></div>
    </div>
</div>

<?php if($setting->bg_music_path): ?>
<audio id="bgMusic" src="<?php echo e(asset($setting->bg_music_path)); ?>" loop preload="auto"></audio>
<?php endif; ?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <div id="flashOverlay" class="fixed inset-0 bg-white pointer-events-none z-50 opacity-0"></div>

    <!-- TAHAP 1: PENGAMBILAN FOTO -->
    <div id="captureSection" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between relative shadow-2xl">
            <div class="flex items-center justify-between mb-3 z-10">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider" data-i18n="studio.viewfinder">Live Viewfinder</span>
                </div>
                <select id="cameraSelect" class="bg-slate-950 border border-slate-700 text-slate-300 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none">
                    <option value="">Memuat Kamera...</option>
                </select>
            </div>

            <div class="relative bg-black rounded-xl overflow-hidden aspect-[4/3] flex items-center justify-center border border-slate-800">
                <video id="videoFeed" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                <!-- Live theme overlay (dog ears / hearts) — mirip photobooth-io -->
                <div id="themeLiveOverlay" class="absolute inset-0 pointer-events-none z-10 hidden flex flex-col items-center justify-between py-6">
                    <div id="themeLiveTop" class="text-3xl"></div>
                    <div id="themeLiveBottom" class="text-2xl"></div>
                </div>

                <div id="countdownOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-20">
                    <span id="countdownNumber" class="text-8xl sm:text-9xl font-black text-white drop-shadow-[0_0_25px_rgba(215,82,154,0.8)] animate-bounce">3</span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-400">
                    <span data-i18n="studio.shots">Foto</span>: <span id="currentShotNumber" class="font-bold text-white text-sm">1</span> / <span id="totalShotsNumber" class="font-bold text-white text-sm">4</span>
                </div>

                <div class="flex items-center gap-3">
                    <button id="btnStartAutoShoot" class="px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-camera"></i>
                        <span data-i18n="studio.autoshoot">Jepret Otomatis (3s)</span>
                    </button>
                    <button id="btnManualSnap" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700">
                        <i class="fa-solid fa-circle-dot"></i> <span data-i18n="studio.manual">1x Jepret</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Thumbnails -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-2xl">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white" data-i18n="studio.results">Hasil Foto</h3>
                    <button id="btnResetShots" class="text-xs text-rose-400 hover:text-rose-300">
                        <i class="fa-solid fa-rotate-left"></i> <span data-i18n="studio.reset">Ulang</span>
                    </button>
                </div>
                <div id="thumbnailsContainer" class="space-y-2 max-h-[420px] overflow-y-auto pr-1"></div>
            </div>

            <button id="btnProceedToEdit" disabled class="w-full mt-4 py-3.5 rounded-xl bg-emerald-600 disabled:bg-slate-800 disabled:text-slate-500 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg flex items-center justify-center gap-2">
                <span data-i18n="studio.proceed">Lanjut ke Kustomisasi Frame</span>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </button>
        </div>
    </div>

    <!-- TAHAP 2: KUSTOMISASI CANVAS -->
    <div id="editorSection" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col items-center justify-center shadow-2xl">
            <div class="text-xs font-semibold text-slate-400 mb-2 flex items-center justify-between w-full">
                <span data-i18n="studio.preview">Preview Strip</span>
                <button id="btnBackToCapture" class="text-xs text-slate-400 hover:text-white">
                    <i class="fa-solid fa-camera"></i> <span data-i18n="studio.retake">Foto Ulang</span>
                </button>
            </div>
            <div class="bg-slate-950 p-2 rounded-xl border border-slate-800 max-h-[580px] overflow-auto flex items-center justify-center">
                <canvas id="photoStripCanvas" class="max-h-[540px] max-w-full rounded shadow-2xl"></canvas>
            </div>
        </div>

        <!-- Panel Kustomisasi -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl flex flex-col justify-between">
            <div class="space-y-6">
                <!-- 1. Warna Bingkai -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider" data-i18n="studio.frame_color">1. Warna Bingkai</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">Custom:</span>
                            <input type="color" id="frameColorCustom" value="<?php echo e($setting->default_frame_color); ?>" <?php echo e($setting->lock_frame_color ? 'disabled' : ''); ?> class="w-6 h-6 rounded border-0 cursor-pointer bg-transparent">
                        </div>
                    </div>
                    <div class="grid grid-cols-6 sm:grid-cols-9 gap-2">
                        <button class="color-preset w-full h-8 rounded-lg border-2 border-slate-600 bg-white" data-color="#FFFFFF"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-black" data-color="#111111"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-rose-200" data-color="#FECDD3"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-amber-100" data-color="#FEF3C7"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-emerald-200" data-color="#A7F3D0"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-sky-200" data-color="#BAE6FD"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-purple-200" data-color="#E9D5FF"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-indigo-900" data-color="#312E81"></button>
                        <button class="color-preset w-full h-8 rounded-lg border border-slate-700 bg-rose-600" data-color="#E11D48"></button>
                    </div>
                </div>

                <!-- 2. Filter Visual -->
                <div>
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2" data-i18n="studio.filter">2. Filter Visual</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-brand-950 border border-brand-500 text-brand-300" data-filter="normal">Normal</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="bw">B&W Retro</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="vintage">Vintage Film</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="warm">Warm Glow</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="cool">Cool Tone</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="sepia">Sepia 80s</button>
                    </div>
                </div>

                <!-- 2b. Bentuk Foto -->
                <div>
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2" data-i18n="studio.shape">Bentuk Foto</label>
                    <div class="grid grid-cols-4 gap-2" id="shapeGroup">
                        <button class="shape-btn py-2 px-2 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-shape="none"><i class="fa-solid fa-square"></i> <span data-i18n="studio.shape_none">Kotak</span></button>
                        <button class="shape-btn py-2 px-2 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-shape="soft"><i class="fa-regular fa-square"></i> <span data-i18n="studio.shape_soft">Soft</span></button>
                        <button class="shape-btn py-2 px-2 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-shape="circle"><i class="fa-regular fa-circle"></i> <span data-i18n="studio.shape_circle">Lingkaran</span></button>
                        <button class="shape-btn py-2 px-2 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-shape="heart"><i class="fa-solid fa-heart"></i> <span data-i18n="studio.shape_heart">Hati</span></button>
                    </div>
                </div>

                <!-- 3. Stiker Digital -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider" data-i18n="studio.stickers">3. Stiker Digital</label>
                        <button id="btnClearStickers" class="text-xs text-rose-400 hover:text-rose-300" data-i18n="studio.clear_stickers">Hapus Semua</button>
                        <span class="text-[10px] text-slate-500">Klik untuk tambah • Geser untuk pindah • Klik 2x untuk hapus</span>
                    </div>
                    <div class="flex flex-wrap gap-2 p-3 bg-slate-950 rounded-xl border border-slate-800 max-h-40 overflow-y-auto">
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="❤️">❤️</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="💗">💗</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="💕">💕</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="✨">✨</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="⭐">⭐</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌟">🌟</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="💫">💫</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎀">🎀</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌸">🌸</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌺">🌺</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌼">🌼</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌈">🌈</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🐱">🐱</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🐶">🐶</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🐾">🐾</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🦴">🦴</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🍒">🍒</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🍓">🍓</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌿">🌿</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🦋">🦋</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🔥">🔥</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌞">🌞</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌙">🌙</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎉">🎉</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎈">🎈</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎄">🎄</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="❄️">❄️</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="💎">💎</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="👑">👑</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🍕">🍕</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="☕">☕</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎵">🎵</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="💜">💜</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🌟">🌟</button>
                    </div>
                </div>

                <!-- 4. Text & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5" data-i18n="studio.custom_text">Teks Kustom</label>
                        <input type="text" id="customTextInput" value="<?php echo e($setting->default_brand_text); ?>" <?php echo e($setting->lock_brand_text ? 'disabled' : ''); ?> class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2.5 <?php echo e($setting->lock_brand_text ? 'opacity-60 cursor-not-allowed' : ''); ?>">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5" data-i18n="studio.date_stamp">Stempel Tanggal</label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" id="toggleDateStamp" checked class="rounded border-slate-700 text-brand-600">
                            <span>Tampilkan Tanggal (<?php echo e(date('d M Y')); ?>)</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                            <input type="checkbox" id="toggleTimeStamp" class="rounded border-slate-700 text-brand-600">
                            <span data-i18n="studio.time_stamp">Stempel Waktu</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-800 flex justify-end">
                <button id="btnSaveFinalPhoto" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm shadow-xl flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-check"></i>
                    <span data-i18n="studio.save">Selesai & Simpan Foto</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Kiosk anti-salah-guna: blokir Back & Tab switching saat sesi foto
history.pushState(null, '', location.href);
window.addEventListener('popstate', () => history.pushState(null, '', location.href));
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        document.body.style.filter = 'blur(12px)';
    } else {
        document.body.style.filter = '';
    }
});
</script>
<script>
    const layoutType = "<?php echo e($session->layout_type); ?>";
    const frameTheme = "<?php echo e($frameTheme); ?>";
    const customFrameUrl = <?php echo json_encode($customFrameUrl, 15, 512) ?>;
    const saveUrl = "<?php echo e(route('photobooth.studio.save', ['token' => $session->session_token])); ?>";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const downloadUrl = "<?php echo e($downloadUrl); ?>";

    const isEventSession = <?php echo e($isEvent ? 'true' : 'false'); ?>;
    const totalDurationSeconds = isEventSession ? 999999 : <?php echo e($session->duration_minutes * 60); ?>;
    let remainingSeconds = totalDurationSeconds;

    // Pengaturan dari admin
    const businessLogoUrl = <?php echo json_encode($setting->business_logo_path ? asset($setting->business_logo_path) : null, 15, 512) ?>;
    const logoPosition = "<?php echo e($setting->logo_position ?? 'bottom-center'); ?>";
    const enableCountdownSound = <?php echo e($setting->enable_countdown_sound ? 'true' : 'false'); ?>;
    const greenscreenEnabled = <?php echo e(($setting->enable_greenscreen && $setting->greenscreen_bg_path) ? 'true' : 'false'); ?>;
    const greenscreenBgUrl = <?php echo json_encode($setting->greenscreen_bg_path ? asset($setting->greenscreen_bg_path) : null, 15, 512) ?>;
    const bgMusicUrl = <?php echo json_encode($setting->bg_music_path ? asset($setting->bg_music_path) : null, 15, 512) ?>;
    const lockPhotoShape = <?php echo e($setting->lock_photo_shape ? 'true' : 'false'); ?>;

    // Live theme overlay (dog/hearts/vintage dll) — tampil di viewfinder seperti photobooth-io
    (function() {
        const overlay = document.getElementById('themeLiveOverlay');
        const top = document.getElementById('themeLiveTop');
        const bottom = document.getElementById('themeLiveBottom');
        if (!overlay || !top || !bottom) return;
        const themeMap = {
            dog: ['🐶 🐾', '🐾'],
            hearts: ['💗 ❤ 💗', '♡'],
            vintage: ['est. ' + new Date().getFullYear(), ''],
            solace: ['✦', '✦'],
            classic: ['◆ ◆', '◆ ◆'],
            with_love: ['♡ with love ♡', '♡'],
            holidays: ['❄ ⭐ 🎄 ✨', '❄'],
        };
        const t = themeMap[frameTheme];
        if (t) {
            top.textContent = t[0];
            bottom.textContent = t[1];
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            // vintage/solace framing
            if (frameTheme === 'vintage') overlay.style.border = '3px solid #8B6B3A';
            if (frameTheme === 'solace') overlay.style.border = '6px solid #a5b4fc';
            if (frameTheme === 'classic') overlay.style.border = '2px solid #334155';
            if (frameTheme === 'with_love') top.style.fontStyle = 'italic';
            if (frameTheme === 'holidays') top.style.letterSpacing = '6px';
        }
    })();

    // Konfigurasi layout
    const layoutConfig = {
        'strip_4':   { cols: 1, rows: 4 },
        'strip_3':   { cols: 1, rows: 3 },
        'strip_2':   { cols: 1, rows: 2 },
        'strip_6':   { cols: 2, rows: 3 },
        'strip_e':   { cols: 2, rows: 2 },
        'grid_4':    { cols: 2, rows: 2 },
        'polaroid':  { cols: 1, rows: 1, polaroid: true },
        'hearts':    { cols: 1, rows: 4, theme: 'hearts' },
        'dog':       { cols: 1, rows: 4, theme: 'dog' },
        'vintage':   { cols: 1, rows: 4, theme: 'vintage' },
        'solace':    { cols: 1, rows: 4, theme: 'solace' },
        'classic':   { cols: 1, rows: 4, theme: 'classic' },
        'with_love': { cols: 1, rows: 4, theme: 'with_love' },
        'holidays':  { cols: 1, rows: 4, theme: 'holidays' },
    };
    const cfg = layoutConfig[layoutType] || { cols: 1, rows: 4 };
    const totalShots = cfg.cols * cfg.rows;
    document.getElementById('totalShotsNumber').innerText = totalShots;

    let capturedPhotos = [];
    let stream = null;
    let selectedFrameColor = "<?php echo e($setting->default_frame_color); ?>";
    let selectedFilter = 'normal';
    let selectedPhotoShape = "<?php echo e($setting->default_photo_shape ?? 'none'); ?>";
    let activeStickers = [];
    let customText = "<?php echo e($setting->default_brand_text); ?>";
    let showDateStamp = true;
    let showTimeStamp = false;
    const lockFrameColor = <?php echo e($setting->lock_frame_color ? 'true' : 'false'); ?>;
    const lockBrandText = <?php echo e($setting->lock_brand_text ? 'true' : 'false'); ?>;
    let logoImg = null;

    // ============ TIMER ============
    const timerText = document.getElementById('sessionTimerText');
    const timerBadge = document.getElementById('sessionTimerBadge');
    const progressBar = document.getElementById('sessionProgressBar');

    if (isEventSession) {
        timerText.innerText = '∞';
        timerBadge.className = "flex items-center gap-1.5 px-3 py-1 rounded-lg bg-brand-950 border border-brand-800 text-brand-300 font-mono font-bold text-sm";
        timerBadge.querySelector('i').className = 'fa-solid fa-infinity';
        progressBar.style.width = '100%';
    } else {
        function updateTimerDisplay() {
            if (remainingSeconds <= 0) {
                timerText.innerText = "00:00";
                progressBar.style.width = "0%";
                alert("Batas waktu sesi berakhir!");
                autoSaveAndFinish();
                return;
            }
            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            timerText.innerText = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            progressBar.style.width = `${(remainingSeconds / totalDurationSeconds) * 100}%`;
            if (remainingSeconds <= 60) {
                timerBadge.className = "flex items-center gap-1.5 px-3 py-1 rounded-lg bg-rose-950 border border-rose-700 text-rose-400 font-mono font-bold text-sm animate-pulse";
            }
            remainingSeconds--;
        }
        updateTimerDisplay();
        setInterval(updateTimerDisplay, 1000);
    }

    // ============ KAMERA ============
    const video = document.getElementById('videoFeed');
    const cameraSelect = document.getElementById('cameraSelect');

    async function initCamera(deviceId = null) {
        if (stream) stream.getTracks().forEach(t => t.stop());
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: deviceId ? { deviceId: { exact: deviceId } } : { width: { ideal: 1280 }, height: { ideal: 960 } },
                audio: false
            });
            video.srcObject = stream;
            const devices = await navigator.mediaDevices.enumerateDevices();
            cameraSelect.innerHTML = '';
            devices.filter(d => d.kind === 'videoinput').forEach((dev, idx) => {
                const opt = document.createElement('option');
                opt.value = dev.deviceId;
                opt.text = dev.label || `Kamera ${idx + 1}`;
                if (deviceId === dev.deviceId) opt.selected = true;
                cameraSelect.appendChild(opt);
            });
        } catch (e) {
            console.error("Gagal membuka kamera:", e);
        }
    }
    initCamera();
    cameraSelect.addEventListener('change', () => initCamera(cameraSelect.value));

    // ============ MUSIK LATAR ============
    const bgMusic = document.getElementById('bgMusic');
    const btnToggleMusic = document.getElementById('btnToggleMusic');
    if (bgMusic) {
        bgMusic.volume = 0.4;
        bgMusic.play().catch(() => {});
        if (btnToggleMusic) {
            btnToggleMusic.addEventListener('click', () => {
                if (bgMusic.paused) { bgMusic.play(); btnToggleMusic.innerHTML = '<i class="fa-solid fa-volume-high"></i>'; }
                else { bgMusic.pause(); btnToggleMusic.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>'; }
            });
        }
    }

    // ============ SOUND COUNTDOWN ============
    let audioCtx = null;
    function beep() {
        if (!enableCountdownSound) return;
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const o = audioCtx.createOscillator();
            const g = audioCtx.createGain();
            o.frequency.value = 880;
            o.type = 'sine';
            g.gain.value = 0.15;
            o.connect(g); g.connect(audioCtx.destination);
            o.start();
            setTimeout(() => o.stop(), 150);
        } catch (e) {}
    }

    // ============ HELPERS ============
    function loadImage(url) {
        return new Promise((res, rej) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => res(img);
            img.onerror = rej;
            img.src = url;
        });
    }

    function drawCover(ctx, img, x, y, w, h) {
        const ir = img.width / img.height;
        const tr = w / h;
        let dw, dh, dx, dy;
        if (ir > tr) { dh = h; dw = h * ir; dx = x - (dw - w) / 2; dy = y; }
        else { dw = w; dh = w / ir; dx = x; dy = y - (dh - h) / 2; }
        ctx.drawImage(img, dx, dy, dw, dh);
    }

    function drawHeartPath(ctx, cx, cy, size) {
        const s = size / 32;
        ctx.beginPath();
        for (let t = 0; t <= Math.PI * 2 + 0.05; t += 0.05) {
            const x = 16 * Math.pow(Math.sin(t), 3);
            const y = 13 * Math.cos(t) - 5 * Math.cos(2 * t) - 2 * Math.cos(3 * t) - Math.cos(4 * t);
            const px = cx + x * s;
            const py = cy - y * s;
            if (t === 0) ctx.moveTo(px, py); else ctx.lineTo(px, py);
        }
        ctx.closePath();
    }

    function clipShape(ctx, x, y, w, h, shape) {
        if (shape === 'circle') {
            ctx.beginPath();
            ctx.ellipse(x + w / 2, y + h / 2, w / 2, h / 2, 0, 0, Math.PI * 2);
            ctx.clip();
        } else if (shape === 'soft') {
            const r = Math.min(w, h) * 0.14;
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.arcTo(x + w, y, x + w, y + h, r);
            ctx.arcTo(x + w, y + h, x, y + h, r);
            ctx.arcTo(x, y + h, x, y, r);
            ctx.arcTo(x, y, x + w, y, r);
            ctx.closePath();
            ctx.clip();
        } else if (shape === 'heart') {
            drawHeartPath(ctx, x + w / 2, y + h / 2, Math.min(w, h) * 0.92);
            ctx.clip();
        }
    }

    // ============ PENGAMBILAN FOTO ============
    const countdownOverlay = document.getElementById('countdownOverlay');
    const countdownNumber = document.getElementById('countdownNumber');
    const flashOverlay = document.getElementById('flashOverlay');
    const btnStartAutoShoot = document.getElementById('btnStartAutoShoot');
    const btnManualSnap = document.getElementById('btnManualSnap');
    const thumbnailsContainer = document.getElementById('thumbnailsContainer');
    const btnProceedToEdit = document.getElementById('btnProceedToEdit');

    async function snapSinglePhoto() {
        const offCanvas = document.createElement('canvas');
        offCanvas.width = 640;
        offCanvas.height = 480;
        const ctx = offCanvas.getContext('2d');
        ctx.translate(offCanvas.width, 0);
        ctx.scale(-1, 1);
        // Capture dengan cover (crop) agar foto tidak gepeng/melar
        const vw = video.videoWidth || 640, vh = video.videoHeight || 480;
        const ir = vw / vh, tr = offCanvas.width / offCanvas.height;
        let vdw, vdh, vdx, vdy;
        if (ir > tr) { vdh = offCanvas.height; vdw = vdh * ir; vdx = (offCanvas.width - vdw) / 2; vdy = 0; }
        else { vdw = offCanvas.width; vdh = vdw / ir; vdx = 0; vdy = (offCanvas.height - vdh) / 2; }
        ctx.drawImage(video, vdx, vdy, vdw, vdh);

        let dataUrl = offCanvas.toDataURL('image/jpeg', 0.95);

        // Green screen: ganti latar hijau dgn background virtual
        if (greenscreenEnabled && greenscreenBgUrl) {
            try {
                const bg = await loadImage(greenscreenBgUrl);
                const tw = offCanvas.width, th = offCanvas.height;
                const tmp = document.createElement('canvas');
                tmp.width = tw; tmp.height = th;
                const tctx = tmp.getContext('2d');
                drawCover(tctx, bg, 0, 0, tw, th);
                const fctx = offCanvas.getContext('2d');
                const imgData = fctx.getImageData(0, 0, tw, th);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    const r = d[i], g = d[i + 1], b = d[i + 2];
                    if (g > 90 && g > r + 30 && g > b + 30) d[i + 3] = 0;
                }
                fctx.putImageData(imgData, 0, 0);
                tctx.drawImage(offCanvas, 0, 0);
                dataUrl = tmp.toDataURL('image/jpeg', 0.95);
            } catch (e) { console.error('Green screen error', e); }
        }

        flashOverlay.classList.remove('flash-active');
        void flashOverlay.offsetWidth;
        flashOverlay.classList.add('flash-active');

        capturedPhotos.push(dataUrl);
        renderThumbnails();
        document.getElementById('currentShotNumber').innerText = Math.min(capturedPhotos.length + 1, totalShots);

        if (capturedPhotos.length >= totalShots) {
            btnProceedToEdit.disabled = false;
            btnStartAutoShoot.disabled = true;
        }
    }

    async function runCountdown(seconds = 3) {
        countdownOverlay.classList.remove('hidden');
        for (let i = seconds; i > 0; i--) {
            countdownNumber.innerText = i;
            beep();
            await new Promise(r => setTimeout(r, 1000));
        }
        countdownOverlay.classList.add('hidden');
        snapSinglePhoto();
    }

    btnManualSnap.addEventListener('click', () => {
        if (capturedPhotos.length < totalShots) snapSinglePhoto();
    });

    btnStartAutoShoot.addEventListener('click', async () => {
        btnStartAutoShoot.disabled = true;
        while (capturedPhotos.length < totalShots) {
            await runCountdown(3);
            if (capturedPhotos.length < totalShots) await new Promise(r => setTimeout(r, 1500));
        }
    });

    document.getElementById('btnResetShots').addEventListener('click', () => {
        capturedPhotos = [];
        renderThumbnails();
        btnProceedToEdit.disabled = true;
        btnStartAutoShoot.disabled = false;
        document.getElementById('currentShotNumber').innerText = '1';
    });

    function renderThumbnails() {
        thumbnailsContainer.innerHTML = '';
        capturedPhotos.forEach((src, idx) => {
            const div = document.createElement('div');
            div.className = 'relative rounded-lg overflow-hidden border border-slate-700 bg-black aspect-[4/3]';
            div.innerHTML = `<img src="${src}" class="w-full h-full object-cover"><span class="absolute top-1.5 left-1.5 bg-black/70 text-white text-[10px] font-bold px-2 py-0.5 rounded">#${idx + 1}</span>`;
            thumbnailsContainer.appendChild(div);
        });
    }

    // ============ EDITOR ============
    const canvas = document.getElementById('photoStripCanvas');
    const ctx = canvas.getContext('2d');

    btnProceedToEdit.addEventListener('click', () => {
        document.getElementById('captureSection').classList.add('hidden');
        document.getElementById('editorSection').classList.remove('hidden');
        renderCanvasStrip();
    });
    document.getElementById('btnBackToCapture').addEventListener('click', () => {
        document.getElementById('editorSection').classList.add('hidden');
        document.getElementById('captureSection').classList.remove('hidden');
    });

    // Bentuk foto
    document.querySelectorAll('.shape-btn').forEach(btn => {
        if (lockPhotoShape) btn.disabled = true;
        if (btn.dataset.shape === selectedPhotoShape) btn.classList.add('active');
        btn.addEventListener('click', () => {
            if (lockPhotoShape) return;
            selectedPhotoShape = btn.dataset.shape;
            document.querySelectorAll('.shape-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderCanvasStrip();
        });
    });

    document.querySelectorAll('.color-preset').forEach(btn => {
        btn.addEventListener('click', () => {
            if (lockFrameColor) return;
            selectedFrameColor = btn.dataset.color;
            renderCanvasStrip();
        });
    });
    document.getElementById('frameColorCustom').addEventListener('input', e => {
        if (lockFrameColor) return;
        selectedFrameColor = e.target.value;
        renderCanvasStrip();
    });

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.className = 'filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200');
            btn.className = 'filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-brand-950 border border-brand-500 text-brand-300';
            selectedFilter = btn.dataset.filter;
            renderCanvasStrip();
        });
    });

    // Posisi stiker yang sudah diatur agar tidak menumpuk (pinggir frame)
    const stickerSlots = [
        { fx: 0.13, fy: 0.10 }, { fx: 0.87, fy: 0.10 },
        { fx: 0.13, fy: 0.50 }, { fx: 0.87, fy: 0.50 },
        { fx: 0.13, fy: 0.90 }, { fx: 0.87, fy: 0.90 },
        { fx: 0.50, fy: 0.07 }, { fx: 0.50, fy: 0.95 },
    ];
    document.querySelectorAll('.sticker-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const slot = stickerSlots[activeStickers.length % stickerSlots.length];
            activeStickers.push({
                char: btn.dataset.sticker,
                fx: slot.fx,
                fy: slot.fy,
                size: 40,
                rot: (Math.random() - 0.5) * 0.5
            });
            renderCanvasStrip();
        });
    });
    document.getElementById('btnClearStickers').addEventListener('click', () => {
        activeStickers = [];
        renderCanvasStrip();
    });

    // Drag & drop stiker pada canvas (geser posisi, klik ganda untuk hapus)
    let dragSticker = -1;
    let dragOffset = { x: 0, y: 0 };
    function canvasPoint(e) {
        const r = canvas.getBoundingClientRect();
        const sx = canvas.width / r.width, sy = canvas.height / r.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: (clientX - r.left) * sx, y: (clientY - r.top) * sy };
    }
    function stickerAt(px, py) {
        for (let i = activeStickers.length - 1; i >= 0; i--) {
            const stk = activeStickers[i];
            const cx = stk.fx * canvas.width, cy = stk.fy * canvas.height;
            const half = stk.size * 0.7;
            if (Math.abs(px - cx) < half && Math.abs(py - cy) < half) return i;
        }
        return -1;
    }
    function onDown(e) {
        if (activeStickers.length === 0) return;
        const p = canvasPoint(e);
        const idx = stickerAt(p.x, p.y);
        if (idx >= 0) {
            dragSticker = idx;
            dragOffset.x = p.x - activeStickers[idx].fx * canvas.width;
            dragOffset.y = p.y - activeStickers[idx].fy * canvas.height;
            e.preventDefault();
        }
    }
    function onMove(e) {
        if (dragSticker < 0) return;
        const p = canvasPoint(e);
        let fx = (p.x - dragOffset.x) / canvas.width;
        let fy = (p.y - dragOffset.y) / canvas.height;
        fx = Math.max(0.04, Math.min(0.96, fx));
        fy = Math.max(0.04, Math.min(0.96, fy));
        activeStickers[dragSticker].fx = fx;
        activeStickers[dragSticker].fy = fy;
        renderCanvasStrip();
        e.preventDefault();
    }
    function onUp() { dragSticker = -1; }
    function onDbl(e) {
        const p = canvasPoint(e);
        const idx = stickerAt(p.x, p.y);
        if (idx >= 0) { activeStickers.splice(idx, 1); renderCanvasStrip(); }
    }
    canvas.addEventListener('mousedown', onDown);
    canvas.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
    canvas.addEventListener('touchstart', onDown, { passive: false });
    canvas.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('touchend', onUp);
    canvas.addEventListener('dblclick', onDbl);

    document.getElementById('customTextInput').addEventListener('input', e => {
        if (lockBrandText) return;
        customText = e.target.value;
        renderCanvasStrip();
    });
    document.getElementById('toggleDateStamp').addEventListener('change', e => {
        showDateStamp = e.target.checked;
        renderCanvasStrip();
    });
    document.getElementById('toggleTimeStamp').addEventListener('change', e => {
        showTimeStamp = e.target.checked;
        renderCanvasStrip();
    });

    // Load logo watermark
    if (businessLogoUrl) {
        loadImage(businessLogoUrl).then(img => { logoImg = img; renderCanvasStrip(); }).catch(() => {});
    }

    const photoImgCache = {};
    function getPhoto(i) {
        if (photoImgCache[i]) return Promise.resolve(photoImgCache[i]);
        return new Promise(res => {
            const image = new Image();
            image.onload = () => { photoImgCache[i] = image; res(image); };
            image.onerror = () => res(null);
            image.src = capturedPhotos[i];
        });
    }

    async function renderCanvasStrip() {
        if (capturedPhotos.length === 0) return;

        const margin = 30;
        const spacing = 18;
        const footerHeight = 220;
        const cols = cfg.cols;
        const rows = cfg.rows;
        const darkFrame = (selectedFrameColor === '#111111' || selectedFrameColor === '#312E81');

        let photoWidth, photoHeight, totalWidth, totalHeight;

        if (cfg.polaroid) {
            photoWidth = 360; photoHeight = 360;
            totalWidth = margin * 2 + photoWidth;
            totalHeight = margin + photoHeight + 80 + margin;
        } else {
            photoWidth = cols === 1 ? 340 : 300;
            photoHeight = cols === 1 ? 240 : 225;
            totalWidth = margin * 2 + photoWidth * cols + spacing * (cols - 1);
            totalHeight = margin * 2 + photoHeight * rows + spacing * (rows - 1) + footerHeight;
        }

        canvas.width = totalWidth;
        canvas.height = totalHeight;

        ctx.fillStyle = selectedFrameColor;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Bingkai dalam tipis agar warna frame terlihat rapi (al style photobooth-io)
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = 'rgba(0,0,0,0.12)';
        ctx.strokeRect(2, 2, canvas.width - 4, canvas.height - 4);

        for (let i = 0; i < capturedPhotos.length && i < cols * rows; i++) {
            const img = await getPhoto(i);
            if (!img) continue;

            let x, y;
            if (cfg.polaroid) { x = margin; y = margin; }
            else {
                const c = i % cols;
                const r = Math.floor(i / cols);
                x = margin + c * (photoWidth + spacing);
                y = margin + r * (photoHeight + spacing);
            }

            ctx.save();
            if (selectedFilter === 'bw') ctx.filter = 'grayscale(100%) contrast(125%)';
            else if (selectedFilter === 'sepia') ctx.filter = 'sepia(85%) contrast(110%)';
            else if (selectedFilter === 'vintage') ctx.filter = 'sepia(35%) contrast(115%) saturate(125%)';
            else if (selectedFilter === 'warm') ctx.filter = 'sepia(20%) saturate(145%)';
            else if (selectedFilter === 'cool') ctx.filter = 'saturate(115%) hue-rotate(15deg)';

            if (selectedPhotoShape !== 'none') {
                ctx.beginPath();
                clipShape(ctx, x, y, photoWidth, photoHeight, selectedPhotoShape);
            }
            drawCover(ctx, img, x, y, photoWidth, photoHeight);
            ctx.restore();
        }

        if (cfg.theme) drawThemeDecoration(cfg.theme, darkFrame);

        if (customFrameUrl) {
            try {
                const frameImg = await new Promise((res, rej) => {
                    const image = new Image();
                    image.onload = () => res(image);
                    image.onerror = rej;
                    image.src = customFrameUrl;
                });
                ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);
            } catch (e) { console.error('Frame overlay error', e); }
        }

        activeStickers.forEach(stk => {
            const px = stk.fx * canvas.width;
            const py = stk.fy * canvas.height;
            ctx.save();
            ctx.translate(px, py);
            ctx.rotate(stk.rot || 0);
            ctx.font = `${stk.size}px Arial`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(stk.char, 0, 0);
            ctx.restore();
        });
        ctx.textBaseline = 'alphabetic';

        // Logo watermark bisnis
        if (logoImg) {
            const lw = canvas.width * 0.16;
            const lh = lw * (logoImg.height / logoImg.width);
            let lx, ly;
            if (logoPosition === 'top-left') { lx = 15; ly = 15; }
            else if (logoPosition === 'top-right') { lx = canvas.width - lw - 15; ly = 15; }
            else if (logoPosition === 'bottom-left') { lx = 15; ly = canvas.height - lh - 15; }
            else if (logoPosition === 'bottom-right') { lx = canvas.width - lw - 15; ly = canvas.height - lh - 15; }
            else if (logoPosition === 'center') { lx = (canvas.width - lw) / 2; ly = (canvas.height - lh) / 2; }
            else { lx = (canvas.width - lw) / 2; ly = canvas.height - lh - 15; }
            ctx.drawImage(logoImg, lx, ly, lw, lh);
        }

        // ===== QR Code (scan to download) di bagian bawah, di atas teks brand =====
        if (downloadUrl) {
            try {
                const qrSize = cfg.polaroid ? 110 : 130;
                const qrCanvas = document.createElement('canvas');
                qrCanvas.width = 300; qrCanvas.height = 300;
                await new Promise((resolve, reject) => {
                    new QRCode(qrCanvas, { text: downloadUrl, width: 300, height: 300, correctLevel: QRCode.CorrectLevel.M });
                    resolve();
                });
                const qrImg = await new Promise((res, rej) => {
                    const image = new Image();
                    image.onload = () => res(image);
                    image.onerror = rej;
                    image.src = qrCanvas.toDataURL();
                });

                // Posisi QR: di tengah, di antara foto terakhir dan teks brand
                const qrX = (canvas.width - qrSize) / 2;
                const qrY = canvas.height - footerHeight + 24;

                // Kotak putih di belakang QR (QR selalu butuh background kontras)
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(qrX - 8, qrY - 8, qrSize + 16, qrSize + 16);
                ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);

                // Label "Scan untuk Download"
                ctx.textAlign = 'center';
                ctx.font = '10px "Plus Jakarta Sans", sans-serif';
                ctx.fillStyle = darkFrame ? '#94A3B8' : '#64748B';
                ctx.fillText('SCAN UNTUK DOWNLOAD', canvas.width / 2, qrY + qrSize + 22);
            } catch (e) {
                console.error('QR render error', e);
            }
        }

        ctx.textAlign = 'center';
        const footerY = cfg.polaroid ? canvas.height - 24 : canvas.height - 48;
        const dateY = cfg.polaroid ? canvas.height - 8 : canvas.height - 28;

        if (cfg.theme === 'with_love') {
            ctx.fillStyle = darkFrame ? '#FBCFE8' : '#BE185D';
            ctx.font = 'italic bold 22px "Plus Jakarta Sans", cursive';
            ctx.fillText('with love ♡', canvas.width / 2, footerY);
        } else if (customText) {
            ctx.fillStyle = darkFrame ? '#FFFFFF' : '#1E293B';
            ctx.font = 'bold 16px "Plus Jakarta Sans", sans-serif';
            ctx.fillText(customText.toUpperCase(), canvas.width / 2, footerY);
        }

        if (showDateStamp && !cfg.polaroid) {
            const todayStr = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            ctx.font = '11px "Plus Jakarta Sans", monospace';
            ctx.fillStyle = darkFrame ? '#94A3B8' : '#64748B';
            ctx.fillText(todayStr, canvas.width / 2, dateY);
        } else if (showDateStamp && cfg.polaroid) {
            const todayStr = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            ctx.font = 'italic 13px "Plus Jakarta Sans", cursive';
            ctx.fillStyle = darkFrame ? '#E2E8F0' : '#475569';
            ctx.fillText(todayStr, canvas.width / 2, dateY);
        }

        if (showTimeStamp && !cfg.polaroid) {
            const nowStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            ctx.font = '11px "Plus Jakarta Sans", monospace';
            ctx.fillStyle = darkFrame ? '#94A3B8' : '#64748B';
            ctx.fillText('pukul ' + nowStr, canvas.width / 2, dateY - 14);
        }
    }

    function drawThemeDecoration(theme, darkFrame) {
        const W = canvas.width, H = canvas.height;
        const accent = darkFrame ? '#F9A8D4' : '#DB2777';

        if (theme === 'hearts') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const hearts = ['❤', '♡', '💗'];
            ctx.font = '26px Arial';
            for (let i = 0; i < 6; i++) {
                ctx.fillText(hearts[i % hearts.length], 18, 70 + i * 55);
                ctx.fillText(hearts[(i + 1) % hearts.length], W - 18, 70 + i * 55);
            }
            ctx.font = '30px Arial';
            ctx.fillText('💗', W / 2, H - 62);
            ctx.textBaseline = 'alphabetic';
        }
        if (theme === 'dog') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = '30px Arial';
            ctx.fillText('🐾', 22, 60); ctx.fillText('🐶', W - 22, 60);
            ctx.fillText('🦴', 22, H - 70); ctx.fillText('🐾', W - 22, H - 70);
            ctx.font = '24px Arial';
            for (let i = 0; i < 4; i++) ctx.fillText('🐾', W / 2 + (i % 2 ? 40 : -40), 90 + i * 50);
            ctx.textBaseline = 'alphabetic';
        }
        if (theme === 'vintage') {
            ctx.strokeStyle = darkFrame ? '#D6C7A1' : '#8B6B3A';
            ctx.lineWidth = 3; ctx.strokeRect(14, 14, W - 28, H - 28);
            ctx.lineWidth = 1; ctx.strokeRect(20, 20, W - 40, H - 40);
            ctx.fillStyle = darkFrame ? '#D6C7A1' : '#8B6B3A';
            ctx.textAlign = 'left'; ctx.font = 'italic 12px "Plus Jakarta Sans", serif';
            ctx.fillText('est. ' + new Date().getFullYear(), 26, H - 34);
        }
        if (theme === 'solace') {
            ctx.strokeStyle = darkFrame ? '#A5B4FC' : '#C4B5FD';
            ctx.lineWidth = 6; ctx.strokeRect(16, 16, W - 32, H - 32);
            ctx.fillStyle = darkFrame ? '#E0E7FF' : '#7C3AED';
            ctx.textAlign = 'center'; ctx.font = '22px Arial';
            ctx.fillText('✦', W / 2, H - 60);
        }
        if (theme === 'classic') {
            ctx.strokeStyle = darkFrame ? '#F1F5F9' : '#334155';
            ctx.lineWidth = 2; ctx.strokeRect(16, 16, W - 32, H - 32);
            ctx.lineWidth = 1; ctx.strokeRect(22, 22, W - 44, H - 44);
            ctx.fillStyle = darkFrame ? '#F1F5F9' : '#334155';
            ctx.textAlign = 'center'; ctx.font = '16px Arial';
            ctx.fillText('◆', 26, 28); ctx.fillText('◆', W - 26, 28);
            ctx.fillText('◆', 26, H - 26); ctx.fillText('◆', W - 26, H - 26);
        }
        if (theme === 'with_love') {
            ctx.strokeStyle = accent; ctx.lineWidth = 2;
            ctx.setLineDash([8, 6]); ctx.strokeRect(16, 16, W - 32, H - 32);
            ctx.setLineDash([]);
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = '24px Arial'; ctx.fillText('♡', W / 2, 60);
            ctx.textBaseline = 'alphabetic';
        }
        if (theme === 'holidays') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const deco = ['❄', '⭐', '🎄', '✨'];
            ctx.font = '24px Arial';
            const pts = [[20, 60], [W - 20, 60], [20, H - 70], [W - 20, H - 70], [W / 2, 50], [W / 2, H - 60]];
            pts.forEach((p, i) => ctx.fillText(deco[i % deco.length], p[0], p[1]));
            ctx.strokeStyle = darkFrame ? '#93C5FD' : '#2563EB';
            ctx.lineWidth = 2; ctx.setLineDash([6, 6]); ctx.strokeRect(14, 14, W - 28, H - 28);
            ctx.setLineDash([]);
            ctx.textBaseline = 'alphabetic';
        }
    }

    async function autoSaveAndFinish() {
        await renderCanvasStrip();
        const base64Image = canvas.toDataURL('image/png', 1.0);
        try {
            const res = await fetch(saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ image_data: base64Image })
            });
            const data = await res.json();
            if (data.success) window.location.href = data.redirect;
        } catch (e) {
            console.error("Save error:", e);
        }
    }

    document.getElementById('btnSaveFinalPhoto').addEventListener('click', () => autoSaveAndFinish());
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views/photobooth/studio.blade.php ENDPATH**/ ?>