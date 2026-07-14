@extends('layouts.platform')

@section('title', 'Platform Dashboard — Aish Agentic AI')
@section('heading', 'Platform Dashboard')
@section('subheading', 'Operator overview across all tenants.')

@section('content')
    <section aria-labelledby="tenant-counts-heading" class="mb-8">
        <h2 id="tenant-counts-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tenants</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tenantCards = [
                    ['label' => 'Total', 'key' => 'total'],
                    ['label' => 'Active', 'key' => 'active'],
                    ['label' => 'Suspended', 'key' => 'suspended'],
                    ['label' => 'Deletion pending', 'key' => 'deletion_pending'],
                ];
            @endphp
            @foreach ($tenantCards as $card)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $tenantCounts[$card['key']] ?? 0 }}</dd>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section aria-labelledby="subscription-counts-heading">
            <h2 id="subscription-counts-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Subscriptions by status</h2>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if (empty($subscriptionCounts))
                    <p class="text-sm text-slate-500 dark:text-slate-400">No subscriptions yet.</p>
                @else
                    <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($subscriptionCounts as $status => $count)
                            <div class="flex items-center justify-between py-2 first:pt-0 last:pb-0">
                                <dt class="text-sm text-slate-600 dark:text-slate-300">{{ $status }}</dt>
                                <dd class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $count }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </section>

        <section aria-labelledby="notification-counts-heading">
            <h2 id="notification-counts-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Notifications</h2>
            <div class="grid grid-cols-3 gap-4">
                @php
                    $notifCards = [
                        ['label' => 'Queued', 'key' => 'queued'],
                        ['label' => 'Sent', 'key' => 'sent'],
                        ['label' => 'Failed', 'key' => 'failed'],
                    ];
                @endphp
                @foreach ($notifCards as $card)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</dt>
                        <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $notificationCounts[$card['key']] ?? 0 }}</dd>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section aria-labelledby="recent-activity-heading" class="mt-8">
        <h2 id="recent-activity-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Recent platform activity</h2>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($recentAudit->isEmpty())
                <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    No platform activity recorded yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">Event</th>
                                <th scope="col" class="px-4 py-3 font-medium">Actor</th>
                                <th scope="col" class="px-4 py-3 font-medium">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($recentAudit as $entry)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $entry->event }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $entry->actor?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $entry->created_at?->diffForHumans() ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection
