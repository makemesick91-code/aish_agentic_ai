@extends('layouts.platform')

@section('title', $plan->name . ' — Plans — Aish Agentic AI')
@section('heading', $plan->name)
@section('subheading', $plan->code . ' v' . $plan->version)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Plan</h2>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $plan->code }} v{{ $plan->version }} — {{ $plan->name }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $plan->status->label() }}</span>
                </div>

                @if ($canManage)
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($plan->status->value !== 'active')
                            <form method="POST" action="{{ route('platform.plans.activate', $plan) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                                    Activate plan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('platform.plans.retire', $plan) }}"
                                  onsubmit="return confirm('Retire {{ $plan->name }}?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300">
                                    Retire plan
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Features</h2>
                </div>
                @if ($plan->features->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        No entitlements configured yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Key</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Type</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($plan->features as $feature)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $feature->key }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $feature->type->value }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $feature->typedValue() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        @if ($canManage)
            <section class="lg:col-span-1">
                <form method="POST" action="{{ route('platform.plans.features.store', $plan) }}"
                      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    @csrf
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Add / update entitlement</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="feature-key" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Key</label>
                            <select id="feature-key" name="key" required
                                    class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                                @foreach ($entitlementKeys as $key => $type)
                                    <option value="{{ $key }}" @selected(old('key') === $key)>{{ $key }} ({{ $type->value }})</option>
                                @endforeach
                            </select>
                            @error('key')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="feature-value" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Value</label>
                            <input id="feature-value" name="value" type="text" value="{{ old('value') }}" required
                                   class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950">
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Booleans use true/false; integers use a number or -1 for unlimited.</p>
                            @error('value')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Save entitlement
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
@endsection
