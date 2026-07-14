<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aish Agentic AI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    @php
        /** @var \App\Tenancy\TenantContext $ctx */
        $ctx = app(\App\Tenancy\TenantContext::class);
        $currentTenant = $ctx->tenant();
        $currentBranch = $ctx->hasBranch() ? $ctx->branch() : null;
        $navLinks = [
            ['route' => 'dashboard', 'label' => 'Dashboard'],
            ['route' => 'branches.index', 'label' => 'Branches'],
            ['route' => 'users.index', 'label' => 'Users'],
            ['route' => 'audit.index', 'label' => 'Audit'],
            ['route' => 'tenant.edit', 'label' => 'Settings'],
        ];
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold">
                        Aish Agentic AI
                    </a>
                    <span class="hidden text-slate-300 sm:inline dark:text-slate-600">/</span>
                    <span class="hidden text-sm text-slate-600 sm:inline dark:text-slate-300">{{ $currentTenant->name }}</span>
                </div>

                <nav aria-label="Primary" class="order-last w-full sm:order-none sm:w-auto">
                    <ul class="flex flex-wrap gap-1">
                        @foreach ($navLinks as $link)
                            <li>
                                <a href="{{ route($link['route']) }}"
                                   @class([
                                       'rounded-md px-3 py-2 text-sm font-medium',
                                       'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' => request()->routeIs($link['route']),
                                       'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs($link['route']),
                                   ])
                                   @if (request()->routeIs($link['route'])) aria-current="page" @endif>
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('branch.select') }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Branch: {{ $currentBranch?->name ?? 'All branches' }}
                    </a>
                    <span class="hidden text-sm text-slate-500 md:inline dark:text-slate-400">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <div class="mb-6">
                <h1 class="text-xl font-semibold">@yield('heading', 'Dashboard')</h1>
                @hasSection('subheading')
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">@yield('subheading')</p>
                @endif
            </div>

            @if (session('status'))
                <div role="status" class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
