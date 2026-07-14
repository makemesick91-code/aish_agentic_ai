@extends('layouts.guest')

@section('title', 'Choose a workspace — Aish Agentic AI')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">Choose a workspace</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Select which workspace you want to work in.</p>

        <form method="POST" action="{{ route('tenant.select.store') }}" class="mt-6 space-y-3">
            @csrf
            <ul class="space-y-2">
                @foreach ($memberships as $membership)
                    <li>
                        <button type="submit" name="tenant" value="{{ $membership->tenant->ulid }}"
                                class="flex w-full items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm hover:border-indigo-400 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:hover:border-indigo-500 dark:hover:bg-indigo-950">
                            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $membership->tenant->name }}</span>
                            <span aria-hidden="true" class="text-slate-400">&rarr;</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </form>
    </div>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400">Sign out</button>
        </form>
    </div>
@endsection
