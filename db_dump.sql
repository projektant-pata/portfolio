--
-- PostgreSQL database dump
--

\restrict hjPxWczsfE05y2BrgsMlFeslYhtVAG9qu3imj8S1h00CZsWnF0aBiiZusY4OlzF

-- Dumped from database version 17.10 (Debian 17.10-1.pgdg13+1)
-- Dumped by pg_dump version 17.10 (Debian 17.10-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: about_cards; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.about_cards (
    id uuid NOT NULL,
    title jsonb,
    text jsonb,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: article_badge; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.article_badge (
    article_id uuid NOT NULL,
    badge_id uuid NOT NULL
);


--
-- Name: articles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.articles (
    id uuid NOT NULL,
    slug character varying(255) NOT NULL,
    date date NOT NULL,
    thumbnail_url character varying(255),
    user_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    header json NOT NULL,
    description json,
    content json,
    sort_order integer DEFAULT 0 NOT NULL
);


--
-- Name: badges; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.badges (
    id uuid NOT NULL,
    slug character varying(255) NOT NULL,
    name jsonb NOT NULL,
    color character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: experience_badge; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.experience_badge (
    experience_id bigint NOT NULL,
    badge_id uuid NOT NULL
);


--
-- Name: experiences; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.experiences (
    id bigint NOT NULL,
    type character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    year jsonb,
    url character varying(255),
    image_path character varying(255),
    sort_order smallint DEFAULT '0'::smallint NOT NULL,
    links json,
    is_special boolean DEFAULT false NOT NULL,
    title json NOT NULL,
    subtitle json,
    content json,
    CONSTRAINT experiences_type_check CHECK (((type)::text = ANY ((ARRAY['work'::character varying, 'life'::character varying])::text[])))
);


--
-- Name: experiences_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.experiences_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: experiences_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.experiences_id_seq OWNED BY public.experiences.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.links (
    id uuid NOT NULL,
    project_id uuid NOT NULL,
    alt jsonb NOT NULL,
    img_url character varying(255),
    url character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: project_badge; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_badge (
    project_id uuid NOT NULL,
    badge_id uuid NOT NULL
);


--
-- Name: projects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projects (
    id uuid NOT NULL,
    year smallint NOT NULL,
    slug character varying(255) NOT NULL,
    img_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    header json NOT NULL,
    description json,
    sort_order integer DEFAULT 0 NOT NULL
);


--
-- Name: reviews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reviews (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    "position" jsonb,
    text jsonb,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: stats; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stats (
    id uuid NOT NULL,
    value jsonb,
    text jsonb,
    value_id character varying(255),
    source character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone,
    remember_token character varying(100),
    profile_picture_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_admin boolean DEFAULT false NOT NULL
);


--
-- Name: experiences id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experiences ALTER COLUMN id SET DEFAULT nextval('public.experiences_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Data for Name: about_cards; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.about_cards (id, title, text, sort_order, created_at, updated_at) FROM stdin;
019f8695-c5ba-709b-83db-3ad4bd319747	{"cs": "O mně", "en": "About me"}	{"cs": "Ahoj! Jsem Richard Hývl, začínající softwarový vývojář a freelancer s vášní. V současné době jsem studentem na SPŠE Pardubice a vůdčí osobností skupiny webových vývojářů <span>Prezz.</span>", "en": "Hi there! I'm Richard Hývl, a starting software developer and freelancer with a passion. Currently, I'm a student at SPŠE Pardubice and leading figure in a Web developing group called <span>Prezz.</span>"}	0	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c5c5-71d4-97e2-44236f234bbc	{"cs": "Co mám rád?", "en": "What do I like?"}	{"cs": "Od mládí jsem byl vášnivým šachistou. Ve 2. třídě jsem vyhrával proti středoškolákům na místním šachovém turnaji. S velkou přestávkou jsem zpět, s obnovenou vášní pro hru. Šachy mě naučily <span>kritickému myšlení</span>, <span>strategii</span> a důležité <span>trpělivosti</span> — dovednostem, které považuji za neuvěřitelně cenné na své cestě softwarového vývojáře.<br><br>Mám také opravdu moc rád <span>sumečky</span> a <span>rockovou hudbu</span> :)", "en": "From a young age I was a passionate chess player. I was winning in 2nd grade against highschoolers at local chess tournaments. With a great break I'm back, with a renewed passion for the game. Chess has taught me <span>critical thinking</span>, <span>strategy</span>, and the important <span>patience</span> — skills that I've found incredibly valuable in my journey as a software developer.<br><br>I also really really love <span>catfishes</span> and <span>Rock music</span> :)"}	1	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c5c8-7220-96ca-17f3f1944bfa	{"cs": "Co mě pohání?", "en": "What drives me?"}	{"cs": "Pohání mě zvědavost a touha pomáhat druhým lidem. Rád se učím, rozvíjím svou osobnost a jednou budu úspěšný člověk.", "en": "I'm driven by curiosity and the desire to help other people. I thrive on learning, growing my personality and one day becoming a successful person."}	2	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c5cb-700d-93b1-7ec0a94d69b5	{"cs": "Jak jsme se sem dostali?", "en": "How did we get here?"}	{"cs": "Moje cesta začala poté, co jsem se kvůli zdravotním problémům vrátil z gymnázia na základní školu. Naštěstí měla základní škola rozšířenou výuku v oboru IT.<br><br>Tam jsem se zamiloval do technologií, do neomezených možností vytvářet a inovovat nové věci — bylo to jako sen. Tam jsem vytvořil své první webové stránky. Bohužel je hosting smazal a já neměl žádnou zálohu.<br><br>Na střední škole se mé nadšení rozmohlo ještě víc, což mě dovedlo k několika vítězstvím, jako například vyhrát hackathon a stát se freelancerem.", "en": "My journey started after I went back to elementary school from gymnasium due to health issues. Luckily the elementary school had extended teaching in the field of IT.<br><br>There, I fell in love with the technology, the unlimited options to create and innovate new things — it was like a dream. There, I developed my first website. Sadly, it was deleted by the hosting and I had no backup.<br><br>At high school, my passion thrived even more, leading me to achieve multiple victories, like winning a hackathon and becoming a freelancer."}	3	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c5ce-7278-8c49-8491039891f4	{"cs": "Dobrovolnictví?", "en": "Volunteering?"}	{"cs": "Dobrovolně jsem se účastnil několika komunitních akcí. Pomohlo mi to rozvíjet prezentační dovednosti.<br><br>Čeho jsem byl součástí:<ul><li><p><span>Program PEER:</span> Program, který pomáhá dospívajícím pochopit nebezpečí drog a šikany. Byl jsem vzdělán a poté jsem prezentoval svým vrstevníkům.</p></li><li><p><span>ČESKÝ DEN PROTI RAKOVINĚ:</span> Organizace vytvořená za účelem sbírání finančních prostředků na pomoc lidem s rakovinou prostřednictvím prodeje květinových odznaků na ulicích.</p></li></ul>", "en": "I have volunteered at a few community events. It helped me develop presenting skills.<br><br>What was I part of:<ul><li><p><span>PEER program:</span> A program that helps teenagers understand the dangers of drugs and bullying. I was educated and then presented to my peers.</p></li><li><p><span>CZECH DAY AGAINST CANCER:</span> An organisation that collects funds to help people with cancer by selling flower badges on streets.</p></li></ul>"}	4	2026-07-21 21:29:50	2026-07-21 21:29:50
\.


--
-- Data for Name: article_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.article_badge (article_id, badge_id) FROM stdin;
\.


--
-- Data for Name: articles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.articles (id, slug, date, thumbnail_url, user_id, created_at, updated_at, header, description, content, sort_order) FROM stdin;
\.


--
-- Data for Name: badges; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.badges (id, slug, name, color, created_at, updated_at) FROM stdin;
019f805e-7389-7121-9e04-a65a5b8a49fb	competition	{"cs": "Soutěž", "en": "Competition"}	#EAB308	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-738b-70a6-a9d6-241a9e10eef1	work	{"cs": "Práce", "en": "Work"}	#60A5FA	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-738d-701b-9d4a-f25a4182ed98	certificate	{"cs": "Certifikát", "en": "Certificate"}	#34D399	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-738f-7355-81e7-dc5cfecc15ef	education	{"cs": "Vzdělání", "en": "Education"}	#38BDF8	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-7391-73b6-b1b5-75b1ba171f28	hardware	{"cs": "Hardware", "en": "Hardware"}	#F59E0B	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-7395-7328-8f62-13e0f3bb0a2d	it	{"cs": "IT", "en": "IT"}	#818CF8	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-7398-73d5-a252-9c116d83070c	java	{"cs": "Java", "en": "Java"}	#F97316	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-739b-7129-bdb0-dccce49349c5	php	{"cs": "PHP", "en": "PHP"}	#A78BFA	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-739e-7077-822a-6f4b9fee88e1	python	{"cs": "Python", "en": "Python"}	#2DD4BF	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73a1-7348-b9e4-b9e586559c8a	laravel	{"cs": "Laravel", "en": "Laravel"}	#FF2D20	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73a5-7144-bf43-0f2ba6baa685	symfony	{"cs": "Symfony", "en": "Symfony"}	#64748B	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73a9-72f0-868b-e62d2ee92e41	spring-boot	{"cs": "Spring Boot", "en": "Spring Boot"}	#6DB33F	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73ad-70f5-8590-c19a1caf550f	javascript	{"cs": "JavaScript", "en": "JavaScript"}	#F7DF1E	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73b0-708e-bcea-a43921a95ac3	blockchain	{"cs": "Blockchain", "en": "Blockchain"}	#22D3EE	2026-07-20 16:31:41	2026-07-20 16:31:41
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
portfolio-cache-settings.all	a:7:{s:13:"hero_suptitle";a:2:{s:2:"cs";s:17:"👋 Ahoj světe!";s:2:"en";s:17:"👋 Hello world!";}s:10:"hero_title";a:2:{s:2:"cs";s:34:"Jsem <span>projektant-pata</span>,";s:2:"en";s:35:"I’m <span>projektant-pata</span>,";}s:10:"hero_roles";a:2:{s:2:"cs";a:5:{i:0;s:21:"Full-stack vývojář";i:1;s:9:"Šachista";i:2;s:21:"Spring Boot inženýr";i:3;s:19:"Laravel řemeslník";i:4;s:20:"Řešitel problémů";}s:2:"en";a:5:{i:0;s:20:"Full-stack developer";i:1;s:12:"Chess player";i:2;s:20:"Spring Boot engineer";i:3;s:17:"Laravel craftsman";i:4;s:14:"Problem solver";}}s:11:"tools_title";a:2:{s:2:"cs";s:9:"Nástroje";s:2:"en";s:5:"Tools";}s:13:"reviews_title";a:2:{s:2:"cs";s:9:"Reference";s:2:"en";s:7:"Reviews";}s:11:"about_title";a:2:{s:2:"cs";s:6:"O mně";s:2:"en";s:8:"About me";}s:11:"stats_title";a:2:{s:2:"cs";s:15:"Moje statistiky";s:2:"en";s:8:"My Stats";}}	2100116715
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: experience_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experience_badge (experience_id, badge_id) FROM stdin;
9	019f805e-738b-70a6-a9d6-241a9e10eef1
10	019f805e-7389-7121-9e04-a65a5b8a49fb
10	019f805e-739e-7077-822a-6f4b9fee88e1
11	019f805e-738b-70a6-a9d6-241a9e10eef1
11	019f805e-73ad-70f5-8590-c19a1caf550f
11	019f805e-73b0-708e-bcea-a43921a95ac3
12	019f805e-738b-70a6-a9d6-241a9e10eef1
12	019f805e-739b-7129-bdb0-dccce49349c5
12	019f805e-73a5-7144-bf43-0f2ba6baa685
13	019f805e-738d-701b-9d4a-f25a4182ed98
13	019f805e-7391-73b6-b1b5-75b1ba171f28
14	019f805e-738f-7355-81e7-dc5cfecc15ef
14	019f805e-7398-73d5-a252-9c116d83070c
14	019f805e-739b-7129-bdb0-dccce49349c5
14	019f805e-73a1-7348-b9e4-b9e586559c8a
15	019f805e-7389-7121-9e04-a65a5b8a49fb
15	019f805e-7395-7328-8f62-13e0f3bb0a2d
16	019f805e-738d-701b-9d4a-f25a4182ed98
\.


--
-- Data for Name: experiences; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experiences (id, type, created_at, updated_at, year, url, image_path, sort_order, links, is_special, title, subtitle, content) FROM stdin;
9	work	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2024", "en": "2024"}	\N	images/work/erasmus.png	1	[{"url":"https:\\/\\/erasmus-plus.ec.europa.eu\\/","alt":"Erasmus+"}]	f	{"en":"Erasmus","cs":"Erasmus"}	{"en":"Work trip to Spain made possible by the European Union","cs":"Pracovn\\u00ed cesta do \\u0160pan\\u011blska umo\\u017en\\u011bn\\u00e1 Evropskou uni\\u00ed"}	{"en":"","cs":""}
10	life	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2024", "en": "2024"}	\N	images/work/astropi.png	2	[{"url":"https:\\/\\/www.spse.cz\\/clanek.php?id=1103","alt":"SP\\u0160E"}]	f	{"en":"Hackathon AstroPi","cs":"Hackathon AstroPi"}	{"en":"1st team place","cs":"1. t\\u00fdmov\\u00e9 m\\u00edsto"}	{"en":"Space themed hackathon made possible by ESA, with 2 goals \\u2014 Mission Zero and Mission Space Lab, in a team with Petr Machovec and Ond\\u0159ej Ku\\u010dera.\\n\\n**Mission Zero** \\u2014 a Python program animating an 8x8 display on the AstroPi, reactive to the environment (change of heat or pressure).\\n\\n**Mission Space Lab** \\u2014 a Python program that calculates the speed of the ISS by taking 2 pictures of Earth and matching anchor points.","cs":"Hackathon s vesm\\u00edrnou tematikou, kter\\u00fd umo\\u017enila ESA, se dv\\u011bma c\\u00edli \\u2014 Mission Zero a Mission Space Lab, v t\\u00fdmu s Petrem Machovcem a Ond\\u0159ejem Ku\\u010derou.\\n\\n**Mission Zero** \\u2014 program v Pythonu animovan\\u00fd na displeji 8x8 na AstroPi, reaktivn\\u00ed na prost\\u0159ed\\u00ed (zm\\u011bna tepla nebo tlaku).\\n\\n**Mission Space Lab** \\u2014 program v Pythonu, kter\\u00fd vypo\\u010d\\u00edt\\u00e1 rychlost ISS t\\u00edm, \\u017ee z n\\u00ed po\\u0159\\u00edd\\u00ed 2 sn\\u00edmky Zem\\u011b a n\\u00e1sledn\\u011b porovn\\u00e1 kotevn\\u00ed body."}
11	work	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2024", "en": "2024"}	\N	images/work/byevolution.jpg	3	[{"url":"https:\\/\\/byevolution.com\\/","alt":"ByEvolution"}]	f	{"en":"ByEvolution","cs":"ByEvolution"}	{"en":"Work experience in Erasmus CIT+","cs":"Pracovn\\u00ed st\\u00e1\\u017e na Erasmus CIT+"}	{"en":"I was selected for an Erasmus trip in Spain. Here I worked 14 days in ByEvolution Creative Factory, a firm that specializes in blockchain and crypto technology.\\n\\n- Automatization of application in Selenium\\n- Front-end with HTML, Tailwind, Alpine.js","cs":"Byl jsem vybr\\u00e1n na Erasmus do \\u0160pan\\u011blska. Zde jsem 14 dn\\u00ed pracoval v ByEvolution Creative Factory, firm\\u011b, kter\\u00e1 se specializuje na blockchain a krypto technologie.\\n\\n- Automatizace aplikace v Seleniu\\n- Front-end s HTML, Tailwind, Alpine.js"}
12	work	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2024", "en": "2024"}	\N	images/work/pekneweby.jpg	4	[{"url":"https:\\/\\/www.usladovnychrudim.cz\\/","alt":"U Sladovny"}]	t	{"en":"PekneWeby","cs":"PekneWeby"}	{"en":"Part-time job","cs":"Brig\\u00e1dn\\u00ed pr\\u00e1ce"}	{"en":"Took part in complete rebranding of restaurant U Sladovny in Chrudim.\\n\\n- Took part in Front-end + Back-end of a website made in Symfony\\n- [Website](https:\\/\\/www.usladovnychrudim.cz\\/)","cs":"\\u00da\\u010dastnil jsem se kompletn\\u00edho rebrandingu restaurace U Sladovny v Chrudimi.\\n\\n- \\u00da\\u010dastnil jsem se Front-endu + Back-endu webov\\u00fdch str\\u00e1nek vytvo\\u0159en\\u00fdch v Symfony\\n- [Odkaz zde](https:\\/\\/www.usladovnychrudim.cz\\/)"}
13	life	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2023", "en": "2023"}	\N	images/work/cisco.jpg	5	[{"url":"https:\\/\\/netacad.cz\\/it-essentials\\/","alt":"NetAcad"}]	f	{"en":"Cisco IT Essentials","cs":"Cisco IT Essentials"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}
14	life	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2022 - nyní", "en": "2022 - now"}	\N	images/work/spse.png	6	[{"url":"https:\\/\\/www.spse.cz\\/","alt":"SP\\u0160E"}]	f	{"en":"Student","cs":"Student"}	{"en":"SP\\u0160E a VO\\u0160 Pardubice","cs":"SP\\u0160E a VO\\u0160 Pardubice"}	{"en":"School education with focus on full-stack web development and app development.\\n\\n- HTML + CSS + JS in front-end\\n- PHP + Laravel in back-end\\n- MySQL in databases\\n- Java in app development\\n- Basics in Photoshop and Illustrator\\n- Basics in web design\\n- Developed interest in cybersecurity","cs":"\\u0160koln\\u00ed vzd\\u011bl\\u00e1n\\u00ed se zam\\u011b\\u0159en\\u00edm na full-stack webov\\u00fd v\\u00fdvoj a v\\u00fdvoj aplikac\\u00ed.\\n\\n- HTML + CSS + JS ve front-endu\\n- PHP + Laravel v back-endu\\n- MySQL v datab\\u00e1z\\u00edch\\n- Java ve v\\u00fdvoji aplikac\\u00ed\\n- Z\\u00e1klady Photoshopu a Illustratoru\\n- Z\\u00e1klady webov\\u00e9ho designu\\n- Vyvinut\\u00fd z\\u00e1jem o kybernetickou bezpe\\u010dnost"}
15	life	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2021", "en": "2021"}	\N	images/work/it-slot.png	7	[{"url":"https:\\/\\/www.it-slot.cz\\/results\\/year\\/2021","alt":"IT-Slot"}]	f	{"en":"IT-Slot","cs":"IT-Slot"}	{"en":"11th place out of 8320 students","cs":"11. m\\u00edsto z 8320 student\\u016f"}	{"en":"","cs":""}
16	life	2026-07-20 16:31:41	2026-07-21 17:16:55	{"cs": "2021 - nyní", "en": "2021 - now"}	\N	images/work/ecdl.png	8	[{"url":"https:\\/\\/www.ecdl.cz\\/","alt":"ECDL"}]	f	{"en":"ECDL","cs":"ECDL"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: links; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links (id, project_id, alt, img_url, url, created_at, updated_at) FROM stdin;
019f805e-73bd-7158-951c-33bfdc440b9e	019f805e-73b8-71a5-a477-f00cb7d79456	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://hyvlri22.llmp.spse-net.cz/	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73c2-7140-b517-3c161b2c58b0	019f805e-73b8-71a5-a477-f00cb7d79456	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/SPSE-WP	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73d3-73ff-80df-89d7dc134b33	019f805e-73d0-70f5-8500-7bffe172311b	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://www.usladovnychrudim.cz/	2026-07-20 16:31:41	2026-07-20 16:31:41
019f805e-73e1-7147-86af-fc3526f83832	019f805e-73de-7297-99c8-7f17934dc91f	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/portfolio	2026-07-20 16:31:41	2026-07-20 16:31:41
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_04_01_152222_create_badges_table	1
5	2026_04_01_152223_create_articles_table	1
6	2026_04_01_152223_create_projects_table	1
7	2026_04_01_152224_create_article_badge_table	1
8	2026_04_01_152225_create_links_table	1
9	2026_04_01_152225_create_project_badge_table	1
10	2026_04_02_075634_create_experiences_table	1
11	2026_04_02_082317_add_fields_to_experiences_table	1
12	2026_04_02_181802_add_links_to_experiences_table	1
13	2026_04_02_181802_create_experience_badge_table	1
14	2026_04_02_185808_add_is_special_to_experiences_table	1
15	2026_04_02_192327_change_type_enum_in_experiences_table	1
16	2026_04_02_200956_convert_experience_columns_to_json	1
17	2026_04_07_090758_convert_experience_year_to_json	1
18	2026_04_07_091725_convert_badges_name_to_json	1
19	2026_04_07_094457_convert_articles_columns_to_json	1
20	2026_04_07_100038_convert_projects_columns_to_json	1
21	2026_04_07_100606_convert_links_alt_to_json	1
22	2026_04_10_080904_add_indexes_to_experiences_and_links_tables	1
23	2026_04_10_153357_add_sort_order_to_articles_and_projects_tables	1
24	2026_04_12_165907_seed_badge_colors	1
25	2026_07_21_000001_add_is_admin_to_users_table	2
26	2026_07_21_000002_create_settings_table	3
27	2026_07_21_000003_create_stats_table	3
28	2026_07_21_000004_create_reviews_table	3
29	2026_07_21_000005_create_about_cards_table	3
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: project_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.project_badge (project_id, badge_id) FROM stdin;
019f805e-73b8-71a5-a477-f00cb7d79456	019f805e-73ad-70f5-8590-c19a1caf550f
019f805e-73d0-70f5-8500-7bffe172311b	019f805e-739b-7129-bdb0-dccce49349c5
019f805e-73d0-70f5-8500-7bffe172311b	019f805e-73a5-7144-bf43-0f2ba6baa685
019f805e-73de-7297-99c8-7f17934dc91f	019f805e-739b-7129-bdb0-dccce49349c5
019f805e-73de-7297-99c8-7f17934dc91f	019f805e-73a1-7348-b9e4-b9e586559c8a
\.


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.projects (id, year, slug, img_url, created_at, updated_at, header, description, sort_order) FROM stdin;
019f805e-73b8-71a5-a477-f00cb7d79456	2022	spse-hub	images/projects/spse_wp.png	2026-07-20 16:31:41	2026-07-20 16:31:41	{"en":"SP\\u0160E Hub","cs":"SP\\u0160E Rozcestn\\u00edk"}	{"en":"The SPSE Hub is a project created under the guidance of Mr. Nitrogen to teach how to make a website. It marks my beginnings and has a nostalgic effect on me \\u2013 so I'm including it. Built using HTML5, CSS3, and JavaScript.","cs":"Tento projekt je rozcestn\\u00edk v\\u0161ech webov\\u00fdch str\\u00e1nek, kter\\u00e9 jsem vytvo\\u0159il p\\u0159i studiu na st\\u0159edn\\u00ed \\u0161kole p\\u0159i pln\\u011bn\\u00ed \\u00fakol\\u016f od u\\u010ditele Ren\\u00e9ho \\"Dus\\u00edka\\" Duse. Jsou to jedny z m\\u00fdch prvn\\u00edch str\\u00e1nek (prvn\\u00ed jsme vytv\\u00e1\\u0159eli u\\u017e na z\\u00e1kladn\\u00ed \\u0161kole)."}	0
019f805e-73d0-70f5-8500-7bffe172311b	2025	u-sladovny	images/projects/usladovny.png	2026-07-20 16:31:41	2026-07-20 16:31:41	{"en":"U Sladovny","cs":"U Sladovny"}	{"en":"A project I was part of during my part-time work at PekneWeby. The plan was simple \\u2013 rebrand and rebuild the restaurant. My part was front-end and back-end. And the result? I think PekneWeby did a fabulous job.","cs":"Projekt, na kter\\u00e9m jsem se pod\\u00edlel b\\u011bhem sv\\u00e9 pr\\u00e1ce na \\u010d\\u00e1ste\\u010dn\\u00fd \\u00favazek v PekneWeby. Pl\\u00e1n byl jednoduch\\u00fd - rebranding a rekonstrukce restaurace. M\\u016fj z\\u00e1b\\u011br byl ve front-endu a back-endu. A v\\u00fdsledek? Mysl\\u00edm, \\u017ee firma PekneWeby odvedla b\\u00e1je\\u010dnou pr\\u00e1ci."}	0
019f805e-73de-7297-99c8-7f17934dc91f	2026	portfolio	images/projects/portfolio.png	2026-07-20 16:31:41	2026-07-20 16:31:41	{"en":"Portfolio","cs":"Portf\\u00f3lio"}	{"en":"I think everybody making something artistic should have a portfolio in any form that displays their mind, creativity, and most importantly, personality \\u2013 and I believe that web developers are artists too.","cs":"Mysl\\u00edm si, \\u017ee ka\\u017ed\\u00fd, kdo d\\u011bl\\u00e1 n\\u011bco um\\u011bleck\\u00e9ho, by m\\u011bl m\\u00edt portfolio v jak\\u00e9koli podob\\u011b, kter\\u00e9 ukazuje jeho um, kreativitu a hlavn\\u011b osobnost - a v\\u011b\\u0159\\u00edm, \\u017ee i webov\\u00ed v\\u00fdvoj\\u00e1\\u0159i jsou um\\u011blci."}	0
\.


--
-- Data for Name: reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reviews (id, name, "position", text, sort_order, created_at, updated_at) FROM stdin;
019f8695-c4d1-708e-802a-837cb68af602	Petr Machovec	{"cs": "Spoluzakladatel Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard vždy dodává čistý, efektivní kód a má skvělý smysl pro uživatelsky přívětivý design. Spolehlivý a talentovaný týmový hráč!\\"", "en": "\\"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A reliable and talented team player!\\""}	0	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c4db-72fa-bd2d-1a05fb78615b	ChatGPT	{"cs": "The best AI", "en": "The best AI"}	{"cs": "\\"Richardova oddanost zlepšování svého řemesla a sdílení nápadů z něj dělá inspirativního a cenného kolegu.\\"", "en": "\\"Richard’s commitment to improving his craft and sharing ideas makes him an inspiring and valuable colleague.\\""}	1	2026-07-21 21:29:50	2026-07-21 21:29:50
019f8695-c4de-7109-b22d-11632c3b1515	Ondřej Kučera	{"cs": "Co-founder of Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard se rychle přizpůsobuje novým nástrojům a s důvěrou a kreativitou se vypořádává se složitými projekty.\\"", "en": "\\"Richard adapts quickly to new tools and tackles complex projects with confidence and creativity.\\""}	2	2026-07-21 21:29:50	2026-07-21 21:29:50
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
h0YOKNPBQjBm9F8QE2h43paJKWoQB1X3MYtlVXdO	\N	172.20.0.5	Mozilla/5.0 (X11; Linux x86_64; rv:151.0) Gecko/20100101 Firefox/151.0	eyJfdG9rZW4iOiJJdklqZWJqWHptR01lN09zWnhKMHZwc253bGZrYlQzTnp1YVZzQXZtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BvcnRmb2xpby5wcm9qZWt0YW50LXBhdGEuY3oiLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==	1785168260
HSN835Ds5kdVtFbYKoR3DzwDFxB1dNddMxyHvR02	\N	172.20.0.5	Mozilla/5.0 (X11; Linux x86_64; rv:151.0) Gecko/20100101 Firefox/151.0	eyJfdG9rZW4iOiJiaTVLbWRQMUZqNWZNdGlWeFpISEpxVTl0N0lUVmtDaUF3YWZIM0pxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL3BvcnRmb2xpby5wcm9qZWt0YW50LXBhdGEuY3pcL3Byb2plY3RzIiwicm91dGUiOiJwcm9qZWN0cyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1785168385
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.settings (id, key, value, created_at, updated_at) FROM stdin;
1	hero_suptitle	{"cs": "👋 Ahoj světe!", "en": "👋 Hello world!"}	2026-07-21 21:29:49	2026-07-21 21:33:27
2	hero_title	{"cs": "Jsem <span>projektant-pata</span>,", "en": "I’m <span>projektant-pata</span>,"}	2026-07-21 21:29:49	2026-07-21 21:33:27
8	hero_roles	{"cs": ["Full-stack vývojář", "Šachista", "Spring Boot inženýr", "Laravel řemeslník", "Řešitel problémů"], "en": ["Full-stack developer", "Chess player", "Spring Boot engineer", "Laravel craftsman", "Problem solver"]}	2026-07-21 21:33:27	2026-07-21 21:33:27
5	tools_title	{"cs": "Nástroje", "en": "Tools"}	2026-07-21 21:29:49	2026-07-21 21:33:27
6	reviews_title	{"cs": "Reference", "en": "Reviews"}	2026-07-21 21:29:49	2026-07-21 21:33:27
7	about_title	{"cs": "O mně", "en": "About me"}	2026-07-21 21:29:49	2026-07-21 21:33:27
4	stats_title	{"cs": "Moje statistiky", "en": "My Stats"}	2026-07-21 21:29:49	2026-07-21 21:39:54
\.


--
-- Data for Name: stats; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.stats (id, value, text, value_id, source, sort_order, created_at, updated_at) FROM stdin;
019f8695-c3e2-7171-91c7-79109d53ba36	{"cs": "Junior", "en": "Junior"}	{"cs": "Profesionální úroveň", "en": "Professional Level"}	\N	\N	0	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3ec-705e-925f-e68cd2eb2106	{"cs": "5+", "en": "5+"}	{"cs": "Projektů dokončeno", "en": "Projects Completed"}	\N	\N	1	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3ef-7019-9f56-0c0b9507da80	\N	{"cs": "Roky zkušeností", "en": "Years of experience"}	\N	years_experience	2	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3f1-72d0-87aa-19f073547145	{"cs": "2", "en": "2"}	{"cs": "Země dosaženy", "en": "Countries Reached"}	\N	\N	3	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3f3-7003-87fe-5b80cdeed8af	\N	{"cs": "Let věku", "en": "Years old"}	\N	age	4	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3f5-72a3-a240-2b4af3686cda	{"cs": "Načítání..", "en": "Loading.."}	{"cs": "Nejvyšší šachové elo", "en": "Highest chess elo"}	elo	\N	5	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3f7-71d3-8fdd-1a7283d20787	{"cs": "♞", "en": "♞"}	{"cs": "Nejoblíbenější figura", "en": "Favorite piece"}	\N	\N	6	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3f9-7000-94b5-1730f607eb7f	{"cs": "∞", "en": "∞"}	{"cs": "Vypitých šálků kávy", "en": "Coffee consumed"}	\N	\N	7	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3fb-7075-817b-c7ed61a687c9	{"cs": "4", "en": "4"}	{"cs": "Vyhraný hackathon", "en": "Hackathons won"}	\N	\N	8	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3fd-7285-b8cf-4947d705f6b3	{"cs": "18", "en": "18"}	{"cs": "GitHub repozitářů", "en": "GitHub repositories"}	github-repos	\N	9	2026-07-21 21:29:49	2026-07-21 21:29:49
019f8695-c3ff-7277-af4d-13134126e282	{"cs": "404", "en": "404"}	{"cs": "Hodin spánku", "en": "Hours slept"}	\N	\N	10	2026-07-21 21:29:49	2026-07-21 21:29:49
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, remember_token, profile_picture_url, created_at, updated_at, is_admin) FROM stdin;
019f805e-737e-716d-8598-f2bc669c5d32	Richard Hyvl	richard.hyvl@gmail.com	2026-07-20 16:31:41	$2y$12$U/9evRx6sFqmmrOhukcSIe07nd6BENF9TWrN6O85nMpHyNNzmqdqa	\N	\N	\N	\N	\N	2026-07-20 16:31:41	2026-07-21 21:25:37	t
\.


--
-- Name: experiences_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.experiences_id_seq', 16, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 29, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.settings_id_seq', 8, true);


--
-- Name: about_cards about_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.about_cards
    ADD CONSTRAINT about_cards_pkey PRIMARY KEY (id);


--
-- Name: article_badge article_badge_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.article_badge
    ADD CONSTRAINT article_badge_pkey PRIMARY KEY (article_id, badge_id);


--
-- Name: articles articles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_pkey PRIMARY KEY (id);


--
-- Name: articles articles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_slug_unique UNIQUE (slug);


--
-- Name: badges badges_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.badges
    ADD CONSTRAINT badges_pkey PRIMARY KEY (id);


--
-- Name: badges badges_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.badges
    ADD CONSTRAINT badges_slug_unique UNIQUE (slug);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: experience_badge experience_badge_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experience_badge
    ADD CONSTRAINT experience_badge_pkey PRIMARY KEY (experience_id, badge_id);


--
-- Name: experiences experiences_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experiences
    ADD CONSTRAINT experiences_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: links links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links
    ADD CONSTRAINT links_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: project_badge project_badge_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_badge
    ADD CONSTRAINT project_badge_pkey PRIMARY KEY (project_id, badge_id);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: projects projects_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_slug_unique UNIQUE (slug);


--
-- Name: reviews reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: stats stats_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stats
    ADD CONSTRAINT stats_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: experiences_sort_order_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX experiences_sort_order_index ON public.experiences USING btree (sort_order);


--
-- Name: experiences_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX experiences_type_index ON public.experiences USING btree (type);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: links_project_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX links_project_id_index ON public.links USING btree (project_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: article_badge article_badge_article_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.article_badge
    ADD CONSTRAINT article_badge_article_id_foreign FOREIGN KEY (article_id) REFERENCES public.articles(id) ON DELETE CASCADE;


--
-- Name: article_badge article_badge_badge_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.article_badge
    ADD CONSTRAINT article_badge_badge_id_foreign FOREIGN KEY (badge_id) REFERENCES public.badges(id) ON DELETE CASCADE;


--
-- Name: articles articles_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: experience_badge experience_badge_badge_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experience_badge
    ADD CONSTRAINT experience_badge_badge_id_foreign FOREIGN KEY (badge_id) REFERENCES public.badges(id) ON DELETE CASCADE;


--
-- Name: experience_badge experience_badge_experience_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.experience_badge
    ADD CONSTRAINT experience_badge_experience_id_foreign FOREIGN KEY (experience_id) REFERENCES public.experiences(id) ON DELETE CASCADE;


--
-- Name: links links_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.links
    ADD CONSTRAINT links_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: project_badge project_badge_badge_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_badge
    ADD CONSTRAINT project_badge_badge_id_foreign FOREIGN KEY (badge_id) REFERENCES public.badges(id) ON DELETE CASCADE;


--
-- Name: project_badge project_badge_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_badge
    ADD CONSTRAINT project_badge_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict hjPxWczsfE05y2BrgsMlFeslYhtVAG9qu3imj8S1h00CZsWnF0aBiiZusY4OlzF

