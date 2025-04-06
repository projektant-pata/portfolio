console.log('pp');
function updateTime() {
    const now = new Date();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const timeString = `${hours}:${minutes}`;

    document.getElementById('mobile-top-left-clock').textContent = timeString;
}
window.onload = function (){
    updateTime();
    setInterval(updateTime, 1000);
}

