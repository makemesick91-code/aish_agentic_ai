@extends('layouts.app')

@section('title', 'Results — ' . $survey->name)
@section('heading', 'Results: ' . $survey->name)
@section('subheading', 'Operational survey metrics for this workspace.')

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs uppercase text-slate-500">{{ __('Completed responses') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $overview['total_completed'] }}</p>
        </div>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold">{{ __('Current version metrics') }}</h2>
        @if (empty($overview['current_version_metrics']))
            <p class="mt-2 text-sm text-slate-500">{{ __('No scored questions or no responses yet.') }}</p>
        @else
            <table class="mt-3 min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="text-left text-xs uppercase text-slate-500">
                    <tr><th class="py-2">Question</th><th class="py-2">Metric</th><th class="py-2">Valid</th><th class="py-2">Value</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($overview['current_version_metrics'] as $key => $metric)
                        <tr>
                            <td class="py-2 font-medium">{{ $key }}</td>
                            <td class="py-2 uppercase">{{ $metric['metric'] }}</td>
                            <td class="py-2">{{ $metric['valid_count'] }}</td>
                            <td class="py-2">
                                @if ($metric['metric'] === 'csat'){{ $metric['csat_percentage'] ?? '—' }}%
                                @elseif ($metric['metric'] === 'nps'){{ $metric['nps_score'] ?? '—' }}
                                @else{{ $metric['average'] ?? '—' }}@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
