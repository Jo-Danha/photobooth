@extends('layouts.app')

@section('title', 'Photobooth Studio Live - Ambil Foto & Edit')

@section('styles')
<style>
    @keyframes cameraFlash {
        0% { opacity: 0; }
        50% { opacity: 1; }
        100% { opacity: 0; }
    }
    .flash-active {
        animation: cameraFlash 0.25s ease-out;
    }
</style>
@endsection

@section('content')
<!-- Top Bar: Timer Sesi -->
<div class="bg-slate-900 border-b border-slate-800 sticky top-16 z-40 px-4 py-2.5">
    <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-400 hidden sm:inline">Paket:</span>
            <span class="text-xs font-bold px-2.5 py-1 rounded bg-brand-950 text-brand-300 border border-brand-800/60">
                {{ $session->package_name }}
            </span>
        </div>

        <div class="flex items-center gap-2">
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

<div class="max-w-6xl mx-auto px-4 py-6">
    <div id="flashOverlay" class="fixed inset-0 bg-white pointer-events-none z-50 opacity-0"></div>

    <!-- TAHAP 1: PENGAMBILAN FOTO -->
    <div id="captureSection" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between relative shadow-2xl">
            <div class="flex items-center justify-between mb-3 z-10">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Live Viewfinder</span>
                </div>
                <select id="cameraSelect" class="bg-slate-950 border border-slate-700 text-slate-300 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none">
                    <option value="">Memuat Kamera...</option>
                </select>
            </div>

            <div class="relative bg-black rounded-xl overflow-hidden aspect-[4/3] flex items-center justify-center border border-slate-800">
                <video id="videoFeed" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                
                <div id="countdownOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-20">
                    <span id="countdownNumber" class="text-8xl sm:text-9xl font-black text-white drop-shadow-[0_0_25px_rgba(215,82,154,0.8)] animate-bounce">3</span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-400">
                    Foto: <span id="currentShotNumber" class="font-bold text-white text-sm">1</span> / <span id="totalShotsNumber" class="font-bold text-white text-sm">4</span>
                </div>

                <div class="flex items-center gap-3">
                    <button id="btnStartAutoShoot" class="px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-camera"></i>
                        <span>Jepret Otomatis (3s)</span>
                    </button>
                    <button id="btnManualSnap" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700">
                        <i class="fa-solid fa-circle-dot"></i> 1x Jepret
                    </button>
                </div>
            </div>
        </div>

        <!-- Thumbnails -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-2xl">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-white">Hasil Foto</h3>
                    <button id="btnResetShots" class="text-xs text-rose-400 hover:text-rose-300">
                        <i class="fa-solid fa-rotate-left"></i> Ulang
                    </button>
                </div>
                <div id="thumbnailsContainer" class="space-y-2 max-h-[420px] overflow-y-auto pr-1"></div>
            </div>

            <button id="btnProceedToEdit" disabled class="w-full mt-4 py-3.5 rounded-xl bg-emerald-600 disabled:bg-slate-800 disabled:text-slate-500 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg flex items-center justify-center gap-2">
                <span>Lanjut ke Kustomisasi Frame</span>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </button>
        </div>
    </div>

    <!-- TAHAP 2: KUSTOMISASI CANVAS -->
    <div id="editorSection" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col items-center justify-center shadow-2xl">
            <div class="text-xs font-semibold text-slate-400 mb-2 flex items-center justify-between w-full">
                <span>Preview Strip</span>
                <button id="btnBackToCapture" class="text-xs text-slate-400 hover:text-white">
                    <i class="fa-solid fa-camera"></i> Foto Ulang
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
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">1. Warna Bingkai</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">Custom:</span>
                            <input type="color" id="frameColorCustom" value="#FFFFFF" class="w-6 h-6 rounded border-0 cursor-pointer bg-transparent">
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
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">2. Filter Visual</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-brand-950 border border-brand-500 text-brand-300" data-filter="normal">Normal</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="bw">B&W Retro</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="vintage">Vintage Film</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="warm">Warm Glow</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="cool">Cool Tone</button>
                        <button class="filter-btn py-2 px-3 rounded-lg text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-200" data-filter="sepia">Sepia 80s</button>
                    </div>
                </div>

                <!-- 3. Stiker Digital -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">3. Stiker Digital</label>
                        <button id="btnClearStickers" class="text-xs text-rose-400 hover:text-rose-300">Hapus Semua</button>
                    </div>
                    <div class="flex flex-wrap gap-2 p-3 bg-slate-950 rounded-xl border border-slate-800">
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="❤️">❤️</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="✨">✨</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🎀">🎀</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="⭐">⭐</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🐱">🐱</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🍒">🍒</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🍀">🍀</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🦋">🦋</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🔥">🔥</button>
                        <button class="sticker-btn text-2xl p-1.5 hover:scale-125 transition-transform" data-sticker="🕶️">🕶️</button>
                    </div>
                </div>

                <!-- 4. Text & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Teks Kustom</label>
                        <input type="text" id="customTextInput" value="PHOTOBOOTH.IO" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Stempel Tanggal</label>
                        <label class="flex items-center gap-2 text-xs text-slate-300 mt-2 cursor-pointer">
                            <input type="checkbox" id="toggleDateStamp" checked class="rounded border-slate-700 text-brand-600">
                            <span>Tampilkan Tanggal ({{ date('d M Y') }})</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-800 flex justify-end">
                <button id="btnSaveFinalPhoto" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm shadow-xl flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-check"></i>
                    <span>Selesai & Simpan Foto</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const layoutType = "{{ $session->layout_type }}";
    const totalDurationSeconds = {{ $session->duration_minutes * 60 }};
    let remainingSeconds = {{ $remainingSeconds }};
    const saveUrl = "{{ route('photobooth.studio.save', ['token' => $session->session_token]) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const shotsMap = { 'strip_4': 4, 'strip_3': 3, 'strip_2': 2, 'grid_4': 4, 'polaroid': 1 };
    const totalShots = shotsMap[layoutType] || 4;
    document.getElementById('totalShotsNumber').innerText = totalShots;

    let capturedPhotos = [];
    let stream = null;
    let selectedFrameColor = '#FFFFFF';
    let selectedFilter = 'normal';
    let activeStickers = [];
    let customText = 'PHOTOBOOTH.IO';
    let showDateStamp = true;

    // Timer Sesi
    const timerText = document.getElementById('sessionTimerText');
    const timerBadge = document.getElementById('sessionTimerBadge');
    const progressBar = document.getElementById('sessionProgressBar');

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

    // Inisialisasi Kamera
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

    // Pengambilan Foto
    const countdownOverlay = document.getElementById('countdownOverlay');
    const countdownNumber = document.getElementById('countdownNumber');
    const flashOverlay = document.getElementById('flashOverlay');
    const btnStartAutoShoot = document.getElementById('btnStartAutoShoot');
    const btnManualSnap = document.getElementById('btnManualSnap');
    const thumbnailsContainer = document.getElementById('thumbnailsContainer');
    const btnProceedToEdit = document.getElementById('btnProceedToEdit');

    function snapSinglePhoto() {
        const offCanvas = document.createElement('canvas');
        offCanvas.width = 640;
        offCanvas.height = 480;
        const ctx = offCanvas.getContext('2d');
        ctx.translate(offCanvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, offCanvas.width, offCanvas.height);

        flashOverlay.classList.remove('flash-active');
        void flashOverlay.offsetWidth;
        flashOverlay.classList.add('flash-active');

        capturedPhotos.push(offCanvas.toDataURL('image/jpeg', 0.95));
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

    // Editor Canvas
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

    document.querySelectorAll('.color-preset').forEach(btn => {
        btn.addEventListener('click', () => {
            selectedFrameColor = btn.dataset.color;
            renderCanvasStrip();
        });
    });
    document.getElementById('frameColorCustom').addEventListener('input', e => {
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

    document.querySelectorAll('.sticker-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            activeStickers.push({
                char: btn.dataset.sticker,
                x: 80 + Math.random() * 150,
                y: 100 + Math.random() * 300,
                size: 38
            });
            renderCanvasStrip();
        });
    });

    document.getElementById('btnClearStickers').addEventListener('click', () => {
        activeStickers = [];
        renderCanvasStrip();
    });

    document.getElementById('customTextInput').addEventListener('input', e => {
        customText = e.target.value;
        renderCanvasStrip();
    });
    document.getElementById('toggleDateStamp').addEventListener('change', e => {
        showDateStamp = e.target.checked;
        renderCanvasStrip();
    });

    async function renderCanvasStrip() {
        if (capturedPhotos.length === 0) return;

        const photoWidth = 340;
        const photoHeight = 240;
        const margin = 30;
        const spacing = 18;
        const bottomFooterHeight = 90;

        let totalHeight = (margin * 2) + (photoHeight * capturedPhotos.length) + (spacing * (capturedPhotos.length - 1)) + bottomFooterHeight;
        let totalWidth = photoWidth + (margin * 2);

        if (layoutType === 'grid_4') {
            totalWidth = (margin * 2) + (photoWidth * 2) + spacing;
            totalHeight = (margin * 2) + (photoHeight * 2) + spacing + bottomFooterHeight;
        }

        canvas.width = totalWidth;
        canvas.height = totalHeight;

        ctx.fillStyle = selectedFrameColor;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        for (let i = 0; i < capturedPhotos.length; i++) {
            const img = await new Promise(res => {
                const image = new Image();
                image.onload = () => res(image);
                image.src = capturedPhotos[i];
            });

            let x = margin;
            let y = margin + (i * (photoHeight + spacing));

            if (layoutType === 'grid_4') {
                x = margin + ((i % 2) * (photoWidth + spacing));
                y = margin + (Math.floor(i / 2) * (photoHeight + spacing));
            }

            ctx.save();
            if (selectedFilter === 'bw') ctx.filter = 'grayscale(100%) contrast(125%)';
            else if (selectedFilter === 'sepia') ctx.filter = 'sepia(85%) contrast(110%)';
            else if (selectedFilter === 'vintage') ctx.filter = 'sepia(35%) contrast(115%) saturate(125%)';
            else if (selectedFilter === 'warm') ctx.filter = 'sepia(20%) saturate(145%)';
            else if (selectedFilter === 'cool') ctx.filter = 'saturate(115%) hue-rotate(15deg)';

            ctx.drawImage(img, x, y, photoWidth, photoHeight);
            ctx.restore();
        }

        activeStickers.forEach(stk => {
            ctx.font = `${stk.size}px Arial`;
            ctx.fillText(stk.char, stk.x, stk.y);
        });

        ctx.fillStyle = (selectedFrameColor === '#111111' || selectedFrameColor === '#312E81') ? '#FFFFFF' : '#1E293B';
        ctx.textAlign = 'center';

        if (customText) {
            ctx.font = 'bold 16px Plus Jakarta Sans, sans-serif';
            ctx.fillText(customText.toUpperCase(), canvas.width / 2, canvas.height - 48);
        }

        if (showDateStamp) {
            const todayStr = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            ctx.font = '11px Plus Jakarta Sans, monospace';
            ctx.fillStyle = (selectedFrameColor === '#111111' || selectedFrameColor === '#312E81') ? '#94A3B8' : '#64748B';
            ctx.fillText(todayStr, canvas.width / 2, canvas.height - 28);
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
@endsection