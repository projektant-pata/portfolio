@extends('base')

@section('title', 'Experience')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/experience.css') }}">
@endsection
@section('scripts')
    <script src="{{asset('js/experience.js')}}" defer></script>
@endsection

@section('content')
    <section id="experience">
        <h2>Experience</h2>
        <div id="experience-timeline"></div>
        <div id="experience-content">
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>2024</h4>
                    <h3><span>Hackathon AstroPi</span></h3>
                    <p class="mini">1st team place in hackathon</p>
                    <p>Space themed hackathon made possible by ESA, with 2 goals - Mission Zero and Mission Space Lab,
                        in team with Petr Machovec and Ondřej Kučera <br> <br>

                        <span>Mission Zero </span>- program in Python animation on 8x8 display on AstroPi, which is
                        reactive to the environment (change of heat or pressure)
                        <br> <br>

                        <span>Mission Space Lab</span> - program in Python, that calculates speed of ISS by taking 2
                        pictures of earth from there and then matching anchor points
                    </p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Competition
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Python
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>2024</h4>
                    <h3><span>ByEvolution</span></h3>
                    <p class="mini">Work experience in Erasmus CIT+</p>
                    <p>I was selected for Erasmus trip in Spain. Here i worked 14 days in ByEvolution Creative Factory,
                        firm that specialize in blockchain and crypto technology.
                    </p><br>
                    <ul>
                        <li><p>Automatization of application in Selenium</p></li>
                        <li><p>Front-end with HTML, Tailwind, Alpine.js</p></li>

                    </ul>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Work
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Block-chain
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Full-stack
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card experience-content-row-specialcard">
                    <h4>2024</h4>
                    <h3><span>PěknéWeby</span></h3>
                    <p class="mini">Part-time job</p>
                    <p>Took part in complete rebranding of restaurant U Sladovny in Chrudim.
                    </p><br>
                    <ul>
                        <li><p>Took part in Front-end + Back-end of website made in Symfony</p></li>
                        <li><p><a href="https://www.usladovnychrudim.cz/" target="_blank"><span>Link here</span></a></p>
                        </li>
                    </ul>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Work
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Symfony
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>2023 - now</h4>
                    <h3><span>Cisco IT Essentials</span></h3>
                    <p class="mini">Certificate</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Certificate
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Hardware
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>2022 - now</h4>
                    <h3><span>Student</span></h3>
                    <p class="mini">SPŠE a VOŠ Pardubice</p>
                    <p>School education with focus on full-stack web development and app development.
                    </p><br>
                    <ul>
                        <li><p>HTML + CSS + JS in front-end </p></li>
                        <li><p>PHP + Laravel in back-end</p></li>
                        <li><p>MySQL in databases</p></li>
                        <li><p>Java in app development</p></li>
                        <li><p>Basics in Photoshop and Illustrator</p></li>
                        <li><p>Basics in web design</p></li>
                        <li><p>Developed interest in cybersecurity</p></li>

                    </ul>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Education
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Full-stack
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            Java
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card experience-content-row-specialcard">
                    <h4>2022</h4>
                    <h3><span>IT-Slot</span></h3>
                    <p class="mini">11th place out of 8320 students</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Competition
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            IT
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>2022</h4>
                    <h3><span>Cisco</span></h3>
                    <p class="mini">Certificate</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            Certificate
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            MS Office
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection
