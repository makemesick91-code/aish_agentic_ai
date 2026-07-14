@extends('layouts.platform')

@section('title', 'Platform Users — Aish Agentic AI')
@section('heading', 'Platform users')
@section('subheading', 'Operators with platform-level roles.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($assignments->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No platform operators assigned yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Name</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Email</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Role</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $assignment->user?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $assignment->user?->email ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $assignment->role->label() }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('platform.users.remove-role', $assignment) }}" class="flex justify-end"
                                                  onsubmit="return confirm('Remove {{ $assignment->role->label() }} from {{ $assignment->user?->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $assignments->links() }}
            </div>
        </section>

        <section class="lg:col-span-1">
            <form method="POST" action="{{ route('platform.users.invite') }}"
                  class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Invite operator</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="invite-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                        <input id="invite-name" name="name" type="text" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="invite-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                        <input id="invite-email" name="email" type="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="invite-role" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Role</label>
                        <select id="invite-role" name="role" required
                                class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        Send invitation
                    </button>
                    <p class="text-xs text-slate-500 dark:text-slate-400">The operator sets their own password via a reset link; no password is entered here.</p>
                </div>
            </form>
        </section>
    </div>
@endsection
