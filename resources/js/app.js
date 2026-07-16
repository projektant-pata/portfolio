// ── Mobile nav ──────────────────────────────────────────────

function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

function getCookie(name) {
    const cname = name + '=';
    for (let cookie of decodeURIComponent(document.cookie).split(';')) {
        cookie = cookie.trim();
        if (cookie.startsWith(cname)) {
            return cookie.substring(cname.length);
        }
    }
    return '';
}

function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');

    if (typeof locale !== 'undefined' && locale === 'en') {
        hours = hours % 12 || 12;
    }

    const clock = document.getElementById('mobile-nav-clock');
    if (clock) {
        clock.textContent = `${String(hours).padStart(2, '0')}:${minutes}`;
    }
}

function toggleMobileNav() {
    if (window.innerWidth > 992) {
        return;
    }

    const nav = document.getElementById('mobile-nav');
    const overlay = document.getElementById('mobile-nav-overlay');

    nav?.classList.toggle('active');
    overlay?.classList.toggle('active');
    document.body.classList.toggle('no-scroll');
}

function applyTheme(theme) {
    const nav = document.getElementById('mobile-nav');
    const weatherImg = document.getElementById('mobile-nav-weather-img');

    if (theme === 'light') {
        document.documentElement.classList.add('light-theme');
        if (nav) nav.style.backgroundImage = "url('/images/mobile/wallpapers/wallpaper_light.webp')";
        if (weatherImg) weatherImg.src = '/images/mobile/icons/weather_light.webp';
    } else {
        document.documentElement.classList.remove('light-theme');
        if (nav) nav.style.backgroundImage = "url('/images/mobile/wallpapers/wallpaper_dark.webp')";
        if (weatherImg) weatherImg.src = '/images/mobile/icons/weather_dark.png';
    }
}

function toggleTheme() {
    const isLight = document.documentElement.classList.contains('light-theme');
    const next = isLight ? 'dark' : 'light';
    applyTheme(next);
    setCookie('theme', next, 7);
}

// ── Work / Education tab toggle ──────────────────────────────

function initWorkToggle() {
    const workBtn = document.getElementById('work-top-btn-work');
    const lifeBtn = document.getElementById('work-top-btn-life');
    const workContent = document.getElementById('work-bot-content-work');
    const lifeContent = document.getElementById('work-bot-content-life');

    if (!workBtn || !lifeBtn) {
        return;
    }

    workBtn.addEventListener('click', () => {
        workContent.style.display = 'block';
        lifeContent.style.display = 'none';
        workBtn.classList.add('active');
        lifeBtn.classList.remove('active');
    });

    lifeBtn.addEventListener('click', () => {
        workContent.style.display = 'none';
        lifeContent.style.display = 'block';
        lifeBtn.classList.add('active');
        workBtn.classList.remove('active');
    });
}

// ── About Me — live stats ────────────────────────────────────

function fetchChessElo() {
    const elo = document.getElementById('elo');
    if (!elo) {
        return;
    }

    fetch('https://api.chess.com/pub/player/ObviousCommander/stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Chess API error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            elo.textContent = data?.chess_rapid?.best?.rating ?? elo.textContent;
        })
        .catch(error => console.error('Chess elo fetch failed:', error));
}

function fetchGithubRepos() {
    const repos = document.getElementById('github-repos');
    if (!repos) {
        return;
    }

    fetch('https://api.github.com/users/projektant-pata')
        .then(response => {
            if (!response.ok) {
                throw new Error('GitHub API error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            repos.textContent = data?.public_repos ?? repos.textContent;
        })
        .catch(error => console.error('GitHub repos fetch failed:', error));
}

document.addEventListener('DOMContentLoaded', () => {
    // Apply saved theme
    applyTheme(getCookie('theme') || 'dark');

    // Clock
    updateClock();
    setInterval(updateClock, 1000);

    // Toggle button
    document.getElementById('toggle-mobile-nav')?.addEventListener('click', toggleMobileNav);
    document.getElementById('mobile-nav-overlay')?.addEventListener('click', toggleMobileNav);

    // Theme toggle (weather icon in dock)
    document.getElementById('mobile-nav-weather')?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleTheme();
    });

    initWorkToggle();

    fetchChessElo();
    fetchGithubRepos();
});
