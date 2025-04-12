<button id="toggle-mobile">☰</button>
<nav id="mobile">
    <div id="mobile-top">
        <div id="mobile-top-left">
            <p id="mobile-top-left-clock" class="mini">xx:xx</p>
        </div>
        <div id="mobile-top-mid">
            <div id="mobile-top-mid-speaker"></div>
            <div id="mobile-top-mid-cam"></div>
        </div>
        <div id="mobile-top-right">
            <img src="{{asset('images/mobile/top/signal.png')}}" alt="signal">
            <img src="{{asset('images/mobile/top/wifi.png')}}" alt="wifi">
            <img src="{{asset('images/mobile/top/battery.png')}}" alt="battery">
        </div>
    </div>
    <div id="mobile-mid">
        <div class="mobile-mid-row">
            <div class="mobile-mid-app">
                <a href="{{route('home')}}">
                    <img src="{{asset('images/mobile/icons/home.png')}}" alt="home">
                </a>
                <p class="mini">Home</p>
            </div>
            <div class="mobile-mid-app">
                <a href="{{route('about-me')}}">
                    <img src="{{asset('images/mobile/icons/contacts.png')}}" alt="home">
                </a>
                <p class="mini">About me</p>
            </div>
            <div class="mobile-mid-app">
                <a href="{{route('experience')}}">
                    <img src="{{asset('images/mobile/icons/todo.png')}}" alt="home">
                </a>
                <p class="mini">Experience</p>
            </div>
            <div class="mobile-mid-app">
                <a href="{{route('projects')}}">
                    <img src="{{asset('images/mobile/icons/notes.png')}}" alt="home">
                </a>
                <p class="mini">Projects</p>
            </div>
        </div>
        <div class="mobile-mid-row">
            <div class="mobile-mid-app">
                <a href="https://x.com/projektantPata" target="_blank">
                    <img src="{{asset('images/mobile/icons/x.webp')}}" alt="x">
                </a>
                <p class="mini">X</p>
            </div>
            <div class="mobile-mid-app">
                <a href="https://www.instagram.com/richardhyvl/" target="_blank">
                    <img src="{{asset('images/mobile/icons/instagram.webp')}}" alt="instagram">
                </a>
                <p class="mini">Instagram</p>

            </div>
            <div class="mobile-mid-app">
                <a href="https://www.linkedin.com/in/richardhyvl/" target="_blank">
                    <img src="{{asset('images/mobile/icons/linkedin.webp')}}" alt="linkedin">
                </a>
                <p class="mini">LinkedIn</p>

            </div>
            <div class="mobile-mid-app">
                <a href="https://github.com/projektant-pata" target="_blank">
                    <img src="{{asset('images/mobile/icons/github.webp')}}" alt="github">
                </a>
                <p class="mini">GitHub</p>
            </div>

        </div>
        <div class="mobile-mid-row">
            <div class="mobile-mid-app">
                <a href="">
                    <img src="{{asset('images/mobile/icons/web.png')}}" alt="SPSE-WP">
                </a>
                <p class="mini">SPŠE-WP</p>
            </div>
        </div>
        <div id="mobile-mid-row-bottom" class="mobile-mid-row">
            <div class="mobile-mid-app">
                <a href="#">
                    <img src="{{asset('images/mobile/icons/messages.png')}}" alt="home">
                </a>
            </div>
            <div id="mobile-weather" class="mobile-mid-app">
                <a href="#">
                    <img id="mobile-weather-img" src="{{asset('images/mobile/icons/weather_dark.png')}}" alt="home">
                </a>
            </div>
            <div class="mobile-mid-app">
                <a href="#">
                    <img src="{{asset('images/mobile/icons/translator.png')}}" alt="home">
                </a>
            </div>
            <div class="mobile-mid-app">
                <a href="#">
                    <img src="{{asset('images/mobile/icons/music.png')}}" alt="home">
                </a>
            </div>
        </div>
        <div id="mobile-mid-darkness-bottom"></div>
    </div>

    <div id="mobile-bottom"></div>
</nav>
