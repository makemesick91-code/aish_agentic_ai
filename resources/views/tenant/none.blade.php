@extends('layouts.guest')

@section('title', 'No workspace access — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">No workspace access yet</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Your account is active, but it is not yet a member of any workspace.
            Ask a workspace owner or administrator to invite you.
        </p>

        <div class="mt-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Sign out
                </button>
            </form>
        </div>
    </div>
@endsection
