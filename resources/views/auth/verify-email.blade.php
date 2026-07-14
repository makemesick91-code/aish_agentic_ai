@extends('layouts.guest')

@section('title', 'Verify your email — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">Verify your email</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Thanks for signing in. Please confirm your email address by clicking the link we just sent you.
            If you did not receive the email, we can send another.
        </p>

        @if (session('status') === 'verification-link-sent')
            <div role="status" class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="mt-6 flex items-center justify-between gap-3">
            <form method="POST" action="{{ url('/email/verification-notification') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    Resend verification email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-slate-600 hover:text-slate-800 dark:text-slate-300">Sign out</button>
            </form>
        </div>
    </div>
@endsection
