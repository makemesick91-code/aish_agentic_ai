@extends('layouts.platform')

@section('title', 'Subscriptions — Aish Agentic AI')
@section('heading', 'Subscriptions')
@section('subheading', 'Commercial state per tenant. No payment is taken here.')

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($subscriptions->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                No subscriptions assigned yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Tenant</th>
                            <th scope="col" class="px-4 py-3 font-medium">Plan</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            @if ($canManage)
                                <th scope="col" class="px-4 py-3 font-medium">Transition</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $subscription->tenant?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $subscription->plan?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $subscription->status->label() }}</span>
                                </td>
                                @if ($canManage)
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('platform.subscriptions.transition', $subscription->ulid) }}" class="flex flex-wrap items-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label for="status-{{ $subscription->ulid }}" class="sr-only">Status for {{ $subscription->tenant?->name }}</label>
                                                <select id="status-{{ $subscription->ulid }}" name="status" required
                                                        class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label for="reason-{{ $subscription->ulid }}" class="sr-only">Reason for {{ $subscription->tenant?->name }}</label>
                                                <input id="reason-{{ $subscription->ulid }}" name="reason" type="text" placeholder="Reason (optional)"
                                                       class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                                            </div>
                                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                                                Apply
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>

    @if ($canManage)
        <section class="mt-8 max-w-xl">
            <form method="POST" action="{{ route('platform.subscriptions.assign') }}"
                  class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Assign subscription</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="assign-tenant" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tenant</label>
                        <select id="assign-tenant" name="tenant" required
                                class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->ulid }}">{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                        @error('tenant')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="assign-plan" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Plan</label>
                        <select id="assign-plan" name="plan" required
                                class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->ulid }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        @error('plan')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <button type="submit"
                        class="mt-4 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    Assign subscription
                </button>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">No payment is taken; assignment sets commercial state only.</p>
            </form>
        </section>
    @endif
@endsection
