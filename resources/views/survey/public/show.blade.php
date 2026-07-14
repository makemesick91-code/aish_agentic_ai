@extends('layouts.guest')

@section('title', $view->version->title . ' — Aish Agentic AI')

@section('content')
    <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $view->version->title }}</h1>

        @if ($view->version->introduction)
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $view->version->introduction }}</p>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                {{ __('Please correct the highlighted answers.') }}
            </div>
        @endif

        <form method="POST" action="{{ $submitUrl }}" class="mt-6 space-y-6">
            @csrf

            @foreach ($view->version->questions as $question)
                @php $name = 'answers[' . $question->question_key . ']'; @endphp
                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        {{ $question->prompt }}
                        @if ($question->required)<span class="text-red-500" aria-hidden="true">*</span>@endif
                    </legend>
                    @if ($question->help_text)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $question->help_text }}</p>
                    @endif

                    @if ($question->type->usesNumericScale())
                        @php [$min, $max] = $question->scaleBounds(); @endphp
                        <div class="flex flex-wrap gap-2">
                            @for ($i = $min; $i <= $max; $i++)
                                <label class="inline-flex items-center gap-1 text-sm">
                                    <input type="radio" name="{{ $name }}" value="{{ $i }}">{{ $i }}
                                </label>
                            @endfor
                        </div>
                    @elseif ($question->type->usesBoolean())
                        <div class="flex gap-4 text-sm">
                            <label class="inline-flex items-center gap-1"><input type="radio" name="{{ $name }}" value="1">{{ __('Ya') }}</label>
                            <label class="inline-flex items-center gap-1"><input type="radio" name="{{ $name }}" value="0">{{ __('Tidak') }}</label>
                        </div>
                    @elseif ($question->type->usesText())
                        <textarea name="{{ $name }}" rows="3" maxlength="{{ $question->validation_config['max_length'] ?? 2000 }}"
                            class="w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-950"></textarea>
                    @elseif ($question->type->allowsMultiple())
                        <div class="space-y-1">
                            @foreach ($question->options as $option)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="answers[{{ $question->question_key }}][]" value="{{ $option->option_key }}">{{ $option->label }}
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-1">
                            @foreach ($question->options as $option)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="{{ $name }}" value="{{ $option->option_key }}">{{ $option->label }}
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @error('answers.' . $question->question_key)
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endforeach

            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                {{ __('Kirim') }}
            </button>
        </form>
    </div>
@endsection
