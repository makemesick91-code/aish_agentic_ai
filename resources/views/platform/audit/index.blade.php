@extends('layouts.platform')

@section('title', 'Audit — Aish Agentic AI')
@section('heading', 'Platform audit log')
@section('subheading', 'Append-only record of platform operator actions.')

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($events->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                No audit events recorded yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Event</th>
                            <th scope="col" class="px-4 py-3 font-medium">Actor</th>
                            <th scope="col" class="px-4 py-3 font-medium">Subject</th>
                            <th scope="col" class="px-4 py-3 font-medium">Metadata</th>
                            <th scope="col" class="px-4 py-3 font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($events as $event)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $event->event }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $event->actor?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $event->subject_type ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                    @if (! empty($event->metadata))
                                        {{ implode(', ', array_keys($event->metadata)) }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $event->created_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $events->links() }}
    </div>
@endsection
