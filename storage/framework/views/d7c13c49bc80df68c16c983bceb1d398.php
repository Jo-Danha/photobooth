<?php $__env->startSection('title', 'Choose Your Layout - Photobooth'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* ---- Gaya halaman pilih layout, ditiru dari photobooth-io.cc/chooseLayout.html ---- */
    .choose-layout-wrap {
        background: linear-gradient(180deg, rgba(148,163,184,0.06) 0%, transparent 100%);
        overflow-x: hidden;
        width: 100%;
    }
    .layout-heading {
        font-size: 2.6rem;
        line-height: 1.15;
        margin-top: 24px;
        text-align: center;
        letter-spacing: -0.02em;
        color: #fff;
    }
    .layout-heading .pink {
        color: var(--brand-400, #f26a8d);
    }
    .layout-subtext {
        font-size: 1.35rem;
        text-align: center;
        margin-bottom: 28px;
        color: var(--gray-text, #7a7979);
        font-style: italic;
    }

    /* Navigation wrapper (persis photobooth-io) */
    .layout-nav-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        width: 100%;
        max-width: 900px;
        margin: 8px auto 0;
        overflow: hidden;
        padding: 0 8px;
    }

    /* Scroll container */
    .layout-scroll-container {
        width: 100%;
        max-width: 900px;
        overflow-x: auto;
        overflow-y: hidden;
        height: auto;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        min-width: 0;
        flex: 1 1 auto;
    }
    .layout-scroll-container::-webkit-scrollbar { height: 8px; }
    .layout-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .layout-scroll-container::-webkit-scrollbar-thumb { background: var(--brand-500, #f26a8d); border-radius: 4px; }
    .layout-scroll-container::-webkit-scrollbar-thumb:hover { background: var(--brand-600, #d94f79); }

    #layout-settings {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        gap: 36px;
        padding: 18px 10px 6px;
    }

    .layout-contents {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-width: fit-content;
        flex-shrink: 0;
    }

    .layout-holder {
        width: fit-content;
        height: fit-content;
        background: none;
        border: none;
        transition: transform 0.5s ease-in-out;
        cursor: pointer;
        position: relative;
    }
    .layout-holder:hover { transform: scale(1.08); }

    .new-layout-wrapper,
    .new-layout-wrapper-v2 {
        position: relative;
        display: inline-block;
    }

    /* Badges (::after ala photobooth-io) */
    .layout-holder .new-layout-wrapper::after {
        content: 'TRY IT NOW';
        position: absolute;
        top: -10px;
        right: -12px;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        color: #fa1172;
        background: #FFF8A5;
        padding: 3px 7px;
        border-radius: 7px;
        transform: rotate(4deg);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        z-index: 20;
        white-space: nowrap;
    }
    .layout-holder .new-layout-wrapper-v2::after {
        content: 'NEW Layout';
        position: absolute;
        top: -10px;
        right: -12px;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        color: #fff;
        background: #fa1172;
        padding: 3px 7px;
        border-radius: 7px;
        transform: rotate(4deg);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        z-index: 20;
        white-space: nowrap;
    }
    .layout-holder.badge-popular .new-layout-wrapper::after { content: 'POPULER'; color: #fff; background: #f26a8d; }

    .layout-img,
    .special-layout-img {
        max-height: 240px;
        width: auto;
        max-width: 100px;
        height: auto;
        border: 1px solid var(--black, #1e293b);
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.35);
        background: #0f172a;
        display: block;
    }

    .layout-label {
        font-size: 1.05rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 2px;
        color: #fff;
    }
    .layout-description {
        color: var(--lightGray, #6c757d);
        font-size: 0.85rem;
        text-align: center;
        line-height: 1.45;
    }

    /* Navigation button styling (persis photobooth-io) */
    .layout-nav-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 2px solid var(--brand-500, #f26a8d);
        background-color: #fff;
        color: var(--brand-500, #f26a8d);
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    .layout-nav-btn:hover:not(:disabled) {
        background-color: var(--brand-500, #f26a8d);
        color: #fff;
        box-shadow: 0 4px 12px rgba(242, 106, 141, 0.3);
        transform: scale(1.05);
    }
    .layout-nav-btn:active:not(:disabled) { transform: scale(0.95); }
    .layout-nav-btn:disabled {
        border-color: #94a3b8;
        color: #94a3b8;
        background-color: #fff;
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* Selected ring */
    .layout-contents input:checked + .layout-holder .layout-img,
    .layout-contents input:checked + .layout-holder .special-layout-img {
        border: 3px solid #f26a8d;
        box-shadow: 0 0 0 5px rgba(242,106,141,0.25), 0 12px 28px rgba(242,106,141,0.35);
    }
    .check-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f26a8d;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 21;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
        font-size: 0.7rem;
        border: 2px solid rgba(255,255,255,0.35);
    }
    .layout-contents input:checked + .layout-holder .check-badge { display: flex; }

    /* mobile / tablet */
    @media only screen and (max-width: 1024px) {
        .layout-nav-wrapper { gap: 14px; max-width: 100%; }
        .layout-scroll-container { max-width: 100%; }
        #layout-settings { gap: 30px; }
        .layout-img, .special-layout-img { max-width: 88px; max-height: 210px; }
        .layout-heading { font-size: 2.2rem; margin-top: 6px; }
        .layout-subtext { font-size: 1rem; margin-bottom: 22px; }
        .layout-nav-btn { width: 42px; height: 42px; }
    }
    @media only screen and (max-width: 640px) {
        #layout-settings { gap: 24px; }
        .layout-img, .special-layout-img { max-width: 78px; max-height: 185px; border-radius: 10px; }
        .layout-label { font-size: 0.9rem; }
        .layout-description { font-size: 0.75rem; }
        .layout-nav-btn { width: 38px; height: 38px; }
        .layout-holder .new-layout-wrapper::after,
        .layout-holder .new-layout-wrapper-v2::after { font-size: 0.5rem; padding: 2px 5px; top: -9px; right: -8px; }
    }

    /* ========== Ukuran kartu (dari admin) ========== */
    .layout-nav-wrapper[data-size="small"] .layout-img,
    .layout-nav-wrapper[data-size="small"] .special-layout-img { max-height: 190px; max-width: 78px; }
    .layout-nav-wrapper[data-size="large"] .layout-img,
    .layout-nav-wrapper[data-size="large"] .special-layout-img { max-height: 290px; max-width: 124px; }

    /* ========== Mode Grid (dari admin) ========== */
    .layout-nav-wrapper.grid-mode {
        display: block;
        max-width: 100%;
    }
    .layout-nav-wrapper.grid-mode .layout-scroll-container {
        max-width: 100%;
        overflow: visible;
        padding: 0;
    }
    .layout-nav-wrapper.grid-mode #layout-settings {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-start;
        gap: 18px;
        max-width: 1160px;
        margin: 0 auto;
    }
    .layout-nav-wrapper.grid-mode .layout-nav-btn { display: none; }
    .layout-nav-wrapper.grid-mode .layout-contents {
        align-items: center;
        flex: 0 1 auto;
    }
    .layout-nav-wrapper.grid-mode[data-size="small"] .layout-contents { flex-basis: 110px; }
    .layout-nav-wrapper.grid-mode[data-size="medium"] .layout-contents { flex-basis: 135px; }
    .layout-nav-wrapper.grid-mode[data-size="large"] .layout-contents { flex-basis: 170px; }
    @media only screen and (max-width: 640px) {
        .layout-nav-wrapper.grid-mode[data-size="small"] .layout-contents { flex-basis: 84px; }
        .layout-nav-wrapper.grid-mode[data-size="medium"] .layout-contents { flex-basis: 100px; }
        .layout-nav-wrapper.grid-mode[data-size="large"] .layout-contents { flex-basis: 126px; }
    }

    /* ========== Responsif: layar sempit otomatis jadi grid (wrap) ==========
       Mode slideshow default memakai strip horizontal ~2074px sehingga di
       ponsel/tablet potret tampak "melebar ke kanan". Pada layar <=820px kita
       paksa wrap jadi grid rata, apa pun mode yang dipilih admin. */
    @media only screen and (max-width: 820px) {
        .layout-nav-wrapper:not(.grid-mode) { display: block; max-width: 100%; }
        .layout-nav-wrapper:not(.grid-mode) .layout-scroll-container { max-width: 100%; overflow: visible; padding: 0; }
        .layout-nav-wrapper:not(.grid-mode) #layout-settings {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 18px;
            max-width: 640px;
            margin: 0 auto;
        }
        .layout-nav-wrapper:not(.grid-mode) .layout-nav-btn { display: none; }
        .layout-nav-wrapper:not(.grid-mode) .layout-contents { align-items: center; flex: 0 1 auto; }
        .layout-nav-wrapper:not(.grid-mode)[data-size="small"] .layout-contents { flex-basis: 96px; }
        .layout-nav-wrapper:not(.grid-mode)[data-size="medium"] .layout-contents { flex-basis: 120px; }
        .layout-nav-wrapper:not(.grid-mode)[data-size="large"] .layout-contents { flex-basis: 145px; }
    }

    /* ========== Mode Lock (kiosk dikunci admin) ========== */
    .layout-nav-wrapper.is-locked .layout-nav-btn { display: none; }
    .layout-nav-wrapper.is-locked .layout-scroll-container { overflow: hidden; }
    .layout-holder.is-locked {
        opacity: 0.45;
        filter: grayscale(0.75);
        cursor: not-allowed;
        pointer-events: none;
    }
    .layout-holder.is-locked:hover { transform: none; }

    /* Reset memory button */
    .layout-memory-reset {
        display: none;
    }
    .layout-memory-reset.have-memory { display: inline-flex; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isManual = ($setting->booth_mode ?? 'mandiri') === 'manual';
    $newLayouts = ['ar'];
?>

<div class="choose-layout-wrap min-h-screen relative">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header ala photobooth-io.cc (heading lower-case custom) -->
        <div class="text-center">
            <div class="flex justify-center mb-4 gap-3">
                <?php if($isManual): ?>
                <a href="<?php echo e(route('photobooth.gallery')); ?>" class="text-xs font-bold text-slate-300 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 hover:bg-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-images text-brand-400"></i> <span data-i18n="booth.gallery">Gallery Hasil</span>
                </a>
                <?php endif; ?>
                <button type="button" id="layoutMemoryReset" class="layout-memory-reset text-xs font-bold text-slate-300 px-3 py-1.5 rounded-full bg-slate-800 border border-slate-700 hover:bg-slate-700 items-center gap-2">
                    <i class="fa-solid fa-rotate-left text-brand-400"></i> <span data-i18n="booth.resetChoice">Reset Pilihan</span>
                </button>
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-400 bg-brand-950/80 border border-brand-800/60 px-3 py-1.5 rounded-full">
                    <?php if($isManual): ?> Wedding Mode — Gratis <?php elseif($isLocked): ?> Template Dikunci Operator <?php else: ?> Langkah 1 dari 3: Pilih Template <?php endif; ?>
                </span>
                <?php if($isManual): ?>
                <span class="text-[10px] font-bold text-emerald-300 bg-emerald-950/60 border border-emerald-800/60 px-3 py-1.5 rounded-full">
                    <i class="fa-solid fa-heart"></i> Gratis
                </span>
                <?php endif; ?>
                <?php if($isLocked): ?>
                <span class="text-[10px] font-bold text-amber-300 bg-amber-950/60 border border-amber-800/60 px-3 py-1.5 rounded-full">
                    <i class="fa-solid fa-lock"></i> Kiosk Terkunci
                </span>
                <?php endif; ?>
            </div>

            <h1 class="layout-heading">
                choose <span class="pink">your</span> layout
            </h1>
            <p class="layout-subtext">Select from our collection of photo booth layouts</p>
        </div>

        <form action="<?php echo e(route('photobooth.session.create')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php if($isLocked): ?>
            <input type="hidden" name="layout_type" value="<?php echo e($checkedLayout); ?>">
            <?php endif; ?>

            <!-- Layout Slideshow (persis photobooth-io.cc: tombol kiri/kanan + scroll horizontal tersembunyi) -->
            <div class="layout-nav-wrapper <?php echo e($layoutDisplayMode === 'grid' ? 'grid-mode' : ''); ?> <?php echo e($isLocked ? 'is-locked' : ''); ?>" data-size="<?php echo e($layoutDisplaySize); ?>" id="layoutNavWrapper">
                <button type="button" class="layout-nav-btn layout-nav-btn-left" id="layoutNavLeft" aria-label="Previous layouts">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                <div class="layout-scroll-container" id="layoutScrollContainer">
                    <div id="layout-settings">
                        <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isNew = in_array($pkg['id'], $newLayouts);
                            $wrapper = $isNew ? 'new-layout-wrapper-v2' : 'new-layout-wrapper';
                            $shots = (int) $pkg['shots'];
                            $cols = str_starts_with($pkg['id'], 'grid') ? 2 : 1;
                            $rows = str_starts_with($pkg['id'], 'grid') ? (int) ceil($shots / 2) : $shots;
                            $sizeLabel = "Size " . ($cols > 1 ? "{$cols} x {$rows} Strip" : "1 x {$rows} Strip") . " ({$shots} Pose)";
                            $price = $isManual ? 'Gratis' : 'Rp ' . number_format($pkg['price'], 0, ',', '.');
                        ?>
                        <div class="layout-contents" data-layout-id="<?php echo e($pkg['id']); ?>">
                            <input type="radio" name="layout_type" id="layout_<?php echo e($pkg['id']); ?>"
                                   value="<?php echo e($pkg['id']); ?>" class="sr-only layout-radio"
                                   data-default="<?php echo e($index === 0 ? '1' : '0'); ?>"
                                   <?php echo e(($index === 0) ? 'checked' : ''); ?>

                                   <?php echo e($isLocked ? 'disabled' : ''); ?>>
                            <label for="layout_<?php echo e($pkg['id']); ?>" class="layout-holder <?php echo e($pkg['popular'] ? 'badge-popular' : ''); ?> <?php echo e($isLocked ? 'is-locked' : ''); ?>">
                                <div class="<?php echo e($wrapper); ?>">
                                    <img src="<?php echo e(asset('layout-previews/'.$pkg['id'].'.png')); ?>"
                                         class="<?php echo e($isNew ? 'special-layout-img' : 'layout-img'); ?>"
                                         alt="<?php echo e($pkg['name']); ?>" loading="lazy" decoding="async"/>
                                </div>
                                <span class="check-badge"><i class="fa-solid fa-check"></i></span>
                            </label>
                            <h2 class="layout-label"><?php echo e($pkg['name']); ?></h2>
                            <div>
                                <p class="layout-description"><?php echo e($sizeLabel); ?></p>
                                <p class="layout-description" style="font-weight:700;color:<?php echo e($isManual ? '#34d399' : ($setting->is_payment_enabled ? '#fff' : '#34d399')); ?>;"><?php echo e($price); ?> · <?php echo e($pkg['duration']); ?> menit</p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <button type="button" class="layout-nav-btn layout-nav-btn-right" id="layoutNavRight" aria-label="Next layouts">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <?php if($isManual): ?>
            <div class="flex justify-center mt-4">
                <div class="w-full sm:w-96 bg-emerald-950/50 border border-emerald-800 rounded-2xl p-3 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-heart text-emerald-400"></i>
                    <span class="text-xs font-bold text-emerald-300">Mode Wedding (Manual) — Gratis tanpa QRIS, seperti photobooth-io.cc</span>
                </div>
            </div>
            <?php elseif(!$setting->is_payment_enabled): ?>
            <div class="flex justify-center mt-4">
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
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    // Layout Navigation System (persis photobooth-io.cc/layout-navigation.js)
    const layoutScrollContainer = document.getElementById('layoutScrollContainer');
    const layoutNavLeft = document.getElementById('layoutNavLeft');
    const layoutNavRight = document.getElementById('layoutNavRight');
    const layoutNavWrapper = document.getElementById('layoutNavWrapper');
    const gridMode = document.querySelector('.layout-nav-wrapper.grid-mode') !== null;
    const isLocked = <?php echo e($isLocked ? 'true' : 'false'); ?>;
    const autoScroll = <?php echo e($autoScroll ? 'true' : 'false'); ?>;
    const autoScrollInterval = <?php echo e($autoScrollInterval); ?>;

    // Scroll distance per button click
    const SCROLL_DISTANCE = 200;

    // --- Memory pilihan layout (localStorage) ---
    const MEMORY_KEY = 'photobooth_last_layout';
    const layoutRadios = document.querySelectorAll('.layout-radio');

    function rememberLayout() {
        const checked = document.querySelector('.layout-radio:checked');
        if (!checked) return;
        try { localStorage.setItem(MEMORY_KEY, checked.value); } catch (e) {}
        const resetBtn = document.getElementById('layoutMemoryReset');
        if (resetBtn) resetBtn.classList.add('have-memory');
    }

    // --- Auto-scroll slideshow (mode demo booth) ---
    let autoScrollTimer = null;
    let autoScrollPaused = false;

    function startAutoScroll() {
        if (!autoScroll || gridMode || isLocked || autoScrollPaused) return;
        stopAutoScrollLoop();
        autoScrollTimer = setInterval(() => {
            if (autoScrollPaused) return;
            const { scrollLeft, scrollWidth, clientWidth } = layoutScrollContainer;
            const atEnd = scrollLeft + clientWidth >= scrollWidth - 10;
            if (atEnd) {
                layoutScrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                layoutScrollContainer.scrollBy({ left: SCROLL_DISTANCE, behavior: 'smooth' });
            }
            setTimeout(updateNavigationButtonStates, 300);
        }, autoScrollInterval * 1000);
    }
    function stopAutoScrollLoop() {
        if (autoScrollTimer) { clearInterval(autoScrollTimer); autoScrollTimer = null; }
    }
    window.addEventListener('load', () => {
        updateNavigationButtonStates();
        setTimeout(updateNavigationButtonStates, 100);
        setTimeout(updateNavigationButtonStates, 300);
        setTimeout(updateNavigationButtonStates, 600);
        startAutoScroll();
    });

    // Hentikan auto-scroll saat pengguna menyentuh / scroll manual
    layoutScrollContainer.addEventListener('touchstart', () => { autoScrollPaused = true; stopAutoScrollLoop(); }, { once: false });
    layoutScrollContainer.addEventListener('mouseenter', () => stopAutoScrollLoop());
    layoutScrollContainer.addEventListener('mouseleave', startAutoScroll);

    // Restore pilihan terakhir (kecuali mode lock)
    // NB: TANPA auto-scroll — scroll strip ke kartu yang jauh bikin tampilan
    // "langsung menyamping / melebar ke kanan" saat halaman dibuka ulang.
    (function restoreMemory() {
        if (isLocked) return;
        let remembered = null;
        try { remembered = localStorage.getItem(MEMORY_KEY); } catch (e) {}
        if (!remembered) return;

        const radio = Array.from(layoutRadios).find(r => r.value === remembered);
        if (radio) {
            radio.checked = true;
            setTimeout(updateNavigationButtonStates, 350);
        }
        const resetBtn = document.getElementById('layoutMemoryReset');
        if (resetBtn) resetBtn.classList.add('have-memory');
    })();

    // Simpan pilihan saat user memilih
    layoutRadios.forEach(r => r.addEventListener('change', rememberLayout));

    // Tombol Reset pilihan (bersihkan memori & kembali ke layout pertama)
    const memoryResetBtn = document.getElementById('layoutMemoryReset');
    if (memoryResetBtn) {
        memoryResetBtn.addEventListener('click', () => {
            try { localStorage.removeItem(MEMORY_KEY); } catch (e) {}
            memoryResetBtn.classList.remove('have-memory');
            const firstRadio = document.querySelector('.layout-radio[data-default="1"]');
            if (firstRadio) firstRadio.checked = true;
            if (!gridMode) layoutScrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
            rememberLayout();
            setTimeout(updateNavigationButtonStates, 350);
        });
    }

    // Initialize button states
    function updateNavigationButtonStates() {
        if (gridMode || isLocked) { if (layoutNavLeft) layoutNavLeft.disabled = true; if (layoutNavRight) layoutNavRight.disabled = true; return; }
        const { scrollLeft, scrollWidth, clientWidth } = layoutScrollContainer;

        // Disable left button if at the start
        layoutNavLeft.disabled = scrollLeft <= 0;

        // Disable right button if at the end
        layoutNavRight.disabled = scrollLeft + clientWidth >= scrollWidth - 10;
    }

    // Handle left button click
    layoutNavLeft.addEventListener('click', () => {
        autoScrollPaused = true; stopAutoScrollLoop();
        layoutScrollContainer.scrollBy({
            left: -SCROLL_DISTANCE,
            behavior: 'smooth'
        });
        setTimeout(updateNavigationButtonStates, 300);
    });

    // Handle right button click
    layoutNavRight.addEventListener('click', () => {
        autoScrollPaused = true; stopAutoScrollLoop();
        layoutScrollContainer.scrollBy({
            left: SCROLL_DISTANCE,
            behavior: 'smooth'
        });
        setTimeout(updateNavigationButtonStates, 300);
    });

    // Update button states when user manually scrolls
    layoutScrollContainer.addEventListener('scroll', updateNavigationButtonStates);

    // --- Cegah lompatan horizontal saat kartu diklik (browser auto-scroll fokus radio) ---
    let lastScrollLeft = layoutScrollContainer.scrollLeft;
    layoutNavWrapper.addEventListener('pointerdown', () => { lastScrollLeft = layoutScrollContainer.scrollLeft; }, true);
    layoutRadios.forEach(r => r.addEventListener('focus', () => {
        const wasSmooth = layoutScrollContainer.style.scrollBehavior;
        layoutScrollContainer.style.scrollBehavior = 'auto';
        requestAnimationFrame(() => {
            layoutScrollContainer.scrollLeft = lastScrollLeft;
            requestAnimationFrame(() => {
                layoutScrollContainer.style.scrollBehavior = wasSmooth;
                updateNavigationButtonStates();
            });
        });
    }));

    // Update on window resize
    window.addEventListener('resize', updateNavigationButtonStates);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views/photobooth/index.blade.php ENDPATH**/ ?>