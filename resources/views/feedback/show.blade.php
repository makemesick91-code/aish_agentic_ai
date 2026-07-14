@extends('layouts.app')

@section('title', 'Feedback — Aish Agentic AI')
@section('heading', 'Feedback item')
@section('subheading', $feedback->ulid)

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:bg-rose-950 dark:text-rose-200" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Source + metrics --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-sm font-semibold uppercase text-slate-500">{{ __('Source') }}</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-400">{{ __('Survey') }}</dt><dd>{{ $feedback->survey?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('Campaign') }}</dt><dd>{{ $feedback->campaign?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('Version') }}</dt><dd>#{{ $feedback->survey_version_id ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('Completed') }}</dt><dd>{{ $feedback->surveyResponse?->submitted_at?->toDayDateTimeString() ?? '—' }}</dd></div>
                    @php($m = $feedback->metric_snapshot ?? [])
                    <div><dt class="text-slate-400">CSAT</dt><dd>{{ $m['csat'] ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">NPS</dt><dd>{{ $m['nps'] ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">CES</dt><dd>{{ $m['ces'] ?? '—' }}</dd></div>
                </dl>
                @if ($canViewContent && $feedback->search_content)
                    <div class="mt-4">
                        <h3 class="text-xs font-semibold uppercase text-slate-500">{{ __('Response text') }}</h3>
                        <p class="mt-1 whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-950">{{ $feedback->search_content }}</p>
                    </div>
                @endif
            </section>

            {{-- Notes --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-sm font-semibold uppercase text-slate-500">{{ __('Internal notes') }}</h2>
                <ul class="space-y-3">
                    @forelse ($feedback->notes->sortByDesc('id') as $note)
                        <li class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-950">
                            <p class="whitespace-pre-line">{{ $note->body }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $note->author?->name ?? __('System') }} · {{ $note->created_at?->diffForHumans() }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">{{ __('No notes yet.') }}</li>
                    @endforelse
                </ul>
                @can('addNote', $feedback)
                    <form method="POST" action="{{ route('feedback.notes.store', $feedback) }}" class="mt-4">
                        @csrf
                        <label for="body" class="sr-only">{{ __('Add a note') }}</label>
                        <textarea id="body" name="body" rows="2" maxlength="5000" required
                            class="w-full rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700" placeholder="{{ __('Add an internal note…') }}"></textarea>
                        <button type="submit" class="mt-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">{{ __('Add note') }}</button>
                    </form>
                @endcan
            </section>

            {{-- Attachments --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-sm font-semibold uppercase text-slate-500">{{ __('Attachments') }}</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($feedback->attachments->where('state', \App\Enums\FeedbackAttachmentState::Available) as $attachment)
                        <li class="flex items-center justify-between rounded-lg bg-slate-50 p-2 dark:bg-slate-950">
                            <a class="text-emerald-600 hover:underline" href="{{ route('feedback.attachments.download', [$feedback, $attachment]) }}">{{ $attachment->original_filename }}</a>
                            <span class="text-xs text-slate-400">{{ number_format($attachment->size_bytes / 1024, 0) }} KB</span>
                        </li>
                    @empty
                        <li class="text-slate-400">{{ __('No attachments.') }}</li>
                    @endforelse
                </ul>
                @can('manageAttachments', $feedback)
                    <form method="POST" action="{{ route('feedback.attachments.store', $feedback) }}" enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
                        @csrf
                        <label for="file" class="sr-only">{{ __('Attach a file') }}</label>
                        <input id="file" name="file" type="file" required class="text-sm" />
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Upload') }}</button>
                    </form>
                    <p class="mt-1 text-xs text-slate-400">{{ __('PDF, JPEG, PNG, or WebP up to 10 MB.') }}</p>
                @endcan
            </section>

            {{-- Timeline --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-3 text-sm font-semibold uppercase text-slate-500">{{ __('Timeline') }}</h2>
                <ol class="space-y-2 text-sm">
                    @foreach ($feedback->events->sortByDesc('id') as $event)
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-none rounded-full bg-emerald-500" aria-hidden="true"></span>
                            <div>
                                <p>{{ $event->type->label() }}</p>
                                <p class="text-xs text-slate-400">{{ $event->actor?->name ?? __('System') }} · {{ $event->created_at?->diffForHumans() }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        {{-- Sidebar: status, assignee, tags --}}
        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-2 text-sm font-semibold uppercase text-slate-500">{{ __('Status') }}</h2>
                <p class="mb-3"><span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium dark:bg-slate-800">{{ $feedback->status->label() }}</span></p>
                @can('manageStatus', $feedback)
                    <form method="POST" action="{{ route('feedback.status', $feedback) }}" class="space-y-2">
                        @csrf
                        <label for="status" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Change status') }}</label>
                        <select id="status" name="status" class="w-full rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                            @foreach (\App\Enums\FeedbackStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <input name="reason" type="text" maxlength="500" placeholder="{{ __('Reason (required to reopen)') }}"
                            class="w-full rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700" aria-label="{{ __('Reason') }}" />
                        <button type="submit" class="w-full rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">{{ __('Update') }}</button>
                    </form>
                @endcan
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-2 text-sm font-semibold uppercase text-slate-500">{{ __('Assignee') }}</h2>
                <p class="mb-3 text-sm">{{ $feedback->assignee?->name ?? __('Unassigned') }}</p>
                @can('assign', $feedback)
                    <form method="POST" action="{{ route('feedback.assign', $feedback) }}" class="space-y-2">
                        @csrf
                        <label for="assignee_id" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Assign to user id') }}</label>
                        <input id="assignee_id" name="assignee_id" type="number" min="1"
                            class="w-full rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700" />
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">{{ __('Assign') }}</button>
                            <button type="submit" name="assignee_id" value="" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Unassign') }}</button>
                        </div>
                    </form>
                @endcan
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-2 text-sm font-semibold uppercase text-slate-500">{{ __('Tags') }}</h2>
                <ul class="mb-3 flex flex-wrap gap-2">
                    @forelse ($feedback->tags as $tag)
                        <li class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs dark:bg-slate-800">
                            {{ $tag->name }}
                            @can('tag', $feedback)
                                <form method="POST" action="{{ route('feedback.tags.detach', [$feedback, $tag]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-500" aria-label="{{ __('Remove tag :name', ['name' => $tag->name]) }}">&times;</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">{{ __('No tags.') }}</li>
                    @endforelse
                </ul>
                @can('tag', $feedback)
                    <form method="POST" action="{{ route('feedback.tags.attach', $feedback) }}" class="flex gap-2">
                        @csrf
                        <label for="tag_id" class="sr-only">{{ __('Attach tag') }}</label>
                        <select id="tag_id" name="tag_id" class="flex-1 rounded-lg border-slate-300 text-sm dark:bg-slate-900 dark:border-slate-700">
                            @foreach ($availableTags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Attach') }}</button>
                    </form>
                @endcan
            </section>
        </div>
    </div>
@endsection
