@extends('layouts.app')

@section('title', 'Customers — Aish Agentic AI')
@section('heading', 'Customers')
@section('subheading', 'Canonical customer profiles resolved from verified identities across your branches.')

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

    <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex flex-wrap items-end gap-3" role="search">
        <div>
            <label for="q" class="block text-xs font-medium text-slate-600 dark:text-slate-400">
                {{ $canViewContact ? __('Search name or contact') : __('Search name') }}
            </label>
            <input type="search" name="q" id="q" value="{{ $search }}"
                   class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                   placeholder="{{ $canViewContact ? __('Name, email, or phone') : __('Name') }}">
        </div>
        <div>
            <label for="status" class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Status') }}</label>
            <select name="status" id="status"
                    class="mt-1 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                <option value="">{{ __('Active profiles') }}</option>
                @foreach ($statuses as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white dark:bg-slate-100 dark:text-slate-900">
            {{ __('Apply') }}
        </button>
    </form>

    @if ($customers->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('No customers match this view yet. Profiles are created only when a verified identity is resolved — anonymous responses never create a customer.') }}
            </p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <caption class="sr-only">{{ __('Customer profiles') }}</caption>
                <thead class="bg-slate-50 text-left dark:bg-slate-900">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">{{ __('Customer') }}</th>
                        <th scope="col" class="px-4 py-3 font-medium">{{ __('Contact') }}</th>
                        <th scope="col" class="px-4 py-3 font-medium">{{ __('Branch') }}</th>
                        <th scope="col" class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                        <th scope="col" class="px-4 py-3 font-medium">{{ __('Last seen') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($customers as $customer)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="font-medium underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900 dark:decoration-slate-600">
                                    {{ $customer->display_name ?? __('Unnamed customer') }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{-- Masked unless the viewer holds customer.view-contact (rule 36). --}}
                                {{ $canViewContact ? ($customer->contact_email ?? '—') : ($customer->maskedContactEmail() ?? '—') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $customer->primaryBranch?->name ?? __('Tenant-wide') }}
                            </td>
                            <td class="px-4 py-3">
                                {{-- Status is conveyed by text, not colour alone (accessibility). --}}
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs dark:bg-slate-800">
                                    {{ $customer->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $customer->last_seen_at?->diffForHumans() ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $customers->links() }}</div>
    @endif
@endsection
