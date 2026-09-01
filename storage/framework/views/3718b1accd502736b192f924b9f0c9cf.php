<?php $__env->startSection('title', 'Gallery Hasil - Photobooth'); ?>

<?php $__env->startSection('content'); ?>
<div class="relative">
    <div class="flex items-center justify-between px-4 py-4 border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-16 z-40">
        <h1 class="text-lg font-extrabold text-white flex items-center gap-2">
            <i class="fa-solid fa-images text-brand-400"></i>
            <span data-i18n="gallery.title">Gallery Hasil Photobooth</span>
        </h1>
        <a href="<?php echo e(route('photobooth.index')); ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg flex items-center gap-2">
            <i class="fa-solid fa-camera"></i> <span data-i18n="booth.cta">Mulai Foto</span>
        </a>
    </div>

    <?php if($sessions->isEmpty()): ?>
    <div class="text-center py-24 text-slate-500 text-sm" data-i18n="gallery.empty">Belum ada foto yang tersimpan.</div>
    <?php else: ?>
    <!-- Slideshow Besar -->
    <div class="relative h-[60vh] bg-slate-950 flex items-center justify-center overflow-hidden">
        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <img src="<?php echo e(asset($s->result_image_path)); ?>" data-slide="<?php echo e($i); ?>" class="gallery-slide absolute max-h-full max-w-full rounded-xl shadow-2xl object-contain transition-opacity duration-700 <?php echo e($i === 0 ? 'opacity-100' : 'opacity-0'); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button id="galPrev" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-800/90 border border-slate-700 text-white"><i class="fa-solid fa-chevron-left"></i></button>
        <button id="galNext" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-800/90 border border-slate-700 text-white"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <!-- Strip Thumbnail -->
    <div class="flex gap-3 overflow-x-auto px-4 py-4 bg-slate-900">
        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <img src="<?php echo e(asset($s->result_image_path)); ?>" data-thumb="<?php echo e($i); ?>" class="gallery-thumb h-20 rounded-lg object-contain bg-white border-2 border-transparent cursor-pointer shrink-0">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    (function () {
        const slides = document.querySelectorAll('.gallery-slide');
        const thumbs = document.querySelectorAll('.gallery-thumb');
        if (!slides.length) return;
        let idx = 0;
        let timer = null;

        function show(n) {
            idx = (n + slides.length) % slides.length;
            slides.forEach((s, i) => s.classList.toggle('opacity-100', i === idx) || s.classList.toggle('opacity-0', i !== idx));
            thumbs.forEach((t, i) => t.classList.toggle('border-brand-500', i === idx));
        }
        function next() { show(idx + 1); }
        function prev() { show(idx - 1); }
        function start() { stop(); timer = setInterval(next, 3500); }
        function stop() { if (timer) clearInterval(timer); }

        document.getElementById('galNext').addEventListener('click', () => { next(); start(); });
        document.getElementById('galPrev').addEventListener('click', () => { prev(); start(); });
        thumbs.forEach(t => t.addEventListener('click', () => { show(parseInt(t.dataset.thumb)); start(); }));

        start();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views\photobooth\gallery.blade.php ENDPATH**/ ?>