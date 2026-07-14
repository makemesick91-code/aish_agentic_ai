@extends('layouts.app')

@section('title', 'Branches — Aish Agentic AI')
@section('heading', 'Branches')
@section('subheading', 'Manage the branches in this workspace.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($branches->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No branches yet. Create the first one using the form.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Name</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Code</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $branch->name }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $branch->code }}</td>
                                        <td class="px-4 py-3">
                                            @if ($branch->isActive())
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($branch->isActive())
                                                <form method="POST" action="{{ route('branches.deactivate', $branch) }}" class="flex justify-end"
                                                      onsubmit="return confirm('Deactivate {{ $branch->name }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">Deactivate</button>
                                                </form>
                                            @else
                                                <span class="flex justify-end text-xs text-slate-400">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $branches->links() }}
            </div>
        </section>

        <section class="lg:col-span-1">
            <form method="POST" action="{{ route('branches.store') }}"
                  class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Add a branch</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="branch-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                        <input id="branch-name" name="name" type="text" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="branch-code" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Code</label>
                        <input id="branch-code" name="code" type="text" value="{{ old('code') }}" maxlength="32" required
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('code')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="branch-timezone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Timezone</label>
                            <input id="branch-timezone" name="timezone" type="text" value="{{ old('timezone') }}" placeholder="Asia/Makassar"
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @error('timezone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="branch-locale" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Locale</label>
                            <input id="branch-locale" name="locale" type="text" value="{{ old('locale') }}" placeholder="en"
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @error('locale')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        Create branch
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
