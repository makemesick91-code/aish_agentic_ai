@extends('layouts.app')

@section('title', 'Notifications — Aish Agentic AI')
@section('heading', 'Notifications')
@section('subheading', 'Your in-app notifications for this workspace.')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $unreadCount }} unread
            </p>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($notifications->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    You have no notifications yet.
                </div>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($notifications as $notification)
                        <li @class([
                            'flex items-start justify-between gap-4 px-5 py-4',
                            'bg-indigo-50/40 dark:bg-indigo-950/20' => ! $notification->isRead(),
                        ])>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    @unless ($notification->isRead())
                                        <span class="inline-block h-2 w-2 flex-none rounded-full bg-indigo-500" aria-label="Unread"></span>
                                    @endunless
                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $notification->subject }}</p>
                                </div>
                                @if ($notification->body)
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $notification->body }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                            @unless ($notification->isRead())
                                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="flex-none">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                        Mark read
                                    </button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
