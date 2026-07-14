@extends('layouts.app')

@section('title', 'Select branch — Aish Agentic AI')
@section('heading', 'Select a branch')
@section('subheading', 'Choose the branch you want to work in.')

@section('content')
    <div class="max-w-2xl">
        @if ($branches->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                No active branches are available to you yet.
            </div>
        @else
            <form method="POST" action="{{ route('branch.select.store') }}">
                @csrf
                <ul class="space-y-2">
                    @foreach ($branches as $branch)
                        <li>
                            <button type="submit" name="branch" value="{{ $branch->ulid }}"
                                    class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm shadow-sm hover:border-indigo-400 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-indigo-500 dark:hover:bg-indigo-950">
                                <span>
                                    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $branch->name }}</span>
                                    <span class="ml-2 text-xs text-slate-400">{{ $branch->code }}</span>
                                </span>
                                <span aria-hidden="true" class="text-slate-400">&rarr;</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </form>
        @endif
    </div>
@endsection
