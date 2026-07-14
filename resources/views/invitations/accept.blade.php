@extends('layouts.guest')

@section('title', 'Accept invitation — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">You've been invited</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            This invitation is for <span class="font-medium text-slate-700 dark:text-slate-200">{{ $email }}</span>
            as <span class="font-medium text-slate-700 dark:text-slate-200">{{ $invitation->role }}</span>.
        </p>

        <form method="POST" action="{{ route('invitations.accept.store', ['token' => $token]) }}" class="mt-6 space-y-4">
            @csrf

            @if ($claimable)
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Your name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Create a password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">At least 12 characters, with upper &amp; lower case and a number.</p>
                    @error('password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                </div>
            @else
                <p class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    You already have an account for this email. Sign in if needed, then accept to join this workspace.
                </p>
            @endif

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                Accept invitation
            </button>
        </form>
    </div>
@endsection
