@extends('layouts.platform')

@section('title', 'Tenants — Aish Agentic AI')
@section('heading', 'Tenants')
@section('subheading', 'All tenants provisioned on the platform.')

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($tenants->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                No tenants provisioned yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Name</th>
                            <th scope="col" class="px-4 py-3 font-medium">Slug</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 font-medium">Subscription</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($tenants as $tenant)
                            @php $subscription = $subscriptions[$tenant->id] ?? null; @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $tenant->name }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $tenant->slug }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $tenant->status->value }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    @if ($subscription)
                                        {{ $subscription->plan?->name ?? 'No plan' }}
                                        <span class="text-slate-400 dark:text-slate-500">·</span>
                                        {{ $subscription->status->label() }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">None</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
@endsection
