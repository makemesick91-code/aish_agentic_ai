@extends('layouts.app')

@section('title', $campaign->name . ' — Campaign')
@section('heading', $campaign->name)
@section('subheading', 'Status: ' . $campaign->status->label())

@section('content')
    @if (session('status'))<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

    <div class="flex flex-wrap gap-2">
        @can('activate', $campaign)
            @if ($campaign->status === \App\Enums\CampaignStatus::Draft || $campaign->status === \App\Enums\CampaignStatus::Paused)
                <form method="POST" action="{{ route('survey-campaigns.activate', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white">{{ __('Activate') }}</button></form>
            @endif
        @endcan
        @can('pause', $campaign)
            @if ($campaign->status === \App\Enums\CampaignStatus::Active)
                <form method="POST" action="{{ route('survey-campaigns.pause', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:border-slate-700">{{ __('Pause') }}</button></form>
            @endif
        @endcan
        @can('end', $campaign)
            @if (in_array($campaign->status, [\App\Enums\CampaignStatus::Active, \App\Enums\CampaignStatus::Paused], true))
                <form method="POST" action="{{ route('survey-campaigns.end', $campaign) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-red-600 dark:border-slate-700">{{ __('End') }}</button></form>
            @endif
        @endcan
    </div>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold">{{ __('Public link & QR') }}</h2>
            <p class="mt-2 break-all text-xs text-slate-600 dark:text-slate-300">{{ $publicUrl }}</p>
            <img src="{{ $qrUrl }}" alt="{{ __('Survey QR code') }}" class="mt-3 h-40 w-40 rounded border border-slate-200 dark:border-slate-800">
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold">{{ __('Send a unique invitation') }}</h2>
            <form method="POST" action="{{ route('survey-invitations.store') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="hidden" name="campaign_ulid" value="{{ $campaign->ulid }}">
                <input type="email" name="recipient_email" required placeholder="pasien@example.com" class="flex-1 rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white">{{ __('Send') }}</button>
            </form>

            <table class="mt-4 min-w-full text-xs">
                <thead class="text-left uppercase text-slate-500"><tr><th class="py-1">Recipient</th><th class="py-1">Status</th></tr></thead>
                <tbody>
                    @foreach ($invitations as $invitation)
                        <tr><td class="py-1">{{ $invitation->recipient_email ?? '—' }}</td><td class="py-1">{{ $invitation->status->label() }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
