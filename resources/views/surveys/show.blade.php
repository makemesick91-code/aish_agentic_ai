@extends('layouts.app')

@section('title', $survey->name . ' — Aish Agentic AI')
@section('heading', $survey->name)
@section('subheading', 'Status: ' . $survey->status->label())

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950">{{ $errors->first() }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        @can('publish', $survey)
            <form method="POST" action="{{ route('surveys.publish', $survey) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white">{{ __('Publish draft') }}</button></form>
        @endcan
        @if ($current)
            <a class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:border-slate-700" href="{{ route('surveys.results', $survey) }}">{{ __('Results') }}</a>
            @can('update', $survey)
                <form method="POST" action="{{ route('surveys.new-version', $survey) }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:border-slate-700">{{ __('New version from published') }}</button></form>
            @endcan
        @endif
        @can('pause', $survey)
            @if ($survey->status === \App\Enums\SurveyStatus::Published)
                <form method="POST" action="{{ route('surveys.pause', $survey) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:border-slate-700">{{ __('Pause') }}</button></form>
            @elseif ($survey->status === \App\Enums\SurveyStatus::Paused)
                <form method="POST" action="{{ route('surveys.resume', $survey) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs dark:border-slate-700">{{ __('Resume') }}</button></form>
            @endif
        @endcan
        @can('archive', $survey)
            @if ($survey->status !== \App\Enums\SurveyStatus::Archived)
                <form method="POST" action="{{ route('surveys.archive', $survey) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-red-600 dark:border-slate-700">{{ __('Archive') }}</button></form>
            @endif
        @endcan
    </div>

    @if ($draft)
        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">{{ __('Draft version') }} v{{ $draft->version_number }}</h2>
                <a class="text-xs text-emerald-600 hover:underline" href="{{ route('surveys.preview', ['survey' => $survey, 'version' => $draft->ulid]) }}">{{ __('Preview') }}</a>
            </div>

            <ol class="mt-3 space-y-3">
                @foreach ($draft->questions as $question)
                    <li class="rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-800">
                        <div class="font-medium">{{ $loop->iteration }}. {{ $question->prompt }} <span class="text-xs text-slate-500">({{ $question->type->label() }})</span></div>
                        @if ($question->type->usesOptions())
                            <ul class="mt-1 list-disc pl-5 text-xs text-slate-500">
                                @foreach ($question->options as $option)<li>{{ $option->label }}</li>@endforeach
                            </ul>
                            @can('update', $survey)
                                <form method="POST" action="{{ route('surveys.options.store', ['survey' => $survey, 'question' => $question->ulid]) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <input name="option_key" placeholder="key" required class="rounded border p-1 text-xs dark:bg-slate-950">
                                    <input name="label" placeholder="label" required class="rounded border p-1 text-xs dark:bg-slate-950">
                                    <input name="value" placeholder="value" required class="rounded border p-1 text-xs dark:bg-slate-950">
                                    <input name="display_order" type="number" value="{{ $question->options->count() + 1 }}" class="w-16 rounded border p-1 text-xs dark:bg-slate-950">
                                    <button class="rounded bg-slate-700 px-2 py-1 text-xs text-white">+ opt</button>
                                </form>
                            @endcan
                        @endif
                    </li>
                @endforeach
            </ol>

            @can('update', $survey)
                <form method="POST" action="{{ route('surveys.questions.store', $survey) }}" class="mt-4 grid gap-2 sm:grid-cols-2">
                    @csrf
                    <input name="question_key" placeholder="{{ __('question key (a-z0-9_)') }}" required class="rounded border p-2 text-sm dark:bg-slate-950">
                    <select name="type" class="rounded border p-2 text-sm dark:bg-slate-950">
                        @foreach (\App\Enums\QuestionType::cases() as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach
                    </select>
                    <input name="prompt" placeholder="{{ __('prompt') }}" required class="rounded border p-2 text-sm dark:bg-slate-950 sm:col-span-2">
                    <input name="display_order" type="number" value="{{ $draft->questions->count() + 1 }}" class="rounded border p-2 text-sm dark:bg-slate-950">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="required" value="1">{{ __('Required') }}</label>
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white sm:col-span-2">{{ __('Add question') }}</button>
                </form>
            @endcan
        </section>
    @else
        <p class="mt-6 text-sm text-slate-500">{{ __('No editable draft. Create a new version from the published one to edit.') }}</p>
    @endif
@endsection
