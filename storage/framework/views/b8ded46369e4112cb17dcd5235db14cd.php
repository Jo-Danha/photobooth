

<?php $__env->startSection('title', 'Hasil Foto Anda - Photobooth'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-950 border border-emerald-700 text-emerald-400 mb-3">
                <i class="fa-solid fa-check text-xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Foto Anda Siap!</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Unduh berkas resolusi tinggi, cetak langsung, atau simpan ke Google Drive / HP Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <!-- Tampilan Foto Asli di Kiri -->
            <div class="flex justify-center bg-slate-950 p-4 rounded-2xl border border-slate-800 shadow-inner">
                <img id="mainResultImage" src="<?php echo e(asset($session->result_image_path)); ?>" alt="Hasil Foto Photobooth" class="max-h-[500px] rounded-lg shadow-2xl object-contain">
            </div>

            <!-- Panel Aksi & QR Code di Kanan -->
            <div class="flex flex-col items-center sm:items-start space-y-6">
                <!-- QR Code Box -->
                <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800/80 flex flex-col items-center text-center w-full shadow-lg">
                    <span class="text-xs font-bold text-slate-300 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-mobile-screen-button text-brand-400"></i> Scan QR untuk Download di HP
                    </span>
                    <div class="p-3 bg-white rounded-xl shadow-md">
                        <img src="<?php echo e($qrCodeUrl); ?>" alt="Download QR Code" class="w-44 h-44">
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2 flex items-center gap-1">
                        <i class="fa-brands fa-google-drive text-emerald-400"></i> Foto otomatis tersimpan di Google Drive & siap diunduh.
                    </p>
                </div>

                <!-- Tombol Action -->
                <div class="w-full space-y-3">
                    <!-- Tombol Download PNG -->
                    <a href="<?php echo e(asset($session->result_image_path)); ?>" download="Photobooth_<?php echo e($session->order_id); ?>.png" class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg flex items-center justify-center gap-2 transition-all active:scale-95">
                        <i class="fa-solid fa-download"></i>
                        <span>Download PNG Resolusi Penuh</span>
                    </a>

                    <!-- Tombol Cetak Khusus Foto -->
                    <button onclick="printOnlyPhoto()" class="w-full py-3.5 px-6 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-lg flex items-center justify-center gap-2 transition-all active:scale-95">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Foto (Print Booth)</span>
                    </button>

                    <!-- Tombol Google Drive jika tersedia -->
                    <?php if(isset($session->metadata['gdrive_link'])): ?>
                        <a href="<?php echo e($session->metadata['gdrive_link']); ?>" target="_blank" class="w-full py-3 px-6 rounded-xl bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-700/60 text-emerald-300 font-semibold text-xs flex items-center justify-center gap-2 transition-all">
                            <i class="fa-brands fa-google-drive text-sm"></i>
                            <span>Buka Foto di Google Drive</span>
                        </a>
                    <?php endif; ?>

                    <?php if($setting->enable_email): ?>
                        <div class="w-full bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                            <label class="text-xs font-bold text-slate-300 flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-envelope text-brand-400"></i> Kirim Foto ke Email
                            </label>
                            <div class="flex gap-2">
                                <input type="email" id="emailInput" placeholder="nama@email.com" class="flex-1 bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2.5 focus:border-brand-500">
                                <button id="btnSendEmail" class="px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs whitespace-nowrap">Kirim</button>
                            </div>
                            <p id="emailStatus" class="text-[11px] mt-2 hidden"></p>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo e(route('photobooth.index')); ?>" class="w-full py-2 text-center text-xs text-slate-400 hover:text-white block transition-colors">
                        <i class="fa-solid fa-house"></i> Kembali ke Menu Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    <?php if($setting->enable_email): ?>
        const btnSendEmail = document.getElementById('btnSendEmail');
        const emailInput = document.getElementById('emailInput');
        const emailStatus = document.getElementById('emailStatus');
        const CSRF = "<?php echo e(csrf_token()); ?>";

        function showEmailStatus(msg, ok) {
            emailStatus.textContent = msg;
            emailStatus.className = 'text-[11px] mt-2 ' + (ok ? 'text-emerald-400' : 'text-rose-400');
            emailStatus.classList.remove('hidden');
        }

        if (btnSendEmail) {
            btnSendEmail.addEventListener('click', async () => {
                const email = emailInput.value.trim();
                if (!email) { showEmailStatus('Masukkan alamat email.', false); return; }
                btnSendEmail.disabled = true;
                btnSendEmail.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                try {
                    const res = await fetch("<?php echo e(route('photobooth.email', ['token' => $session->session_token])); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email, _token: CSRF })
                    });
                    const data = await res.json();
                    showEmailStatus(data.message, data.success);
                    if (data.success) emailInput.value = '';
                } catch (e) {
                    showEmailStatus('Gagal menghubungi server.', false);
                } finally {
                    btnSendEmail.disabled = false;
                    btnSendEmail.textContent = 'Kirim';
                }
            });
        }
    <?php endif; ?>

    function printOnlyPhoto() {
        const img = document.getElementById('mainResultImage');
        if (!img || !img.src) {
            alert("Gambar foto belum selesai dimuat.");
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Cetak Foto - <?php echo e($session->order_id); ?></title>
                    <style>
                        @page { size: auto; margin: 0; }
                        body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #ffffff; }
                        img { max-width: 100%; max-height: 100vh; object-fit: contain; display: block; margin: auto; }
                    </style>
                </head>
                <body>
                    <img src="${img.src}" onload="setTimeout(() => { window.print(); window.close(); }, 300);" />
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\photobooth\resources\views\photobooth\result.blade.php ENDPATH**/ ?>