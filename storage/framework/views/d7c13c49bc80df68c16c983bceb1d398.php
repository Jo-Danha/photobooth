<?php $__env->startSection('title', 'Pilih Layout & Mulai Foto - Photobooth'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?php
        $isManual = ($setting->booth_mode ?? 'mandiri') === 'manual';
    ?>
    <div class="text-center max-w-2xl mx-auto mb-5">
        <?php if($isManual): ?>
        <div class="flex justify-center mb-3">
            <a href="<?php echo e(route('photobooth.gallery')); ?>" class="text-xs font-bold text-slate-300 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 hover:bg-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-images text-brand-400"></i> <span data-i18n="booth.gallery">Gallery Hasil</span>
            </a>
        </div>
        <?php endif; ?>
        <span class="text-xs font-bold uppercase tracking-widest text-brand-400 bg-brand-950/80 border border-brand-800/60 px-3 py-1 rounded-full" data-i18n="booth.step">
            <?php if($isManual): ?> Wedding Mode — Gratis <?php else: ?> Langkah 1 dari 3: Pilih Template <?php endif; ?>
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-4 tracking-tight" data-i18n="booth.hero_title">
            Pilih Format & Layout Foto Favoritmu
        </h1>
        <p class="text-slate-400 mt-2 text-sm" data-i18n="booth.hero_sub">
            <?php if($isManual): ?>
                Pilih layout favoritmu, langsung foto tanpa bayar — seperti photobooth-io.cc — QR download otomatis ada di foto!
            <?php else: ?>
                Geser ke kiri/kanan untuk melihat semua template. Bayar instan dengan QRIS, nikmati sesi foto bebas bergaya!
            <?php endif; ?>
        </p>
    </div>

    <form action="<?php echo e(route('photobooth.session.create')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="relative">
            <!-- Tombol Navigasi -->
            <button type="button" id="btnPrev" class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 z-20 w-11 h-11 items-center justify-center rounded-full bg-slate-800/90 border border-slate-700 text-white shadow-lg hover:bg-slate-700 backdrop-blur">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" id="btnNext" class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 z-20 w-11 h-11 items-center justify-center rounded-full bg-slate-800/90 border border-slate-700 text-white shadow-lg hover:bg-slate-700 backdrop-blur">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <!-- Track Slider -->
            <div id="templateTrack" class="flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pt-7 pb-3 px-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="relative group cursor-pointer snap-center shrink-0 w-[82%] sm:w-[240px] lg:w-[280px] <?php if($pkg['popular']): ?> sm:scale-105 z-10 <?php endif; ?>">
                    <input type="radio" name="layout_type" value="<?php echo e($pkg['id']); ?>" class="peer sr-only" <?php echo e($pkg['popular'] ? 'checked' : ''); ?>>

                    <div class="h-full bg-slate-900/90 border-2 border-slate-800 rounded-3xl p-5 flex flex-col justify-between transition-all duration-200 peer-checked:border-brand-500 peer-checked:bg-slate-900 peer-checked:shadow-xl peer-checked:shadow-brand-500/20 hover:border-slate-600 min-h-[330px] <?php if($pkg['popular']): ?> ring-1 ring-brand-500/40 <?php endif; ?>">
                        <?php if($pkg['popular']): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-brand-600 to-indigo-600 text-white text-[11px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider shadow whitespace-nowrap">
                            ⭐ Paling Populer
                        </div>
                        <?php endif; ?>

                        <div>
                        <div class="w-full h-44 bg-slate-950 rounded-2xl mb-3 p-2 flex items-center justify-center border border-slate-800/80 overflow-hidden">
                            <img src="<?php echo e(asset('layout-previews/'.$pkg['id'].'.png')); ?>" alt="<?php echo e($pkg['name']); ?>" class="mx-auto block h-full w-auto max-w-full rounded-md shadow-md" style="height:100%;width:auto;" loading="lazy">
                        </div>

                            <h3 class="text-base font-bold text-white group-hover:text-brand-300 transition-colors">
                                <?php echo e($pkg['name']); ?>

                            </h3>
                            <p class="text-[11px] text-slate-400 mt-1 leading-snug">
                                <?php echo e($pkg['description']); ?>

                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                            <div>
                                <span class="inline-block text-[10px] font-semibold text-brand-300 bg-brand-950/70 border border-brand-800/60 px-2 py-0.5 rounded-full mb-1"><?php echo e($pkg['shots']); ?> Foto</span>
                                <span class="text-[11px] text-slate-400 block">Batas Sesi: <?php echo e($pkg['duration']); ?> Menit</span>
                                <?php if($isManual): ?>
                                    <span class="text-sm font-extrabold text-emerald-400">Gratis</span>
                                <?php else: ?>
                                    <span class="text-lg font-extrabold text-white">Rp <?php echo e(number_format($pkg['price'], 0, ',', '.')); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 border-slate-600 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-check text-white text-xs opacity-0 group-has-[:checked]:opacity-100"></i>
                            </div>
                        </div>
                    </div>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <p class="text-center text-[11px] text-slate-500 mt-3"><i class="fa-solid fa-hand-pointer"></i> Geser / scroll untuk melihat template lainnya</p>
        </div>

        <?php if($isManual): ?>
        <div class="flex justify-center mt-5">
            <div class="w-full sm:w-96 bg-emerald-950/50 border border-emerald-800 rounded-2xl p-3 flex items-center justify-center gap-2">
                <i class="fa-solid fa-heart text-emerald-400"></i>
                <span class="text-xs font-bold text-emerald-300">Mode Wedding (Manual) — Gratis tanpa QRIS, seperti photobooth-io.cc</span>
            </div>
        </div>
        <?php elseif(!$setting->is_payment_enabled): ?>
        <div class="flex justify-center mt-5">
            <div class="w-full sm:w-96 bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-col items-center gap-2">
                <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5"><i class="fa-solid fa-gift"></i> Mode Event / Rental Gratis Aktif</span>
                <?php if($setting->event_voucher_code): ?>
                <input type="text" name="voucher_code" placeholder="Masukkan kode voucher (opsional)" value="<?php echo e($setting->event_voucher_code); ?>" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 text-center uppercase font-mono focus:border-brand-500">
                <?php else: ?>
                <input type="text" name="voucher_code" placeholder="Kode voucher (opsional)" class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 text-center uppercase font-mono focus:border-brand-500">
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex justify-center mt-5">
            <button type="submit" class="w-full sm:w-auto px-10 py-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-base shadow-lg shadow-brand-500/25 flex items-center justify-center gap-3 transition-all active:scale-95">
                <span><?php if($isManual): ?> Mulai Foto Gratis <?php elseif($setting->is_payment_enabled): ?> Lanjut <?php else: ?> Mulai Foto Gratis <?php endif; ?></span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    (function () {
        const track = document.getElementById('templateTrack');
        const cards = Array.from(track.querySelectorAll('label'));

        function cardStep() {
            return cards[0] ? cards[0].offsetWidth + 16 : 296;
        }

        function centerCard(el) {
            const left = el.offsetLeft - (track.clientWidth - el.offsetWidth) / 2;
            track.scrollTo({ left, behavior: 'smooth' });
        }

        function updateNav() {
            const atStart = track.scrollLeft < 10;
            const atEnd = (track.scrollLeft + track.clientWidth) >= (track.scrollWidth - 10);
            document.getElementById('btnPrev').style.opacity = atStart ? '0.35' : '1';
            document.getElementById('btnNext').style.opacity = atEnd ? '0.35' : '1';
        }

        document.getElementById('btnPrev').addEventListener('click', () => track.scrollBy({ left: -cardStep(), behavior: 'smooth' }));
        document.getElementById('btnNext').addEventListener('click', () => track.scrollBy({ left: cardStep(), behavior: 'smooth' }));
        track.addEventListener('scroll', updateNav);
        window.addEventListener('resize', updateNav);

        // Pusatkan template "Paling Populer" saat load (favorit di tengah)
        const checked = track.querySelector('input[type=radio]:checked');
        if (checked) {
            const popularCard = checked.closest('label');
            setTimeout(() => centerCard(popularCard), 150);
        }
        updateNav();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views/photobooth/index.blade.php ENDPATH**/ ?>