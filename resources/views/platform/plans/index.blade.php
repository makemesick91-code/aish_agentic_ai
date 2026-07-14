@extends('layouts.platform')

@section('title', 'Plans — Aish Agentic AI')
@section('heading', 'Plans')
@section('subheading', 'Commercial plan catalog.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($plans->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No plans defined yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Code</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Version</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Name</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Features</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($plans as $plan)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $plan->code }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">v{{ $plan->version }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $plan->name }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $plan->status->label() }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $plan->features_count }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('platform.plans.show', $plan) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $plans->links() }}
            </div>
        </section>

        @if ($canManage)
            <section class="lg:col-span-1">
                <form method="POST" action="{{ route('platform.plans.store') }}"
                      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    @csrf
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Create plan</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="plan-code" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Code</label>
                            <input id="plan-code" name="code" type="text" value="{{ old('code') }}" required
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @error('code')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="plan-version" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Version</label>
                            <input id="plan-version" name="version" type="number" min="1" value="{{ old('version', 1) }}" required
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @error('version')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="plan-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                            <input id="plan-name" name="name" type="text" value="{{ old('name') }}" required
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="plan-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                            <textarea id="plan-description" name="description" rows="3"
                                      class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">{{ old('description') }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Create plan
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
@endsection
