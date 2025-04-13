function timeLineTrimmer() {
    let timeLine = document.getElementById('experience-timeline');
    let content = document.getElementById('experience-content');
    let firstDiv = content.querySelector('.experience-content-row');
    let lastDiv = content.querySelector('.experience-content-row:last-child');

    let firstDivHeight = firstDiv.offsetHeight;
    let lastDivHeight = lastDiv.offsetHeight;

    timeLine.style.top = `${firstDivHeight/2}px`;
    timeLine.style.height = 'calc(100% - ' + (firstDivHeight/2) + 'px)';
}
function coolerTimeLine(){
    let timeLine = document.getElementById('experience-timeline');
    let triangle = document.getElementById('experience-triangle')
    let ender document.getElementById('experience-timelineender')

    let top = timeLine.style.top;
    triangle.style.top = top;


}
timeLineTrimmer();
coolerTimeLine()

console.log('pp');
