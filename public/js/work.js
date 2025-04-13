function toggleWork(){
    const workBtn = document.getElementById('work-top-btn-work');
    const eduBtn = document.getElementById('work-top-btn-edu');

    const work = document.getElementById('work-bot-content-work');
    const education = document.getElementById('work-bot-content-edu');

    workBtn.addEventListener('click', () => {
        work.style.display = 'block';
        education.style.display = 'none';
        workBtn.classList.add('active');
        eduBtn.classList.remove('active');
    });

    eduBtn.addEventListener('click', () => {
        work.style.display = 'none';
        education.style.display = 'block';
        workBtn.classList.remove('active');
        eduBtn.classList.add('active');
    });
}

toggleWork()
