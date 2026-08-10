
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

// ── Theme ────────────────────────────────────────────────────
// The `theme` cookie ('dark' | 'light' | 'system', default 'dark')
// is the single source of truth for public pages, admin and Flux.
// partials/theme.blade.php applies the same rule before first paint.

const prefersDark = () => window.matchMedia('(prefers-color-scheme: dark)');

function resolveTheme(preference) {
    return preference === 'system'
        ? (prefersDark().matches ? 'dark' : 'light')
        : (preference === 'light' ? 'light' : 'dark');
}

function applyTheme(preference) {
    const nav = document.getElementById('mobile-nav');
    const weatherImg = document.getElementById('mobile-nav-weather-img');
    const isDark = resolveTheme(preference) === 'dark';

    document.documentElement.classList.toggle('dark', isDark);

    if (isDark) {
        if (nav) nav.style.backgroundImage = "url('/images/mobile/wallpapers/wallpaper_dark.webp')";
        if (weatherImg) weatherImg.src = '/images/mobile/icons/weather_dark.png';
    } else {
        if (nav) nav.style.backgroundImage = "url('/images/mobile/wallpapers/wallpaper_light.webp')";
        if (weatherImg) weatherImg.src = '/images/mobile/icons/weather_light.webp';
    }
}

function toggleTheme() {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    setTheme(next);
}

function setTheme(preference) {
    setCookie('theme', preference, 365);
    applyTheme(preference);
}

/** Exposed for the admin appearance settings page (pages/settings/⚡appearance.blade.php). */
window.getThemePreference = () => getCookie('theme') || 'dark';
window.setThemePreference = setTheme;

// ── Work / Education tab toggle ──────────────────────────────

function crossfadeContent(target, other) {
    if (target === other || target.style.display === 'block') {
        return;
    }

    other.classList.add('is-fading');
    target.classList.add('is-fading');

    setTimeout(() => {
        other.style.display = 'none';
        target.style.display = 'block';

        requestAnimationFrame(() => {
            requestAnimationFrame(() => target.classList.remove('is-fading'));
        });
    }, 180);
}

function initWorkToggle() {
    const workBtn = document.getElementById('work-top-btn-work');
    const lifeBtn = document.getElementById('work-top-btn-life');
    const workContent = document.getElementById('work-bot-content-work');
    const lifeContent = document.getElementById('work-bot-content-life');

    if (!workBtn || !lifeBtn) {
        return;
    }

    workBtn.addEventListener('click', () => {
        crossfadeContent(workContent, lifeContent);
        workBtn.classList.add('active');
        lifeBtn.classList.remove('active');
        workBtn.setAttribute('aria-pressed', 'true');
        lifeBtn.setAttribute('aria-pressed', 'false');
    });

    lifeBtn.addEventListener('click', () => {
        crossfadeContent(lifeContent, workContent);
        lifeBtn.classList.add('active');
        workBtn.classList.remove('active');
        lifeBtn.setAttribute('aria-pressed', 'true');
        workBtn.setAttribute('aria-pressed', 'false');
    });
}

// ── Hero rotator (typewriter) ─────────────────────────────────

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initHeroRotator() {
    const el = document.getElementById('hero-rotator');
    if (!el || prefersReducedMotion()) {
        return;
    }

    let roles;
    try {
        roles = JSON.parse(el.dataset.roles);
    } catch {
        return;
    }
    if (!Array.isArray(roles) || roles.length < 2) {
        return;
    }

    const TYPE_MS = 55;
    const DELETE_MS = 35;
    const PAUSE_MS = 1600;
    let roleIndex = 0;

    // Flattens a role's HTML into {text, gold} runs — any character nested
    // inside an element (e.g. `<span>`) is marked gold, everything else
    // plain — so the typewriter can type *through* markup one visible
    // character at a time instead of only ever handling plain text.
    const parseRoleSegments = (html) => {
        const template = document.createElement('template');
        template.innerHTML = html;
        const segments = [];
        const walk = (node, gold) => {
            node.childNodes.forEach((child) => {
                if (child.nodeType === Node.TEXT_NODE) {
                    if (child.textContent) {
                        segments.push({ text: child.textContent, gold });
                    }
                } else if (child.nodeType === Node.ELEMENT_NODE) {
                    walk(child, true);
                }
            });
        };
        walk(template.content, false);
        return segments;
    };

    const escapeHtml = (text) => text.replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[c]));

    // Renders the first `count` visible characters of the parsed segments.
    // Text is re-escaped and gold runs are always wrapped in a plain
    // `<span>` built here — the source HTML string itself is never
    // assigned to innerHTML, so admin-entered content can't inject markup.
    const renderSegments = (segments, count) => {
        let remaining = count;
        let html = '';
        for (const segment of segments) {
            if (remaining <= 0) {
                break;
            }
            const slice = segment.text.slice(0, remaining);
            remaining -= slice.length;
            const escaped = escapeHtml(slice);
            html += segment.gold ? `<span>${escaped}</span>` : escaped;
        }
        return html;
    };

    const type = (segments, total, i, done) => {
        el.innerHTML = renderSegments(segments, i);
        if (i < total) {
            setTimeout(() => type(segments, total, i + 1, done), TYPE_MS);
        } else {
            setTimeout(done, PAUSE_MS);
        }
    };

    const erase = (segments, total, i, done) => {
        el.innerHTML = renderSegments(segments, i);
        if (i > 0) {
            setTimeout(() => erase(segments, total, i - 1, done), DELETE_MS);
        } else {
            done();
        }
    };

    const cycle = () => {
        const segments = parseRoleSegments(roles[roleIndex]);
        const total = segments.reduce((sum, segment) => sum + segment.text.length, 0);
        type(segments, total, 0, () => {
            erase(segments, total, total, () => {
                roleIndex = (roleIndex + 1) % roles.length;
                cycle();
            });
        });
    };

    cycle();
}

// ── Scroll reveal ─────────────────────────────────────────────

function setStagger(containerSelector) {
    document.querySelectorAll(containerSelector).forEach((container) => {
        [...container.children].forEach((child, i) => child.style.setProperty('--i', i));
    });
}

function initScrollReveal() {
    setStagger('.stats-cards');
    setStagger('.tools-row');
    setStagger('.reviews-row');

    const targets = document.querySelectorAll(
        '.portfolio-section:not(.hero-page), .stats-cards-card, .tools-row-card'
    );
    const carousels = document.querySelectorAll('.reviews-carousel');
    const revealCards = (carousel) => {
        carousel.querySelectorAll('.reviews-row-card').forEach((card) => card.classList.add('is-visible'));
    };

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        carousels.forEach(revealCards);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    targets.forEach((el) => observer.observe(el));

    // Carousel pages beyond the first are clipped by the viewport's
    // `overflow: hidden` and never individually intersect on their own, so
    // reveal every card in the track at once when the carousel scrolls in.
    const reviewsObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    revealCards(entry.target);
                    reviewsObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    carousels.forEach((el) => reviewsObserver.observe(el));
}

function initHeroEntrance() {
    requestAnimationFrame(() => {
        document.querySelector('.hero-page')?.classList.add('hero-loaded');
    });
}

// ── Scroll progress bar ───────────────────────────────────────

function initScrollProgress() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) {
        return;
    }

    const update = () => {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
        bar.style.width = `${progress}%`;
    };

    window.addEventListener('scroll', update, { passive: true });
    update();
}

// ── Scroll-to-top FAB ─────────────────────────────────────────

function initScrollToTop() {
    const btn = document.getElementById('scroll-top');
    if (!btn) {
        return;
    }

    const THRESHOLD = 400;
    let shown = false;

    const update = () => {
        const shouldShow = window.scrollY > THRESHOLD;
        if (shouldShow === shown) {
            return;
        }
        shown = shouldShow;

        if (shouldShow) {
            btn.hidden = false;
            requestAnimationFrame(() => btn.classList.add('is-visible'));
        } else {
            btn.classList.remove('is-visible');
        }
    };

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    });

    window.addEventListener('scroll', update, { passive: true });
    update();
}

// ── Stat count-up ─────────────────────────────────────────────

function initStatCountUp() {
    const spans = document.querySelectorAll('.stats-cards-card h3 span');
    if (!spans.length || prefersReducedMotion() || !('IntersectionObserver' in window)) {
        return;
    }

    const animate = (el) => {
        const raw = el.textContent.trim();
        const match = raw.match(/^(\d+)(.*)$/);
        if (!match) {
            return;
        }

        const target = parseInt(match[1], 10);
        const suffix = match[2];
        const duration = 900;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = `${Math.round(target * progress)}${suffix}`;
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.5 }
    );

    spans.forEach((span) => observer.observe(span));
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

// ── Reviews carousel ──────────────────────────────────────────

/**
 * Reviews carousel: the viewport is not user-scrollable (overflow: hidden);
 * a timer advances it one step at a time and wraps back to the start.
 * Steps are card-aligned (each card's own offset, clamped to the maximum
 * scroll) so every visible card is always whole — paging by viewport width
 * would cut the last card whenever the card count isn't a multiple of the
 * cards-per-view. Dots stay clickable and track the current step.
 */
function initReviewsCarousel() {
    const AUTOPLAY_DELAY = 5000;

    const carousel = document.querySelector('[data-reviews-carousel]');
    const viewport = carousel?.querySelector('.reviews-carousel-viewport');
    const row = carousel?.querySelector('.reviews-row');
    const dotsWrap = carousel?.querySelector('.reviews-carousel-dots');

    if (!carousel || !viewport || !row || !dotsWrap) {
        return;
    }

    /** @type {number[]} scroll offsets, one per reachable card-aligned step */
    let steps = [0];

    const measureSteps = () => {
        const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
        const rowLeft = row.getBoundingClientRect().left + viewport.scrollLeft;

        const offsets = [...row.children].map(
            (card) => Math.min(Math.round(card.getBoundingClientRect().left + viewport.scrollLeft - rowLeft), maxScroll)
        );

        // Cards past the last full page all clamp to maxScroll — keep one step.
        steps = [...new Set(offsets)];
    };

    const pageCount = () => steps.length;

    const currentPage = () => {
        let closest = 0;
        steps.forEach((offset, i) => {
            if (Math.abs(offset - viewport.scrollLeft) < Math.abs(steps[closest] - viewport.scrollLeft)) {
                closest = i;
            }
        });

        return closest;
    };

    const syncDots = () => {
        const active = currentPage();
        [...dotsWrap.children].forEach((dot, i) => {
            if (i === active) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    };

    const goToPage = (index) => {
        viewport.scrollTo({
            left: steps[index] ?? 0,
            behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        });
    };

    const buildDots = () => {
        measureSteps();
        const pages = pageCount();
        carousel.classList.toggle('is-static', pages <= 1);

        dotsWrap.replaceChildren();
        for (let i = 0; i < pages; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'reviews-carousel-dot';
            dot.setAttribute('aria-label', `${i + 1}`);
            dot.addEventListener('click', () => {
                goToPage(i);
                restartAutoplay();
            });
            dotsWrap.appendChild(dot);
        }
        syncDots();
    };

    let timer = null;

    const stopAutoplay = () => {
        clearInterval(timer);
        timer = null;
    };

    const startAutoplay = () => {
        stopAutoplay();
        if (pageCount() <= 1) {
            return;
        }
        timer = setInterval(() => {
            goToPage((currentPage() + 1) % pageCount());
        }, AUTOPLAY_DELAY);
    };

    const restartAutoplay = () => {
        if (timer) {
            startAutoplay();
        }
    };

    viewport.addEventListener('scroll', syncDots, { passive: true });

    new ResizeObserver(() => {
        buildDots();
        startAutoplay();
    }).observe(viewport);

    // Pause while the visitor is reading (hover/focus) or the tab is hidden.
    carousel.addEventListener('pointerenter', stopAutoplay);
    carousel.addEventListener('pointerleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', startAutoplay);
    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoplay() : startAutoplay();
    });

    buildDots();
    startAutoplay();
}

// wire:navigate morphs <html> to match the freshly-fetched page's raw markup,
// which wipes the `dark` class set by partials/theme.blade.php's pre-paint
// script (that script only runs on a full page load). Reapply on every SPA nav.
document.addEventListener('livewire:navigated', () => {
    applyTheme(getCookie('theme') || 'dark');
});

document.addEventListener('DOMContentLoaded', () => {
    // Apply saved theme (wallpaper + icons; the class itself is set in <head>)
    applyTheme(getCookie('theme') || 'dark');

    // Follow the OS while the preference is 'system'
    prefersDark().addEventListener('change', () => {
        if ((getCookie('theme') || 'dark') === 'system') {
            applyTheme('system');
        }
    });

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

    initHeroRotator();
    initHeroEntrance();
    initScrollReveal();
    initReviewsCarousel();
    initScrollProgress();
    initScrollToTop();
    initStatCountUp();
});
