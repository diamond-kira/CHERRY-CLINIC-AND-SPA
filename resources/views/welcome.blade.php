<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Thoughtful clinic care and restorative spa treatments at Cherry Zephyr Clinic & Spa.">
    <meta name="theme-color" content="#f5f7f5">
    <title>Cherry Zephyr Clinic &amp; Spa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f7f5] text-[#202a28] antialiased">
    <header class="relative z-20 border-b border-[#dfe7e2] bg-white">
        <div class="mx-auto flex min-h-[78px] max-w-[1440px] items-center justify-between gap-6 px-5 sm:px-8 lg:px-12">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="Cherry Zephyr home">
                <span class="grid size-10 place-items-center rounded-xl bg-[#a92d43] font-[Manrope] text-xs font-extrabold text-white">CZ</span>
                <span class="font-[Manrope] text-sm font-extrabold leading-tight sm:text-base">Cherry Zephyr<span class="mt-1 block font-sans text-[10px] font-semibold uppercase tracking-[0.12em] text-[#687873]">Clinic &amp; Spa</span></span>
            </a>

            <nav class="hidden items-center gap-8 text-[13px] font-semibold text-[#45534e] lg:flex" aria-label="Main navigation">
                <a class="transition-colors hover:text-[#a92d43]" href="{{ route('home') }}">Home</a>
                <a class="transition-colors hover:text-[#a92d43]" href="{{ route('about') }}">About Us</a>
                <a class="transition-colors hover:text-[#a92d43]" href="{{ route('produce') }}">Our Produce</a>
                <a class="transition-colors hover:text-[#a92d43]" href="{{ route('investors') }}">Investors</a>
            </nav>

            <div class="hidden items-center gap-3 sm:flex">
                @auth
                    <a class="text-[13px] font-bold text-[#45534e] hover:text-[#a92d43]" href="{{ route('dashboard') }}">My dashboard</a>
                @else
                    <a class="text-[13px] font-bold text-[#45534e] hover:text-[#a92d43]" href="{{ route('login') }}">Sign in</a>
                    <a class="rounded-md bg-[#a92d43] px-4 py-3 text-[12px] font-bold text-white transition-colors hover:bg-[#812337]" href="{{ route('register') }}">Create account</a>
                @endauth
            </div>

            <details class="group relative lg:hidden">
                <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-md border border-[#dfe7e2] text-[#34413c]" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </summary>
                <nav class="absolute right-0 top-12 z-30 grid min-w-48 gap-1 rounded-md border border-[#dfe7e2] bg-white p-2 shadow-lg" aria-label="Mobile navigation">
                    <a class="rounded px-3 py-2 text-sm hover:bg-[#f3f6f4]" href="{{ route('home') }}">Home</a>
                    <a class="rounded px-3 py-2 text-sm hover:bg-[#f3f6f4]" href="{{ route('about') }}">About Us</a>
                    <a class="rounded px-3 py-2 text-sm hover:bg-[#f3f6f4]" href="{{ route('produce') }}">Our Produce</a>
                    <a class="rounded px-3 py-2 text-sm hover:bg-[#f3f6f4]" href="{{ route('investors') }}">Investors</a>
                    @auth
                        <a class="rounded px-3 py-2 text-sm font-bold text-[#a92d43] hover:bg-[#f3f6f4]" href="{{ route('dashboard') }}">My dashboard</a>
                    @else
                        <a class="rounded px-3 py-2 text-sm font-bold text-[#a92d43] hover:bg-[#f3f6f4]" href="{{ route('login') }}">Sign in</a>
                    @endauth
                </nav>
            </details>
        </div>
    </header>

    <main>
        <section class="mx-auto grid min-h-[620px] max-w-[1440px] overflow-hidden bg-[#e9efeb] lg:min-h-[680px] lg:grid-cols-[0.88fr_1.12fr]">
            <div class="flex flex-col justify-center px-6 py-16 sm:px-10 lg:px-16 xl:px-24">
                <p class="mb-5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#a92d43]">Care for your whole self</p>
                <h1 class="max-w-[620px] font-[Manrope] text-[42px] font-extrabold leading-[1.08] sm:text-[54px] lg:text-[61px]">Feel well.<br><span class="text-[#397565]">Live fully.</span></h1>
                <p class="mt-6 max-w-[440px] text-[15px] leading-7 text-[#53615b]">Personalized clinic care and restorative spa treatments, brought together in one calm, welcoming place.</p>
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <a class="rounded-md bg-[#a92d43] px-5 py-3.5 text-[13px] font-bold text-white transition-colors hover:bg-[#812337]" href="{{ route('register') }}">Book your first visit</a>
                    <a class="rounded-md border border-[#bac9c1] bg-white/70 px-5 py-3.5 text-[13px] font-bold text-[#344b42] transition-colors hover:bg-white" href="{{ route('produce') }}">Explore our care</a>
                </div>
                <div class="mt-12 flex flex-wrap gap-x-8 gap-y-3 border-t border-[#cbd7d0] pt-5 text-[11px] font-semibold text-[#63716a]">
                    <span>Clinic consultations</span><span>Spa &amp; wellness</span><span>Care that stays connected</span>
                </div>
            </div>
            <div class="relative min-h-[350px] overflow-hidden lg:min-h-full">
                <img class="absolute inset-0 size-full object-cover object-center" src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&amp;fit=crop&amp;w=1800&amp;q=85" alt="Quiet spa treatment room with natural light and warm wood details" fetchpriority="high">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#17241f]/75 to-transparent px-6 pb-7 pt-24 sm:px-10">
                    <p class="font-[Manrope] text-lg font-bold text-white">A little more room to feel like yourself.</p>
                    <p class="mt-1 text-xs text-white/80">Thoughtful care, in a setting made for you.</p>
                </div>
            </div>
        </section>

        <section id="about" class="scroll-mt-6 bg-white px-5 py-20 sm:px-8 lg:py-28">
            <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-[0.92fr_1.08fr] lg:gap-20">
                <div class="relative min-h-[320px] overflow-hidden rounded-md sm:min-h-[420px]">
                    <img class="absolute inset-0 size-full object-cover" src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&amp;fit=crop&amp;w=1200&amp;q=85" alt="Healthcare professional offering attentive patient care" loading="lazy">
                </div>
                <div>
                    <p class="mb-4 text-[11px] font-extrabold uppercase tracking-[0.17em] text-[#a92d43]">About Cherry Zephyr</p>
                    <h2 class="max-w-xl font-[Manrope] text-3xl font-extrabold leading-tight sm:text-4xl">Good care begins with being heard.</h2>
                    <p class="mt-6 max-w-xl text-[14px] leading-7 text-[#65716c]">We bring clinical expertise and restorative wellness together with a simple belief: care should feel personal, considered, and easy to return to.</p>
                    <p class="mt-4 max-w-xl text-[14px] leading-7 text-[#65716c]">From your first appointment to the details of your ongoing care, our team is here to make each visit feel clear and comfortable.</p>
                    <a class="mt-7 inline-flex items-center gap-2 text-[13px] font-bold text-[#397565] hover:text-[#a92d43]" href="{{ route('about') }}">More about our approach <span aria-hidden="true">&#8594;</span></a>
                </div>
            </div>
        </section>

        <section id="produce" class="scroll-mt-6 px-5 py-20 sm:px-8 lg:py-24">
            <div class="mx-auto max-w-6xl">
                <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <p class="mb-3 text-[11px] font-extrabold uppercase tracking-[0.17em] text-[#a92d43]">Our care</p>
                        <h2 class="font-[Manrope] text-3xl font-extrabold sm:text-4xl">Care for every part of you.</h2>
                    </div>
                    <p class="max-w-md text-sm leading-6 text-[#65716c]">Explore considered services from clinical support to restorative spa treatments.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <article class="rounded-md border border-[#e0e7e2] bg-white p-6 sm:p-7">
                        <span class="mb-6 grid size-10 place-items-center rounded-full bg-[#f8e9eb] font-[Manrope] text-xs font-extrabold text-[#a92d43]">01</span>
                        <h3 class="font-[Manrope] text-lg font-extrabold">Clinic care</h3>
                        <p class="mt-3 text-[13px] leading-6 text-[#65716c]">One-to-one consultations and thoughtful care plans shaped around your needs.</p>
                    </article>
                    <article class="rounded-md border border-[#e0e7e2] bg-white p-6 sm:p-7">
                        <span class="mb-6 grid size-10 place-items-center rounded-full bg-[#e5f0eb] font-[Manrope] text-xs font-extrabold text-[#397565]">02</span>
                        <h3 class="font-[Manrope] text-lg font-extrabold">Spa &amp; wellness</h3>
                        <p class="mt-3 text-[13px] leading-6 text-[#65716c]">Restorative treatments and expert guidance in a calm, welcoming setting.</p>
                    </article>
                    <article class="rounded-md border border-[#e0e7e2] bg-white p-6 sm:p-7">
                        <span class="mb-6 grid size-10 place-items-center rounded-full bg-[#f5eddd] font-[Manrope] text-xs font-extrabold text-[#926b2f]">03</span>
                        <h3 class="font-[Manrope] text-lg font-extrabold">Connected appointments</h3>
                        <p class="mt-3 text-[13px] leading-6 text-[#65716c]">Book and manage visits with a team that keeps your experience in view.</p>
                    </article>
                </div>
                <div class="mt-8 text-center">
                    <a class="inline-flex rounded-md bg-[#397565] px-5 py-3.5 text-[13px] font-bold text-white transition-colors hover:bg-[#28594d]" href="{{ route('register') }}">Find your next appointment</a>
                </div>
            </div>
        </section>

        <section id="investors" class="scroll-mt-6 bg-[#263e36] px-5 py-20 text-white sm:px-8 lg:py-24">
            <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="mb-4 text-[11px] font-extrabold uppercase tracking-[0.17em] text-[#e8bfc5]">Investors</p>
                    <h2 class="max-w-2xl font-[Manrope] text-3xl font-extrabold leading-tight sm:text-4xl">Building a thoughtful future for care.</h2>
                    <p class="mt-5 max-w-2xl text-[14px] leading-7 text-white/75">Cherry Zephyr is focused on trusted relationships, considered service, and the long-term wellbeing of the communities we serve. For investor enquiries, please contact our team.</p>
                </div>
                <a class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/35 px-5 text-[13px] font-bold text-white transition-colors hover:bg-white hover:text-[#263e36]" href="mailto:investors@cherryzephyr.example">Investor enquiries</a>
            </div>
        </section>
    </main>

    <footer class="bg-[#1e302a] px-5 py-8 text-white sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <a class="font-[Manrope] text-sm font-extrabold" href="{{ route('home') }}">Cherry Zephyr <span class="font-sans font-medium text-white/65">Clinic &amp; Spa</span></a>
            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-xs text-white/75" aria-label="Footer navigation">
                <a class="hover:text-white" href="{{ route('about') }}">About Us</a>
                <a class="hover:text-white" href="{{ route('produce') }}">Our Produce</a>
                <a class="hover:text-white" href="{{ route('investors') }}">Investors</a>
                <a class="hover:text-white" href="{{ route('login') }}">Sign in</a>
            </nav>
            <p class="text-[11px] text-white/55">&copy; {{ now()->year }} Cherry Zephyr Clinic &amp; Spa</p>
        </div>
    </footer>
</body>
</html>