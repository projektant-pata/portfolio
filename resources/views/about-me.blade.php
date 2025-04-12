@extends('base')

@section('title', 'About Me')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/about-me.css') }}">
@endsection
@section('scripts')
    <script defer src="{{ asset('js/about-me.js') }}"></script>
    <script defer src="{{ asset('js/about-me-api.js') }}"></script>
@endsection
@section('content')
    <section id="about-me">
        <h2>About me</h2>
        <div id="about-me-content">
            <div class="about-me-row-card">
                <h3>Who am I?</h3>
                <p>Hi there! I’m Richard Hývl, a starting software developer and freelancer with a passion.
                    Currently,
                    I’m a
                    student at SPŠE Pardubice and leading figure in a Web developing group called
                    <span>Prezz. </span></p>
            </div>
            <div class="about-me-row-card">
                <h3>What drives me?</h3>
                <p>I´m driven by curiosity, the desire to help other people. I am thrive on learning and growing my
                    personality and one day be succesful man.</p>
            </div>
            <div class="about-me-row-card">
                <h3>Volunteering?</h3>
                <p>I have volunteered at few community events. It helped me develop presenting skills and Lorem
                    Ipsum
                    Pyco <br>
                    What was I part of:
                    PEER program: Program that helps teenagers understand the dangers of drugs and bullying. I was
                    educated and then was presenting to my Peers.
                    CZECH DAY AGAINST CANCER : Organisation made to collect funds to help people with cancer by
                    selling
                    flower badge on streets.
                    PEER program: sds</p>
            </div>
            <div class="about-me-row-card">
                <h3>What do I like?</h3>
                <p>From a young age i was passionate chess player. I was winning in 2nd grade against highschoolers
                    at
                    local chess tournament. With a great break I am back, with a renewed passion for the game. Chess
                    has
                    taught me <span>critical thinking</span>, strategy, and the importance of patience - skills that
                    I’ve found
                    incredibly valuable in my journey as a software developer.
                    <br>
                    <br>
                    I also really really love catfishes and Rock music :)</p>
            </div>
            <div class="about-me-row-card">
                <h3>How did we get here?</h3>
                <p>My journey started after I went back to elementary school from gymnasium due to health issues.
                    Luckily the elementary school had extended teaching in the field of IT.
                    <br>
                    <br>
                    There, i fell in love with the technology, the unlimited options to create and inovate new
                    things,
                    it was like a dream. There, I developed my first website. Sadly, it was deleted by hosting and i
                    had
                    no backup.
                    <br>
                    <br>
                    At highschool, my passion thrived even more, leading me to achive multiple victories, like
                    winning
                    hackathon, becoming freelancer.</p>
            </div>
        </div>
    </section>
    <section id="stats">
        <h2>My Stats</h2>
        <article id="stats-content">
            <div class="stats-content-row-card">
                <h3><span>Junior</span></h3>
                <p>Professional Level</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>5+    </span></h3>
                <p>Projects Completed</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>2</span></h3>
                <p>Years of experience</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>2</span></h3>
                <p>Countries Reached</p>
            </div>

            <div class="stats-content-row-card">
                <h3><span>18</span></h3>
                <p>Years old</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span id="elo">Loading..</span></h3>
                <p>Highest chess elo</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>♞</span></h3>
                <p>Favorite piece</p>
            </div>

            <div class="stats-content-row-card">
                <h3><span>∞</span></h3>
                <p>Coffee consumed</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>1</span></h3>
                <p>Hackathons won</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span id="github-repos">Loading..</span></h3>
                <p>GitHub repositories</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>404</span></h3>
                <p>Hours slept</p>
            </div>
        </article>
    </section>

@endsection
