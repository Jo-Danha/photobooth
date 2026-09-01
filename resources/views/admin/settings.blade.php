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
                <a href="{{ route('admin.reports') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 flex items-center gap-2 border border-slate-700 shadow">
                    <i class="fa-solid fa-chart-line"></i> Laporan
                </a>
                <a href="{{ route('admin.gallery') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 flex items-center gap-2 border border-slate-700 shadow">
                    <i class="fa-solid fa-images"></i> Gallery
                </a>
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
        <div id="sec-payment" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-credit-card text-emerald-400"></i>
                    <span>1. Status & Sistem Pembayaran (QRIS / Mode Event Gratis)</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
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
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">
                    @if($setting->is_lock_mode) <input type="hidden" name="is_lock_mode" value="1"> @endif
                    @if($setting->lock_brand_text) <input type="hidden" name="lock_brand_text" value="1"> @endif
                    @if($setting->lock_frame_color) <input type="hidden" name="lock_frame_color" value="1"> @endif

                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white text-sm block">Aktifkan Pembayaran</span>
                            <span class="text-xs text-slate-400">Jika dimatikan, booth otomatis masuk ke <b>Mode Event / Rental Gratis</b> (langsung foto tanpa bayar).</span>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_payment_enabled" id="payEnabledHidden" value="{{ $setting->is_payment_enabled ? '1' : '0' }}">
                            <input type="checkbox" id="payEnabledToggle" {{ $setting->is_payment_enabled ? 'checked' : '' }} class="sr-only peer" onchange="document.getElementById('payEnabledHidden').value=this.checked?'1':'0'; this.form.submit()">
                            <div class="w-14 h-7 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Pilihan Metode Pembayaran -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-3">Metode Pembayaran yang Tersedia</label>
                        <div class="flex flex-wrap gap-3">
                            @php $pm = $setting->payment_methods ?: ['qris']; @endphp
                            <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-slate-900 rounded-lg px-3 py-2 border border-slate-800">
                                <input type="checkbox" name="payment_methods[]" value="qris" {{ in_array('qris', $pm) ? 'checked' : '' }} class="rounded border-slate-700 text-brand-600">
                                <i class="fa-solid fa-qrcode text-brand-400"></i> QRIS
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-slate-900 rounded-lg px-3 py-2 border border-slate-800">
                                <input type="checkbox" name="payment_methods[]" value="cash" {{ in_array('cash', $pm) ? 'checked' : '' }} class="rounded border-slate-700 text-brand-600">
                                <i class="fa-solid fa-money-bill-wave text-emerald-400"></i> Tunai
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-slate-900 rounded-lg px-3 py-2 border border-slate-800">
                                <input type="checkbox" name="payment_methods[]" value="transfer" {{ in_array('transfer', $pm) ? 'checked' : '' }} class="rounded border-slate-700 text-brand-600">
                                <i class="fa-solid fa-building-columns text-indigo-400"></i> Transfer Bank
                            </label>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-2 block">Pilih satu atau lebih. Di layar booth, pengunjung dapat memilih metode yang aktif.</span>
                    </div>

                    <!-- Mode & Sumber QRIS -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950 space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Sumber QRIS Pembayaran</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer bg-slate-900 rounded-lg p-3 border border-slate-800">
                                    <input type="radio" name="qris_mode" value="upload" {{ ($setting->qris_mode ?? 'upload') == 'upload' ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                    <div>
                                        <span class="font-bold text-white block">Upload Gambar QRIS</span>
                                        <span class="text-slate-500 text-[11px]">Pakai screenshot QRIS statis dari bank/e-wallet Anda.</span>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer bg-slate-900 rounded-lg p-3 border border-slate-800">
                                    <input type="radio" name="qris_mode" value="dynamic" {{ $setting->qris_mode == 'dynamic' ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                    <div>
                                        <span class="font-bold text-white block">QRIS Dinamis (Otomatis)</span>
                                        <span class="text-slate-500 text-[11px]">Generate otomatis berdasar nominal & QRIS string Anda (REAL & scannable).</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Raw QRIS String (Mode Dinamis)</label>
                            <textarea name="qris_merchant_string" rows="2" placeholder="Tempel QRIS string dari aplikasi bank/e-wallet Anda (diawali 000201010212...)" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-[11px] font-mono rounded-lg px-3 py-2 focus:border-brand-500">{{ $setting->qris_merchant_string }}</textarea>
                            <span class="text-[10px] text-slate-500 mt-1 block">Didapat dengan scan QRIS Anda lalu copy teksnya. Sistem akan menyisipkan nominal & membuat QR baru setiap transaksi.</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">PSP (Opsional)</label>
                                <select name="qris_provider" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-2">
                                    <option value="" {{ !$setting->qris_provider ? 'selected' : '' }}>Tanpa PSP</option>
                                    <option value="midtrans" {{ $setting->qris_provider == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    <option value="xendit" {{ $setting->qris_provider == 'xendit' ? 'selected' : '' }}>Xendit</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">API Key PSP</label>
                                <input type="text" name="qris_api_key" value="{{ $setting->qris_api_key }}" placeholder="Server Key / API Key" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-2 font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Merchant ID</label>
                                <input type="text" name="qris_merchant_id" value="{{ $setting->qris_merchant_id }}" placeholder="Opsional" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-2">
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-500 block">Bila PSP diisi, status pembayaran otomatis terkonfirmasi via webhook (butuh pemasangan webhook di akun PSP ke <code>/webhook/payment/midtrans</code> &amp; <code>/webhook/payment/qris</code>).</span>
                    </div>

                    <!-- Info Rekening (Tunai/Transfer) -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Info Rekening / Tunai (untuk metode Transfer & Tunai)</label>
                        <textarea name="bank_account" rows="2" placeholder="Misal: BCA 1234567890 a.n. Photobooth Anda | Tunai ke operator booth" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:border-brand-500">{{ $setting->bank_account }}</textarea>
                    </div>

                    <!-- Upload QRIS Resmi (Mode Upload) -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Upload Gambar QRIS (Mode Upload)</label>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="shrink-0">
                                @if($setting->qris_image_path)
                                    <img src="{{ asset($setting->qris_image_path) }}" alt="QRIS" class="w-28 h-28 object-contain bg-white rounded-lg border border-slate-700 p-1">
                                @else
                                    <div class="w-28 h-28 flex items-center justify-center bg-slate-900 rounded-lg border border-dashed border-slate-700 text-slate-600 text-xs text-center p-2">Belum ada QRIS</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <input type="file" name="qris_image" accept="image/png,image/jpg,image/jpeg,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                                <span class="text-[10px] text-slate-500 mt-1 block">Format PNG/JPG/WebP, maks 2 MB. Digunakan bila mode = Upload Gambar QRIS.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Harga per Template -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-3">Nominal Harga per Template (Rp)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($packages as $pkg)
                            <div class="flex items-center justify-between gap-2 bg-slate-900 rounded-lg px-3 py-2 border border-slate-800">
                                <span class="text-xs text-slate-300 truncate">{{ $pkg['name'] }}</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-slate-500">Rp</span>
                                    <input type="number" min="0" step="500" name="prices[{{ $pkg['id'] }}]" value="{{ $setting->getPriceForLayout($pkg['id']) }}" class="w-24 bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1.5 text-right focus:border-brand-500">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan QRIS & Harga
                        </button>
                    </div>
                </form>
            </div>

            <!-- 1E. TAMPILAN BOOTH — MODE / UKURAN / LAYOUT YANG DITAMPILKAN -->
            @php
                $visibleIds = $setting->layout_visible_ids ?? [];
            @endphp
            <div id="sec-display" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-tv text-brand-400"></i>
                    <span>1E. Tampilan Booth di Layar — Pilih Template yang Tampil</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                    Atur cara halaman <b>"choose your layout"</b> tampil di layar booth publik: mode <b>Slideshow</b> (geser kanan-kiri dengan tombol ← →) atau <b>Grid</b> (rapi berjajar rata), pilih ukuran kartu, lalu centang layout mana yang ditampilkan. Semua yang tidak dicentang otomatis disembunyikan.
                </p>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf

                    <!-- Mode tampilan -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="relative flex cursor-pointer rounded-xl border-2 p-4 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-950/30 {{ ($setting->layout_display_mode ?? 'slideshow') === 'slideshow' ? 'border-brand-500 bg-brand-950/30' : 'border-slate-700 bg-slate-950' }}">
                            <input type="radio" name="layout_display_mode" value="slideshow" class="sr-only" {{ ($setting->layout_display_mode ?? 'slideshow') === 'slideshow' ? 'checked' : '' }}>
                            <div class="flex gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0"><i class="fa-solid fa-arrow-right-arrow-left text-brand-400"></i></div>
                                <div>
                                    <div class="text-sm font-bold text-white">Slideshow</div>
                                    <div class="text-[11px] text-slate-400">Satu baris ke samping, geser dengan tombol ← →. Rapi & hemat layar (ala photobooth-io).</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-xl border-2 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/30 {{ ($setting->layout_display_mode ?? 'slideshow') === 'grid' ? 'border-emerald-500 bg-emerald-950/30' : 'border-slate-700 bg-slate-950' }}">
                            <input type="radio" name="layout_display_mode" value="grid" class="sr-only" {{ ($setting->layout_display_mode ?? 'slideshow') === 'grid' ? 'checked' : '' }}>
                            <div class="flex gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0"><i class="fa-solid fa-table-cells-large text-emerald-400"></i></div>
                                <div>
                                    <div class="text-sm font-bold text-white">Grid Rapi</div>
                                    <div class="text-[11px] text-slate-400">Semua kartu terlihat sekaligus, disusun berjajar rata & proporsional, muat satu layar.</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-xl border-2 p-4 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-950/30 {{ ($setting->layout_display_mode ?? 'slideshow') === 'auto' ? 'border-amber-500 bg-amber-950/30' : 'border-slate-700 bg-slate-950' }}">
                            <input type="radio" name="layout_display_mode" value="auto" class="sr-only" {{ ($setting->layout_display_mode ?? 'slideshow') === 'auto' ? 'checked' : '' }}>
                            <div class="flex gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center shrink-0"><i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i></div>
                                <div>
                                    <div class="text-sm font-bold text-white">Otomatis</div>
                                    <div class="text-[11px] text-slate-400">Kecil = grid, banyak = slideshow. Sistem memilih yang paling pas agar muat satu layar.</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Ukuran kartu -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-3">Ukuran Kartu Preview</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @php $sizes = ['small' => ['Small • Kompak', 'Banyak layout muat, pas untuk tablet kecil', 'fa-compress'], 'medium' => ['Medium • Standar', 'Seimbang & jelas, rekomendasi Full HD', 'fa-maximize'], 'large' => ['Large • Besar', 'Dominan & mudah dipilih dari kejauhan', 'fa-expand']]; @endphp
                            @foreach($sizes as $key => [$label, $desc, $icon])
                            <label class="relative flex cursor-pointer rounded-xl border-2 p-3 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-950/30 {{ ($setting->layout_display_size ?? 'medium') === $key ? 'border-brand-500 bg-brand-950/30' : 'border-slate-700 bg-slate-950' }}">
                                <input type="radio" name="layout_display_size" value="{{ $key }}" class="sr-only" {{ ($setting->layout_display_size ?? 'medium') === $key ? 'checked' : '' }}>
                                <div class="flex gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center shrink-0"><i class="fa-solid {{ $icon }} text-brand-400"></i></div>
                                    <div>
                                        <div class="text-xs font-bold text-white">{{ $label }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $desc }}</div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Auto-scroll slideshow -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-3">Auto-scroll Slideshow (Mode Demonstrasi Booth)</label>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer">
                                <input type="hidden" name="layout_auto_scroll" value="0">
                                <input type="checkbox" name="layout_auto_scroll" value="1" {{ $setting->layout_auto_scroll ? 'checked' : '' }} class="rounded border-slate-700 text-brand-600">
                                <i class="fa-solid fa-circle-play text-brand-400"></i>
                                Aktifkan auto-scroll
                            </label>
                            <label class="flex items-center gap-2 text-xs text-slate-200">
                                <span class="text-slate-400">Geser otomatis tiap</span>
                                <input type="number" name="layout_auto_scroll_interval" min="2" max="60" value="{{ $setting->layout_auto_scroll_interval ?? 5 }}" class="w-20 bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1.5 text-center focus:border-brand-500">
                                <span class="text-slate-400">detik</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Slide akan bergeser sendiri ke kanan (loop) agar tampilan booth hidup. Berlaku untuk mode Slideshow; berhenti bila admin/lock aktif atau pengguna menyentuh layar.</p>
                    </div>

                    <!-- Daftar layout yg ditampilkan -->
                    <div class="mt-4 p-4 rounded-xl border border-slate-800 bg-slate-950">
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Layout yang Ditampilkan di Booth</label>
                        <p class="text-[11px] text-slate-500 mb-3">Kosongkan semua (tidak ada centang) = tampilkan SEMUA layout.</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                            @foreach($packages as $pkg)
                            <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer bg-slate-900 rounded-lg px-2.5 py-2 border border-slate-800 hover:border-brand-700">
                                <input type="checkbox" name="layout_visible_ids[]" value="{{ $pkg['id'] }}" {{ in_array($pkg['id'], $visibleIds) ? 'checked' : '' }} class="rounded border-slate-700 text-brand-600">
                                <img src="{{ asset('layout-previews/'.$pkg['id'].'.png') }}" alt="" class="w-6 h-auto rounded object-cover border border-slate-700" onerror="this.style.display='none'">
                                <span class="truncate">{{ $pkg['name'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Tampilan Booth
                        </button>
                    </div>
                </form>
            </div>

<!-- 1B. GOOGLE DRIVE & UNDUHAN (QR FOTO) -->
        <div id="sec-gdrive" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-brands fa-google-drive text-emerald-400"></i>
                    <span>1B. Google Drive & Unduhan Foto (QR Otomatis)</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                    Foto hasil otomatis di-<b>upload ke Google Drive kamu sendiri (My Drive)</b> lalu QR di layar hasil & di dalam foto berisi <b>link unduhan langsung</b> &mdash; tamu tinggal scan. Pilih <b>OAuth</b> untuk akun pribadi <b>atau</b> Service Account (Shared Drive).
                </p>

                @if(session('success'))
                    <div class="mb-4 px-3 py-2 rounded-lg bg-emerald-950 border border-emerald-700 text-emerald-300 text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 px-3 py-2 rounded-lg bg-rose-950 border border-rose-800 text-rose-300 text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
                @endif

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @if($hasOAuth)
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-emerald-950 border border-emerald-700 text-emerald-300">
                            <i class="fa-solid fa-circle-check"></i> Terhubung ke Google Drive (OAuth - My Drive)
                        </span>
                    @elseif($hasServiceAccount)
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-amber-950 border border-amber-700 text-amber-300">
                            <i class="fa-solid fa-circle-check"></i> Service Account terpasang (Shared Drive)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-rose-950 border border-rose-800 text-rose-300">
                            <i class="fa-solid fa-triangle-exclamation"></i> Belum terhubung ke Google Drive
                        </span>
                    @endif
                    @if(!$hasOAuthClient && !$hasServiceAccount)
                        <span class="inline-flex items-center gap-1.5 text-[11px] px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-400">
                            OAuth Client belum diisi di .env
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    @if($hasOAuth)
                        <form action="{{ route('admin.gdrive.disconnect') }}" method="POST" onsubmit="return confirm('Putuskan koneksi Google Drive?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold flex items-center gap-2">
                                <i class="fa-solid fa-link-slash"></i> Putuskan Koneksi (OAuth)
                            </button>
                        </form>
                    @elseif($hasOAuthClient)
                        <a href="{{ route('admin.gdrive.connect') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2">
                            <i class="fa-brands fa-google"></i> Hubungkan Google Drive (Login)
                        </a>
                    @else
                        <button disabled class="px-4 py-2 rounded-xl bg-slate-800 text-slate-500 text-xs font-bold flex items-center gap-2 cursor-not-allowed border border-slate-700">
                            <i class="fa-brands fa-google"></i> Hubungkan Google Drive (isi .env dulu)
                        </button>
                    @endif
                    <button type="button" id="btnTestDrive" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 border border-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-plug-circle-check"></i> Tes Koneksi
                    </button>
                    <span id="driveTestResult" class="text-xs font-semibold"></span>
                </div>

                <details class="group bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden mb-4">
                    <summary class="px-4 py-3 text-xs font-bold text-slate-200 cursor-pointer flex items-center justify-between hover:bg-slate-800">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-circle-question text-emerald-400"></i> Tutorial Lengkap — Cara Hubungkan Google Drive & QR Download</span>
                        <i class="fa-solid fa-chevron-down text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="px-4 py-4 text-xs leading-relaxed text-slate-300 space-y-4 border-t border-slate-700 bg-slate-900/50">
                        <div class="p-3 rounded-lg bg-amber-950/50 border border-amber-800 text-amber-200">
                            <b>Kenapa Service Account lama gagal?</b> Service Account tidak punya kuota storage di <b>My Drive</b> pribadi → error <code>storageQuotaExceeded</code>. Solusi: pakai <b>OAuth 2.0</b> (login dengan akun Google kamu sendiri, mis. <code>ganangdewha@gmail.com</code>). File akan masuk ke folder My Drive kamu, QR langsung download dari GDrive.
                        </div>
                        <div>
                            <b class="text-white">A. Buat OAuth Client di Google Cloud Console</b>
                            <ol class="list-decimal ml-5 mt-1.5 space-y-1 text-slate-400">
                                <li>Buka <a href="https://console.cloud.google.com" target="_blank" class="text-emerald-400 underline">console.cloud.google.com</a> → pilih project <code>photobooth-app-506709</code> (atau buat baru).</li>
                                <li><b>APIs & Services → Enabled APIs</b> → Enable <b>Google Drive API</b>.</li>
                                <li><b>APIs & Services → OAuth consent screen</b> → User Type <b>External</b> → isi App name (mis. Photobooth) → Add Test User <code>ganangdewha@gmail.com</code> → Scopes tambah <code>../auth/drive.file</code> → Save.</li>
                                <li><b>Credentials → Create Credentials → OAuth Client ID</b> → Application type <b>Web application</b> → Authorized redirect URIs tambah:
                                    <div class="mt-1 p-2 bg-slate-950 border border-slate-700 rounded font-mono text-emerald-300">http://localhost:8000/admin/gdrive/callback</div>
                                    Jika sudah online, tambah juga <code>https://domain-kamu.com/admin/gdrive/callback</code>
                                </li>
                                <li>Copy <b>Client ID</b> dan <b>Client Secret</b>.</li>
                            </ol>
                        </div>
                        <div>
                            <b class="text-white">B. Isi .env & Hubungkan</b>
                            <ol class="list-decimal ml-5 mt-1.5 space-y-1 text-slate-400">
                                <li>Buka <code>D:\xampp\htdocs\photobooth\.env</code> isi:
                                    <div class="mt-1 p-2 bg-slate-950 border border-slate-700 rounded font-mono text-[11px] leading-5">GOOGLE_OAUTH_CLIENT_ID=xxx.apps.googleusercontent.com<br>GOOGLE_OAUTH_CLIENT_SECRET=GOCSPX-xxx<br>GOOGLE_OAUTH_REDIRECT_URI=http://localhost:8000/admin/gdrive/callback<br>GOOGLE_DRIVE_FOLDER_ID=1L6YR5LzvxSZsLDGQZPwpEpdbyhKDtUQl</div>
                                    Folder ID adalah dari URL Drive kamu: <code>https://drive.google.com/drive/folders/&lt;ID&gt;</code>
                                </li>
                                <li>Jalankan <code>php artisan config:clear</code> (atau restart XAMPP).</li>
                                <li>Kembali ke halaman ini → tombol akan berubah jadi <b class="text-emerald-400">Hubungkan Google Drive (Login)</b> → Klik → login Google → <b>Allow</b> → balik dengan pesan <code>Terhubung ke Google Drive kamu!</code></li>
                                <li>Klik <b>Tes Koneksi</b> → harus <code>Koneksi OAuth Google Drive BERHASIL! Folder kamu valid.</code></li>
                                <li>Ambil foto trial di booth → cek folder Drive kamu, file <code>photobooth_ORD-xxx.png</code> muncul (permission Anyone Reader otomatis).</li>
                            </ol>
                        </div>
                        <div>
                            <b class="text-white">C. Cara Kerja QR Download</b>
                            <ul class="list-disc ml-5 mt-1.5 space-y-1 text-slate-400">
                                <li><b>QR di dalam foto</b> (atas tulisan PHOTO-ME) + <b>QR di layar hasil</b> sama: berisi <code>webContentLink</code> GDrive (<code>https://drive.google.com/uc?id=...&export=download</code>) → scan langsung download tanpa login.</li>
                                <li>Halaman <code>/download/{token}</code> juga ada: tombol <b>Simpan Ulang ke Galeri HP</b> (<code>/file/{token}</code>) + tombol <b>Buka File Asli di Google Drive</b> jika sudah terupload.</li>
                                <li>Jika Drive belum terhubung, QR fallback ke <code>/download/{token}</code> di server lokal — isi <b>Domain Publik</b> (mis. <code>https://booth.domain.com</code>) agar HP di jaringan lain bisa buka.</li>
                                <li>Jika pakai Service Account (bukan OAuth), hanya bisa untuk <b>Shared Drive</b>: buat Shared Drive → Add member service account sebagai Content Manager → pakai Folder ID Shared Drive.</li>
                            </ul>
                        </div>
                    </div>
                </details>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="is_payment_enabled" value="{{ $setting->is_payment_enabled ? '1' : '0' }}">
                    <input type="hidden" name="default_brand_text" value="{{ $setting->default_brand_text }}">
                    <input type="hidden" name="default_frame_color" value="{{ $setting->default_frame_color }}">
                    <input type="hidden" name="admin_username" value="{{ $setting->admin_username }}">
                    <input type="hidden" name="admin_password" value="{{ $setting->admin_password }}">
                    <input type="hidden" name="admin_pin" value="{{ $setting->admin_pin }}">
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">

                    <div>
                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Service Account JSON</label>
                        <input type="file" name="service_account_file" accept=".json,application/json"
                               class="block w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-600 file:text-white file:font-bold file:cursor-pointer bg-slate-950 border border-slate-700 rounded-lg cursor-pointer">
                        <p class="text-[11px] text-slate-500 mt-1">Buat Service Account di Google Cloud Console, aktifkan <b>Google Drive API</b>, unduh JSON, lalu <b>bagikan Folder Drive</b> ke email service account tersebut.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Folder ID Google Drive</label>
                            <input type="text" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}"
                                   placeholder="Contoh: 1A2B3C4D5E6F7G8h9i0j" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2.5 focus:border-emerald-500">
                            <p class="text-[11px] text-slate-500 mt-1">ID folder dari URL Drive (kosongkan = masuk ke root).</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Domain Publik (opsional)</label>
                            <input type="url" name="public_domain_url" value="{{ $setting->public_domain_url }}"
                                   placeholder="https://booth.domain.com" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2.5 focus:border-emerald-500">
                            <p class="text-[11px] text-slate-500 mt-1">Alamat publik booth (untuk fallback QR bila Drive tidak dipakai).</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-800 pt-4 mt-1">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Kirim Foto ke Email (Gmail / SMTP)</label>
                                <p class="text-[11px] text-slate-500 mt-0.5">Aktifkan agar tamu bisa meminta foto dikirim ke email mereka. Atur SMTP di file <code>.env</code> (lihat panduan Gmail App Password).</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="hidden" name="enable_email" value="0">
                                <input type="checkbox" name="enable_email" value="1" {{ $setting->enable_email ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-checked:bg-emerald-600 rounded-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </div>
                        <div class="mt-3">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Nama Pengirim Email (From Name)</label>
                            <input type="text" name="email_from_name" value="{{ $setting->email_from_name }}" placeholder="Photobooth Kami"
                                   class="w-full sm:w-1/2 bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2.5 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Drive
                        </button>
                    </div>
                </form>
            </div>

            <!-- 1D. MODE OPERASIONAL — MANDIRI vs MANUAL (WEDDING) -->
            <div id="sec-mode" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-people-group text-brand-400"></i>
                    <span>1D. Mode Operasional — Mandiri (QRIS) vs Manual (Wedding Gratis)</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                    <b>Mandiri</b>: self-service, bayar QRIS per layout, <b>galeri disembunyikan</b> (privasi). <b>Manual / Wedding</b>: gratis tanpa nominal seperti <code>photobooth-io.cc/index.html</code> → START langsung foto, <b>galeri publik tampil</b> (semua tamu bisa lihat siapa aja sudah foto). QR download otomatis di foto tetap ada di kedua mode.
                </p>
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="is_payment_enabled" value="{{ $setting->is_payment_enabled ? '1' : '0' }}">
                    <input type="hidden" name="default_brand_text" value="{{ $setting->default_brand_text }}">
                    <input type="hidden" name="default_frame_color" value="{{ $setting->default_frame_color }}">
                    <input type="hidden" name="admin_username" value="{{ $setting->admin_username }}">
                    <input type="hidden" name="admin_password" value="{{ $setting->admin_password }}">
                    <input type="hidden" name="admin_pin" value="{{ $setting->admin_pin }}">
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">
                    <input type="hidden" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}">
                    <input type="hidden" name="public_domain_url" value="{{ $setting->public_domain_url }}">
                    <input type="hidden" name="enable_email" value="{{ $setting->enable_email ? '1' : '0' }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex cursor-pointer rounded-xl border-2 p-4 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-950/30 {{ ($setting->booth_mode ?? 'mandiri') === 'mandiri' ? 'border-brand-500 bg-brand-950/30' : 'border-slate-700 bg-slate-950' }}">
                            <input type="radio" name="booth_mode" value="mandiri" class="sr-only" {{ ($setting->booth_mode ?? 'mandiri') === 'mandiri' ? 'checked' : '' }}>
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center shrink-0"><i class="fa-solid fa-store text-brand-400"></i></div>
                                <div>
                                    <div class="text-sm font-bold text-white">Mandiri — QRIS Bayar</div>
                                    <div class="text-[11px] text-slate-400">Bayar per foto via QRIS, galeri disembunyikan (privasi).</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-xl border-2 p-4 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/30 {{ ($setting->booth_mode ?? 'mandiri') === 'manual' ? 'border-emerald-500 bg-emerald-950/30' : 'border-slate-700 bg-slate-950' }}">
                            <input type="radio" name="booth_mode" value="manual" class="sr-only" {{ ($setting->booth_mode ?? 'mandiri') === 'manual' ? 'checked' : '' }}>
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-900 flex items-center justify-center shrink-0"><i class="fa-solid fa-heart text-emerald-400"></i></div>
                                <div>
                                    <div class="text-sm font-bold text-white">Manual / Wedding — Gratis</div>
                                    <div class="text-[11px] text-slate-400">Tanpa nominal, seperti photobooth-io, galeri publik tampil.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Mode
                    </button>
                </form>
            </div>

            <!-- 1C. PREVIEW LAYOUT — EDIT GAMBAR CONTOH -->
            <div id="sec-preview" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-2 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-images text-brand-400"></i>
                    <span>1C. Preview Layout — Edit Gambar Contoh (Pilih Format & Layout)</span>
                </h2>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                    Gambar preview di halaman <b>"Pilih Format & Layout Foto Favoritmu"</b>. Upload PNG/JPG custom per layout untuk mengganti template auto-generate. Kosongkan = pakai default. Klik <b>Reset</b> untuk generate ulang.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($packages as $pkg)
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 flex flex-col">
                        <div class="aspect-[3/4] bg-slate-900 rounded-lg overflow-hidden border border-slate-800 flex items-center justify-center mb-2">
                            <img src="{{ asset('layout-previews/'.$pkg['id'].'.png') }}?v={{ file_exists(public_path('layout-previews/'.$pkg['id'].'.png')) ? filemtime(public_path('layout-previews/'.$pkg['id'].'.png')) : time() }}" alt="{{ $pkg['name'] }}" class="w-full h-full object-contain" onerror="this.style.display='none'">
                        </div>
                        <div class="text-[11px] font-bold text-white leading-tight">{{ $pkg['name'] }}</div>
                        <div class="text-[10px] text-slate-500 mb-2">{{ $pkg['shots'] }} Foto • {{ $pkg['id'] }}</div>
                        <form action="{{ route('admin.layout.preview.upload') }}" method="POST" enctype="multipart/form-data" class="mt-auto space-y-1.5">
                            @csrf
                            <input type="hidden" name="layout_type" value="{{ $pkg['id'] }}">
                            <input type="file" name="preview_image" accept="image/*" class="block w-full text-[10px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-brand-600 file:text-white file:text-[10px] file:font-bold bg-slate-900 border border-slate-700 rounded">
                            <button type="submit" class="w-full py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-bold">Upload</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-slate-500 mt-3">Tips: Ukuran ideal strip 400×1164, grid 678×618, polaroid 420×500. File akan overwrite <code>public/layout-previews/{id}.png</code>.</p>
            </div>

            <!-- 2. KALIBRASI KAMERA & ISO HARDWARE -->
            <div id="sec-camera" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
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
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">
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
            <div id="sec-lock" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-lock text-amber-400"></i>
                    <span>3. Mode Kunci (Kiosk Lock) & Kredensial Admin</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @if($setting->is_payment_enabled) <input type="hidden" name="is_payment_enabled" value="1"> @endif
                    <input type="hidden" name="camera_device_id" value="{{ $setting->camera_device_id }}">
                    <input type="hidden" name="camera_brightness" value="{{ $setting->camera_brightness }}">
                    <input type="hidden" name="camera_contrast" value="{{ $setting->camera_contrast }}">
                    <input type="hidden" name="camera_iso" value="{{ $setting->camera_iso }}">
                    <input type="hidden" name="camera_saturation" value="{{ $setting->camera_saturation }}">
                    <input type="hidden" name="google_drive_folder_id" value="{{ $setting->google_drive_folder_id }}">
                    <input type="hidden" name="public_domain_url" value="{{ $setting->public_domain_url }}">
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="shrink-0">
                                    @if($setting->favicon_path)
                                        <img src="{{ asset($setting->favicon_path) }}" alt="Favicon" class="w-10 h-10 rounded-lg border border-slate-700 bg-slate-900">
                                    @else
                                        <div class="w-10 h-10 rounded-lg border border-dashed border-slate-700 bg-slate-900 flex items-center justify-center text-slate-600 text-xs">?</div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Ganti Favicon</label>
                                    <input type="file" name="favicon" accept="image/png,image/svg+xml,image/x-icon,image/jpeg" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Nama Aplikasi / Booth (Tampil di Header & Title)</label>
                                <input type="text" name="app_name" value="{{ $setting->app_name }}" maxlength="30" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:border-brand-500">
                            </div>

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

                            <div class="pt-3 border-t border-slate-800 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Warna Tema UI</label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="theme_color" value="{{ $setting->theme_color ?? '#c2337d' }}" class="w-10 h-10 rounded-lg border-0 cursor-pointer bg-transparent">
                                        <span class="text-xs font-mono text-slate-400">{{ $setting->theme_color ?? '#c2337d' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Bahasa Antarmuka</label>
                                    <select name="ui_language" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                        <option value="id" {{ ($setting->ui_language ?? 'id') == 'id' ? 'selected' : '' }}>Indonesia (ID)</option>
                                        <option value="en" {{ $setting->ui_language == 'en' ? 'selected' : '' }}>English (EN)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-800 space-y-3">
                                <div class="flex items-center gap-4 p-3 rounded-xl bg-slate-950 border border-slate-800">
                                    <div class="shrink-0">
                                        @if($setting->business_logo_path)
                                            <img src="{{ asset($setting->business_logo_path) }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-lg border border-slate-700 p-1">
                                        @else
                                            <div class="w-12 h-12 flex items-center justify-center bg-slate-900 rounded-lg border border-dashed border-slate-700 text-slate-600 text-xs text-center">No Logo</div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1">Logo Watermark Bisnis</label>
                                        <input type="file" name="business_logo" accept="image/png,image/jpg,image/jpeg,image/webp,image/svg+xml" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Posisi Logo</label>
                                        <select name="logo_position" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                            <option value="bottom-center" {{ ($setting->logo_position ?? 'bottom-center') == 'bottom-center' ? 'selected' : '' }}>Bawah Tengah</option>
                                            <option value="top-left" {{ $setting->logo_position == 'top-left' ? 'selected' : '' }}>Kiri Atas</option>
                                            <option value="top-right" {{ $setting->logo_position == 'top-right' ? 'selected' : '' }}>Kanan Atas</option>
                                            <option value="bottom-left" {{ $setting->logo_position == 'bottom-left' ? 'selected' : '' }}>Kiri Bawah</option>
                                            <option value="bottom-right" {{ $setting->logo_position == 'bottom-right' ? 'selected' : '' }}>Kanan Bawah</option>
                                            <option value="center" {{ $setting->logo_position == 'center' ? 'selected' : '' }}>Tengah</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Bentuk Foto Default</label>
                                        <select name="default_photo_shape" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5">
                                            <option value="none" {{ ($setting->default_photo_shape ?? 'none') == 'none' ? 'selected' : '' }}>Kotak (None)</option>
                                            <option value="soft" {{ $setting->default_photo_shape == 'soft' ? 'selected' : '' }}>Soft Edge</option>
                                            <option value="circle" {{ $setting->default_photo_shape == 'circle' ? 'selected' : '' }}>Lingkaran</option>
                                            <option value="heart" {{ $setting->default_photo_shape == 'heart' ? 'selected' : '' }}>Hati</option>
                                        </select>
                                    </div>
                                </div>
                                <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer">
                                    <input type="hidden" name="lock_photo_shape" value="0">
                                    <input type="checkbox" name="lock_photo_shape" value="1" {{ $setting->lock_photo_shape ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                    <div>
                                        <span class="font-bold text-white block">Kunci Bentuk Foto</span>
                                        <span class="text-slate-500 text-[11px]">Pengunjung tidak bisa mengubah bentuk foto (pakai default booth).</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                            <span class="text-xs font-bold text-amber-400 block mb-2">Batasi Pengeditan oleh Pengunjung:</span>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer">
                                <input type="hidden" name="lock_brand_text" value="0">
                                <input type="checkbox" name="lock_brand_text" value="1" {{ $setting->lock_brand_text ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-white block">Kunci Teks Brand</span>
                                    <span class="text-slate-500 text-[11px]">Pengunjung tidak bisa mengganti/menghapus nama brand booth Anda.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer">
                                <input type="hidden" name="lock_frame_color" value="0">
                                <input type="checkbox" name="lock_frame_color" value="1" {{ $setting->lock_frame_color ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-white block">Kunci Warna Bingkai</span>
                                    <span class="text-slate-500 text-[11px]">Warna bingkai terkunci pada warna default booth.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer pt-2 border-t border-slate-800">
                                <input type="hidden" name="is_lock_mode" value="0">
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

            <!-- 4. SUASANA BOOTH & MODE EVENT -->
            <div id="sec-ambience" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-wand-magic-sparkles text-brand-400"></i>
                    <span>4. Suasana Booth & Mode Event</span>
                </h2>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @if($setting->is_payment_enabled) <input type="hidden" name="is_payment_enabled" value="1"> @endif
                    @if($setting->is_lock_mode) <input type="hidden" name="is_lock_mode" value="1"> @endif
                    @if($setting->lock_brand_text) <input type="hidden" name="lock_brand_text" value="1"> @endif
                    @if($setting->lock_frame_color) <input type="hidden" name="lock_frame_color" value="1"> @endif
                    @if($setting->lock_photo_shape) <input type="hidden" name="lock_photo_shape" value="1"> @endif
                    @if($setting->enable_countdown_sound) <input type="hidden" name="enable_countdown_sound" value="1"> @endif
                    @if($setting->enable_greenscreen) <input type="hidden" name="enable_greenscreen" value="1"> @endif
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
                    <input type="hidden" name="app_name" value="{{ $setting->app_name }}">
                    <input type="hidden" name="theme_color" value="{{ $setting->theme_color ?? '#c2337d' }}">
                    <input type="hidden" name="ui_language" value="{{ $setting->ui_language ?? 'id' }}">
                    <input type="hidden" name="logo_position" value="{{ $setting->logo_position ?? 'bottom-center' }}">
                    <input type="hidden" name="default_photo_shape" value="{{ $setting->default_photo_shape ?? 'none' }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Musik Latar -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Musik Latar Booth (MP3)</label>
                            <div class="flex items-center gap-3 mb-2">
                                @if($setting->bg_music_path)
                                    <span class="text-[11px] text-emerald-400"><i class="fa-solid fa-music"></i> Aktif</span>
                                @else
                                    <span class="text-[11px] text-slate-500">Belum diatur</span>
                                @endif
                            </div>
                            <input type="file" name="bg_music" accept="audio/mpeg,audio/wav,audio/ogg" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                            <span class="text-[10px] text-slate-500 mt-1 block">Maks 10 MB. Diputar otomatis di layar booth (mute-able). BGM bawaan: "Carefree" by Kevin MacLeod (incompetech.com, CC BY 3.0).</span>
                        </div>

                        <!-- Virtual Background / Green Screen -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                            <label class="flex items-start gap-3 text-xs text-slate-300 cursor-pointer mb-3">
                                <input type="hidden" name="enable_greenscreen" value="0">
                                <input type="checkbox" name="enable_greenscreen" value="1" {{ $setting->enable_greenscreen ? 'checked' : '' }} class="mt-0.5 rounded border-slate-700 text-brand-600">
                                <div>
                                    <span class="font-bold text-white block">Aktifkan Green Screen / Virtual BG</span>
                                    <span class="text-slate-500 text-[11px]">Ganti latar hijau otomatis dengan gambar di bawah.</span>
                                </div>
                            </label>
                            <div class="flex items-center gap-3 mb-2">
                                @if($setting->greenscreen_bg_path)
                                    <img src="{{ asset($setting->greenscreen_bg_path) }}" class="w-16 h-12 object-cover rounded border border-slate-700">
                                @else
                                    <div class="w-16 h-12 rounded border border-dashed border-slate-700 flex items-center justify-center text-slate-600 text-[10px]">No BG</div>
                                @endif
                                <span class="text-[11px] text-slate-500">Background virtual</span>
                            </div>
                            <input type="file" name="greenscreen_bg" accept="image/png,image/jpg,image/jpeg,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700">
                        </div>

                        <!-- Countdown Sound -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white text-sm block">Suara Countdown</span>
                                <span class="text-xs text-slate-400">Bunyi "tick" sebelum jepret otomatis.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="enable_countdown_sound" value="0">
                                <input type="checkbox" name="enable_countdown_sound" value="1" {{ $setting->enable_countdown_sound ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-800 peer-checked:bg-brand-600 rounded-full after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <!-- Event Voucher -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Kode Voucher Mode Event</label>
                            <input type="text" name="event_voucher_code" value="{{ $setting->event_voucher_code }}" placeholder="Misal: GRATIS2025" class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 font-mono uppercase">
                            <span class="text-[10px] text-slate-500 mt-1 block">Tampil di layar booth saat Mode Event Gratis (opsional, untuk pelacakan).</span>
                        </div>

                        <!-- Footer Booth -->
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Teks Footer Halaman Booth</label>
                            <textarea name="footer_text" rows="3" placeholder="Terima kasih telah menggunakan Photobooth kami..." class="w-full bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:border-brand-500">{{ $setting->footer_text }}</textarea>
                            <span class="text-[10px] text-slate-500 mt-1 block">Teks ini tampil di bagian bawah halaman booth (beranda, gallery, hasil). Mendukung multiple baris.</span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Suasana & Event
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KOLOM KANAN (1 Kolom): NAVIGASI + UPLOAD FRAME BERGAMBAR -->
        <div class="space-y-8">
            <!-- Sidebar Navigasi Bagian (jangkar, tetap terlihat saat scroll) -->
            <div class="lg:sticky lg:top-20 bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
                <h2 class="text-xs font-bold text-white uppercase tracking-widest flex items-center gap-2 mb-3 border-b border-slate-800 pb-3">
                    <i class="fa-solid fa-bars text-brand-400"></i>
                    <span>Menu Cepat</span>
                </h2>
                <nav class="space-y-1">
                    <a href="#sec-payment" data-inav="sec-payment" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-credit-card w-4 text-brand-400"></i> Pembayaran & Event</a>
                    <a href="#sec-display" data-inav="sec-display" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-tv w-4 text-brand-400"></i> Tampilan Booth di Layar</a>
                    <a href="#sec-mode" data-inav="sec-mode" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-people-group w-4 text-brand-400"></i> Mode Operasional</a>
                    <a href="#sec-camera" data-inav="sec-camera" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-sliders w-4 text-brand-400"></i> Kalibrasi Kamera</a>
                    <a href="#sec-lock" data-inav="sec-lock" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-lock w-4 text-amber-400"></i> Mode Kunci Kiosk</a>
                    <a href="#sec-ambience" data-inav="sec-ambience" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-wand-magic-sparkles w-4 text-brand-400"></i> Suasana Booth & Event</a>
                    <a href="#sec-preview" data-inav="sec-preview" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-images w-4 text-brand-400"></i> Preview Layout</a>
                    <a href="#sec-gdrive" data-inav="sec-gdrive" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-brands fa-google-drive w-4 text-emerald-400"></i> Google Drive</a>
                    <a href="#sec-upload" data-inav="sec-upload" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-arrow-up-from-bracket w-4 text-brand-400"></i> Tambah Template Frame</a>
                    <a href="#sec-frames" data-inav="sec-frames" class="inav-link flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-brand-950/50 hover:text-brand-300 border border-transparent transition-all"><i class="fa-solid fa-layer-group w-4 text-brand-400"></i> Koleksi Frame Aktif</a>
                </nav>
            </div>

            <div id="sec-upload" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
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

                <!-- Panduan Ukuran Template Frame -->
                <div class="mt-4 bg-slate-950 border border-slate-800 rounded-xl p-4">
                    <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-ruler-combined"></i> Panduan Ukuran Template (Contoh)
                    </h3>
                    <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">
                        Buat frame dengan <b>latar transparan (PNG/WebP)</b>. Gambar akan di-<i>stretch</i> menyesuaikan canvas hasil,
                        jadi <b>rasio aspek template harus sama</b> dengan layout tujuannya agar tidak melar. Resolusi disarankan
                        <b>2×–3×</b> dari ukuran canvas di bawah (tajam untuk cetak 300 DPI). Maksimal ukuran file <b>5 MB</b>.
                        Upload terpisah untuk tiap <b>Format Layout</b> agar pas di semua frame.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[11px] text-slate-300 border-collapse">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-800">
                                    <th class="text-left py-1.5 pr-2">Format Layout</th>
                                    <th class="text-left py-1.5 px-2">Canvas Hasil</th>
                                    <th class="text-left py-1.5 px-2">Template Disarankan (3×)</th>
                                    <th class="text-left py-1.5 px-2">Rasio</th>
                                    <th class="text-left py-1.5 px-2">Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frameSizes as $layout => $sz)
                                <tr class="border-b border-slate-800/60">
                                    <td class="py-1.5 pr-2 font-mono text-brand-300">{{ $layout }}</td>
                                    <td class="py-1.5 px-2">{{ $sz['w'] }} × {{ $sz['h'] }} px</td>
                                    <td class="py-1.5 px-2">{{ $sz['w']*3 }} × {{ $sz['h']*3 }} px</td>
                                    <td class="py-1.5 px-2">{{ number_format($sz['w']/$sz['h'], 3) }}</td>
                                    <td class="py-1.5 px-2">
                                        <a href="{{ route('admin.frames.template.download', ['layout' => $layout]) }}" class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-brand-300 border border-slate-700" title="Download contoh template SVG">
                                            <i class="fa-solid fa-download"></i> SVG
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2">
                        Contoh: untuk <b>strip_4</b> gunakan template <b>1200 × 3492 px</b> (rasio 0.343). Pastikan ilustrasi/dekorasi
                        ditempatkan di area tepi (margin) karena bagian tengah akan tertutup foto.
                    </p>
                </div>
            </div>

            <!-- Daftar Template Frame Aktif -->
            <div id="sec-frames" class="scroll-mt-24 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
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

    // Tes koneksi Google Drive
    const btnTestDrive = document.getElementById('btnTestDrive');
    const driveTestResult = document.getElementById('driveTestResult');
    if (btnTestDrive) {
        btnTestDrive.addEventListener('click', async () => {
            driveTestResult.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menguji...';
            driveTestResult.className = 'text-xs font-semibold text-slate-400';
            try {
                const res = await fetch("{{ route('admin.gdrive.test') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content') }
                });
                const data = await res.json();
                if (data.ok) {
                    driveTestResult.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + data.message;
                    driveTestResult.className = 'text-xs font-semibold text-emerald-400';
                } else {
                    driveTestResult.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + data.message;
                    driveTestResult.className = 'text-xs font-semibold text-rose-400';
                }
            } catch (e) {
                driveTestResult.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Gagal menghubungi server.';
                driveTestResult.className = 'text-xs font-semibold text-rose-400';
            }
        });
    }

    // Sidebar navigasi: sorot bagian aktif saat scroll + scroll halus saat klik
    const inavLinks = document.querySelectorAll('.inav-link');
    const inavSections = Array.from(inavLinks).map(a => document.getElementById(a.dataset.inav)).filter(Boolean);
    function updateINavActive() {
        const marker = 120;
        let current = inavSections[0];
        inavSections.forEach(sec => { if (sec && sec.getBoundingClientRect().top <= marker) current = sec; });
        inavLinks.forEach(a => {
            const active = current && a.dataset.inav === current.id;
            a.classList.toggle('bg-brand-950/50', active);
            a.classList.toggle('text-brand-300', active);
            a.classList.toggle('border-brand-700/60', active);
            if (active) a.classList.add('text-brand-300');
        });
    }
    window.addEventListener('scroll', updateINavActive, { passive: true });
    window.addEventListener('resize', updateINavActive);
    setTimeout(updateINavActive, 300);
    inavLinks.forEach(a => {
        a.addEventListener('click', (e) => {
            const sec = document.getElementById(a.dataset.inav);
            if (!sec) return;
            e.preventDefault();
            const y = sec.getBoundingClientRect().top + window.scrollY - 88;
            window.scrollTo({ top: y, behavior: 'smooth' });
        });
    });
</script>
@endsection