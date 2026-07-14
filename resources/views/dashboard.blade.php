@extends('layouts.app')

@section('title', 'Dashboard — Aish Agentic AI')
@section('heading', 'Workspace overview')
@section('subheading', 'Foundation status for this workspace. No business data exists yet.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Current context</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Workspace</dt>
                    <dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $tenant->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Branch</dt>
                    <dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $branch?->name ?? 'All branches' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Signed in as</dt>
                    <dd class="text-right font-medium text-slate-800 dark:text-slate-100">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="text-right text-slate-600 dark:text-slate-300">{{ $user->email }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-slate-500 dark:text-slate-400">Your roles</dt>
                    <dd class="flex flex-wrap gap-1">
                        @forelse ($roles as $role)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ $role }}</span>
                        @empty
                            <span class="text-sm text-slate-500 dark:text-slate-400">No roles assigned</span>
                        @endforelse
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Foundation capabilities</h2>
            <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($foundationStatus as $label => $available)
                    <li class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-sm dark:border-slate-800">
                        <span aria-hidden="true" class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">&check;</span>
                        <span class="text-slate-700 dark:text-slate-200">{{ $label }}</span>
                    </li>
                @endforeach
            </ul>

            <h2 class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Not implemented in this step</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">These modules are planned and are intentionally not built or measured yet.</p>
            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($notImplemented as $label)
                    <li class="flex items-center gap-2 rounded-lg border border-dashed border-slate-200 px-3 py-2 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        <span aria-hidden="true" class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">&ndash;</span>
                        <span>{{ $label }}</span>
                        <span class="ml-auto text-xs font-medium uppercase tracking-wide text-slate-400">Not implemented</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection
