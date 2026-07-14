@extends('layouts.app')

@section('title', 'Surveys — Aish Agentic AI')
@section('heading', 'Surveys')
@section('subheading', 'Create and manage feedback surveys for this workspace.')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($surveys->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        {{ __('No surveys yet. Create the first one.') }}
                    </div>
                @else
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-950">
                            <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($surveys as $survey)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $survey->name }}</td>
                                    <td class="px-4 py-3">{{ $survey->status->label() }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a class="text-emerald-600 hover:underline" href="{{ route('surveys.show', $survey) }}">{{ __('Open') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="mt-4">{{ $surveys->links() }}</div>
        </section>

        <section>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold">{{ __('New survey') }}</h2>
                @if ($errors->any())
                    <p class="mt-2 text-xs text-red-600">{{ $errors->first() }}</p>
                @endif
                <form method="POST" action="{{ route('surveys.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <input name="name" required maxlength="255" placeholder="{{ __('Survey name') }}"
                        class="w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                    <textarea name="description" rows="2" placeholder="{{ __('Internal description') }}"
                        class="w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950"></textarea>
                    <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">{{ __('Create') }}</button>
                </form>
            </div>
        </section>
    </div>
@endsection
