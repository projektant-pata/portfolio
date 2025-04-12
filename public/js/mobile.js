// Funkce pro nastavení cookie (název, hodnota, počet dní platnosti)
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // počet milisekund za daný počet dní
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

// Funkce pro získání hodnoty cookie podle názvu
function getCookie(name) {
    const cname = name + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookies = decodedCookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.indexOf(cname) === 0) {
            return cookie.substring(cname.length, cookie.length);
        }
    }
    return "";
}

function updateTime() {
    const now = new Date();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const timeString = `${hours}:${minutes}`;

    document.getElementById('mobile-top-left-clock').textContent = timeString;
}

const darkModeImg = document.getElementById('mobile-weather-img');
const mobile = document.getElementById('mobile');

updateTime();
setInterval(updateTime, 1000);

toogleBtnLogic();
addDarkModeListener();
// Při načtení stránky se načte uložené téma z cookie a nastaví se
const theme = getCookie('theme');
if (theme === 'light') {
    document.documentElement.classList.add('light-theme');
    mobile.style.backgroundImage = "url('../images/mobile/wallpapers/wallpaper_light.png')";
    darkModeImg.src = '../images/mobile/icons/weather_light.png';

} else {
    document.documentElement.classList.remove('light-theme');
    mobile.style.backgroundImage = "url('../images/mobile/wallpapers/wallpaper_dark.png')";
    darkModeImg.src = '../images/mobile/icons/weather_dark.png';

}

function toogleBtnLogic() {
    const toggleBtn = document.getElementById('toggle-mobile');
    const overlay = document.getElementById('overlay');
    toggleBtn.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
}

function toggleMenu() {
    if (window.innerWidth <= 992) {
        const mobile = document.getElementById('mobile');
        const overlay = document.getElementById('overlay');
        mobile.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('no-scroll');
    }
}

function addDarkModeListener() {
    const darkModeBtn = document.getElementById('mobile-weather');
    darkModeBtn.addEventListener('click', toggleDarkMode);
}

function toggleDarkMode() {
    const mobile = document.getElementById('mobile');
    const darkModeImg = document.getElementById('mobile-weather-img');
    // Přepneme globální třídu pro téma
    document.documentElement.classList.toggle('light-theme');

    // Nastavení tapety podle aktuálního tématu
    if (!document.documentElement.classList.contains('light-theme')) {
        mobile.style.backgroundImage = "url('../images/mobile/wallpapers/wallpaper_dark.png')";
        darkModeImg.src = '../images/mobile/icons/weather_dark.png';
        setCookie('theme', 'dark', 7);
    } else {
        mobile.style.backgroundImage = "url('../images/mobile/wallpapers/wallpaper_light.png')";
        darkModeImg.src = '../images/mobile/icons/weather_light.png';

        setCookie('theme', 'light', 7);
    }
}
