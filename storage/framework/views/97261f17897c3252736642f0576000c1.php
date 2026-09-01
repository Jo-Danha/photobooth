<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#0f172a;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:520px;margin:0 auto;background:#1e293b;border-radius:16px;overflow:hidden;margin-top:24px;">
        <div style="background:linear-gradient(135deg,#c2337d,#6366f1);padding:24px 28px;">
            <h1 style="margin:0;color:#ffffff;font-size:20px;">Terima kasih telah berfoto! 📸</h1>
        </div>
        <div style="padding:28px;color:#e2e8f0;">
            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">
                Halo! Berikut adalah foto photobooth Anda sebagai lampiran di email ini.
            </p>
            <p style="margin:0 0 16px;font-size:13px;color:#94a3b8;">
                Order ID: <strong style="color:#f8fafc;"><?php echo e($orderId); ?></strong><br>
                Foto juga tersimpan di Google Drive dan dapat diunduh melalui QR di mesin photobooth.
            </p>
            <p style="margin:0;font-size:12px;color:#64748b;">
                Simpan foto ini baik-baik ya. File akan otomatis dihapus dari server kami dalam 3 hari.
            </p>
        </div>
        <div style="padding:0 28px 24px;color:#64748b;font-size:12px;">
            &copy; <?php echo e(date('Y')); ?> <?php echo e($appName); ?>

        </div>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\photobooth\resources\views\emails\photo.blade.php ENDPATH**/ ?>