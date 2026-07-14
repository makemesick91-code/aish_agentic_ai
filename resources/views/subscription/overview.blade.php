@extends('layouts.app')

@section('title', 'Subscription — Aish Agentic AI')
@section('heading', 'Subscription')
@section('subheading', 'Your workspace plan, status, and effective entitlements.')

@section('content')
    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($subscription === null)
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No subscription is assigned to this workspace yet. Contact your administrator.
                </p>
            @else
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Current plan</p>
                        <p class="mt-1 text-lg font-semibold text-slate-800 dark:text-slate-100">{{ $subscription->plan?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
                        <p class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-300">{{ $subscription->status->label() }}</p>
                    </div>
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-slate-400">Trial ends</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $subscription->trial_ends_at?->toDayDateTimeString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Period ends</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $subscription->current_period_ends_at?->toDayDateTimeString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Grace ends</dt>
                        <dd class="text-slate-700 dark:text-slate-300">{{ $subscription->grace_ends_at?->toDayDateTimeString() ?? '—' }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-slate-400">Payment and invoicing are not part of this foundation; no paid state is implied.</p>
            @endif
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Effective entitlements</h2>
                <p class="mt-1 text-xs text-slate-400">Entitlement definitions only. The features they gate are not implemented in this foundation.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Feature</th>
                            <th scope="col" class="px-6 py-3 font-medium">Value</th>
                            <th scope="col" class="px-6 py-3 font-medium">Allowed</th>
                            <th scope="col" class="px-6 py-3 font-medium">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($entitlements as $entitlement)
                            <tr>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">{{ $entitlement['feature_key'] }}</td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-300">
                                    @php($value = $entitlement['effective_value'])
                                    @if (is_bool($value))
                                        {{ $value ? 'Enabled' : 'Disabled' }}
                                    @elseif ($value === -1)
                                        Unlimited
                                    @else
                                        {{ $value ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if ($entitlement['allowed'])
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Yes</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $entitlement['reason_code'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Usage</h2>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                <span class="font-medium">{{ $usage['meter'] }}</span> — {{ $usage['total'] }} recorded in {{ $usage['period'] }}.
            </p>
            <p class="mt-1 text-xs text-slate-400">This is the single internal verification meter; it is not billing-grade reconciliation.</p>
        </section>
    </div>
@endsection
