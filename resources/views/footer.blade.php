<footer>
    <div id="footer-container">
        <h2>projektant-pata</h2>
        <div id="footer-container-content">
            <div id="footer-container-content-left">
                <h4><span>projektant-pata</span></h4>
                <p>&copy; {{ date('Y') }} | {{ __('footer.rights') }}</p>
            </div>
            <div id="footer-container-content-mid">
                <h4><span>{{ __('footer.nav_title') }}</span></h4>
                <a href="{{ route('home') }}"><p>{{ __('footer.nav1') }}</p></a>
                <a href="{{ route('about-me') }}"><p>{{ __('footer.nav2') }}</p></a>
                <a href="{{ route('experience') }}"><p>{{ __('footer.nav3') }}</p></a>
                <a href="{{ route('projects') }}"><p>{{ __('footer.nav4') }}</p></a>
            </div>
            <div id="footer-container-content-right">
                <h4><span>{{ __('footer.soc_title') }}</span></h4>
                <div id="footer-container-content-right-links">
                    <a href="mailto:richard.hyvl@gmail.com" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/email.png")}}" alt="">
                        <p>{{ __('footer.soc1') }}</p>
                    </a>
                    <a href="https://www.instagram.com/richardhyvl/" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/instagram.webp")}}" alt="">
                        <p>{{ __('footer.soc2') }}</p>
                    </a>
                    <a href="https://x.com/projektantPata" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/x.webp")}}" alt="">
                        <p>{{ __('footer.soc3') }}</p>
                    </a>
                    <a href="https://www.linkedin.com/in/richardhyvl/" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/linkedin.webp")}}" alt="">
                        <p>{{ __('footer.soc4') }}</p>
                    </a>
                    <a href="https://github.com/projektant-pata" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/github.webp")}}" alt="">
                        <p>{{ __('footer.soc5') }}</p>
                    </a>
                    <a href="https://www.chess.com/member/obviouscommander" class="footer-container-content-right-links-link" target="_blank">
                        <img src="{{asset("images/mobile/icons/chess.png")}}" alt="">
                        <p>{{ __('footer.soc6') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
