@extends('layouts.app')

@section('title', 'Customer 360 — Aish Agentic AI')
{{-- e() is required: the layout renders @yield('heading') unescaped, and display_name originates
     with an untrusted party (rule 36; rule 04). --}}
@section('heading', e($customer->display_name ?? __('Unnamed customer')))
@section('subheading', __('Canonical profile, resolved identities, consent history, and interaction timeline.'))

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

    @if ($customer->isMerged())
        <div class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200" role="status">
            {{ __('This profile has been merged into another customer. It is retained so the merge can be reversed.') }}
            @if ($customer->mergedInto)
                <a href="{{ route('customers.show', $customer->mergedInto) }}" class="underline">{{ __('View the surviving profile') }}</a>
            @endif
        </div>
    @endif

    <section aria-label="{{ __('Profile summary') }}" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs uppercase text-slate-500">{{ __('Interactions') }}</p>
            <p class="text-2xl font-semibold">{{ $summary['feedback_count'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs uppercase text-slate-500">{{ __('Identities') }}</p>
            <p class="text-2xl font-semibold">{{ $summary['identity_count'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs uppercase text-slate-500">{{ __('Merged profiles') }}</p>
            <p class="text-2xl font-semibold">{{ $summary['merged_customer_count'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs uppercase text-slate-500">{{ __('Last seen') }}</p>
            <p class="text-sm font-semibold">{{ $customer->last_seen_at?->diffForHumans() ?? '—' }}</p>
        </div>
    </section>

    <section aria-label="{{ __('Contact details') }}" class="mb-6 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <h2 class="mb-2 text-sm font-semibold">{{ __('Contact') }}</h2>
        @unless ($summary['contact_visible'])
            <p class="mb-2 text-xs text-slate-500">
                {{ __('Contact details are partially hidden. You do not have permission to view customer contact information.') }}
            </p>
        @endunless
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs uppercase text-slate-500">{{ __('Email') }}</dt>
                <dd>{{ $summary['contact_email'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-slate-500">{{ __('Phone') }}</dt>
                <dd>{{ $summary['contact_phone'] ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section aria-label="{{ __('Consent history') }}" class="mb-6 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <h2 class="mb-3 text-sm font-semibold">{{ __('Consent') }}</h2>
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            @foreach ($consentStates as $state)
                <div>
                    <dt class="text-xs uppercase text-slate-500">{{ $state['type']->label() }}</dt>
                    <dd>
                        {{-- "Not recorded" is deliberately distinct from "declined" (rule 36). --}}
                        @if ($state['decision'] === null)
                            <span class="text-slate-500">{{ __('Not recorded') }}</span>
                        @elseif ($state['decision'])
                            {{ __('Accepted') }}
                        @else
                            {{ __('Declined') }}
                        @endif
                        @if ($state['recorded_at'])
                            <span class="text-xs text-slate-500">
                                ({{ $state['recorded_at']->diffForHumans() }}, {{ $state['text_version'] }})
                            </span>
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section aria-label="{{ __('Resolved identities') }}" class="mb-6 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <h2 class="mb-3 text-sm font-semibold">{{ __('Resolved identities') }}</h2>
        @if ($identities->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No identities resolved yet.') }}</p>
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($identities as $identity)
                    <li class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs dark:bg-slate-800">
                            {{ $identity->identity_type->label() }}
                        </span>
                        <span class="text-slate-600 dark:text-slate-400">
                            {{ __('via') }} {{ $identity->source_type->label() }}
                        </span>
                        <span class="text-xs text-slate-500">
                            {{ $identity->is_deterministic ? __('Verified match') : __('Suggested') }}
                        </span>
                        {{-- The identity VALUE is never rendered: it is stored only as a keyed hash (ADR 0071). --}}
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section aria-label="{{ __('Interaction timeline') }}" class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
        <h2 class="mb-3 text-sm font-semibold">{{ __('Interactions') }}</h2>
        @if ($interactions->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No interactions recorded for this customer yet.') }}</p>
        @else
            <ol class="space-y-3 text-sm">
                @foreach ($interactions as $item)
                    <li class="border-l-2 border-slate-200 pl-3 dark:border-slate-700">
                        <p class="font-medium">
                            <a href="{{ route('feedback.show', $item) }}" class="underline underline-offset-2">
                                {{ __('Feedback') }} · {{ $item->status->label() }}
                            </a>
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $item->created_at?->diffForHumans() }}
                            @if ($item->branch) · {{ $item->branch->name }} @endif
                        </p>
                    </li>
                @endforeach
            </ol>

            <div class="mt-4">{{ $interactions->links() }}</div>
        @endif
    </section>

    @if ($canMerge && ! $customer->isMerged())
        <section aria-label="{{ __('Merge another profile') }}" class="mt-6 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <h2 class="mb-2 text-sm font-semibold">{{ __('Merge a duplicate into this profile') }}</h2>
            <p class="mb-3 text-xs text-slate-500">
                {{ __('The merged profile is retained, not deleted, so this can be reversed later.') }}
            </p>
            <form method="POST" action="{{ route('customers.merge', $customer) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label for="merged_customer" class="block text-xs font-medium">{{ __('Duplicate customer ID') }}</label>
                    <input type="text" name="merged_customer" id="merged_customer" required
                           class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <div class="grow">
                    <label for="reason" class="block text-xs font-medium">{{ __('Reason') }}</label>
                    <input type="text" name="reason" id="reason" required minlength="8"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white dark:bg-slate-100 dark:text-slate-900">
                    {{ __('Merge') }}
                </button>
            </form>
        </section>
    @endif
@endsection
