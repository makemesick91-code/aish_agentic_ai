@extends('layouts.guest')

@section('title', 'Reset password — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">Forgot your password?</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Enter your email and we will send a password reset link.
        </p>

        <form method="POST" action="{{ url('/forgot-password') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                Email password reset link
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ url('/login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Back to sign in</a>
        </p>
    </div>
@endsection
