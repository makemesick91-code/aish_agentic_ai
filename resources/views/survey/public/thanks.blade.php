@extends('layouts.guest')

@section('title', __('Terima kasih') . ' — Aish Agentic AI')

@section('content')
    <div class="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Terima kasih!') }}</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            {{ __('Masukan Anda telah kami terima.') }}
        </p>
    </div>
@endsection
