<?php $__env->startSection('title', 'Laporan Penjualan QRIS - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-brand-500"></i>
                <span>Laporan Penjualan QRIS</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Rekap transaksi harian (Mode Pembayaran QRIS) & ekspor ke CSV.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('admin.settings')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 flex items-center gap-2 border border-slate-700">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
        <div>
            <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Pilih Tanggal</label>
            <input type="date" name="date" value="<?php echo e($date); ?>" class="bg-slate-950 border border-slate-700 text-slate-200 text-sm rounded-xl px-3 py-2.5">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg">
            <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
        </button>
        <a href="<?php echo e(route('admin.reports.export', ['date' => $date])); ?>" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg flex items-center gap-2">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </a>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <span class="text-xs text-slate-400">Total Transaksi</span>
            <div class="text-2xl font-extrabold text-white mt-1"><?php echo e($count); ?></div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 col-span-2 sm:col-span-2">
            <span class="text-xs text-slate-400">Total Pendapatan (<?php echo e($date); ?>)</span>
            <div class="text-2xl font-extrabold text-brand-400 mt-1">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></div>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-8">
        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Grafik 14 Hari Terakhir</h3>
        <div class="space-y-2">
            <?php $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $max = $daily->max('total') ?: 1; ?>
            <div class="flex items-center gap-3 text-xs">
                <span class="w-20 text-slate-400"><?php echo e(\Carbon\Carbon::parse($d->tgl)->format('d/m')); ?></span>
                <div class="flex-1 bg-slate-950 rounded-full h-4 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-600 to-indigo-600" style="width: <?php echo e(($d->total / $max) * 100); ?>%"></div>
                </div>
                <span class="w-24 text-right text-slate-300 font-mono">Rp <?php echo e(number_format($d->total, 0, ',', '.')); ?></span>
                <span class="w-10 text-right text-slate-500"><?php echo e($d->jml); ?>x</span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($daily->isEmpty()): ?>
            <p class="text-xs text-slate-500">Belum ada transaksi.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Detail Transaksi (<?php echo e($date); ?>)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-slate-300 border-collapse">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800">
                        <th class="text-left py-2 pr-2">Order ID</th>
                        <th class="text-left py-2 px-2">Waktu</th>
                        <th class="text-left py-2 px-2">Paket</th>
                        <th class="text-left py-2 px-2">Layout</th>
                        <th class="text-right py-2 px-2">Nominal</th>
                        <th class="text-left py-2 px-2">Voucher</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b border-slate-800/60">
                        <td class="py-2 pr-2 font-mono"><?php echo e($s->order_id); ?></td>
                        <td class="py-2 px-2"><?php echo e($s->created_at->format('H:i:s')); ?></td>
                        <td class="py-2 px-2"><?php echo e($s->package_name); ?></td>
                        <td class="py-2 px-2 font-mono text-brand-300"><?php echo e($s->layout_type); ?></td>
                        <td class="py-2 px-2 text-right font-bold text-white">Rp <?php echo e(number_format($s->amount, 0, ',', '.')); ?></td>
                        <td class="py-2 px-2 text-slate-400"><?php echo e($s->voucher_code ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">Tidak ada transaksi QRIS pada tanggal ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views\admin\reports.blade.php ENDPATH**/ ?>