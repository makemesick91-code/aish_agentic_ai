@extends('layouts.app')

@section('title', 'Feedback Inbox — Aish Agentic AI')
@section('heading', 'Feedback')
@section('subheading', 'Operational inbox for feedback projected from completed survey responses.')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($summary)
        <section aria-label="{{ __('Summary') }}" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs uppercase text-slate-500">{{ __('Total') }}</p>
                <p class="text-2xl font-semibold">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs uppercase text-slate-500">{{ __('Unassigned') }}</p>
                <p class="text-2xl font-semibold">{{ $summary['unassigned'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs uppercase text-slate-500">{{ __('New (7d)') }}</p>
                <p class="text-2xl font-semibold">{{ $summary['recently_created'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs uppercase text-slate-500">{{ __('Resolved (7d)') }}</p>
                <p class="text-2xl font-semibold">{{ $summary['recently_resolved'] }}</p>
            </div>
        </section>
    @endif

    <form method="GET" action="{{ route('feedback.index') }}" class="mb-4 flex flex-wrap items-end gap-3" role="search">
        <div>
            <label for="q" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Search') }}</label>
            <input id="q" name="q" type="search" value="{{ request('q') }}" maxlength="100"
                class="mt-1 rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700" />
        </div>
        <div>
            <label for="status" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Status') }}</label>
            <select id="status" name="status[]" class="mt-1 rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                <option value="">{{ __('Any') }}</option>
                @foreach (\App\Enums\FeedbackStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(in_array($status->value, (array) request('status', []), true))>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Sort') }}</label>
            <select id="sort" name="sort" class="mt-1 rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                <option value="recent" @selected(request('sort') === 'recent')>{{ __('Most recent activity') }}</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Least recent activity') }}</option>
                <option value="created" @selected(request('sort') === 'created')>{{ __('Newest created') }}</option>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            {{ __('Filter') }}
        </button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($items->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('No feedback matches these filters.') }}
            </div>
        @else
            <form method="POST" action="{{ route('feedback.bulk') }}">
                @csrf
                @can('bulkManage', \App\Models\FeedbackItem::class)
                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-4 py-2 dark:border-slate-800 dark:bg-slate-950">
                        <label for="bulk-action" class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Bulk action') }}</label>
                        <select id="bulk-action" name="action" class="rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                            <option value="status">{{ __('Set status') }}</option>
                            <option value="attach-tag">{{ __('Attach tag') }}</option>
                            <option value="remove-tag">{{ __('Remove tag') }}</option>
                        </select>
                        <select name="status" aria-label="{{ __('Status') }}" class="rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                            @foreach (\App\Enums\FeedbackStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Apply') }}</button>
                    </div>
                @endcan
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-950">
                        <tr>
                            <th scope="col" class="px-3 py-3"><span class="sr-only">{{ __('Select') }}</span></th>
                            <th scope="col" class="px-4 py-3">{{ __('Created') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Survey') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Metrics') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Status') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Assignee') }}</th>
                            <th scope="col" class="px-4 py-3 text-right">{{ __('Open') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-3 py-3">
                                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" aria-label="{{ __('Select feedback :id', ['id' => $item->ulid]) }}" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $item->created_at?->diffForHumans() }}</td>
                                <td class="px-4 py-3">
                                    {{ $item->survey?->name ?? __('(survey)') }}
                                    @if ($item->campaign)<span class="block text-xs text-slate-400">{{ $item->campaign->name }}</span>@endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    @php($m = $item->metric_snapshot ?? [])
                                    <span>CSAT: {{ $m['csat'] ?? '—' }}</span> ·
                                    <span>NPS: {{ $m['nps'] ?? '—' }}</span> ·
                                    <span>CES: {{ $m['ces'] ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium dark:bg-slate-800">{{ $item->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $item->assignee?->name ?? __('Unassigned') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a class="text-emerald-600 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500" href="{{ route('feedback.show', $item) }}">{{ __('Open') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        @endif
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
