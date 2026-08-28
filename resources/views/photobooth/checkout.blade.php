@extends('layouts.app')

@section('title', 'Pembayaran - Photobooth')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl text-center">
        <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-amber-950/60 border border-amber-800/60 px-3 py-1 rounded-full">
            Langkah 2 dari 3: Pembayaran
        </span>

        <h2 class="text-2xl font-extrabold text-white mt-4">Pilih Metode Pembayaran</h2>
        <p class="text-xs text-slate-400 mt-1">Selesaikan pembayaran untuk memulai sesi foto.</p>

        @if(count($methods) > 1)
        <div class="flex gap-2 mt-5" id="methodTabs">
            @foreach($methods as $m)
            <button type="button" data-method="{{ $m }}"
                class="method-tab flex-1 py-2.5 rounded-xl text-xs font-bold border transition-all
                {{ strtolower($session->payment_method) === $m ? 'bg-brand-600 border-brand-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-300 hover:border-slate-600' }}">
                @if($m == 'qris')<i class="fa-solid fa-qrcode mr-1"></i> QRIS
                @elseif($m == 'cash')<i class="fa-solid fa-money-bill-wave mr-1"></i> Tunai
                @elseif($m == 'transfer')<i class="fa-solid fa-building-columns mr-1"></i> Transfer
                @endif
            </button>
            @endforeach
        </div>
        @endif

        <!-- Panel QRIS -->
        <div id="panel-qris" class="method-panel mt-5 {{ strtolower($session->payment_method) == 'qris' ? '' : 'hidden' }}">
            <div class="my-4 p-4 bg-white rounded-2xl inline-block shadow-lg">
                <img id="qrisImg" src="{{ $qrUrl }}" alt="QRIS Code" class="w-64 h-64 mx-auto rounded-lg">
                <div class="mt-2 flex items-center justify-center gap-1.5 text-[11px] font-bold text-slate-700">
                    <i class="fa-solid fa-qrcode text-brand-600"></i> QRIS STANDAR NASIONAL
                </div>
            </div>
            <p class="text-[11px] text-slate-400">Buka aplikasi BCA, GoPay, OVO, Dana, ShopeePay, atau m-Banking untuk scan.</p>
        </div>

        <!-- Panel Tunai -->
        <div id="panel-cash" class="method-panel mt-5 {{ strtolower($session->payment_method) == 'cash' ? '' : 'hidden' }}">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-950 border border-emerald-700 flex items-center justify-center text-emerald-400 text-2xl mb-3">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <p class="text-sm text-slate-300">Silakan bayar <b class="text-white">Tunai</b> kepada operator booth.</p>
            @if($bankAccount)
            <p class="text-[11px] text-slate-500 mt-2">{{ $bankAccount }}</p>
            @endif
        </div>

        <!-- Panel Transfer -->
        <div id="panel-transfer" class="method-panel mt-5 {{ strtolower($session->payment_method) == 'transfer' ? '' : 'hidden' }}">
            <div class="w-16 h-16 mx-auto rounded-full bg-indigo-950 border border-indigo-700 flex items-center justify-center text-indigo-400 text-2xl mb-3">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <p class="text-sm text-slate-300">Transfer ke rekening berikut:</p>
            <div class="mt-2 p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-200 font-mono">
                {{ $bankAccount ?: 'Hubungi operator booth untuk info rekening.' }}
            </div>
        </div>

        <!-- Info Order -->
        <div class="bg-slate-950/80 rounded-xl p-4 text-left text-xs space-y-2 border border-slate-800/80 mt-5">
            <div class="flex justify-between"><span class="text-slate-400">Order ID</span><span class="font-mono font-semibold text-slate-200">{{ $session->order_id }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Pilihan Paket</span><span class="font-semibold text-slate-200">{{ $session->package_name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Durasi Sesi Foto</span><span class="font-semibold text-emerald-400">{{ $session->duration_minutes }} Menit</span></div>
            <div class="pt-2 border-t border-slate-800 flex justify-between text-sm">
                <span class="font-bold text-slate-300">Total Tagihan</span>
                <span class="font-extrabold text-brand-400">Rp {{ number_format($session->amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div id="waitingBox" class="flex items-center justify-center gap-2 text-xs text-slate-400 my-5 {{ strtolower($session->payment_method) == 'qris' ? '' : 'hidden' }}">
            <div class="w-3 h-3 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            <span>Menunggu verifikasi pembayaran otomatis...</span>
        </div>

        <!-- Tombol konfirmasi manual (Tunai/Transfer) -->
        <form action="{{ route('photobooth.simulate.pay', ['token' => $session->session_token]) }}" method="POST" class="cash-confirm {{ strtolower($session->payment_method) == 'qris' ? 'hidden' : '' }}">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ strtolower($session->payment_method) == 'cash' ? 'Saya Sudah Bayar (Tunai)' : 'Saya Sudah Transfer' }}</span>
            </button>
        </form>

        <!-- Simulasi bayar untuk QRIS (demo/testing) -->
        @if(strtolower($session->payment_method) == 'qris')
        <form action="{{ route('photobooth.simulate.pay', ['token' => $session->session_token]) }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs shadow flex items-center justify-center gap-2">
                <i class="fa-solid fa-bolt"></i> Simulasi Bayar Instan (Testing/Demo)
            </button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    const methodTabs = document.querySelectorAll('.method-tab');
    const panels = {
        qris: document.getElementById('panel-qris'),
        cash: document.getElementById('panel-cash'),
        transfer: document.getElementById('panel-transfer'),
    };
    const waitingBox = document.getElementById('waitingBox');
    const cashConfirm = document.querySelector('.cash-confirm');
    const qrisImg = document.getElementById('qrisImg');

    function showPanel(method) {
        Object.keys(panels).forEach(k => panels[k].classList.toggle('hidden', k !== method));
        waitingBox.classList.toggle('hidden', method !== 'qris');
        cashConfirm.classList.toggle('hidden', method === 'qris');
        methodTabs.forEach(b => {
            const active = b.dataset.method === method;
            b.classList.toggle('bg-brand-600', active);
            b.classList.toggle('border-brand-500', active);
            b.classList.toggle('text-white', active);
            b.classList.toggle('bg-slate-950', !active);
            b.classList.toggle('border-slate-800', !active);
            b.classList.toggle('text-slate-300', !active);
        });
    }

    methodTabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const method = btn.dataset.method;
            showPanel(method);
            fetch("{{ route('photobooth.checkout.method', ['token' => $session->session_token]) }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ method })
            })
            .then(r => r.json())
            .then(d => { if (d.qr_url && qrisImg) qrisImg.src = d.qr_url; })
            .catch(() => {});
        });
    });

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
