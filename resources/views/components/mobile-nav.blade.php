<button id="toggle-mobile-nav">☰</button>
<div id="mobile-nav-overlay"></div>

<nav id="mobile-nav">
    <div id="mobile-nav-top">
        <div id="mobile-nav-top-left">
            <p id="mobile-nav-clock" class="mini">xx:xx</p>
        </div>
        <div id="mobile-nav-top-mid">
            <div id="mobile-nav-top-mid-speaker"></div>
            <div id="mobile-nav-top-mid-cam"></div>
        </div>
        <div id="mobile-nav-top-right">
            <img src="{{ asset('images/mobile/top/signal.png') }}" alt="signal">
            <img src="{{ asset('images/mobile/top/wifi.png') }}" alt="wifi">
            <img src="{{ asset('images/mobile/top/battery.png') }}" alt="battery">
        </div>
    </div>

    <div id="mobile-nav-mid">
        {{-- Row 1 — main navigation --}}
        <div class="mobile-nav-row">
            <div class="mobile-nav-app">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/mobile/icons/home.png') }}" alt="home">
                </a>
                <p class="mini">{{ __('layout/mobile.nav1') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ route('about-me') }}">
                    <img src="{{ asset('images/mobile/icons/contacts.png') }}" alt="about me">
                </a>
                <p class="mini">{{ __('layout/mobile.nav2') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ route('experience') }}">
                    <img src="{{ asset('images/mobile/icons/experience.webp') }}" alt="experience">
                </a>
                <p class="mini">{{ __('layout/mobile.nav3') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ route('projects') }}">
                    <img src="{{ asset('images/mobile/icons/notes.webp') }}" alt="projects">
                </a>
                <p class="mini">{{ __('layout/mobile.nav4') }}</p>
            </div>
        </div>

        {{-- Row 2 — social links --}}
        <div class="mobile-nav-row">
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.email') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/email.png') }}" alt="email">
                </a>
                <p class="mini">{{ __('layout/mobile.soc1') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.instagram') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/instagram.webp') }}" alt="instagram">
                </a>
                <p class="mini">{{ __('layout/mobile.soc2') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.x') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/x.webp') }}" alt="x">
                </a>
                <p class="mini">{{ __('layout/mobile.soc3') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.linkedin') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/linkedin.webp') }}" alt="linkedin">
                </a>
                <p class="mini">{{ __('layout/mobile.soc4') }}</p>
            </div>
        </div>

        {{-- Row 3 — misc links --}}
        <div class="mobile-nav-row">
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.github') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/github.webp') }}" alt="github">
                </a>
                <p class="mini">{{ __('layout/mobile.soc5') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="{{ config('portfolio.social.chess') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/chess.png') }}" alt="chess">
                </a>
                <p class="mini">{{ __('layout/mobile.soc6') }}</p>
            </div>
            <div class="mobile-nav-app">
                <a href="https://hyvlri22.llmp.spse-net.cz/" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/mobile/icons/safari.webp') }}" alt="SPSE-WP">
                </a>
                <p class="mini">{{ __('layout/mobile.proj1') }}</p>
            </div>
            <div class="mobile-nav-app"></div>
        </div>

    </div>

    <div id="mobile-nav-bottom">
        <div class="mobile-nav-app">
            <a href="{{ config('portfolio.social.email') }}">
                <img src="{{ asset('images/mobile/icons/messages.webp') }}" alt="messages">
            </a>
        </div>
        <div id="mobile-nav-weather" class="mobile-nav-app">
            <button type="button">
                <img id="mobile-nav-weather-img" src="{{ asset('images/mobile/icons/weather_dark.png') }}" alt="theme toggle">
            </button>
        </div>
        <div class="mobile-nav-app">
            <form action="{{ route('language.toggle') }}" method="POST">
                @csrf
                <button type="submit">
                    <img src="{{ asset('images/mobile/icons/translator.png') }}" alt="language">
                </button>
            </form>
        </div>
        <div class="mobile-nav-app mobile-nav-app--decorative">
            <img src="{{ asset('images/mobile/icons/music.webp') }}" alt="">
        </div>
    </div>
</nav>
