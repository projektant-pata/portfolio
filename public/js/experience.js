function timeLineTrimmer() {
    let timeLine = document.getElementById('experience-timeline');
    let content = document.getElementById('experience-content');
    let firstDiv = content.querySelector('.experience-content-row');
    let lastDiv = content.querySelector('.experience-content-row:last-child');
    let firstCircle = firstDiv.querySelector('.experience-content-row-circle');
    let lastCircle = lastDiv.querySelector('.experience-content-row-circle');

    let firstCircleTop = firstCircle.getBoundingClientRect().top;
    let lastCircleTop = lastCircle.getBoundingClientRect().top;

    let bottomTimeline = lastCircle.getBoundingClientRect().bottom - content.getBoundingClientRect().top; // pozice bottom
    let topTimeline = firstCircle.getBoundingClientRect().top - content.getBoundingClientRect().top; // pozice top

    console.log("Top timeline: ", topTimeline);
    console.log("Bottom timeline: ", bottomTimeline);

    timeLine.style.top = topTimeline + 100 + 'px';

    let totalHeight = bottomTimeline - topTimeline;
    timeLine.style.height = totalHeight + 'px';
}

timeLineTrimmer();

console.log('pp');
