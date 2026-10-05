<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · Cherry Zephyr</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="cz-shell">
    <aside class="cz-sidebar">
        <a class="cz-brand" href="{{ route(auth()->user()->dashboardRouteName()) }}">
            <span class="cz-brand-mark">CZ</span>
            <span class="cz-brand-name">Cherry Zephyr<span class="cz-brand-sub">Clinic &amp; Spa</span></span>
        </a>
        <div class="cz-nav-label">Workspace</div>
        <nav class="cz-nav" aria-label="Primary navigation">
            @foreach ($navigation as [$label, $routeName, $permission])
                <a class="cz-nav-link" href="{{ route($routeName) }}" @if (request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            <form class="cz-nav-logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="cz-nav-link cz-logout" type="submit">Logout</button>
            </form>
        </nav>
        <div class="cz-sidebar-bottom">
            <div class="cz-user-chip">
                <div class="cz-user-name">{{ auth()->user()->name }}</div>
                <div class="cz-user-role">{{ auth()->user()->role?->name }}</div>
            </div>
        </div>
    </aside>
    <main class="cz-main">
        <header class="cz-topbar">
            <span class="cz-topbar-title">Cherry Zephyr Clinic &amp; Spa</span>
            <span class="cz-topbar-date">{{ now()->format('l, F j') }}</span>
            <form class="cz-topbar-logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="cz-button" type="submit">Logout</button>
            </form>
        </header>
        <div class="cz-content">
            @if (session('status'))
                <div class="cz-flash" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>