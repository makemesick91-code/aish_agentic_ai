@extends('layouts.app')

@section('title', 'Workspace settings — Aish Agentic AI')
@section('heading', 'Workspace settings')
@section('subheading', 'Manage your workspace profile.')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('tenant.update') }}"
              class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Workspace name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $tenant->name) }}" required
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Timezone</label>
                        <input id="timezone" name="timezone" type="text" value="{{ old('timezone', $tenant->timezone) }}" required
                               placeholder="Asia/Makassar"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('timezone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="locale" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Locale</label>
                        <input id="locale" name="locale" type="text" value="{{ old('locale', $tenant->locale) }}" required
                               placeholder="en"
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('locale')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    Save changes
                </button>
            </div>
        </form>
    </div>
@endsection
