@extends('layouts.app')

@section('title', 'Campaigns — Aish Agentic AI')
@section('heading', 'Survey campaigns')
@section('subheading', 'Distribute a published survey via public link, QR, or invitation.')

@section('content')
    @if ($errors->any())<div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($campaigns->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500">{{ __('No campaigns yet.') }}</div>
                @else
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-950"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Open</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($campaigns as $campaign)
                                <tr><td class="px-4 py-3 font-medium">{{ $campaign->name }}</td><td class="px-4 py-3">{{ $campaign->status->label() }}</td>
                                    <td class="px-4 py-3 text-right"><a class="text-emerald-600 hover:underline" href="{{ route('survey-campaigns.show', $campaign) }}">{{ __('Open') }}</a></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-4">{{ $campaigns->links() }}</div>
        </section>

        <section>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold">{{ __('New campaign') }}</h2>
                <form method="POST" action="{{ route('survey-campaigns.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <input name="survey_ulid" required placeholder="{{ __('Published survey ULID') }}" class="w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <input name="name" required placeholder="{{ __('Campaign name') }}" class="w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">{{ __('Create') }}</button>
                </form>
            </div>
        </section>
    </div>
@endsection
