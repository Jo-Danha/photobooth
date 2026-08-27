@extends('layouts.app')

@section('title', 'Panel Pengaturan Booth - Admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header & Logout -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
                <i class="fa-solid fa-gears text-brand-500"></i>
                <span>Panel Pengaturan Photobooth</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Kelola template frame bergambar (Kemerdekaan/Event), aktif/nonaktifkan pembayaran, kalibrasi ISO kamera, dan kunci kiosk.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('photobooth.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 flex items-center gap-2 border border-slate-700 shadow">
                <i class="fa-solid fa-desktop"></i> Buka Layar Booth
            </a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-950/80 hover:bg-rose-900 border border-rose-800 text-xs font-bold text-rose-300 flex items-center gap-2 shadow">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-950 border border-emerald-700 text-emerald-300 text-xs font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- KOLOM KIRI (2 Kolom): PENGATURAN UTAMA -->
        <div class="lg:col-span-2 space-y-8">

            <!-- 1. KONTROL MODE PEMBAYARAN -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-credit-card text-emerald-400"></i>
                    <span>1. Status & Sistem Pembayaran (QRIS / Mode Event Gratis)</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="camera_device_id" value="{{ $setting->camera_device_id }}">
                    <input type="hidden" name="camera_brightness" value="{{ $setting->camera_brightness }}">
                    <input type="hidden" name="camera_contrast" value="{{ $setting->camera_contrast }}">
                    <input type="hidden" name="camera_iso" value="{{ $setting->camera_iso }}">
                    <input type="hidden" name="camera_saturation" value="{{ $setting->camera_saturation }}">
                    <input type="hidden" name="default_brand_text" value="{{ $setting->default_brand_text }}">
                    <input type="hidden" name="default_frame_color" value="{{ $setting->default_frame_color }}">
                    <input type="hidden" name="admin_username" value="{{ $setting->admin_username }}">
                    <input type="hidden" name="admin_password" value="{{ $setting->admin_password }}">
                    <input type="hidden" name="admin_pin" value="{{ $setting->admin_pin }}">
                    <input type="hidden" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}">
                    <input type="hidden" name="public_domain_url" value="{{ $setting->public_domain_url }}">
                    @if($setting->is_lock_mode) <input type="hidden" name="is_lock_mode" value="1"> @endif
                    @if($setting->lock_brand_text) <input type="hidden" name="lock_brand_text" value="1"> @endif
                    @if($setting->lock_frame_color) <input type="hidden" name="lock_frame_color" value="1"> @endif

                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white text-sm block">Aktifkan Pembayaran QRIS</span>
                            <span class="text-xs text-slate-400">Jika dimatikan, booth otomatis masuk ke <b>Mode Event / Rental Gratis</b> (langsung foto tanpa bayar).</span>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_payment_enabled" value="1" {{ $setting->is_payment_enabled ? 'checked' : '' }} class="sr-only peer" onchange="this.form.submit()">
                            <div class="w-14 h-7 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </form>
            </div>

            <!-- 2. KALIBRASI KAMERA & ISO HARDWARE -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-sliders text-brand-400"></i>
                    <span>2. Kalibrasi Kamera & Sensor (ISO, Brightness, Saturation)</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @if($setting->is_payment_enabled) <input type="hidden" name="is_payment_enabled" value="1"> @endif
                    <input type="hidden" name="default_brand_text" value="{{ $setting->default_brand_text }}">
                    <input type="hidden" name="default_frame_color" value="{{ $setting->default_frame_color }}">
                    <input type="hidden" name="admin_username" value="{{ $setting->admin_username }}">
                    <input type="hidden" name="admin_password" value="{{ $setting->admin_password }}">
                    <input type="hidden" name="admin_pin" value="{{ $setting->admin_pin }}">
                    <input type="hidden" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}">
                    <input type="hidden" name="public_domain_url" value="{{ $setting->public_domain_url }}">
                    @if($setting->is_lock_mode) <input type="hidden" name="is_lock_mode" value="1"> @endif
                    @if($setting->lock_brand_text) <input type="hidden" name="lock_brand_text" value="1"> @endif
                    @if($setting->lock_frame_color) <input type="hidden" name="lock_frame_color" value="1"> @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Pilih Kamera Utama (Device)</label>
                                <select id="adminCameraSelect" name="camera_device_id" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:border-brand-500">
                                    <option value="">Deteksi Otomatis / Default</option>
                                </select>
                            </div>

                            <!-- ISO -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-300 mb-1">
                                    <span>ISO / Exposure Level</span>
                                    <span id="val_iso" class="font-mono text-brand-400">{{ $setting->camera_iso > 0 ? '+'.$setting->camera_iso : $setting->camera_iso }}</span>
                                </div>
                                <input type="range" name="camera_iso" id="range_iso" min="-100" max="100" value="{{ $setting->camera_iso }}" class="w-full accent-brand-500 cursor-pointer">
                            </div>

                            <!-- Brightness -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-300 mb-1">
                                    <span>Brightness (Kecerahan)</span>
                                    <span id="val_brightness" class="font-mono text-brand-400">{{ $setting->camera_brightness }}%</span>
                                </div>
                                <input type="range" name="camera_brightness" id="range_brightness" min="40" max="180" value="{{ $setting->camera_brightness }}" class="w-full accent-brand-500 cursor-pointer">
                            </div>

                            <!-- Contrast -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-300 mb-1">
                                    <span>Contrast (Kontras Gambar)</span>
                                    <span id="val_contrast" class="font-mono text-brand-400">{{ $setting->camera_contrast }}%</span>
                                </div>
                                <input type="range" name="camera_contrast" id="range_contrast" min="50" max="160" value="{{ $setting->camera_contrast }}" class="w-full accent-brand-500 cursor-pointer">
                            </div>

                            <!-- Saturation -->
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-300 mb-1">
                                    <span>Saturation (Kepekatan Warna)</span>
                                    <span id="val_saturation" class="font-mono text-brand-400">{{ $setting->camera_saturation }}%</span>
                                </div>
                                <input type="range" name="camera_saturation" id="range_saturation" min="0" max="180" value="{{ $setting->camera_saturation }}" class="w-full accent-brand-500 cursor-pointer">
                            </div>
                        </div>

                        <!-- Live Preview Box -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex flex-col items-center">
                            <span class="text-xs font-bold text-slate-400 mb-2">Live Preview Kalibrasi Kamera</span>
                            <div class="relative bg-black rounded-lg overflow-hidden aspect-[4/3] w-full border border-slate-700 flex items-center justify-center">
                                <video id="adminPreviewVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-2">Kalibrasi ini otomatis diterapkan saat sesi foto berlangsung.</span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Kalibrasi Kamera
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. MODE KUNCI (KIOSK LOCK) & KREDENSIAL ADMIN -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-lock text-amber-400"></i>
                    <span>3. Mode Kunci (Kiosk Lock) & Kredensial Admin</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @if($setting->is_payment_enabled) <input type="hidden" name="is_payment_enabled" value="1"> @endif
                    <input type="hidden" name="camera_device_id" value="{{ $setting->camera_device_id }}">
                    <input type="hidden" name="camera_brightness" value="{{ $setting->camera_brightness }}">
                    <input type="hidden" name="camera_contrast" value="{{ $setting->camera_contrast }}">
                    <input type="hidden" name="camera_iso" value="{{ $setting->camera_iso }}">
                    <input type="hidden" name="camera_saturation" value="{{ $setting->camera_saturation }}">
                    <input type="hidden" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}">
                    <input type="hidden" name="public_domain_url" value="{{ $setting->public_domain_url }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Teks Brand Bawaan di Bawah Foto</label>
                                <input type="text" name="default_brand_text" value="{{ $setting->default_brand_text }}" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:border-brand-500">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Warna Bingkai Default</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="default_frame_color" value="{{ $setting->default_frame_color }}" class="w-10 h-10 rounded-lg border-0 cursor-pointer bg-transparent">
                                    <span class="text-xs font-mono text-slate-400">{{ $setting->default_frame_color }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                                <div>
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Username Admin</label>
                                    <input type="text" name="admin_username" value="{{ $setting->admin_username }}" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Password Admin</label>
                                    <input type="text" name="admin_password" value="{{ $setting->admin_password }}" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 font-mono">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">PIN Pengaman (4-8 Digit)</label>
                                <input type="password" name="admin_pin" value="{{ $setting->admin_pin }}" maxlength="8" class="w-32 bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 text-center font-mono">
                            </div>
                        </div>

                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                            <span class="text-xs font-bold text-amber-400 block mb-2">Batasi Pengeditan oleh Pengunjung:</span>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" name="lock_brand_text" value="1" {{ $setting->lock_brand_text ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-white block">Kunci Teks Brand</span>
                                    <span class="text-slate-500 text-[11px]">Pengunjung tidak bisa mengganti/menghapus nama brand booth Anda.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" name="lock_frame_color" value="1" {{ $setting->lock_frame_color ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-white block">Kunci Warna Bingkai</span>
                                    <span class="text-slate-500 text-[11px]">Warna bingkai terkunci pada warna default booth.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer pt-2 border-t border-slate-800">
                                <input type="checkbox" name="is_lock_mode" value="1" {{ $setting->is_lock_mode ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-amber-300 block">Kiosk Fullscreen Lock Mode</span>
                                    <span class="text-slate-500 text-[11px]">Sembunyikan tombol gear admin di layar utama saat booth digunakan pengunjung.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Setelan Kunci & Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN (1 Kolom): UPLOAD FRAME BERGAMBAR -->
        <div class="space-y-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-image text-brand-400"></i>
                    <span>Tambah Template Frame Bergambar</span>
                </h2>

                <form action="{{ route('admin.frames.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Nama Frame</label>
                        <input type="text" name="name" placeholder="Misal: Kemerdekaan 17 Agustus" required class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:border-brand-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Kategori</label>
                            <select name="category" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                <option value="Kemerdekaan">Kemerdekaan RI</option>
                                <option value="Ulang Tahun">Ulang Tahun / Party</option>
                                <option value="Pernikahan">Wedding / Engagement</option>
                                <option value="Vintage">Retro / Vintage</option>
                                <option value="Aesthetic">Aesthetic Pastel</option>
                                <option value="Custom">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Format Layout</label>
                            <select name="layout_type" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                <option value="strip_4">Classic Strip (4 Foto)</option>
                                <option value="strip_3">Trio Strip (3 Foto)</option>
                                <option value="strip_2">Duo Strip (2 Foto)</option>
                                <option value="grid_4">Grid 2x2 (4 Foto)</option>
                                <option value="polaroid">Polaroid</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Berkas Gambar Frame (PNG Transparan)</label>
                        <input type="file" name="frame_image" accept="image/png,image/webp" required class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                        <span class="text-[10px] text-slate-500 mt-1 block">Gunakan format PNG transparan dengan gambar ilustrasi (misal Panjat Pinang di pojok/border).</span>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Frame Baru
                    </button>
                </form>
            </div>

            <!-- Daftar Template Frame Aktif -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-sm font-bold text-white mb-3 flex items-center justify-between border-b border-slate-800 pb-2">
                    <span>Koleksi Frame Aktif ({{ count($customFrames) }})</span>
                </h2>

                <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                    @forelse($customFrames as $frame)
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset($frame->frame_image_path) }}" alt="{{ $frame->name }}" class="w-12 h-16 object-contain bg-slate-900 rounded border border-slate-800">
                            <div>
                                <span class="text-xs font-bold text-white block">{{ $frame->name }}</span>
                                <span class="text-[10px] text-brand-400">{{ $frame->category }} • {{ strtoupper($frame->layout_type) }}</span>
                            </div>
                        </div>
                        <form action="{{ route('admin.frames.delete', ['id' => $frame->id]) }}" method="POST" onsubmit="return confirm('Hapus template frame ini?')">
                            @csrf
                            <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 p-2">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="text-center py-6 text-xs text-slate-500">
                        Belum ada template frame bergambar. Silakan upload berkas PNG di atas.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const adminVideo = document.getElementById('adminPreviewVideo');
    const adminCameraSelect = document.getElementById('adminCameraSelect');

    const rangeIso = document.getElementById('range_iso');
    const rangeBrightness = document.getElementById('range_brightness');
    const rangeContrast = document.getElementById('range_contrast');
    const rangeSaturation = document.getElementById('range_saturation');

    const valIso = document.getElementById('val_iso');
    const valBrightness = document.getElementById('val_brightness');
    const valContrast = document.getElementById('val_contrast');
    const valSaturation = document.getElementById('val_saturation');

    let adminStream = null;

    function applyAdminFilters() {
        const iso = parseInt(rangeIso.value);
        const br = parseInt(rangeBrightness.value) + (iso * 0.4);
        const ct = parseInt(rangeContrast.value);
        const st = parseInt(rangeSaturation.value);

        valIso.innerText = iso > 0 ? `+${iso}` : iso;
        valBrightness.innerText = `${rangeBrightness.value}%`;
        valContrast.innerText = `${rangeContrast.value}%`;
        valSaturation.innerText = `${rangeSaturation.value}%`;

        if (adminVideo) {
            adminVideo.style.filter = `brightness(${br}%) contrast(${ct}%) saturate(${st}%)`;
        }
    }

    rangeIso.addEventListener('input', applyAdminFilters);
    rangeBrightness.addEventListener('input', applyAdminFilters);
    rangeContrast.addEventListener('input', applyAdminFilters);
    rangeSaturation.addEventListener('input', applyAdminFilters);

    async function initAdminCamera(deviceId = null) {
        if (adminStream) adminStream.getTracks().forEach(t => t.stop());
        try {
            adminStream = await navigator.mediaDevices.getUserMedia({
                video: deviceId ? { deviceId: { exact: deviceId } } : { width: { ideal: 1280 }, height: { ideal: 960 } },
                audio: false
            });
            if (adminVideo) adminVideo.srcObject = adminStream;

            const devices = await navigator.mediaDevices.enumerateDevices();
            adminCameraSelect.innerHTML = '<option value="">Pilih Kamera</option>';
            devices.filter(d => d.kind === 'videoinput').forEach((dev, idx) => {
                const opt = document.createElement('option');
                opt.value = dev.deviceId;
                opt.text = dev.label || `Kamera ${idx + 1}`;
                if (dev.deviceId === "{{ $setting->camera_device_id }}") opt.selected = true;
                adminCameraSelect.appendChild(opt);
            });
        } catch(e) {
            console.error("Camera init error:", e);
        }
    }

    initAdminCamera("{{ $setting->camera_device_id }}");
    applyAdminFilters();
    adminCameraSelect.addEventListener('change', () => initAdminCamera(adminCameraSelect.value));
</script>
@endsection