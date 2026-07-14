@extends('layouts.platform')

@section('title', $tenant->name . ' — Tenants — Aish Agentic AI')
@section('heading', $tenant->name)
@section('subheading', 'Operator view. No customer, business, or medical data is shown here.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tenant</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Name</dt>
                        <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $tenant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Slug</dt>
                        <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $tenant->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $tenant->status->value }}</span>
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Subscription</h2>
                @if ($subscription)
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Plan</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription->plan?->name ?? 'No plan' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription->status->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Trial ends</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription->trial_ends_at ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Current period ends</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription->current_period_ends_at ?? '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">No subscription assigned.</p>
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notification health</h2>
                <div class="mt-4 grid grid-cols-3 gap-4">
                    @php
                        $notifCards = [
                            ['label' => 'Queued', 'key' => 'queued'],
                            ['label' => 'Sent', 'key' => 'sent'],
                            ['label' => 'Failed', 'key' => 'failed'],
                        ];
                    @endphp
                    @foreach ($notifCards as $card)
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</dt>
                            <dd class="mt-2 text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $notificationCounts[$card['key']] ?? 0 }}</dd>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Support notes</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No customer or medical data.</p>

                @if ($supportNotes->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">No support notes recorded.</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($supportNotes as $note)
                            <li class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                                <p class="text-sm text-slate-800 dark:text-slate-100">{{ $note->body }}</p>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $note->author?->name ?? 'System' }} · {{ $note->created_at?->diffForHumans() ?? '—' }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($canManageNotes)
                    <form method="POST" action="{{ route('platform.tenants.support-notes.store', $tenant) }}" class="mt-6">
                        @csrf
                        <label for="note-body" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Add a support note</label>
                        <textarea id="note-body" name="body" rows="3" required
                                  class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950"></textarea>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No customer or medical data.</p>
                        <button type="submit"
                                class="mt-3 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Add note
                        </button>
                    </form>
                @endif
            </section>
        </div>

        <div class="lg:col-span-1">
            @if ($canManageStatus)
                <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Manage status</h2>

                    <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <label for="suspend-reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Suspend — reason</label>
                        <input id="suspend-reason" name="reason" type="text" required
                               class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300">
                            Suspend tenant
                        </button>
                    </form>

                    <form method="POST" action="{{ route('platform.tenants.reactivate', $tenant) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                            Reactivate tenant
                        </button>
                    </form>

                    <form method="POST" action="{{ route('platform.tenants.deletion-pending', $tenant) }}" class="space-y-2"
                          onsubmit="return confirm('Mark {{ $tenant->name }} as deletion pending?');">
                        @csrf
                        @method('PATCH')
                        <label for="deletion-reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Mark deletion pending — reason</label>
                        <input id="deletion-reason" name="reason" type="text" required
                               class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                            Mark deletion pending
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>
@endsection
