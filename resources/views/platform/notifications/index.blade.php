@extends('layouts.platform')

@section('title', 'Notifications — Aish Agentic AI')
@section('heading', 'Notification health')
@section('subheading', 'Delivery metadata only. Message contents are never shown here.')

@section('content')
    <section aria-labelledby="state-counts-heading" class="mb-8">
        <h2 id="state-counts-heading" class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">By state</h2>
        @if (empty($stateCounts))
            <p class="text-sm text-slate-500 dark:text-slate-400">No notifications recorded yet.</p>
        @else
            <div class="flex flex-wrap gap-3">
                @foreach ($stateCounts as $state => $count)
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        {{ $state }}
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $count }}</span>
                    </span>
                @endforeach
            </div>
        @endif
    </section>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($recent->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                No notifications recorded yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Type</th>
                            <th scope="col" class="px-4 py-3 font-medium">Channel</th>
                            <th scope="col" class="px-4 py-3 font-medium">State</th>
                            <th scope="col" class="px-4 py-3 font-medium">Tenant</th>
                            <th scope="col" class="px-4 py-3 font-medium">Failure code</th>
                            <th scope="col" class="px-4 py-3 font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($recent as $notification)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $notification->type }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $notification->channel }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $notification->state->value }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $notification->tenant_id ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $notification->failure_code ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $notification->created_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $recent->links() }}
    </div>
@endsection
