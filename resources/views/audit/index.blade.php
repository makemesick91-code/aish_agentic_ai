@extends('layouts.app')

@section('title', 'Audit log — Aish Agentic AI')
@section('heading', 'Audit log')
@section('subheading', 'A chronological record of important actions in this workspace.')

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if ($logs->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">No audit records yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">When</th>
                            <th scope="col" class="px-6 py-3 font-medium">Event</th>
                            <th scope="col" class="px-6 py-3 font-medium">Actor</th>
                            <th scope="col" class="px-6 py-3 font-medium">Channel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-slate-600 dark:text-slate-300">
                                    <span title="{{ $log->created_at?->toDateTimeString() }}">{{ $log->created_at?->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-100">{{ $log->event }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->actor?->name ?? 'System' }}</td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $log->channel }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection
