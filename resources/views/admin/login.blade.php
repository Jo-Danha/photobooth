@extends('layouts.app')

@section('title', 'Login Admin Photobooth')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 mx-auto flex items-center justify-center shadow-lg shadow-brand-500/20 mb-3">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white">Login Admin Booth</h1>
            <p class="text-xs text-slate-400 mt-1">Masukkan username & password atau PIN untuk masuk ke panel admin.</p>
        </div>

        @if(session('error'))
        <div class="mb-5 p-3.5 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Username</label>
                <input type="text" name="username" value="admin" required class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-1.5">Password / PIN</label>
                <input type="password" name="password" placeholder="Password: admin123 atau PIN: 1234" required class="w-full bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-sm shadow-xl flex items-center justify-center gap-2 active:scale-95 transition-all mt-6">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Masuk ke Panel Admin</span>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-800 text-center">
            <a href="{{ route('photobooth.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Layar Photobooth
            </a>
        </div>
    </div>
</div>
@endsection