@extends('layouts.app')

@section('title', 'Gallery Hasil - Admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-3">
                <i class="fa-solid fa-images text-brand-500"></i>
                <span>Gallery Hasil Photobooth</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Daftar foto yang telah disimpan (otomatis tersimpan ke Google Drive bila dikonfigurasi).</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-200 flex items-center gap-2 border border-slate-700">
            <i class="fa-solid fa-gear"></i> Pengaturan
        </a>
    </div>

    @if($sessions->isEmpty())
    <div class="text-center py-16 text-slate-500 text-sm">Belum ada foto yang disimpan.</div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($sessions as $s)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-2 shadow">
            <img src="{{ asset($s->result_image_path) }}" alt="{{ $s->order_id }}" class="w-full rounded-lg object-contain bg-white">
            <div class="text-[10px] text-slate-400 mt-1.5 truncate">{{ $s->package_name }}</div>
            <div class="text-[10px] text-slate-500">{{ $s->created_at->format('d/m H:i') }}</div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
