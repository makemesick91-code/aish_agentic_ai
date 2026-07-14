@extends('layouts.app')

@section('title', 'Notification preferences — Aish Agentic AI')
@section('heading', 'Notification preferences')
@section('subheading', 'Choose how you are notified in this workspace. Critical security alerts are always delivered.')

@section('content')
    <form method="POST" action="{{ route('notifications.preferences.update') }}" class="mx-auto max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Channels</h2>
            <div class="mt-4 space-y-3">
                <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="in_app_enabled" value="1" @checked($preference->in_app_enabled ?? true)
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    In-app notifications
                </label>
                <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="email_enabled" value="1" @checked($preference->email_enabled ?? true)
                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Email notifications
                </label>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">By category</h2>
            <p class="mt-1 text-xs text-slate-400">Security alerts cannot be disabled.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Category</th>
                            @foreach ($channels as $channel)
                                <th class="px-4 py-2 font-medium">{{ $channel->label() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($categories as $category)
                            <tr>
                                <td class="py-3 pr-4 text-slate-700 dark:text-slate-300">{{ $category->label() }}</td>
                                @foreach ($channels as $channel)
                                    <td class="px-4 py-3">
                                        <input type="checkbox"
                                               name="categories[{{ $category->value }}][{{ $channel->value }}]" value="1"
                                               @checked($preference->category_overrides[$category->value][$channel->value] ?? true)
                                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Quiet hours (email)</h2>
            <p class="mt-1 text-xs text-slate-400">Within these hours, non-critical emails are held back. Evaluated in your timezone.</p>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="quiet-start" class="block text-sm font-medium text-slate-700 dark:text-slate-300">From</label>
                    <input id="quiet-start" name="quiet_hours_start" type="time" value="{{ old('quiet_hours_start', $preference->quiet_hours_start) }}"
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="quiet-end" class="block text-sm font-medium text-slate-700 dark:text-slate-300">To</label>
                    <input id="quiet-end" name="quiet_hours_end" type="time" value="{{ old('quiet_hours_end', $preference->quiet_hours_end) }}"
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
                <div>
                    <label for="timezone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Timezone</label>
                    <input id="timezone" name="timezone" type="text" value="{{ old('timezone', $preference->timezone ?? 'Asia/Makassar') }}" required
                           class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Save preferences
            </button>
        </div>
    </form>
@endsection
