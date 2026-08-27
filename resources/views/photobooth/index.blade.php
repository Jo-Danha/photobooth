@extends('layouts.app')

@section('title', 'Pilih Layout & Mulai Foto - Photobooth')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="text-center max-w-2xl mx-auto mb-10">
        <span class="text-xs font-bold uppercase tracking-widest text-brand-400 bg-brand-950/80 border border-brand-800/60 px-3 py-1 rounded-full">
            Langkah 1 dari 3: Pilih Template
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">
            Pilih Format & Layout Foto Favoritmu
        </h1>
        <p class="text-slate-400 mt-2 text-sm">
            Bayar instan dengan QRIS, nikmati sesi foto bebas bergaya dengan hitungan mundur dan puluhan pilihan stiker & warna bingkai!
        </p>
    </div>

    <form action="{{ route('photobooth.session.create') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            @foreach($packages as $pkg)
            <label class="relative group cursor-pointer">
                <input type="radio" name="layout_type" value="{{ $pkg['id'] }}" class="peer sr-only" {{ $pkg['popular'] ? 'checked' : '' }}>
                
                <div class="h-full bg-slate-900/90 border-2 border-slate-800 rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 peer-checked:border-brand-500 peer-checked:bg-slate-900 peer-checked:shadow-xl peer-checked:shadow-brand-500/10 hover:border-slate-700">
                    @if($pkg['popular'])
                    <div class="absolute -top-3 right-6 bg-gradient-to-r from-brand-600 to-indigo-600 text-white text-[11px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider shadow">
                        Paling Populer
                    </div>
                    @endif

                    <div>
                        <div class="w-full h-36 bg-slate-950 rounded-xl mb-4 p-3 flex items-center justify-center border border-slate-800/80">
                            @if($pkg['id'] == 'strip_4')
                                <div class="flex flex-col gap-1.5 w-16 p-1.5 bg-slate-800 rounded shadow-inner">
                                    <div class="h-5 bg-slate-700 rounded-sm"></div>
                                    <div class="h-5 bg-slate-700 rounded-sm"></div>
                                    <div class="h-5 bg-slate-700 rounded-sm"></div>
                                    <div class="h-5 bg-slate-700 rounded-sm"></div>
                                </div>
                            @elseif($pkg['id'] == 'strip_3')
                                <div class="flex flex-col gap-2 w-16 p-1.5 bg-slate-800 rounded shadow-inner">
                                    <div class="h-7 bg-slate-700 rounded-sm"></div>
                                    <div class="h-7 bg-slate-700 rounded-sm"></div>
                                    <div class="h-7 bg-slate-700 rounded-sm"></div>
                                </div>
                            @elseif($pkg['id'] == 'strip_2')
                                <div class="flex flex-col gap-2.5 w-16 p-2 bg-slate-800 rounded shadow-inner">
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                </div>
                            @elseif($pkg['id'] == 'grid_4')
                                <div class="grid grid-cols-2 gap-1.5 w-28 p-2 bg-slate-800 rounded shadow-inner">
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                    <div class="h-10 bg-slate-700 rounded-sm"></div>
                                </div>
                            @else
                                <div class="w-20 p-2 bg-slate-800 rounded shadow-inner flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-700 rounded-sm mb-2"></div>
                                    <div class="w-10 h-1.5 bg-slate-600 rounded"></div>
                                </div>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-white group-hover:text-brand-300 transition-colors">
                            {{ $pkg['name'] }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ $pkg['description'] }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block">Batas Sesi: {{ $pkg['duration'] }} Menit</span>
                            <span class="text-xl font-extrabold text-white">Rp {{ number_format($pkg['price'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 border-slate-600 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center">
                            <i class="fa-solid fa-check text-white text-xs opacity-0 group-has-[:checked]:opacity-100"></i>
                        </div>
                    </div>
                </div>
            </label>
            @endforeach
        </div>

        <div class="flex justify-center">
            <button type="submit" class="w-full sm:w-auto px-10 py-4 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-base shadow-lg shadow-brand-500/25 flex items-center justify-center gap-3 transition-all active:scale-95">
                <span>Lanjut ke Pembayaran QRIS</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection