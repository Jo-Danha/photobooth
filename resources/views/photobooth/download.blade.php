<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengunduh Foto Photobooth...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between p-4 selection:bg-pink-500">
    <div class="max-w-md mx-auto w-full text-center my-auto py-6">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-500 mx-auto flex items-center justify-center shadow-lg shadow-emerald-500/20 mb-4 animate-bounce">
            <i class="fa-solid fa-cloud-arrow-down text-white text-2xl"></i>
        </div>

        <h1 class="text-xl font-extrabold text-white">Foto Anda Sedang Diunduh!</h1>
        <p class="text-xs text-slate-400 mt-1 mb-6">Order ID: {{ $session->order_id }}</p>

        <div class="bg-slate-900 p-3 rounded-2xl border border-slate-800 shadow-2xl mb-6 inline-block">
            <img id="mobilePhoto" src="{{ asset($session->result_image_path) }}" alt="Photobooth" class="max-h-[55vh] rounded-lg mx-auto shadow-md">
        </div>

        <div class="space-y-3">
            <a id="btnManualDownload" href="{{ asset($session->result_image_path) }}" download="Photobooth_{{ $session->order_id }}.png" class="w-full py-4 rounded-xl bg-gradient-to-r from-pink-600 to-indigo-600 text-white font-bold text-sm shadow-xl flex items-center justify-center gap-2 active:scale-95 transition-all">
                <i class="fa-solid fa-download"></i>
                <span>Simpan Ulang ke Galeri HP</span>
            </a>

            @if(isset($session->metadata['gdrive_link']))
                <a href="{{ $session->metadata['gdrive_link'] }}" target="_blank" class="w-full py-3.5 rounded-xl bg-emerald-950 border border-emerald-700 text-emerald-300 font-semibold text-xs flex items-center justify-center gap-2">
                    <i class="fa-brands fa-google-drive"></i>
                    <span>Buka File Asli di Google Drive</span>
                </a>
            @endif
        </div>
    </div>

    <footer class="text-center text-[11px] text-slate-600 pb-2">
        Photobooth Studio System &copy; {{ date('Y') }}
    </footer>

    <!-- SKRIP OTOMATIS DOWNLOAD KE HP SAAT DIBUKA -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const photoUrl = "{{ asset($session->result_image_path) }}";
            const filename = "Photobooth_{{ $session->order_id }}.png";

            const autoLink = document.createElement('a');
            autoLink.href = photoUrl;
            autoLink.download = filename;
            document.body.appendChild(autoLink);
            autoLink.click();
            document.body.removeChild(autoLink);
        });
    </script>
</body>
</html>