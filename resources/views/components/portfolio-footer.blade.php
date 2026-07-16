<h2>projektant-pata</h2>
<footer class="portfolio-footer">
    <div class="portfolio-footer-inner">
        <div class="portfolio-footer-brand">
            <h4><span>projektant-pata</span></h4>
            <p>&copy; {{ date('Y') }} | {{ __('layout/footer.rights') }}</p>
        </div>

        <nav class="portfolio-footer-nav">
            <h4><span>{{ __('layout/footer.nav_title') }}</span></h4>
            <div class="portfolio-footer-nav-links">
                <a href="{{ route('home') }}" class="portfolio-footer-nav-link">
                    <img src="{{ asset('images/mobile/icons/home.png') }}" alt="">
                    <p>{{ __('layout/footer.nav1') }}</p>
                </a>
                <a href="{{ route('about-me') }}" class="portfolio-footer-nav-link">
                    <img src="{{ asset('images/mobile/icons/contacts.png') }}" alt="">
                    <p>{{ __('layout/footer.nav2') }}</p>
                </a>
                <a href="{{ route('experience') }}" class="portfolio-footer-nav-link">
                    <img src="{{ asset('images/mobile/icons/experience.webp') }}" alt="">
                    <p>{{ __('layout/footer.nav3') }}</p>
                </a>
                <a href="{{ route('projects') }}" class="portfolio-footer-nav-link">
                    <img src="{{ asset('images/mobile/icons/notes.webp') }}" alt="">
                    <p>{{ __('layout/footer.nav4') }}</p>
                </a>
                <a href="#" class="portfolio-footer-nav-link">
                    <img src="{{ asset('images/mobile/icons/safari.webp') }}" alt="">
                    <p>{{ __('layout/footer.nav5') }}</p>
                </a>
            </div>
        </nav>

        <div class="portfolio-footer-social">
            <h4><span>{{ __('layout/footer.soc_title') }}</span></h4>
            <div class="portfolio-footer-social-links">
                <a href="mailto:richard.hyvl@gmail.com" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/email.png') }}" alt="">
                    <p>{{ __('layout/footer.soc1') }}</p>
                </a>
                <a href="https://www.instagram.com/richardhyvl/" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/instagram.webp') }}" alt="">
                    <p>{{ __('layout/footer.soc2') }}</p>
                </a>
                <a href="https://x.com/projektantPata" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/x.webp') }}" alt="">
                    <p>{{ __('layout/footer.soc3') }}</p>
                </a>
                <a href="https://www.linkedin.com/in/richardhyvl/" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/linkedin.webp') }}" alt="">
                    <p>{{ __('layout/footer.soc4') }}</p>
                </a>
                <a href="https://github.com/projektant-pata" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/github.webp') }}" alt="">
                    <p>{{ __('layout/footer.soc5') }}</p>
                </a>
                <a href="https://www.chess.com/member/obviouscommander" class="portfolio-footer-social-link" target="_blank">
                    <img src="{{ asset('images/mobile/icons/chess.png') }}" alt="">
                    <p>{{ __('layout/footer.soc6') }}</p>
                </a>
            </div>
        </div>
    </div>
</footer>
