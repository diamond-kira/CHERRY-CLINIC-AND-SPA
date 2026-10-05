<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Account' }} · Cherry Zephyr</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="cz-auth-page">
    <section class="cz-auth-side">
        <a class="cz-brand" href="{{ route('home') }}">
            <span class="cz-brand-mark">CZ</span>
            <span class="cz-brand-name">Cherry Zephyr<span class="cz-brand-sub">Clinic &amp; Spa</span></span>
        </a>
        <div class="cz-auth-kicker">
            <h1>Care, calmly coordinated.</h1>
            <p>Appointments and wellness services, managed with care from first visit to follow-up.</p>
        </div>
        <footer>Cherry Zephyr Clinic &amp; Spa</footer>
    </section>
    <section class="cz-auth-main">
        @yield('content')
    </section>
</main>
</body>
</html>