@extends('layouts.app')

@section('title', 'Preview — ' . $survey->name)
@section('heading', 'Preview: ' . $version->title)
@section('subheading', 'Version ' . $version->version_number . ' (' . $version->status->label() . ')')

@section('content')
    <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm font-medium text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300">
        {{ __('PREVIEW — this does not record a response and is not a public link.') }}
    </div>

    <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-lg font-semibold">{{ $version->title }}</h1>
        @if ($version->introduction)<p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $version->introduction }}</p>@endif

        <ol class="mt-4 space-y-4">
            @foreach ($version->questions as $question)
                <li>
                    <p class="text-sm font-medium">{{ $loop->iteration }}. {{ $question->prompt }} @if ($question->required)<span class="text-red-500">*</span>@endif</p>
                    <p class="text-xs text-slate-500">{{ $question->type->label() }}</p>
                    @if ($question->type->usesOptions())
                        <ul class="mt-1 list-disc pl-5 text-xs text-slate-500">
                            @foreach ($question->options as $option)<li>{{ $option->label }}</li>@endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
@endsection
