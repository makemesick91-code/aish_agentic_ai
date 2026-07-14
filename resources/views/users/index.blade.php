@extends('layouts.app')

@section('title', 'Users — Aish Agentic AI')
@section('heading', 'Users & invitations')
@section('subheading', 'Manage workspace memberships, roles, and pending invitations.')

@section('content')
    <div class="space-y-8">
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Members</h2>
            </div>

            @if ($memberships->isEmpty())
                <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">No members yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">User</th>
                                <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                <th scope="col" class="px-6 py-3 font-medium">Role</th>
                                <th scope="col" class="px-6 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($memberships as $membership)
                                @php $memberRole = $membership->user?->getRoleNames()->first(); @endphp
                                <tr class="align-top">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800 dark:text-slate-100">{{ $membership->user?->name ?? 'Unknown user' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $membership->user?->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $membership->status === \App\Enums\MembershipStatus::Active,
                                            'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' => $membership->status === \App\Enums\MembershipStatus::Suspended,
                                            'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' => ! in_array($membership->status, [\App\Enums\MembershipStatus::Active, \App\Enums\MembershipStatus::Suspended], true),
                                        ])>
                                            {{ ucfirst($membership->status->value) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('users.role', $membership) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <label class="sr-only" for="role-{{ $membership->ulid }}">Role for {{ $membership->user?->name }}</label>
                                            <select id="role-{{ $membership->ulid }}" name="role"
                                                    class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role }}" @selected($memberRole === $role)>{{ $role }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Set</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @if ($membership->status === \App\Enums\MembershipStatus::Active)
                                                <form method="POST" action="{{ route('users.suspend', $membership) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-sm font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400">Suspend</button>
                                                </form>
                                            @elseif ($membership->status === \App\Enums\MembershipStatus::Suspended)
                                                <form method="POST" action="{{ route('users.reactivate', $membership) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Reactivate</button>
                                                </form>
                                            @endif

                                            @if ($membership->status !== \App\Enums\MembershipStatus::Revoked)
                                                <form method="POST" action="{{ route('users.revoke', $membership) }}"
                                                      onsubmit="return confirm('Revoke access for {{ $membership->user?->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">Revoke</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pending invitations</h2>
                </div>

                @if ($invitations->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">No pending invitations.</div>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($invitations as $invitation)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                                <div>
                                    <div class="font-medium text-slate-800 dark:text-slate-100">{{ $invitation->email }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $invitation->role }} &middot; expires {{ $invitation->expires_at->diffForHumans() }}
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('invitations.destroy', $invitation) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">Revoke</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="lg:col-span-1">
                <form method="POST" action="{{ route('invitations.store') }}"
                      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    @csrf
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Invite a user</h2>

                    <div class="mt-4 space-y-4">
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
                                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                            @error('role')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <label for="invite-all-branches" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input id="invite-all-branches" name="all_branches" type="checkbox" value="1" checked
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
                            Grant access to all branches
                        </label>

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Send invitation
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
