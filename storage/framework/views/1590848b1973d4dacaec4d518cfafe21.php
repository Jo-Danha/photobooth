

<?php $__env->startSection('title', 'Sesi Berakhir - Photobooth'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <div class="w-16 h-16 rounded-full bg-rose-950 border border-rose-800 text-rose-400 mx-auto flex items-center justify-center mb-4">
            <i class="fa-solid fa-hourglass-end text-2xl"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-white">Sesi Telah Berakhir</h2>
        <p class="text-xs text-slate-400 mt-2 mb-6">
            Batas waktu sesi foto Anda (<?php echo e($session->duration_minutes); ?> menit) telah selesai.
        </p>

        <a href="<?php echo e(route('photobooth.index')); ?>" class="w-full py-3 px-6 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs block">
            Mulai Sesi Baru
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views\photobooth\expired.blade.php ENDPATH**/ ?>