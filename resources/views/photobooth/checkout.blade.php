@extends('layouts.app')

@section('title', 'Pembayaran QRIS - Photobooth')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl text-center">
        <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-amber-950/60 border border-amber-800/60 px-3 py-1 rounded-full">
            Langkah 2 dari 3: Pembayaran
        </span>
        
        <h2 class="text-2xl font-extrabold text-white mt-4">Scan QRIS untuk Memulai</h2>
        <p class="text-xs text-slate-400 mt-1">Buka aplikasi BCA, GoPay, OVO, Dana, ShopeePay, atau m-Banking</p>

        <div class="my-6 p-4 bg-white rounded-2xl inline-block shadow-lg">
            <img src="{{ $session->payment_qr_url }}" alt="QRIS Code" class="w-64 h-64 mx-auto rounded-lg">
            <div class="mt-2 flex items-center justify-center gap-1.5 text-[11px] font-bold text-slate-700">
                <i class="fa-solid fa-qrcode text-brand-600"></i> QRIS STANDAR NASIONAL
            </div>
        </div>

        <div class="bg-slate-950/80 rounded-xl p-4 text-left text-xs space-y-2 border border-slate-800/80 mb-6">
            <div class="flex justify-between">
                <span class="text-slate-400">Order ID</span>
                <span class="font-mono font-semibold text-slate-200">{{ $session->order_id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Pilihan Paket</span>
                <span class="font-semibold text-slate-200">{{ $session->package_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Durasi Sesi Foto</span>
                <span class="font-semibold text-emerald-400">{{ $session->duration_minutes }} Menit</span>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-between text-sm">
                <span class="font-bold text-slate-300">Total Tagihan</span>
                <span class="font-extrabold text-brand-400">Rp {{ number_format($session->amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="flex items-center justify-center gap-2 text-xs text-slate-400 mb-6">
            <div class="w-3 h-3 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            <span>Menunggu verifikasi pembayaran otomatis...</span>
        </div>

        <form action="{{ route('photobooth.simulate.pay', ['token' => $session->session_token]) }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-bolt"></i>
                <span>Simulasi Bayar Instan (Testing/Demo)</span>
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const statusUrl = "{{ route('photobooth.checkout.status', ['token' => $session->session_token]) }}";
    const pollInterval = setInterval(() => {
        fetch(statusUrl)
            .then(res => res.json())
            .then(data => {
                if (data.is_paid) {
                    clearInterval(pollInterval);
                    window.location.href = data.studio_url;
                }
            })
            .catch(err => console.error("Polling error:", err));
    }, 3000);
</script>
@endsection