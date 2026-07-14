@extends('layouts.guest')

@section('title', 'Invitation unavailable — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">This invitation is no longer valid</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            The invitation link may have expired, already been used, or been revoked.
            Please ask a workspace owner or administrator to send you a new invitation.
        </p>

        <a href="{{ url('/login') }}"
           class="mt-6 inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Go to sign in
        </a>
    </div>
@endsection
