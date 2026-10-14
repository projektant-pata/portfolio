--
-- PostgreSQL database dump
--

\restrict vGS1WxhKy92BQdRwt8s47blv2WneVqQpITkwLpW7wLa9gWfEGdXExtGiwr1P4bI

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
    sort_order integer DEFAULT 0 NOT NULL,
    published_at timestamp(0) without time zone
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
    show_on_about boolean DEFAULT false NOT NULL,
    CONSTRAINT experiences_type_check CHECK (((type)::text = ANY (ARRAY[('work'::character varying)::text, ('life'::character varying)::text])))
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
-- Name: interests; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.interests (
    id uuid NOT NULL,
    title jsonb,
    highlight jsonb,
    body jsonb,
    meta jsonb,
    image_path character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    label jsonb,
    pull jsonb,
    facts jsonb
);


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
    alt jsonb,
    img_url character varying(255),
    url character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    kind character varying(20) DEFAULT 'live'::character varying NOT NULL
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
-- Name: path_nodes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.path_nodes (
    id uuid NOT NULL,
    year jsonb,
    title jsonb,
    body jsonb,
    is_now boolean DEFAULT false NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
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
    sort_order integer DEFAULT 0 NOT NULL,
    kind character varying(20) DEFAULT 'personal'::character varying NOT NULL,
    client character varying(255),
    status character varying(20),
    role json
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
    updated_at timestamp(0) without time zone,
    highlight jsonb,
    source character varying(255),
    source_color character varying(255)
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
    updated_at timestamp(0) without time zone,
    tier character varying(255) DEFAULT 'normal'::character varying NOT NULL,
    note jsonb
);


--
-- Name: tools; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tools (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    color character varying(255),
    state character varying(255),
    "group" jsonb,
    placement character varying(255) DEFAULT 'grid'::character varying NOT NULL,
    show_on_home boolean DEFAULT false NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    group_note jsonb
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
-- Data for Name: article_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.article_badge (article_id, badge_id) FROM stdin;
\.


--
-- Data for Name: articles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.articles (id, slug, date, thumbnail_url, user_id, created_at, updated_at, header, description, content, sort_order, published_at) FROM stdin;
\.


--
-- Data for Name: badges; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.badges (id, slug, name, color, created_at, updated_at) FROM stdin;
019f805e-7389-7121-9e04-a65a5b8a49fb	competition	{"cs": "Soutěž", "en": "Competition"}	#EAB308	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-738b-70a6-a9d6-241a9e10eef1	work	{"cs": "Práce", "en": "Work"}	#60A5FA	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-738d-701b-9d4a-f25a4182ed98	certificate	{"cs": "Certifikát", "en": "Certificate"}	#34D399	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-738f-7355-81e7-dc5cfecc15ef	education	{"cs": "Vzdělání", "en": "Education"}	#38BDF8	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-7391-73b6-b1b5-75b1ba171f28	hardware	{"cs": "Hardware", "en": "Hardware"}	#F59E0B	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-7395-7328-8f62-13e0f3bb0a2d	it	{"cs": "IT", "en": "IT"}	#818CF8	2026-07-20 16:31:41	2026-08-29 09:35:22
01a04cde-4c5c-70a0-a027-d975e5322370	hackathon	{"cs": "Hackathon", "en": "Hackathon"}	#F472B6	2026-08-29 09:33:52	2026-08-29 09:35:22
01a04cde-4c5f-7291-a05a-e0288185bbf9	non-profit	{"cs": "Nezisková organizace", "en": "Non-profit"}	#4ADE80	2026-08-29 09:33:52	2026-08-29 09:35:22
019f805e-7398-73d5-a252-9c116d83070c	java	{"cs": "Java", "en": "Java"}	#F97316	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-739b-7129-bdb0-dccce49349c5	php	{"cs": "PHP", "en": "PHP"}	#A78BFA	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-739e-7077-822a-6f4b9fee88e1	python	{"cs": "Python", "en": "Python"}	#2DD4BF	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-73a1-7348-b9e4-b9e586559c8a	laravel	{"cs": "Laravel", "en": "Laravel"}	#FF2D20	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-73a5-7144-bf43-0f2ba6baa685	symfony	{"cs": "Symfony", "en": "Symfony"}	#64748B	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-73a9-72f0-868b-e62d2ee92e41	spring-boot	{"cs": "Spring Boot", "en": "Spring Boot"}	#6DB33F	2026-07-20 16:31:41	2026-08-29 09:35:22
019f805e-73ad-70f5-8590-c19a1caf550f	javascript	{"cs": "JavaScript", "en": "JavaScript"}	#F7DF1E	2026-07-20 16:31:41	2026-08-29 09:35:22
01a04cde-4c70-7038-b110-50814a54e438	typescript	{"cs": "TypeScript", "en": "TypeScript"}	#3178C6	2026-08-29 09:33:52	2026-08-29 09:35:22
01a04cde-4c72-7382-ba8e-2f46b52ae9cf	html	{"cs": "HTML", "en": "HTML"}	#E34F26	2026-08-29 09:33:52	2026-08-29 09:35:22
01a04cde-4c73-71a0-818d-72be875a5fdc	css	{"cs": "CSS", "en": "CSS"}	#2965F1	2026-08-29 09:33:52	2026-08-29 09:35:22
019f805e-73b0-708e-bcea-a43921a95ac3	blockchain	{"cs": "Blockchain", "en": "Blockchain"}	#22D3EE	2026-07-20 16:31:41	2026-08-29 09:35:22
\.


--
-- Data for Name: experience_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experience_badge (experience_id, badge_id) FROM stdin;
61	019f805e-738b-70a6-a9d6-241a9e10eef1
62	019f805e-7389-7121-9e04-a65a5b8a49fb
62	019f805e-739e-7077-822a-6f4b9fee88e1
63	019f805e-738b-70a6-a9d6-241a9e10eef1
63	019f805e-73ad-70f5-8590-c19a1caf550f
63	019f805e-73b0-708e-bcea-a43921a95ac3
64	019f805e-738b-70a6-a9d6-241a9e10eef1
64	019f805e-739b-7129-bdb0-dccce49349c5
64	019f805e-73a5-7144-bf43-0f2ba6baa685
65	019f805e-738d-701b-9d4a-f25a4182ed98
65	019f805e-7391-73b6-b1b5-75b1ba171f28
66	019f805e-738f-7355-81e7-dc5cfecc15ef
66	019f805e-7398-73d5-a252-9c116d83070c
66	019f805e-739b-7129-bdb0-dccce49349c5
66	019f805e-73a1-7348-b9e4-b9e586559c8a
67	019f805e-7389-7121-9e04-a65a5b8a49fb
67	019f805e-7395-7328-8f62-13e0f3bb0a2d
68	019f805e-738d-701b-9d4a-f25a4182ed98
70	019f805e-7389-7121-9e04-a65a5b8a49fb
72	01a04cde-4c5f-7291-a05a-e0288185bbf9
71	01a04cde-4c5f-7291-a05a-e0288185bbf9
68	019f805e-7395-7328-8f62-13e0f3bb0a2d
62	019f805e-7395-7328-8f62-13e0f3bb0a2d
\.


--
-- Data for Name: experiences; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experiences (id, type, created_at, updated_at, year, url, image_path, sort_order, links, is_special, title, subtitle, content, show_on_about) FROM stdin;
61	life	2026-08-29 09:35:22	2026-08-29 15:17:08	{"cs": "2024", "en": "2024"}	\N	storage/experiences/LO0LqtA4zbERxOM2shhPWZw8bYali4aG31vQsp8s.jpg	1	[{"url":"https:\\/\\/erasmus-plus.ec.europa.eu\\/","alt":"Erasmus+"}]	f	{"en":"Erasmus CIT+","cs":"Erasmus CIT+"}	{"en":"Work trip to Spain made possible by the European Union","cs":"Pracovn\\u00ed cesta do \\u0160pan\\u011blska umo\\u017en\\u011bn\\u00e1 Evropskou uni\\u00ed"}	{"en":"","cs":""}	f
63	work	2026-08-29 09:35:22	2026-08-29 15:15:33	{"cs": "2024", "en": "2024"}	\N	storage/experiences/IprQDd8qvwp0UmOpJoF9GuAW1ph86keyMY6Us4Ef.jpg	3	[{"url":"https:\\/\\/byevolution.com\\/","alt":"ByEvolution"}]	f	{"en":"ByEvolution","cs":"ByEvolution"}	{"en":"Work experience in Erasmus CIT+","cs":"Pracovn\\u00ed st\\u00e1\\u017e na Erasmus CIT+"}	{"en":"I was selected for an Erasmus trip in Spain. Here I worked 14 days in ByEvolution Creative Factory, a firm that specializes in blockchain and crypto technology.\\n\\n- Automatization of application in Selenium\\n- Front-end with HTML, Tailwind, Alpine.js","cs":"Byl jsem vybr\\u00e1n na Erasmus do \\u0160pan\\u011blska. Zde jsem 14 dn\\u00ed pracoval v ByEvolution Creative Factory, firm\\u011b, kter\\u00e1 se specializuje na blockchain a krypto technologie.\\n\\n- Automatizace aplikace v Seleniu\\n- Front-end s HTML, Tailwind, Alpine.js"}	f
71	life	2026-08-29 09:35:22	2026-08-29 15:14:27	{"cs": "2023-2025", "en": "2023-2025"}	\N	storage/experiences/LO0LqtA4zbERxOM2shhPWZw8bYali4aG31vQsp8s.jpg	11	\N	f	{"en":"PEER program","cs":"Program PEER"}	{"en":"Trained to teach teenagers about drugs and bullying, then presented to my own peers.","cs":"Vy\\u0161kolen, abych u\\u010dil teenagery o drog\\u00e1ch a \\u0161ikan\\u011b, a pak jsem prezentoval vlastn\\u00edm vrstevn\\u00edk\\u016fm."}	{"en":"","cs":""}	t
64	work	2026-08-29 09:35:22	2026-08-29 15:15:33	{"cs": "2024", "en": "2024"}	\N	storage/experiences/h12A89ck9ARq4rUMEq6eJkkOGfbMLfpfg93WY7p0.jpg	4	[{"url":"https:\\/\\/www.usladovnychrudim.cz\\/","alt":"U Sladovny"}]	t	{"en":"PekneWeby","cs":"PekneWeby"}	{"en":"Part-time job","cs":"Brig\\u00e1dn\\u00ed pr\\u00e1ce"}	{"en":"Took part in complete rebranding of restaurant U Sladovny in Chrudim.\\n\\n- Took part in Front-end + Back-end of a website made in Symfony\\n- [Website](https:\\/\\/www.usladovnychrudim.cz\\/)","cs":"\\u00da\\u010dastnil jsem se kompletn\\u00edho rebrandingu restaurace U Sladovny v Chrudimi.\\n\\n- \\u00da\\u010dastnil jsem se Front-endu + Back-endu webov\\u00fdch str\\u00e1nek vytvo\\u0159en\\u00fdch v Symfony\\n- [Odkaz zde](https:\\/\\/www.usladovnychrudim.cz\\/)"}	f
65	life	2026-08-29 09:35:22	2026-08-29 15:15:33	{"cs": "2023", "en": "2023"}	\N	storage/experiences/B1RRobgMp6Ofrt0MRtEdd87r41fpZvpMEewMxnPl.jpg	5	[{"url":"https:\\/\\/netacad.cz\\/it-essentials\\/","alt":"NetAcad"}]	f	{"en":"Cisco IT Essentials","cs":"Cisco IT Essentials"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}	f
68	life	2026-08-29 09:35:22	2026-08-29 15:15:33	{"cs": "2021 - nyní", "en": "2021 - now"}	\N	storage/experiences/Oi12VGaPqM2vxanB609MgKwgXAIoUW4HkKtp8mA5.png	8	[{"url":"https:\\/\\/www.ecdl.cz\\/","alt":"ECDL"}]	f	{"en":"ECDL","cs":"ECDL"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}	f
72	life	2026-08-29 09:35:22	2026-08-29 15:15:33	{"cs": "2023", "en": "2023"}	\N	storage/experiences/F47932dU1qiugIpo1s4QC1tifMCj48l4jFPCRrwQ.png	12	\N	f	{"en":"Czech Day Against Cancer","cs":"\\u010cesk\\u00fd den proti rakovin\\u011b"}	{"en":"Sold flower badges on the street for cancer patients. The best public-speaking practice there is.","cs":"Prod\\u00e1val jsem kv\\u011btinov\\u00e9 odznaky na ulici pro pacienty s rakovinou. Nejlep\\u0161\\u00ed tr\\u00e9nink ve\\u0159ejn\\u00e9ho projevu, jak\\u00fd existuje."}	{"en":"","cs":""}	t
66	life	2026-08-29 09:35:22	2026-08-29 15:16:08	{"cs": "2022-2026", "en": "2022-2026"}	\N	storage/experiences/LO0LqtA4zbERxOM2shhPWZw8bYali4aG31vQsp8s.jpg	6	[{"url":"https:\\/\\/www.spse.cz\\/","alt":"SP\\u0160E"}]	f	{"en":"Student","cs":"Student"}	{"en":"SP\\u0160E a VO\\u0160 Pardubice","cs":"SP\\u0160E a VO\\u0160 Pardubice"}	{"en":"School education with focus on full-stack web development and app development.\\n\\n- HTML + CSS + JS in front-end\\n- PHP + Laravel in back-end\\n- MySQL in databases\\n- Java in app development\\n- Basics in Photoshop and Illustrator\\n- Basics in web design\\n- Developed interest in cybersecurity","cs":"\\u0160koln\\u00ed vzd\\u011bl\\u00e1n\\u00ed se zam\\u011b\\u0159en\\u00edm na full-stack webov\\u00fd v\\u00fdvoj a v\\u00fdvoj aplikac\\u00ed.\\n\\n- HTML + CSS + JS ve front-endu\\n- PHP + Laravel v back-endu\\n- MySQL v datab\\u00e1z\\u00edch\\n- Java ve v\\u00fdvoji aplikac\\u00ed\\n- Z\\u00e1klady Photoshopu a Illustratoru\\n- Z\\u00e1klady webov\\u00e9ho designu\\n- Vyvinut\\u00fd z\\u00e1jem o kybernetickou bezpe\\u010dnost"}	f
70	work	2026-08-29 09:35:22	2026-08-29 15:23:14	{"cs": "2026", "en": "2026"}	\N	storage/experiences/4SUoVWLWnEj9MKXeJCVrKfdADgVxUTkyBMlZ1ffV.png	10	[{"url":"https:\\/\\/www.spse.cz\\/clanky\\/2026-regionalni-sampionat-skol-sachovy-turnaj","alt":"SPSE"}]	f	{"en":"Regional chess competetion of schools","cs":"Krajsk\\u00fd \\u0161achov\\u00fd turnaj \\u0161kol"}	{"en":"1st place in chess competetion","cs":"1. m\\u00edsto v \\u0161achov\\u00e9m turnaji"}	{"en":"","cs":""}	t
67	work	2026-08-29 09:35:22	2026-08-29 15:23:14	{"cs": "2021", "en": "2021"}	\N	storage/experiences/bXKQc2E8bGETCydIbHrCiOOEXCshl26k30oTqvKu.png	7	[{"url":"https:\\/\\/www.it-slot.cz\\/results\\/year\\/2021","alt":"IT-Slot"}]	f	{"en":"IT-Slot","cs":"IT-Slot"}	{"en":"11th place out of 8320 students","cs":"11. m\\u00edsto z 8320 student\\u016f"}	{"en":"","cs":""}	f
62	work	2026-08-29 09:35:22	2026-08-29 15:23:14	{"cs": "2024", "en": "2024"}	\N	storage/experiences/mQPLYyUrNW0WX0iQ6O60S9bbHqxM1xcOjgOdYxsl.png	2	[{"url":"https:\\/\\/www.spse.cz\\/clanek.php?id=1103","alt":"SP\\u0160E"}]	f	{"en":"Hackathon AstroPi","cs":"Hackathon AstroPi"}	{"en":"1st team place","cs":"1. t\\u00fdmov\\u00e9 m\\u00edsto"}	{"en":"Space themed hackathon made possible by ESA, with 2 goals \\u2014 Mission Zero and Mission Space Lab, in a team with Petr Machovec and Ond\\u0159ej Ku\\u010dera.\\n\\n**Mission Zero** \\u2014 a Python program animating an 8x8 display on the AstroPi, reactive to the environment (change of heat or pressure).\\n\\n**Mission Space Lab** \\u2014 a Python program that calculates the speed of the ISS by taking 2 pictures of Earth and matching anchor points.","cs":"Hackathon s vesm\\u00edrnou tematikou, kter\\u00fd umo\\u017enila ESA, se dv\\u011bma c\\u00edli \\u2014 Mission Zero a Mission Space Lab, v t\\u00fdmu s Petrem Machovcem a Ond\\u0159ejem Ku\\u010derou.\\n\\n**Mission Zero** \\u2014 program v Pythonu animovan\\u00fd na displeji 8x8 na AstroPi, reaktivn\\u00ed na prost\\u0159ed\\u00ed (zm\\u011bna tepla nebo tlaku).\\n\\n**Mission Space Lab** \\u2014 program v Pythonu, kter\\u00fd vypo\\u010d\\u00edt\\u00e1 rychlost ISS t\\u00edm, \\u017ee z n\\u00ed po\\u0159\\u00edd\\u00ed 2 sn\\u00edmky Zem\\u011b a n\\u00e1sledn\\u011b porovn\\u00e1 kotevn\\u00ed body."}	f
73	work	2026-08-29 15:23:14	2026-08-29 15:23:14	{"cs": "2026", "en": "2026"}	\N	\N	13	[]	f	{"en":"Funovation 26","cs":"Funovation 26"}	{"en":"","cs":""}	{"en":"","cs":""}	t
74	work	2026-08-29 15:23:14	2026-08-29 15:23:14	{"cs": "2025", "en": "2025"}	\N	\N	14	[]	f	{"en":"OpenData KHK","cs":"OpenData KHK"}	{"en":"","cs":""}	{"en":"","cs":""}	t
75	work	2026-08-29 15:23:14	2026-08-29 15:23:14	{"cs": "2026", "en": "2026"}	\N	\N	15	[]	f	{"en":"Hackathon NKU","cs":"Hackathon NKU"}	{"en":"","cs":""}	{"en":"","cs":""}	t
76	work	2026-08-29 15:23:14	2026-08-29 15:23:14	{"cs": "2026", "en": "2026"}	\N	\N	16	[]	f	{"en":"Tour-de-App26","cs":"Tour-de-App26"}	{"en":"","cs":""}	{"en":"","cs":""}	t
\.


--
-- Data for Name: interests; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.interests (id, title, highlight, body, meta, image_path, sort_order, created_at, updated_at, label, pull, facts) FROM stdin;
01a04cdf-ad20-73d6-bd43-e7fc0d8a4db1	{"cs": "Šachy od druhé třídy", "en": "Chess since the second grade"}	{"cs": "druhé třídy", "en": "second grade"}	{"cs": "Začal jsem, protože učiteli chyběl jeden hráč do školního týmu, a zůstal jsem, protože mi to dlouho nešlo. Místní turnaje znamenaly sedět naproti středoškolákům, kteří hráli déle, než jsem byl na světě, prohrávat a stejně přijít i příští neděli.\\n\\nJe to jediný koníček, který okamžitě trestá netrpělivost. Pozici nelze zablufovat — buď jste důsledek viděli tři tahy dopředu, nebo ne, a šachovnice vám to řekne do minuty. 1684 je moje nejvyšší rating, což je slušné a zdaleka ne dobré, a s oběma polovinami té věty jsem smířený.", "en": "I started because a teacher needed one more kid to fill out the school team, and stayed because I was bad at it for a long time. Local tournaments meant sitting across from high-schoolers who had been playing longer than I had been alive, losing, and coming back the next Sunday anyway.\\n\\nIt is the only hobby I have that punishes impatience immediately. You cannot bluff a position — either you saw the consequence three moves ago or you did not, and the board tells you which within a minute. 1684 is my peak rating, which is respectable and nowhere near good, and I am fine with both halves of that sentence."}	{"cs": [], "en": []}	\N	0	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "Šachy", "en": "Chess"}	{"cs": "Šachy mě naučily prohrát něco, na čem mi záleželo, a přesto přijít i příští týden.", "en": "Chess is where I learned to lose something I cared about and still show up the following week."}	{"cs": [{"dd": "1684 elo", "dt": "Nejvyšší rating"}, {"dd": "2. třídy", "dt": "Hraju od"}, {"dd": "Jezdec", "dt": "Oblíbená figura"}], "en": [{"dd": "1684 elo", "dt": "Peak rating"}, {"dd": "2nd grade", "dt": "Playing since"}, {"dd": "Knight", "dt": "Favourite piece"}]}
01a04cdf-ad22-73ce-ac45-a5eb8b1c7c80	{"cs": "Police počítačů, které schválně rozbíjím", "en": "A shelf of computers I break on purpose"}	{"cs": "schválně rozbíjím", "en": "break on purpose"}	{"cs": "Tři Raspberry Pi a jeden malý Proxmox box na dřevěné polici, špatně zapojené a ještě hůř popsané. Dohromady na nich běží moje úložiště souborů, monitorovací stack, který mě upozorní, když něco spadne, a sada MQTT senzorů, které loguju teplotu bytu, kterou po mně nikdo nechtěl.\\n\\nVšechno, co umím o Linuxu, Dockeru a síťování, umím proto, že jsem si tenhle rack nejdřív rozbil sám. Doma žádné staging prostředí není — vypnete službu, přestanou fungovat vlastní věci a opravujete to v jedenáct večer, protože chcete zpátky svoje soubory. To je rychlejší učitel než jakýkoliv tutoriál.", "en": "Three Raspberry Pis and one small Proxmox box on a wooden shelf, wired badly, labelled worse. Between them they run my file storage, a monitoring stack that pages me when something falls over, and a set of MQTT sensors that log the temperature of a flat nobody asked me to measure.\\n\\nEverything I know about Linux, Docker and networking, I know because I broke this rack first. There is no staging environment at home — you take the service down, your own stuff stops working, and you fix it at 11pm because you want your files back. That is a faster teacher than any tutorial."}	{"cs": [], "en": []}	\N	1	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "Homelab", "en": "Homelab"}	{"cs": null, "en": null}	{"cs": [{"dd": "3× Pi + 1× Proxmox host", "dt": "Uzly"}, {"dd": "Úložiště · monitoring · MQTT", "dt": "Běží tam"}, {"dd": "Víckrát, než přiznávám", "dt": "Přestavěno"}], "en": [{"dd": "3 Pis + 1 Proxmox host", "dt": "Nodes"}, {"dd": "Storage · monitoring · MQTT", "dt": "Running"}, {"dd": "More times than I admit", "dt": "Rebuilt"}]}
01a04cdf-ad23-71be-aa08-9dcd2c1bfe01	{"cs": "V posilovně odkládám telefon", "en": "The gym is where I put the phone down"}	{"cs": "odkládám telefon", "en": "put the phone down"}	{"cs": "Čtyři tréninky týdně, a takhle to je už pár let. Ne kvůli číslu na čince — kvůli tomu, že je to jediná hodina dne bez notifikací, ticketů a rozdělané branch, která na mě čeká.\\n\\nSpravilo mi to i něco, co kód nedokázal. Pokrok v posilovně je pomalý, nudný a naprosto upřímný, a zvyknout si na to mě zlepšilo v dlouhých projektech, které vás první tři týdny nijak neodmění.", "en": "Four sessions a week, and it has been that way for a couple of years now. Not for a number on a bar — for the fact that it is the one hour of my day with no notifications, no tickets and no half-finished branch waiting for me.\\n\\nIt also fixed something the code could not. Progress in the gym is slow, boring and completely honest, and getting used to that made me much better at long projects that do not reward you for the first three weeks."}	{"cs": [], "en": []}	\N	2	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "Sport", "en": "Sport"}	{"cs": "Ukázat se i ve špatný den je dovednost, a přenáší se dál.", "en": "Showing up on the bad days is a skill, and it transfers."}	{"cs": [{"dd": "4× týdně", "dt": "Frekvence"}, {"dd": "2023", "dt": "Od"}, {"dd": "Skříňka", "dt": "Politika telefonu"}], "en": [{"dd": "4× a week", "dt": "Frequency"}, {"dd": "2023", "dt": "Since"}, {"dd": "Locker", "dt": "Phone policy"}]}
01a04cdf-ad24-73d2-acce-757081df607b	{"cs": "Vlaky a až příliš pečlivě naplánovaná trasa", "en": "Trains, and a route planned too carefully"}	{"cs": "příliš pečlivě naplánovaná trasa", "en": "a route planned too carefully"}	{"cs": "Radši strávím jedenáct hodin ve vlaku než dvě hodiny v letadle. Půlka cesty se pro mě odehrává ještě předtím, než začne: jízdní řády, přípoje, jeden excel, který nikomu neukážu, a plán volný tak akorát, aby zmeškaný přípoj byl historka, ne problém.\\n\\nZatím šest zemí, hlavně po střední Evropě, obvykle s notebookem, který jsem slíbil neotevřít. Co mi z toho zůstalo, je drobnost: všude, kde jsem byl, věci fungují, protože se je někdo obtěžoval udělat funkční — značení, jízdenkový automat, jízdní řád. Jakmile začnete věci stavět sami, nejde si toho nevšímat.", "en": "I would rather spend eleven hours on a train than two in the air. Half of the trip, for me, happens before it starts: timetables, connections, one spreadsheet I refuse to show anyone, and a plan loose enough that a missed connection is a story instead of a problem.\\n\\nSix countries so far, mostly around central Europe, usually with a laptop I promised not to open. The part that stuck is small: everywhere I have been, things work because somebody bothered to make them work — the signage, the ticket machine, the timetable. It is hard to unsee that once you start building things yourself."}	{"cs": [], "en": []}	\N	3	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "Cestování", "en": "Travel"}	{"cs": null, "en": null}	{"cs": [{"dd": "6 a přibývá", "dt": "Zemí"}, {"dd": "Vlak před letadlem", "dt": "Preference"}, {"dd": "Severní trasa", "dt": "Další v plánu"}], "en": [{"dd": "6 and counting", "dt": "Countries"}, {"dd": "Rail over air", "dt": "Preferred"}, {"dd": "Northern route", "dt": "Next up"}]}
\.


--
-- Data for Name: links; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links (id, project_id, alt, img_url, url, created_at, updated_at, kind) FROM stdin;
01a04cdf-ac6a-721b-ab50-5df3e27c0473	01a04cdf-ac67-7239-85f9-52ff91f4fd20	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://hyvlri22.llmp.spse-net.cz/	2026-08-29 09:35:22	2026-08-29 09:35:22	live
01a04cdf-ac6c-7043-b910-774a3d442f0f	01a04cdf-ac67-7239-85f9-52ff91f4fd20	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/SPSE-WP	2026-08-29 09:35:22	2026-08-29 09:35:22	repo
01a04cdf-ac73-72e7-abcb-07262c4d3dff	01a04cdf-ac72-7287-901b-f3a22daa1bae	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://www.usladovnychrudim.cz/	2026-08-29 09:35:22	2026-08-29 09:35:22	live
01a04cdf-ac77-7330-bf1b-8556239cea46	01a04cdf-ac76-72cb-855c-e5e69c67a7aa	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/portfolio	2026-08-29 09:35:22	2026-08-29 09:35:22	repo
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
26	2026_07_21_000002_create_settings_table	2
27	2026_07_21_000003_create_stats_table	2
28	2026_07_21_000004_create_reviews_table	2
29	2026_07_21_000005_create_about_cards_table	2
30	2026_08_01_093443_add_source_and_highlight_to_reviews_table	3
31	2026_08_15_000000_add_published_at_to_articles_table	4
32	2026_08_15_000001_add_metadata_to_projects_and_links_tables	4
33	2026_08_15_000002_drop_not_null_from_links_alt	4
34	2026_08_17_000001_create_path_nodes_table	4
35	2026_08_17_000002_create_interests_table	4
36	2026_08_17_000003_add_tier_and_note_to_stats_table	4
37	2026_08_17_000004_add_show_on_about_to_experiences_table	4
38	2026_08_17_000005_drop_about_cards_table	4
39	2026_08_18_000001_create_tools_table	4
40	2026_08_26_083640_add_chapter_fields_to_interests_table	5
42	2026_08_26_091941_replace_tool_frequency_with_state	6
\.


--
-- Data for Name: path_nodes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.path_nodes (id, year, title, body, is_now, sort_order, created_at, updated_at) FROM stdin;
01a04cdf-ad17-729f-b1ff-68ae77bfabd9	{"cs": "2019", "en": "2019"}	{"cs": "První dotek s IT", "en": "First touch with IT"}	{"cs": "Nastoupil jsem do třídy s rozšířenou výukou IT — první dotek se světem IT, dotek, který rozhodl o mé kariéře. Od té doby jsem na technologiích závislý.", "en": "Took an IT extended class — the first touch with the world of IT, the touch that decided my career. Ever since, I have been hooked on technology."}	f	0	2026-08-29 09:35:22	2026-08-29 09:35:22
01a04cdf-ad19-70a7-86ed-b8d74e2accd6	{"cs": "2020", "en": "2020"}	{"cs": "První web, pak žádný web", "en": "First website, then no website"}	{"cs": "Nadchla mě představa, že věci se dají prostě postavit. Vydal jsem první web, přišel o něj se zrušeným hostingem a naučil se o zálohách tou dražší cestou.", "en": "I fell for the idea that you can just build things. Shipped my first site, lost it to a dead hosting account, and learned about backups the expensive way."}	f	1	2026-08-29 09:35:22	2026-08-29 09:35:22
01a04cdf-ad1a-73e5-a35f-341fb15e50de	{"cs": "2022", "en": "2022"}	{"cs": "SPŠE Pardubice", "en": "SPŠE Pardubice"}	{"cs": "Vybral jsem si školu, která mě nenechá usnout. Tady mi zapadl backend — nejdřív Java, pak všechno kolem ní.", "en": "Chose the school that would keep pushing me. Backend clicked here — Java first, then everything around it."}	f	2	2026-08-29 09:35:22	2026-08-29 09:35:22
01a04cdf-ad1b-7045-a748-0aafd5c58ff4	{"cs": "2024", "en": "2024"}	{"cs": "Soutěže a porota před očima", "en": "Competitions and a jury to face"}	{"cs": "Výhry na hackathonech, celostátní finále a poprvé jsem musel vysvětlovat vlastní architekturu cizím lidem s hodinami v zádech.", "en": "Hackathon wins, a national final, and the first time I had to explain my own architecture to strangers under a clock."}	f	3	2026-08-29 09:35:22	2026-08-29 09:35:22
01a04cdf-ad1c-739d-b022-8c6319f59117	{"cs": "Teď", "en": "Now"}	{"cs": "Freelance a Prezz", "en": "Freelance and Prezz"}	{"cs": "Placená práce pro klienty, vývojářská skupina, kterou pomáhám vést, a platforma v produkci, kterou lidé používají, aniž by znali moje jméno.", "en": "Paid client work, a web dev group to help lead, and a platform in production that people use without knowing my name."}	t	4	2026-08-29 09:35:22	2026-08-29 09:35:22
\.


--
-- Data for Name: project_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.project_badge (project_id, badge_id) FROM stdin;
01a04cdf-ac67-7239-85f9-52ff91f4fd20	019f805e-73ad-70f5-8590-c19a1caf550f
01a04cdf-ac72-7287-901b-f3a22daa1bae	019f805e-739b-7129-bdb0-dccce49349c5
01a04cdf-ac72-7287-901b-f3a22daa1bae	019f805e-73a5-7144-bf43-0f2ba6baa685
01a04cdf-ac76-72cb-855c-e5e69c67a7aa	019f805e-739b-7129-bdb0-dccce49349c5
01a04cdf-ac76-72cb-855c-e5e69c67a7aa	019f805e-73a1-7348-b9e4-b9e586559c8a
01a04cdf-ac7a-718e-a412-4955021ca360	019f805e-738f-7355-81e7-dc5cfecc15ef
01a04cdf-ac7a-718e-a412-4955021ca360	01a04cde-4c72-7382-ba8e-2f46b52ae9cf
01a04cdf-ac7a-718e-a412-4955021ca360	01a04cde-4c73-71a0-818d-72be875a5fdc
01a04cdf-ac7d-7125-a43d-2d00c86f90ec	019f805e-738f-7355-81e7-dc5cfecc15ef
01a04cdf-ac7d-7125-a43d-2d00c86f90ec	01a04cde-4c5c-70a0-a027-d975e5322370
01a04cdf-ac7d-7125-a43d-2d00c86f90ec	01a04cde-4c70-7038-b110-50814a54e438
01a04cdf-ac81-7079-b8b1-30545ceb7d12	019f805e-738f-7355-81e7-dc5cfecc15ef
01a04cdf-ac81-7079-b8b1-30545ceb7d12	01a04cde-4c5c-70a0-a027-d975e5322370
01a04cdf-ac81-7079-b8b1-30545ceb7d12	01a04cde-4c70-7038-b110-50814a54e438
01a04cdf-ac85-72b3-a71c-1394cb15d710	019f805e-738f-7355-81e7-dc5cfecc15ef
01a04cdf-ac85-72b3-a71c-1394cb15d710	01a04cde-4c5c-70a0-a027-d975e5322370
01a04cdf-ac85-72b3-a71c-1394cb15d710	019f805e-7398-73d5-a252-9c116d83070c
01a04cdf-ac85-72b3-a71c-1394cb15d710	01a04cde-4c70-7038-b110-50814a54e438
01a04cdf-ac89-72c2-a5a0-20fedd3569ca	01a04cde-4c5f-7291-a05a-e0288185bbf9
01a04cdf-ac89-72c2-a5a0-20fedd3569ca	01a04cde-4c70-7038-b110-50814a54e438
\.


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.projects (id, year, slug, img_url, created_at, updated_at, header, description, sort_order, kind, client, status, role) FROM stdin;
01a04cdf-ac67-7239-85f9-52ff91f4fd20	2022	spse-hub	images/projects/spse_wp.png	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"SP\\u0160E Hub","cs":"SP\\u0160E Rozcestn\\u00edk"}	{"en":"The SPSE Hub is a project created under the guidance of Mr. Nitrogen to teach how to make a website. It marks my beginnings and has a nostalgic effect on me \\u2013 so I'm including it. Built using HTML5, CSS3, and JavaScript.","cs":"Tento projekt je rozcestn\\u00edk v\\u0161ech webov\\u00fdch str\\u00e1nek, kter\\u00e9 jsem vytvo\\u0159il p\\u0159i studiu na st\\u0159edn\\u00ed \\u0161kole p\\u0159i pln\\u011bn\\u00ed \\u00fakol\\u016f od u\\u010ditele Ren\\u00e9ho \\"Dus\\u00edka\\" Duse. Jsou to jedny z m\\u00fdch prvn\\u00edch str\\u00e1nek (prvn\\u00ed jsme vytv\\u00e1\\u0159eli u\\u017e na z\\u00e1kladn\\u00ed \\u0161kole)."}	0	school	\N	archived	{"en":"Everything \\u2014 my first hand-written site","cs":"V\\u0161echno \\u2014 moje prvn\\u00ed ru\\u010dn\\u011b psan\\u00e9 str\\u00e1nky"}
01a04cdf-ac72-7287-901b-f3a22daa1bae	2025	u-sladovny	images/projects/usladovny.png	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"U Sladovny","cs":"U Sladovny"}	{"en":"A project I was part of during my part-time work at PekneWeby. The plan was simple \\u2013 rebrand and rebuild the restaurant. My part was front-end and back-end. And the result? I think PekneWeby did a fabulous job.","cs":"Projekt, na kter\\u00e9m jsem se pod\\u00edlel b\\u011bhem sv\\u00e9 pr\\u00e1ce na \\u010d\\u00e1ste\\u010dn\\u00fd \\u00favazek v PekneWeby. Pl\\u00e1n byl jednoduch\\u00fd - rebranding a rekonstrukce restaurace. M\\u016fj z\\u00e1b\\u011br byl ve front-endu a back-endu. A v\\u00fdsledek? Mysl\\u00edm, \\u017ee firma PekneWeby odvedla b\\u00e1je\\u010dnou pr\\u00e1ci."}	0	client	PekneWeby	live	{"en":"Front-end and back-end","cs":"Front-end a back-end"}
01a04cdf-ac76-72cb-855c-e5e69c67a7aa	2026	portfolio	images/projects/portfolio.png	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"Portfolio","cs":"Portf\\u00f3lio"}	{"en":"I think everybody making something artistic should have a portfolio in any form that displays their mind, creativity, and most importantly, personality \\u2013 and I believe that web developers are artists too.","cs":"Mysl\\u00edm si, \\u017ee ka\\u017ed\\u00fd, kdo d\\u011bl\\u00e1 n\\u011bco um\\u011bleck\\u00e9ho, by m\\u011bl m\\u00edt portfolio v jak\\u00e9koli podob\\u011b, kter\\u00e9 ukazuje jeho um, kreativitu a hlavn\\u011b osobnost - a v\\u011b\\u0159\\u00edm, \\u017ee i webov\\u00ed v\\u00fdvoj\\u00e1\\u0159i jsou um\\u011blci."}	0	personal	\N	live	{"en":"Design and build, start to finish","cs":"N\\u00e1vrh i realizace, od za\\u010d\\u00e1tku do konce"}
01a04cdf-ac7a-718e-a412-4955021ca360	2020	first-site	\N	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"First site","cs":"Prvn\\u00ed str\\u00e1nky"}	{"en":"My first website. Unfortunately, I have no backup :(. This taught me how important backups are!","cs":"Moje prvn\\u00ed webov\\u00e9 str\\u00e1nky. Bohu\\u017eel nem\\u00e1m z\\u00e1lohu :(. Nau\\u010dilo m\\u011b to, jak d\\u016fle\\u017eit\\u00e9 jsou z\\u00e1lohy!"}	0	school	\N	archived	{"en":"Everything \\u2014 my very first hand-written site","cs":"V\\u0161echno \\u2014 moje \\u00fapln\\u011b prvn\\u00ed ru\\u010dn\\u011b psan\\u00e9 str\\u00e1nky"}
01a04cdf-ac7d-7125-a43d-2d00c86f90ec	2026	lifely	\N	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"Lifely","cs":"Lifely"}	{"en":"Project for the OpenData KHK and Hackathon on NKU (national audit office) hackathons. The idea was to help people find their new place to live by scanning the area and evaluating nearby healthcare, noise from all sources we could get our hands on (like a nearby airport), and stuff like that. Links to OpenData NKU and NKU coming in the future.","cs":"Projekt pro hackathony OpenData KHK a Hackathon on NKU. C\\u00edlem bylo pomoci lidem naj\\u00edt nov\\u00e9 bydli\\u0161t\\u011b skenov\\u00e1n\\u00edm okol\\u00ed a vyhodnocen\\u00edm dostupnosti zdravotn\\u00ed p\\u00e9\\u010de, hluku ze v\\u0161ech zdroj\\u016f, kter\\u00e9 jsme dok\\u00e1zali z\\u00edskat (nap\\u0159\\u00edklad bl\\u00edzk\\u00e9ho leti\\u0161t\\u011b), a podobn\\u00fdch v\\u011bc\\u00ed. Odkazy na OpenData NKU a NKU p\\u0159ibudou v budoucnu."}	0	school	\N	archived	{"en":"Hackathon team member","cs":"\\u010clen hackathonov\\u00e9ho t\\u00fdmu"}
01a04cdf-ac81-7079-b8b1-30545ceb7d12	2026	zemloveka	\N	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"ZemLOVEka","cs":"ZemLOVEka"}	{"en":"A gamified cookguide for people who want to get into cooking. Three modes: Campaign, a story-driven mode where people progress and learn the basics of cooking and cooking safety; Open World, similar to Campaign but where people choose a country and learn its dishes and culture; and a basic cookguide for finding recipes for people who already know what they want. Link to Funovation 26.","cs":"Gamifikovan\\u00fd kucha\\u0159sk\\u00fd pr\\u016fvodce pro lidi, kte\\u0159\\u00ed se cht\\u011bj\\u00ed nau\\u010dit va\\u0159it. T\\u0159i m\\u00f3dy: Kampa\\u0148, p\\u0159\\u00edb\\u011bhov\\u00fd m\\u00f3d, kde lid\\u00e9 postupuj\\u00ed a u\\u010d\\u00ed se z\\u00e1klady va\\u0159en\\u00ed a bezpe\\u010dnosti p\\u0159i va\\u0159en\\u00ed; Otev\\u0159en\\u00fd sv\\u011bt, podobn\\u00fd Kampani, jen si lid\\u00e9 vyberou zemi a u\\u010d\\u00ed se jej\\u00ed j\\u00eddla a kulturu; a z\\u00e1kladn\\u00ed kucha\\u0159sk\\u00fd pr\\u016fvodce pro hled\\u00e1n\\u00ed recept\\u016f pro ty, kdo u\\u017e v\\u011bd\\u00ed, co cht\\u011bj\\u00ed. Odkaz na Funovation 26."}	0	school	\N	archived	{"en":"Hackathon team member","cs":"\\u010clen hackathonov\\u00e9ho t\\u00fdmu"}
01a04cdf-ac85-72b3-a71c-1394cb15d710	2026	teachers-digital-academy	\N	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"Teachers digital Academy","cs":"Teachers digital Academy"}	{"en":"Platform that innovates distance teaching with interactive quizzes, live presentations, and a lot more!","cs":"Platforma, kter\\u00e1 inovuje v\\u00fduku na d\\u00e1lku pomoc\\u00ed interaktivn\\u00edch kv\\u00edz\\u016f, \\u017eiv\\u00fdch prezentac\\u00ed a mnoha dal\\u0161\\u00edch funkc\\u00ed!"}	0	school	\N	archived	{"en":"Hackathon team member","cs":"\\u010clen hackathonov\\u00e9ho t\\u00fdmu"}
01a04cdf-ac89-72c2-a5a0-20fedd3569ca	2026	tourdecloud	\N	2026-08-29 09:35:22	2026-08-29 09:35:22	{"en":"TourdeCloud","cs":"TourdeCloud"}	{"en":"Took part in creating the platform for the next year of Tour-de-App 27. My part was back-end \\u2014 the achievements and leveling system. TourdeCloud is a platform for contestants to upload their projects, show progress, roadmap, and a lot more!","cs":"Pod\\u00edlel jsem se na tvorb\\u011b platformy pro dal\\u0161\\u00ed ro\\u010dn\\u00edk Tour-de-App 27. M\\u016fj pod\\u00edl byl back-end - syst\\u00e9m achievement\\u016f a levelov\\u00e1n\\u00ed. TourdeCloud je platforma pro sout\\u011b\\u017e\\u00edc\\u00ed, kde mohou nahr\\u00e1vat sv\\u00e9 projekty, ukazovat progres, roadmapu a mnoho dal\\u0161\\u00edho!"}	0	client	Tour-de-App	live	{"en":"Back-end \\u2014 achievements and leveling system","cs":"Back-end - syst\\u00e9m achievement\\u016f a levelov\\u00e1n\\u00ed"}
\.


--
-- Data for Name: reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reviews (id, name, "position", text, sort_order, created_at, updated_at, highlight, source, source_color) FROM stdin;
01a04cdf-ad0b-7102-9892-58017d8b1c7f	Petr Machovec	{"cs": "Spoluzakladatel Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard vždy dodává čistý, efektivní kód a má skvělý smysl pro uživatelsky přívětivý design. Spolehlivý a talentovaný týmový hráč!\\"", "en": "\\"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A reliable and talented team player!\\""}	0	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "čistý, efektivní kód", "en": "clean, efficient code"}	LinkedIn	#60A5FA
01a04cdf-ad0d-72e5-bf22-139ab9c3f4cb	ChatGPT	{"cs": "The best AI", "en": "The best AI"}	{"cs": "\\"Richardova oddanost zlepšování svého řemesla a sdílení nápadů z něj dělá inspirativního a cenného kolegu.\\"", "en": "\\"Richard’s commitment to improving his craft and sharing ideas makes him an inspiring and valuable colleague.\\""}	1	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "zlepšování svého řemesla", "en": "improving his craft"}	Reference	#818CF8
01a04cdf-ad0e-72a7-99c2-9bce2fcddfa3	Ondřej Kučera	{"cs": "Co-founder of Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard se rychle přizpůsobuje novým nástrojům a s důvěrou a kreativitou se vypořádává se složitými projekty.\\"", "en": "\\"Richard adapts quickly to new tools and tackles complex projects with confidence and creativity.\\""}	2	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "důvěrou a kreativitou", "en": "confidence and creativity"}	LinkedIn	#60A5FA
01a04cdf-ad0f-70ec-9fec-c571ab4c1ba6	Jana Nováková	{"cs": "Product Owner, freelance klientka", "en": "Product Owner, freelance client"}	{"cs": "\\"Jasná komunikace od prvního dne a kód, který prostě funguje. Richard proměnil vágní zadání v propracovaný produkt dřív, než jsme čekali.\\"", "en": "\\"Clear communication from day one and code that just works. Richard turned a vague brief into a polished product ahead of schedule.\\""}	3	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "dřív, než jsme čekali", "en": "ahead of schedule"}	E-mail	#34D399
01a04cdf-ad10-7302-8a3a-7760963ce495	Tomáš Dvořák	{"cs": "Backend vývojář, bývalý kolega", "en": "Backend engineer, former teammate"}	{"cs": "\\"Jeden z mála vývojářů, kteří si nejdřív přečtou dokumentaci a až pak se ptají. Jeho PR se recenzují radost.\\"", "en": "\\"One of the few developers who actually reads the docs before asking. His PRs are a pleasure to review.\\""}	4	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "recenzují radost", "en": "a pleasure to review"}	LinkedIn	#60A5FA
01a04cdf-ad11-72fb-8a60-58d861d405be	Lucie Horáková	{"cs": "QA inženýrka, Prezz", "en": "QA engineer, Prezz"}	{"cs": "\\"Chyby, které nahlásím na jeho featury, se skoro nikdy nevrací podruhé. Okrajové případy testuje dřív, než se k nim vůbec dostanu.\\"", "en": "\\"Bugs I file against his features rarely come back twice. He tests edge cases before I even get to them.\\""}	5	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "skoro nikdy nevrací podruhé", "en": "rarely come back twice"}	E-mail	#34D399
01a04cdf-ad12-7045-88d5-f2b469968001	Martin Sedlák	{"cs": "CTO, klient rané startupové fáze", "en": "CTO, early-stage startup client"}	{"cs": "\\"Potřebovali jsme někoho, kdo zvládne celý stack pod tlakem termínu. Richard to dodal a architektura vydržela i dlouho poté.\\"", "en": "\\"We needed someone who could own the whole stack under a tight deadline. Richard delivered, and the architecture held up long after.\\""}	6	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "architektura vydržela i dlouho poté", "en": "the architecture held up long after"}	Reference	#818CF8
01a04cdf-ad13-727f-8e0b-efcd73f69b9b	Barbora King	{"cs": "Spoluhráčka v šachovém klubu", "en": "Chess club teammate"}	{"cs": "\\"U šachovnice stejně metodické myšlení jako v kódu — plánuje tři tahy dopředu a nikdy nepanikaří.\\"", "en": "\\"Same methodical thinking at the board as in his code — plans three moves ahead and never panics.\\""}	7	2026-08-29 09:35:22	2026-08-29 09:35:22	\N	\N	\N
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.settings (id, key, value, created_at, updated_at) FROM stdin;
1	hero_suptitle	{"cs": "👋 Ahoj světe!", "en": "👋 Hello world!"}	2026-07-21 21:29:49	2026-08-29 09:35:22
2	hero_title	{"cs": "Jsem <span>projektant-pata</span>,", "en": "I’m <span>projektant-pata</span>,"}	2026-07-21 21:29:49	2026-08-29 09:35:22
4	stats_title	{"cs": "Moje statistiky", "en": "My Stats"}	2026-07-21 21:29:49	2026-08-29 09:35:22
5	tools_title	{"cs": "Nástroje", "en": "Tools"}	2026-07-21 21:29:49	2026-08-29 09:35:22
16	experience_hero_suptitle	{"cs": "🗓️ Kudy jsem prošel", "en": "🗓️ Where I’ve been"}	2026-08-12 12:52:48	2026-08-29 09:35:22
17	experience_hero_title	{"cs": "Moje <span>cesta</span>,", "en": "My <span>journey</span>,"}	2026-08-12 12:52:48	2026-08-29 09:35:22
18	experience_hero_roles	{"cs": ["Certifikáty a <span>soutěže</span>", "<span>PHP</span> nadšenec", "<span>Javu</span> miluju", "<span>JavaScript</span> nesnáším!!", "Od <span>2021</span> dodnes"], "en": ["Certificates & <span>competitions</span>", "<span>PHP</span> enjoyer", "<span>Java</span> lover", "<span>JavaScript</span> hater!!", "From <span>2021</span> to now"]}	2026-08-12 12:52:48	2026-08-29 09:35:22
19	projects_hero_suptitle	{"cs": "🛠️ Co jsem postavil", "en": "🛠️ What I’ve built"}	2026-08-12 12:52:48	2026-08-29 09:35:22
20	projects_hero_title	{"cs": "Věci, co jsem <span>postavil</span>,", "en": "Things I’ve <span>shipped</span>,"}	2026-08-12 12:52:48	2026-08-29 09:35:22
21	projects_hero_roles	{"cs": ["Vedlejšáky, co <span>přežily</span>", "Laravel <span>monolity</span>", "Spring Boot <span>API</span>", "Nasazeno přes <span>Docker</span>", "Postaveno, rozbito, <span>opraveno</span>"], "en": ["Side projects that <span>survived</span>", "Laravel <span>monoliths</span>", "Spring Boot <span>APIs</span>", "Shipped with <span>Docker</span>", "Built, broken, <span>rebuilt</span>"]}	2026-08-12 12:52:48	2026-08-29 09:35:22
22	blog_hero_suptitle	{"cs": "📝 Myslím nahlas", "en": "📝 Thinking out loud"}	2026-08-26 06:37:17	2026-08-29 09:35:22
23	blog_hero_title	{"cs": "Zápisky a <span>co se rozbilo</span>,", "en": "Build logs, and <span>what broke</span>,"}	2026-08-26 06:37:17	2026-08-29 09:35:22
24	blog_hero_roles	{"cs": ["Píše, jen když se <span>něco rozbije</span>", "Markdown <span>fanatik</span>", "<span>Arch</span>, mimochodem", "Pořád ne <span>frontend vývojář</span>", "Commituje ve 2 ráno, <span>publikuje ve 2 odpoledne</span>"], "en": ["Writes only when <span>something breaks</span>", "Markdown <span>maximalist</span>", "<span>Arch</span> btw", "Still not a <span>frontend dev</span>", "Commits at 2am, <span>posts at 2pm</span>"]}	2026-08-26 06:37:17	2026-08-29 09:35:22
32	contact_hero_suptitle	{"cs": "✉️ Ozvěte se", "en": "✉️ Say hello"}	2026-08-26 19:01:43	2026-08-29 09:35:22
33	contact_hero_title	{"cs": "Pojďme si <span>napsat</span>,", "en": "Let’s <span>talk</span>,"}	2026-08-26 19:01:43	2026-08-29 09:35:22
34	contact_hero_roles	{"cs": ["Odpovídám do <span>24 hodin</span>", "Freelance <span>zakázky</span>", "Juniorní <span>pozice</span>", "Hackathonové <span>týmy</span>", "Ano, ne, nebo <span>datum</span>"], "en": ["Replies within <span>24 hours</span>", "Freelance <span>work</span>", "Junior <span>roles</span>", "Hackathon <span>team-ups</span>", "A yes, a no, or <span>a date</span>"]}	2026-08-26 19:01:43	2026-08-29 09:35:22
25	about_intro_lead	{"cs": "Stavím backendy, které přežijí demo, a radši <em>vydám něco malého</em>, než abych popisoval něco velkého.", "en": "I build backends that outlive the demo, and I would rather <em>ship something small</em> than describe something big."}	2026-08-26 06:37:17	2026-08-29 09:35:22
27	about_facts	{"cs": [{"label": "Bydliště", "value": "Pardubice, ČR"}, {"label": "Zaměření", "value": "Backend a web"}, {"label": "Jazyky", "value": "Čeština, angličtina"}, {"label": "Stav", "value": "Otevřen nabídkám"}], "en": [{"label": "Based in", "value": "Pardubice, CZ"}, {"label": "Focus", "value": "Backend & web"}, {"label": "Languages", "value": "Czech, English"}, {"label": "Status", "value": "Open to work"}]}	2026-08-26 06:37:17	2026-08-29 09:35:22
28	about_available	{"cs": "Volný pro freelance zakázky", "en": "Available for freelance work"}	2026-08-26 06:37:17	2026-08-29 09:35:22
29	about_cta_title	{"cs": "Máte něco, co je potřeba <em>postavit</em>?", "en": "Have something that needs <em>building</em>?"}	2026-08-26 06:37:17	2026-08-29 09:35:22
30	about_cta_sub	{"cs": "Zakázka, školní projekt nebo nápad, na který chcete druhý názor — napište a ozvu se.", "en": "Client work, a school project, or an idea you want a second opinion on — write and you will hear back."}	2026-08-26 06:37:17	2026-08-29 09:35:22
31	about_off_end	{"cs": "Ve všech čtyřech je stejný vzorec, a je to ten, který přináším i do práce: pomalý, opakovatelný, upřímný k tomu, co ještě nefunguje. Šachy mě naučily důsledkům, homelab ladění chyb, posilovna tomu, že se prostě ukážu, a vlaky tomu, že dobré věci jsou tiše promyšlené.", "en": "There is a pattern in all four, and it is the same one I bring to work: slow, repeatable, honest about what is not working yet. Chess taught me consequence, the rack taught me debugging, the gym taught me showing up, and the trains taught me that good things are quietly designed."}	2026-08-26 08:39:42	2026-08-29 09:35:22
6	reviews_title	{"cs": "Reference", "en": "Reviews"}	2026-07-21 21:29:49	2026-08-29 09:35:22
7	about_title	{"cs": "O mně", "en": "About me"}	2026-07-21 21:29:49	2026-08-29 09:35:22
13	about_hero_suptitle	{"cs": "👤 whoami", "en": "👤 whoami"}	2026-08-12 12:52:48	2026-08-29 09:35:22
14	about_hero_title	{"cs": "Něco <span>o mně</span>,", "en": "A bit <span>about me</span>,"}	2026-08-12 12:52:48	2026-08-29 09:35:22
15	about_hero_roles	{"cs": ["Život <span>mimo kód</span>", "<span>Arch</span>, mimochodem", "Homelab <span>nadšenec</span>", "Ve dne <span>student</span>", "V noci <span>freelancer</span>", "<span>Šachista</span>"], "en": ["Life <span>outside code</span>", "<span>Arch</span> btw", "<span>Home</span> labber", "Student <span>by day</span>", "Freelancer <span>by night</span>", "<span>Chess</span> player"]}	2026-08-12 12:52:48	2026-08-29 09:35:22
8	hero_roles	{"cs": ["Full-stack <span>vývojář</span>", "<span>Šachista</span>", "Spring Boot <span>inženýr</span>", "Laravel <span>řemeslník</span>", "Řešitel <span>problémů</span>"], "en": ["Full-stack <span>developer</span>", "Chess <span>player</span>", "Spring Boot <span>engineer</span>", "Laravel <span>craftsman</span>", "Problem <span>solver</span>"]}	2026-07-21 21:33:27	2026-08-29 09:35:22
26	about_intro_body	{"cs": ["Jsem Richard Hývl, student <b>SPŠE Pardubice</b> a freelancer. Většinu týdne tvoří Java a PHP — na jedné straně Spring Boot, na druhé Laravel — plus vývojářská skupina <b>Prezz</b>, kterou pomáhám vést.", "Pohání mě zvědavost a chuť být užitečný lidem, kteří moje věci používají. Nejrychleji se učím tím, že rozbíjím vlastní věci — proto existuje homelab."], "en": ["I am Richard Hývl, a student at <b>SPŠE Pardubice</b> and a freelancer. Most of my week is Java and PHP — Spring Boot on one side, Laravel on the other — plus a web dev group called <b>Prezz</b> that I help lead.", "What drives me is curiosity and the wish to be useful to the people who use what I build. I learn fastest by breaking things I own, which is why the homelab exists."]}	2026-08-26 06:37:17	2026-08-29 09:35:22
\.


--
-- Data for Name: stats; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.stats (id, value, text, value_id, source, sort_order, created_at, updated_at, tier, note) FROM stdin;
01a04cdf-acfc-722c-94ac-30829264c886	{"cs": "5+", "en": "5+"}	{"cs": "Vydaných projektů", "en": "Projects shipped"}	\N	\N	0	2026-08-29 09:35:22	2026-08-29 09:35:22	hero	{"cs": "Klientské zakázky, školní projekty a jedna platforma běžící v produkci.", "en": "Client work, school projects and one platform running in production."}
01a04cdf-acfe-72bd-ab6e-90eb6216adab	\N	{"cs": "Let u kódu", "en": "Years writing code"}	\N	years_experience	1	2026-08-29 09:35:22	2026-08-29 09:35:22	hero	{"cs": "Začal jsem v roce 2020 a zatím nenašel důvod přestat.", "en": "Started in 2020 and have not found a reason to stop."}
01a04cdf-acff-73bb-90ab-e271c0ab79b2	{"cs": "Junior", "en": "Junior"}	{"cs": "Profesionální úroveň", "en": "Professional Level"}	\N	\N	2	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad00-722a-860e-e5787eabf83e	\N	{"cs": "Let věku", "en": "Years old"}	\N	age	3	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad01-7014-bfa8-15a3d3ad7a77	{"cs": "2", "en": "2"}	{"cs": "Země dosaženy", "en": "Countries Reached"}	\N	\N	4	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad02-734e-b376-e92b97d299aa	{"cs": "18", "en": "18"}	{"cs": "GitHub repozitářů", "en": "GitHub repositories"}	github-repos	\N	5	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad03-722f-b81c-6884bd37cee9	{"cs": "Načítání..", "en": "Loading.."}	{"cs": "Nejvyšší šachové elo", "en": "Highest chess elo"}	elo	\N	6	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad05-73a3-8e3f-6908001318bd	{"cs": "4", "en": "4"}	{"cs": "Vyhraných hackathonů", "en": "Hackathons won"}	\N	\N	7	2026-08-29 09:35:22	2026-08-29 09:35:22	normal	\N
01a04cdf-ad06-722f-8253-6b08acd0885b	{"cs": "∞", "en": "∞"}	{"cs": "Vypitých šálků kávy", "en": "Coffee consumed"}	\N	\N	8	2026-08-29 09:35:22	2026-08-29 09:35:22	soft	\N
01a04cdf-ad07-702b-b3b6-7245c91a663c	{"cs": "404", "en": "404"}	{"cs": "Hodin spánku, nenalezeno", "en": "Hours slept, not found"}	\N	\N	9	2026-08-29 09:35:22	2026-08-29 09:35:22	soft	\N
\.


--
-- Data for Name: tools; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tools (id, name, color, state, "group", placement, show_on_home, sort_order, created_at, updated_at, group_note) FROM stdin;
01a04cdf-ad28-71d3-8af5-8a4c8ee28f88	Java (Spring Boot)	#F97316	using	{"cs": "Backend", "en": "Backend"}	grid	t	0	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "kde vážně bydlím", "en": "where I actually live"}
01a04cdf-ad2a-711e-bee1-28f2a0ff893c	PHP (Laravel)	#FF2D20	using	{"cs": "Backend", "en": "Backend"}	grid	t	1	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad2b-7361-8220-03b9bca6b130	TypeScript (Next.js)	#A1A1AA	using	{"cs": "Backend", "en": "Backend"}	grid	f	2	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad2c-7017-b653-b9d6d1629476	TypeScript (React)	#3178C6	using	{"cs": "Frontend", "en": "Frontend"}	grid	t	3	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "dost na to dodat celou věc", "en": "enough to ship a whole thing"}
01a04cdf-ad2d-70b0-8db1-96356cf74e7f	HTML & CSS	#38BDF8	using	{"cs": "Frontend", "en": "Frontend"}	grid	f	4	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad2e-7005-b01a-06506bb5d4fe	Blade / Twig	#818CF8	using	{"cs": "Frontend", "en": "Frontend"}	grid	f	5	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad2f-71e9-8cb6-c69308e2df5a	Tailwind	#60A5FA	using	{"cs": "Frontend", "en": "Frontend"}	grid	t	6	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad30-714c-bb5f-dfec7812cb8b	MySQL	#34D399	using	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	f	7	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "naučeno rozbíjením vlastních serverů", "en": "learned by breaking my own servers"}
01a04cdf-ad31-70da-aea3-4651a848adfb	PostgreSQL	#22D3EE	using	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	t	8	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad32-7126-a19b-1d7cb000fc21	Proxmox VE	#E57000	using	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	t	9	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad33-713a-b406-6bad2c9194d8	Raspberry Pi	#F59E0B	experience	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	10	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "testovací stůl, ne práce", "en": "the test bench, not the job"}
01a04cdf-ad34-71c1-977f-792427eda1f8	3D printing	#A3A3A3	experience	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	11	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad35-735b-af9f-9e7251613839	Soldering	#F87171	experience	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	12	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad36-7369-9c20-e21d4d955707	Home automation	#FBBF24	experience	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	13	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad37-7166-acf4-644eaec5056f	Figma	#A259FF	using	{"cs": "Kreativní práce", "en": "Creative work"}	grid	f	14	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "drátěné modely dřív než první řádek kódu", "en": "wireframes before a line of code"}
01a04cdf-ad38-7342-82b3-76522413bf71	Kdenlive	#1E88E5	using	{"cs": "Kreativní práce", "en": "Creative work"}	grid	f	15	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad39-72bf-93a2-6da38a1d8799	GitHub	#9CA3AF	using	{"cs": "Vývojářské nástroje", "en": "Dev tools"}	grid	f	16	2026-08-29 09:35:22	2026-08-29 09:35:22	{"cs": "denní chleba, ne odznak do portfolia", "en": "daily driver, not a portfolio badge"}
01a04cdf-ad3a-7129-b2b6-a164e7fbd590	VS Code	#007ACC	using	{"cs": "Vývojářské nástroje", "en": "Dev tools"}	grid	f	17	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad3b-73e2-b40a-b665e4285087	Claude Code	#D97757	using	{"cs": "Vývojářské nástroje", "en": "Dev tools"}	grid	f	18	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad3c-729d-bf70-21e0cbd4b545	Arch Linux	#1793D1	using	{"cs": "Vývojářské nástroje", "en": "Dev tools"}	grid	f	19	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
01a04cdf-ad3c-729d-bf70-21e0cc05727f	Docker	\N	\N	\N	rotation	f	20	2026-08-29 09:35:22	2026-08-29 09:35:22	\N
\.


--
-- Name: experiences_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.experiences_id_seq', 76, true);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 42, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.settings_id_seq', 34, true);


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
-- Name: interests interests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.interests
    ADD CONSTRAINT interests_pkey PRIMARY KEY (id);


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
-- Name: path_nodes path_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.path_nodes
    ADD CONSTRAINT path_nodes_pkey PRIMARY KEY (id);


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
-- Name: tools tools_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tools
    ADD CONSTRAINT tools_pkey PRIMARY KEY (id);


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
-- Name: articles_published_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX articles_published_at_index ON public.articles USING btree (published_at);


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

\unrestrict vGS1WxhKy92BQdRwt8s47blv2WneVqQpITkwLpW7wLa9gWfEGdXExtGiwr1P4bI

