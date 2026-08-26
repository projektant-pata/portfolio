--
-- PostgreSQL database dump
--

\restrict XN5lOnrUByfYvsluOSmmuTmjqvfeB2hMCoYpU5tecgTBkopBwPp503xvlxqpxca

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

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

-- *not* creating schema, since initdb creates it


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS '';


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
    updated_at timestamp(0) without time zone
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
    frequency character varying(255),
    "group" jsonb,
    placement character varying(255) DEFAULT 'grid'::character varying NOT NULL,
    show_on_home boolean DEFAULT false NOT NULL,
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
-- Data for Name: article_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.article_badge (article_id, badge_id) FROM stdin;
01a00c65-89fd-71fe-a138-14666a6d2789	019f805e-738b-70a6-a9d6-241a9e10eef1
01a00c65-89fd-71fe-a138-14666a6d2789	019f805e-738d-701b-9d4a-f25a4182ed98
01a00c65-89ff-7090-87c9-28ad573326ec	019f805e-7389-7121-9e04-a65a5b8a49fb
01a00c65-89ff-7090-87c9-28ad573326ec	019f805e-738b-70a6-a9d6-241a9e10eef1
01a00c65-8a01-70b9-9ee6-9bf5f7be42d3	019f805e-738b-70a6-a9d6-241a9e10eef1
01a00c65-8a01-70b9-9ee6-9bf5f7be42d3	019f805e-738d-701b-9d4a-f25a4182ed98
01a00c65-8a03-71ad-a42d-ce2f042ff230	019f805e-738b-70a6-a9d6-241a9e10eef1
01a00c65-8a03-71ad-a42d-ce2f042ff230	019f805e-738d-701b-9d4a-f25a4182ed98
01a00c65-8a05-718f-93af-af2cc52ded56	019f805e-7389-7121-9e04-a65a5b8a49fb
01a00c65-8a05-718f-93af-af2cc52ded56	019f805e-738b-70a6-a9d6-241a9e10eef1
\.


--
-- Data for Name: articles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.articles (id, slug, date, thumbnail_url, user_id, created_at, updated_at, header, description, content, sort_order, published_at) FROM stdin;
01a00c65-89fd-71fe-a138-14666a6d2789	assumenda-a-doloremque-5133	2024-09-26	\N	01a00c65-89eb-717e-b765-eecc8e3c9cfc	2026-08-16 21:06:16	2026-08-16 21:06:16	{"en":"Est commodi quis sed.","cs":"Molestias asperiores dolores dignissimos pariatur."}	{"en":"Aut sit expedita dolores illo nobis.","cs":"Consequuntur incidunt harum neque fugit."}	{"en":"Ad perferendis sapiente excepturi molestiae. Minus nulla eum veniam vel. Explicabo praesentium eos molestias dolor.","cs":"Alias accusamus voluptatum laborum sint. Sint consequatur quia tempora et. Neque fugit beatae blanditiis ipsam. Qui laborum magnam ut aut pariatur qui ipsam."}	0	2026-08-15 21:06:16
01a00c65-89ff-7090-87c9-28ad573326ec	dolore-qui-voluptatem-5723	2024-12-19	\N	01a00c65-89f4-704c-8f1d-57f29fa27814	2026-08-16 21:06:16	2026-08-16 21:06:16	{"en":"Neque magni et ad.","cs":"Tenetur veniam iusto sed et."}	{"en":"Et saepe quo enim.","cs":"Fuga qui odit ipsam temporibus eaque."}	{"en":"Nostrum consectetur qui voluptates cum esse saepe. Similique sunt quidem vel optio et enim. Magni porro culpa culpa odio quisquam. Hic rem nihil natus rem laudantium consectetur.","cs":"Adipisci occaecati ab hic necessitatibus ut. Mollitia eveniet quis iusto consequatur."}	0	2026-08-15 21:06:16
01a00c65-8a01-70b9-9ee6-9bf5f7be42d3	id-tenetur-quasi-9322	2025-08-22	\N	01a00c65-89f6-7305-a65c-0ebb21f24e13	2026-08-16 21:06:16	2026-08-16 21:06:16	{"en":"Dolorem eos quaerat magnam sed sed.","cs":"Quo omnis quia."}	{"en":"Nulla sequi eos itaque aperiam in laboriosam deserunt.","cs":"Alias sunt aperiam cupiditate."}	{"en":"Eligendi ipsam molestias dolor ab laboriosam corporis. Et consequatur reiciendis sit omnis officia. Omnis assumenda non veniam saepe. Ut aspernatur sunt odit a.","cs":"Alias pariatur nemo molestias ratione soluta aut dolorum. Enim harum ut inventore qui eaque assumenda. Enim deleniti praesentium molestiae aut hic."}	0	2026-08-15 21:06:16
01a00c65-8a03-71ad-a42d-ce2f042ff230	et-quia-accusantium-qui-7709	2024-09-19	\N	01a00c65-89f8-7178-a621-77c30a42b868	2026-08-16 21:06:16	2026-08-16 21:06:16	{"en":"Veniam dolores aut esse.","cs":"Deleniti suscipit exercitationem cumque."}	{"en":"Qui dignissimos voluptatibus ut veniam voluptas non.","cs":"Sed est ut ipsam quos et beatae."}	{"en":"Numquam provident corrupti fuga atque eius sit fugit sequi. Rerum voluptatem aliquam id unde. Architecto ab earum aut consequuntur. Nostrum maxime nemo quasi ut in repellendus.","cs":"Dolore aliquam ut et ut dolor deserunt dolore. Eum dolor officiis voluptatem velit consequatur. Doloribus vitae est eius deleniti est."}	0	2026-08-15 21:06:16
01a00c65-8a05-718f-93af-af2cc52ded56	quod-nemo-sed-suscipit-reiciendis-3280	2026-07-01	\N	01a00c65-89fb-7171-b4ab-b1f10df684ed	2026-08-16 21:06:16	2026-08-16 21:06:16	{"en":"Deserunt minima dolore accusamus vel.","cs":"Voluptas facere nesciunt quisquam."}	{"en":"Id numquam nihil necessitatibus delectus architecto ullam sint sunt.","cs":"Assumenda vel maiores ab rem ut."}	{"en":"Ipsam quos alias occaecati perferendis provident sit. Dolorum quibusdam amet quisquam eum ut maiores. Ut alias est esse.","cs":"Explicabo consequatur ea delectus animi. Vel consectetur quos aliquid accusamus qui aut nam consequatur. Amet est impedit sapiente quia modi voluptas tempore."}	0	2026-08-15 21:06:16
\.


--
-- Data for Name: badges; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.badges (id, slug, name, color, created_at, updated_at) FROM stdin;
019f805e-739b-7129-bdb0-dccce49349c5	php	{"cs": "PHP", "en": "PHP"}	#A78BFA	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-739e-7077-822a-6f4b9fee88e1	python	{"cs": "Python", "en": "Python"}	#2DD4BF	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-73a1-7348-b9e4-b9e586559c8a	laravel	{"cs": "Laravel", "en": "Laravel"}	#FF2D20	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-73a5-7144-bf43-0f2ba6baa685	symfony	{"cs": "Symfony", "en": "Symfony"}	#64748B	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-73a9-72f0-868b-e62d2ee92e41	spring-boot	{"cs": "Spring Boot", "en": "Spring Boot"}	#6DB33F	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-73ad-70f5-8590-c19a1caf550f	javascript	{"cs": "JavaScript", "en": "JavaScript"}	#F7DF1E	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-73b0-708e-bcea-a43921a95ac3	blockchain	{"cs": "Blockchain", "en": "Blockchain"}	#22D3EE	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-7389-7121-9e04-a65a5b8a49fb	competition	{"cs": "Soutěž", "en": "Competition"}	#EAB308	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-738b-70a6-a9d6-241a9e10eef1	work	{"cs": "Práce", "en": "Work"}	#60A5FA	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-738d-701b-9d4a-f25a4182ed98	certificate	{"cs": "Certifikát", "en": "Certificate"}	#34D399	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-738f-7355-81e7-dc5cfecc15ef	education	{"cs": "Vzdělání", "en": "Education"}	#38BDF8	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-7391-73b6-b1b5-75b1ba171f28	hardware	{"cs": "Hardware", "en": "Hardware"}	#F59E0B	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-7395-7328-8f62-13e0f3bb0a2d	it	{"cs": "IT", "en": "IT"}	#818CF8	2026-07-20 16:31:41	2026-08-18 03:30:01
019f805e-7398-73d5-a252-9c116d83070c	java	{"cs": "Java", "en": "Java"}	#F97316	2026-07-20 16:31:41	2026-08-18 03:30:01
\.


--
-- Data for Name: experience_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experience_badge (experience_id, badge_id) FROM stdin;
73	019f805e-738b-70a6-a9d6-241a9e10eef1
74	019f805e-739e-7077-822a-6f4b9fee88e1
74	019f805e-7389-7121-9e04-a65a5b8a49fb
75	019f805e-73ad-70f5-8590-c19a1caf550f
75	019f805e-73b0-708e-bcea-a43921a95ac3
75	019f805e-738b-70a6-a9d6-241a9e10eef1
76	019f805e-739b-7129-bdb0-dccce49349c5
76	019f805e-73a5-7144-bf43-0f2ba6baa685
76	019f805e-738b-70a6-a9d6-241a9e10eef1
77	019f805e-738d-701b-9d4a-f25a4182ed98
77	019f805e-7391-73b6-b1b5-75b1ba171f28
78	019f805e-739b-7129-bdb0-dccce49349c5
78	019f805e-73a1-7348-b9e4-b9e586559c8a
78	019f805e-738f-7355-81e7-dc5cfecc15ef
78	019f805e-7398-73d5-a252-9c116d83070c
79	019f805e-7389-7121-9e04-a65a5b8a49fb
79	019f805e-7395-7328-8f62-13e0f3bb0a2d
80	019f805e-738d-701b-9d4a-f25a4182ed98
81	019f805e-7389-7121-9e04-a65a5b8a49fb
81	019f805e-7395-7328-8f62-13e0f3bb0a2d
82	019f805e-7389-7121-9e04-a65a5b8a49fb
83	019f805e-738d-701b-9d4a-f25a4182ed98
84	019f805e-7395-7328-8f62-13e0f3bb0a2d
\.


--
-- Data for Name: experiences; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.experiences (id, type, created_at, updated_at, year, url, image_path, sort_order, links, is_special, title, subtitle, content, show_on_about) FROM stdin;
73	work	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2024", "en": "2024"}	\N	\N	1	[{"url":"https:\\/\\/erasmus-plus.ec.europa.eu\\/","alt":"Erasmus+"}]	f	{"en":"Erasmus","cs":"Erasmus"}	{"en":"Work trip to Spain made possible by the European Union","cs":"Pracovn\\u00ed cesta do \\u0160pan\\u011blska umo\\u017en\\u011bn\\u00e1 Evropskou uni\\u00ed"}	{"en":"","cs":""}	f
74	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2024", "en": "2024"}	\N	\N	2	[{"url":"https:\\/\\/www.spse.cz\\/clanek.php?id=1103","alt":"SP\\u0160E"}]	f	{"en":"Hackathon AstroPi","cs":"Hackathon AstroPi"}	{"en":"1st team place","cs":"1. t\\u00fdmov\\u00e9 m\\u00edsto"}	{"en":"Space themed hackathon made possible by ESA, with 2 goals \\u2014 Mission Zero and Mission Space Lab, in a team with Petr Machovec and Ond\\u0159ej Ku\\u010dera.\\n\\n**Mission Zero** \\u2014 a Python program animating an 8x8 display on the AstroPi, reactive to the environment (change of heat or pressure).\\n\\n**Mission Space Lab** \\u2014 a Python program that calculates the speed of the ISS by taking 2 pictures of Earth and matching anchor points.","cs":"Hackathon s vesm\\u00edrnou tematikou, kter\\u00fd umo\\u017enila ESA, se dv\\u011bma c\\u00edli \\u2014 Mission Zero a Mission Space Lab, v t\\u00fdmu s Petrem Machovcem a Ond\\u0159ejem Ku\\u010derou.\\n\\n**Mission Zero** \\u2014 program v Pythonu animovan\\u00fd na displeji 8x8 na AstroPi, reaktivn\\u00ed na prost\\u0159ed\\u00ed (zm\\u011bna tepla nebo tlaku).\\n\\n**Mission Space Lab** \\u2014 program v Pythonu, kter\\u00fd vypo\\u010d\\u00edt\\u00e1 rychlost ISS t\\u00edm, \\u017ee z n\\u00ed po\\u0159\\u00edd\\u00ed 2 sn\\u00edmky Zem\\u011b a n\\u00e1sledn\\u011b porovn\\u00e1 kotevn\\u00ed body."}	f
75	work	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2024", "en": "2024"}	\N	\N	3	[{"url":"https:\\/\\/byevolution.com\\/","alt":"ByEvolution"}]	f	{"en":"ByEvolution","cs":"ByEvolution"}	{"en":"Work experience in Erasmus CIT+","cs":"Pracovn\\u00ed st\\u00e1\\u017e na Erasmus CIT+"}	{"en":"I was selected for an Erasmus trip in Spain. Here I worked 14 days in ByEvolution Creative Factory, a firm that specializes in blockchain and crypto technology.\\n\\n- Automatization of application in Selenium\\n- Front-end with HTML, Tailwind, Alpine.js","cs":"Byl jsem vybr\\u00e1n na Erasmus do \\u0160pan\\u011blska. Zde jsem 14 dn\\u00ed pracoval v ByEvolution Creative Factory, firm\\u011b, kter\\u00e1 se specializuje na blockchain a krypto technologie.\\n\\n- Automatizace aplikace v Seleniu\\n- Front-end s HTML, Tailwind, Alpine.js"}	f
76	work	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2024", "en": "2024"}	\N	\N	4	[{"url":"https:\\/\\/www.usladovnychrudim.cz\\/","alt":"U Sladovny"}]	t	{"en":"PekneWeby","cs":"PekneWeby"}	{"en":"Part-time job","cs":"Brig\\u00e1dn\\u00ed pr\\u00e1ce"}	{"en":"Took part in complete rebranding of restaurant U Sladovny in Chrudim.\\n\\n- Took part in Front-end + Back-end of a website made in Symfony\\n- [Website](https:\\/\\/www.usladovnychrudim.cz\\/)","cs":"\\u00da\\u010dastnil jsem se kompletn\\u00edho rebrandingu restaurace U Sladovny v Chrudimi.\\n\\n- \\u00da\\u010dastnil jsem se Front-endu + Back-endu webov\\u00fdch str\\u00e1nek vytvo\\u0159en\\u00fdch v Symfony\\n- [Odkaz zde](https:\\/\\/www.usladovnychrudim.cz\\/)"}	f
77	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2023", "en": "2023"}	\N	\N	5	[{"url":"https:\\/\\/netacad.cz\\/it-essentials\\/","alt":"NetAcad"}]	f	{"en":"Cisco IT Essentials","cs":"Cisco IT Essentials"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}	f
78	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2022 - nyní", "en": "2022 - now"}	\N	\N	6	[{"url":"https:\\/\\/www.spse.cz\\/","alt":"SP\\u0160E"}]	f	{"en":"Student","cs":"Student"}	{"en":"SP\\u0160E a VO\\u0160 Pardubice","cs":"SP\\u0160E a VO\\u0160 Pardubice"}	{"en":"School education with focus on full-stack web development and app development.\\n\\n- HTML + CSS + JS in front-end\\n- PHP + Laravel in back-end\\n- MySQL in databases\\n- Java in app development\\n- Basics in Photoshop and Illustrator\\n- Basics in web design\\n- Developed interest in cybersecurity","cs":"\\u0160koln\\u00ed vzd\\u011bl\\u00e1n\\u00ed se zam\\u011b\\u0159en\\u00edm na full-stack webov\\u00fd v\\u00fdvoj a v\\u00fdvoj aplikac\\u00ed.\\n\\n- HTML + CSS + JS ve front-endu\\n- PHP + Laravel v back-endu\\n- MySQL v datab\\u00e1z\\u00edch\\n- Java ve v\\u00fdvoji aplikac\\u00ed\\n- Z\\u00e1klady Photoshopu a Illustratoru\\n- Z\\u00e1klady webov\\u00e9ho designu\\n- Vyvinut\\u00fd z\\u00e1jem o kybernetickou bezpe\\u010dnost"}	f
79	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2021", "en": "2021"}	\N	\N	7	[{"url":"https:\\/\\/www.it-slot.cz\\/results\\/year\\/2021","alt":"IT-Slot"}]	f	{"en":"IT-Slot","cs":"IT-Slot"}	{"en":"11th place out of 8320 students","cs":"11. m\\u00edsto z 8320 student\\u016f"}	{"en":"","cs":""}	f
80	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2021 - nyní", "en": "2021 - now"}	\N	\N	8	[{"url":"https:\\/\\/www.ecdl.cz\\/","alt":"ECDL"}]	f	{"en":"ECDL","cs":"ECDL"}	{"en":"Certificate","cs":"Certifik\\u00e1t"}	{"en":"","cs":""}	f
81	work	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2024", "en": "2024"}	\N	\N	9	[{"url":"https:\\/\\/www.spse.cz\\/clanky\\/2024-soutez-astropi-hackathon-pardubice","alt":"spse"}]	f	{"en":"AstroPi Hackathon 2024","cs":"AstroPi Hackathon 2024"}	{"en":"Hackathon","cs":"Hackathon"}	{"en":"","cs":""}	t
82	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2026", "en": "2026"}	\N	storage/experiences/4SUoVWLWnEj9MKXeJCVrKfdADgVxUTkyBMlZ1ffV.png	10	[{"url":"https:\\/\\/www.spse.cz\\/clanky\\/2026-regionalni-sampionat-skol-sachovy-turnaj","alt":"SPSE"}]	f	{"en":"Regional chess competetion of schools","cs":"Krajsk\\u00fd \\u0161achov\\u00fd turnaj \\u0161kol"}	{"en":"1st place in chess competetion","cs":"1. m\\u00edsto v \\u0161achov\\u00e9m turnaji"}	{"en":"","cs":""}	t
83	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2023 — nyní", "en": "2023 — now"}	\N	\N	11	\N	f	{"en":"PEER program","cs":"Program PEER"}	{"en":"Trained to teach teenagers about drugs and bullying, then presented to my own peers.","cs":"Vy\\u0161kolen, abych u\\u010dil teenagery o drog\\u00e1ch a \\u0161ikan\\u011b, a pak jsem prezentoval vlastn\\u00edm vrstevn\\u00edk\\u016fm."}	{"en":"","cs":""}	t
84	life	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "2023", "en": "2023"}	\N	\N	12	\N	f	{"en":"Czech Day Against Cancer","cs":"\\u010cesk\\u00fd den proti rakovin\\u011b"}	{"en":"Sold flower badges on the street for cancer patients. The best public-speaking practice there is.","cs":"Prod\\u00e1val jsem kv\\u011btinov\\u00e9 odznaky na ulici pro pacienty s rakovinou. Nejlep\\u0161\\u00ed tr\\u00e9nink ve\\u0159ejn\\u00e9ho projevu, jak\\u00fd existuje."}	{"en":"","cs":""}	t
\.


--
-- Data for Name: interests; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.interests (id, title, highlight, body, meta, image_path, sort_order, created_at, updated_at) FROM stdin;
01a012eb-3dfc-7245-9f78-a1d9ae505ec6	{"cs": "Šachy od druhé třídy", "en": "Chess since 2nd grade"}	{"cs": "druhé třídy", "en": "2nd grade"}	{"cs": "Místní turnaje proti středoškolákům mě naučily myslet tři tahy dopředu a prohrávat bez zhroucení. Jezdec je ta figura.", "en": "Local tournaments against high-schoolers taught me to think three moves out and to lose without falling apart. The knight is the piece."}	{"cs": "1684 elo · trpělivost, strategie", "en": "1684 elo · patience, strategy"}	\N	0	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3dff-72e5-9c25-981186117de9	{"cs": "Rock, nahlas", "en": "Rock, loud"}	{"cs": "nahlas", "en": "loud"}	{"cs": "Většina mého kódu vznikla s kytarou v jednom uchu. Koncerty vždycky, když to kalendář dovolí.", "en": "Most of my code was written with a guitar in one ear. Live shows whenever the calendar lets me."}	{"cs": "Koncerty · playlisty · sluchátka", "en": "Concerts · playlists · headphones"}	\N	1	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3e02-71c3-a0a4-cc97e8c88117	{"cs": "Homelab a sumci", "en": "Homelab and catfish"}	{"cs": "sumci", "en": "catfish"}	{"cs": "Stojan Raspberry Pi, které rozbíjím schválně, a ano — sumci. Na obojí se mě lidé ptají víc než na práci.", "en": "A rack of Raspberry Pis I break on purpose, and yes — catfish. Both are hobbies I get asked about more than my job."}	{"cs": "Senzory · self-hosting · rybaření", "en": "Sensors · self-hosting · fishing"}	\N	2	2026-08-18 03:30:01	2026-08-18 03:30:01
\.


--
-- Data for Name: links; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.links (id, project_id, alt, img_url, url, created_at, updated_at, kind) FROM stdin;
01a012eb-3c90-71c2-a413-dc09dea07857	01a012eb-3c8b-7304-a69b-ce8971838e8b	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://hyvlri22.llmp.spse-net.cz/	2026-08-18 03:30:01	2026-08-18 03:30:01	live
01a012eb-3c94-7219-b4d6-57190825a03c	01a012eb-3c8b-7304-a69b-ce8971838e8b	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/SPSE-WP	2026-08-18 03:30:01	2026-08-18 03:30:01	repo
01a012eb-3ca5-70a0-bddf-71812fadaaa8	01a012eb-3ca1-7283-be63-d2589a9c7424	{"cs": "Navštívit web", "en": "Visit website"}	images/projects/icons/web.webp	https://www.usladovnychrudim.cz/	2026-08-18 03:30:01	2026-08-18 03:30:01	live
01a012eb-3cb0-7105-982c-9a085d6ed64c	01a012eb-3cad-7271-aa8b-f785bfdb6748	{"cs": "Zobrazit na GitHubu", "en": "View on GitHub"}	images/mobile/icons/github.webp	https://github.com/projektant-pata/portfolio	2026-08-18 03:30:01	2026-08-18 03:30:01	repo
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
32	2026_08_15_000001_add_metadata_to_projects_and_links_tables	5
33	2026_08_15_000002_drop_not_null_from_links_alt	5
34	2026_08_17_000001_create_path_nodes_table	6
35	2026_08_17_000002_create_interests_table	6
36	2026_08_17_000003_add_tier_and_note_to_stats_table	6
37	2026_08_17_000004_add_show_on_about_to_experiences_table	6
38	2026_08_17_000005_drop_about_cards_table	7
39	2026_08_18_000001_create_tools_table	8
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: path_nodes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.path_nodes (id, year, title, body, is_now, sort_order, created_at, updated_at) FROM stdin;
01a012eb-3de7-70aa-bc62-c63031ad2fc3	{"cs": "2019", "en": "2019"}	{"cs": "Zpátky na základní školu", "en": "Back to elementary school"}	{"cs": "Kvůli zdravotním problémům jsem musel odejít z gymnázia. Základka, na kterou jsem se vrátil, měla rozšířenou výuku IT — nejšťastnější smůla, jakou jsem zažil.", "en": "Health issues pulled me out of gymnasium. The school I returned to happened to teach extended IT — the luckiest bad break I have had."}	f	0	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3dea-7049-94e0-429e83d73adc	{"cs": "2020", "en": "2020"}	{"cs": "První web, pak žádný web", "en": "First website, then no website"}	{"cs": "Nadchla mě představa, že věci se dají prostě postavit. Vydal jsem první web, přišel o něj se zrušeným hostingem a naučil se o zálohách tou dražší cestou.", "en": "I fell for the idea that you can just build things. Shipped my first site, lost it to a dead hosting account, and learned about backups the expensive way."}	f	1	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3ded-7214-bbde-d65380e6c465	{"cs": "2022", "en": "2022"}	{"cs": "SPŠE Pardubice", "en": "SPŠE Pardubice"}	{"cs": "Vybral jsem si školu, která mě nenechá usnout. Tady mi zapadl backend — nejdřív Java, pak všechno kolem ní.", "en": "Chose the school that would keep pushing me. Backend clicked here — Java first, then everything around it."}	f	2	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3def-7116-ab63-1578677c1432	{"cs": "2024", "en": "2024"}	{"cs": "Soutěže a porota před očima", "en": "Competitions and a jury to face"}	{"cs": "Výhry na hackathonech, celostátní finále a poprvé jsem musel vysvětlovat vlastní architekturu cizím lidem s hodinami v zádech.", "en": "Hackathon wins, a national final, and the first time I had to explain my own architecture to strangers under a clock."}	f	3	2026-08-18 03:30:01	2026-08-18 03:30:01
01a012eb-3df3-71c5-9239-67019ce05b16	{"cs": "Teď", "en": "Now"}	{"cs": "Freelance a Prezz", "en": "Freelance and Prezz"}	{"cs": "Placená práce pro klienty, vývojářská skupina, kterou pomáhám vést, a platforma v produkci, kterou lidé používají, aniž by znali moje jméno.", "en": "Paid client work, a web dev group to help lead, and a platform in production that people use without knowing my name."}	t	4	2026-08-18 03:30:01	2026-08-18 03:30:01
\.


--
-- Data for Name: project_badge; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.project_badge (project_id, badge_id) FROM stdin;
01a012eb-3c8b-7304-a69b-ce8971838e8b	019f805e-73ad-70f5-8590-c19a1caf550f
01a012eb-3ca1-7283-be63-d2589a9c7424	019f805e-739b-7129-bdb0-dccce49349c5
01a012eb-3ca1-7283-be63-d2589a9c7424	019f805e-73a5-7144-bf43-0f2ba6baa685
01a012eb-3cad-7271-aa8b-f785bfdb6748	019f805e-739b-7129-bdb0-dccce49349c5
01a012eb-3cad-7271-aa8b-f785bfdb6748	019f805e-73a1-7348-b9e4-b9e586559c8a
\.


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.projects (id, year, slug, img_url, created_at, updated_at, header, description, sort_order, kind, client, status, role) FROM stdin;
01a012eb-3c8b-7304-a69b-ce8971838e8b	2022	spse-hub	images/projects/spse_wp.png	2026-08-18 03:30:01	2026-08-18 03:30:01	{"en":"SP\\u0160E Hub","cs":"SP\\u0160E Rozcestn\\u00edk"}	{"en":"The SPSE Hub is a project created under the guidance of Mr. Nitrogen to teach how to make a website. It marks my beginnings and has a nostalgic effect on me \\u2013 so I'm including it. Built using HTML5, CSS3, and JavaScript.","cs":"Tento projekt je rozcestn\\u00edk v\\u0161ech webov\\u00fdch str\\u00e1nek, kter\\u00e9 jsem vytvo\\u0159il p\\u0159i studiu na st\\u0159edn\\u00ed \\u0161kole p\\u0159i pln\\u011bn\\u00ed \\u00fakol\\u016f od u\\u010ditele Ren\\u00e9ho \\"Dus\\u00edka\\" Duse. Jsou to jedny z m\\u00fdch prvn\\u00edch str\\u00e1nek (prvn\\u00ed jsme vytv\\u00e1\\u0159eli u\\u017e na z\\u00e1kladn\\u00ed \\u0161kole)."}	0	school	\N	archived	{"en":"Everything \\u2014 my first hand-written site","cs":"V\\u0161echno \\u2014 moje prvn\\u00ed ru\\u010dn\\u011b psan\\u00e9 str\\u00e1nky"}
01a012eb-3ca1-7283-be63-d2589a9c7424	2025	u-sladovny	images/projects/usladovny.png	2026-08-18 03:30:01	2026-08-18 03:30:01	{"en":"U Sladovny","cs":"U Sladovny"}	{"en":"A project I was part of during my part-time work at PekneWeby. The plan was simple \\u2013 rebrand and rebuild the restaurant. My part was front-end and back-end. And the result? I think PekneWeby did a fabulous job.","cs":"Projekt, na kter\\u00e9m jsem se pod\\u00edlel b\\u011bhem sv\\u00e9 pr\\u00e1ce na \\u010d\\u00e1ste\\u010dn\\u00fd \\u00favazek v PekneWeby. Pl\\u00e1n byl jednoduch\\u00fd - rebranding a rekonstrukce restaurace. M\\u016fj z\\u00e1b\\u011br byl ve front-endu a back-endu. A v\\u00fdsledek? Mysl\\u00edm, \\u017ee firma PekneWeby odvedla b\\u00e1je\\u010dnou pr\\u00e1ci."}	0	client	PekneWeby	live	{"en":"Front-end and back-end","cs":"Front-end a back-end"}
01a012eb-3cad-7271-aa8b-f785bfdb6748	2026	portfolio	images/projects/portfolio.png	2026-08-18 03:30:01	2026-08-18 03:30:01	{"en":"Portfolio","cs":"Portf\\u00f3lio"}	{"en":"I think everybody making something artistic should have a portfolio in any form that displays their mind, creativity, and most importantly, personality \\u2013 and I believe that web developers are artists too.","cs":"Mysl\\u00edm si, \\u017ee ka\\u017ed\\u00fd, kdo d\\u011bl\\u00e1 n\\u011bco um\\u011bleck\\u00e9ho, by m\\u011bl m\\u00edt portfolio v jak\\u00e9koli podob\\u011b, kter\\u00e9 ukazuje jeho um, kreativitu a hlavn\\u011b osobnost - a v\\u011b\\u0159\\u00edm, \\u017ee i webov\\u00ed v\\u00fdvoj\\u00e1\\u0159i jsou um\\u011blci."}	0	personal	\N	live	{"en":"Design and build, start to finish","cs":"N\\u00e1vrh i realizace, od za\\u010d\\u00e1tku do konce"}
\.


--
-- Data for Name: reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.reviews (id, name, "position", text, sort_order, created_at, updated_at, highlight, source, source_color) FROM stdin;
01a012eb-3dc8-7363-aa2d-26f40c99688e	Petr Machovec	{"cs": "Spoluzakladatel Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard vždy dodává čistý, efektivní kód a má skvělý smysl pro uživatelsky přívětivý design. Spolehlivý a talentovaný týmový hráč!\\"", "en": "\\"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A reliable and talented team player!\\""}	0	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "čistý, efektivní kód", "en": "clean, efficient code"}	LinkedIn	#60A5FA
01a012eb-3dcb-7285-a5b2-c63d84de21c8	ChatGPT	{"cs": "The best AI", "en": "The best AI"}	{"cs": "\\"Richardova oddanost zlepšování svého řemesla a sdílení nápadů z něj dělá inspirativního a cenného kolegu.\\"", "en": "\\"Richard’s commitment to improving his craft and sharing ideas makes him an inspiring and valuable colleague.\\""}	1	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "zlepšování svého řemesla", "en": "improving his craft"}	Reference	#818CF8
01a012eb-3dce-7291-b87b-9f7a498b1b00	Ondřej Kučera	{"cs": "Co-founder of Prezz", "en": "Co-founder of Prezz"}	{"cs": "\\"Richard se rychle přizpůsobuje novým nástrojům a s důvěrou a kreativitou se vypořádává se složitými projekty.\\"", "en": "\\"Richard adapts quickly to new tools and tackles complex projects with confidence and creativity.\\""}	2	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "důvěrou a kreativitou", "en": "confidence and creativity"}	LinkedIn	#60A5FA
01a012eb-3dd1-71f6-be53-05c8a6403d79	Jana Nováková	{"cs": "Product Owner, freelance klientka", "en": "Product Owner, freelance client"}	{"cs": "\\"Jasná komunikace od prvního dne a kód, který prostě funguje. Richard proměnil vágní zadání v propracovaný produkt dřív, než jsme čekali.\\"", "en": "\\"Clear communication from day one and code that just works. Richard turned a vague brief into a polished product ahead of schedule.\\""}	3	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "dřív, než jsme čekali", "en": "ahead of schedule"}	E-mail	#34D399
01a012eb-3dd4-735e-97ed-ae6c79fee210	Tomáš Dvořák	{"cs": "Backend vývojář, bývalý kolega", "en": "Backend engineer, former teammate"}	{"cs": "\\"Jeden z mála vývojářů, kteří si nejdřív přečtou dokumentaci a až pak se ptají. Jeho PR se recenzují radost.\\"", "en": "\\"One of the few developers who actually reads the docs before asking. His PRs are a pleasure to review.\\""}	4	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "recenzují radost", "en": "a pleasure to review"}	LinkedIn	#60A5FA
01a012eb-3dd8-71d0-a355-626d657fe1f3	Lucie Horáková	{"cs": "QA inženýrka, Prezz", "en": "QA engineer, Prezz"}	{"cs": "\\"Chyby, které nahlásím na jeho featury, se skoro nikdy nevrací podruhé. Okrajové případy testuje dřív, než se k nim vůbec dostanu.\\"", "en": "\\"Bugs I file against his features rarely come back twice. He tests edge cases before I even get to them.\\""}	5	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "skoro nikdy nevrací podruhé", "en": "rarely come back twice"}	E-mail	#34D399
01a012eb-3ddb-728c-81a9-8f25147e7226	Martin Sedlák	{"cs": "CTO, klient rané startupové fáze", "en": "CTO, early-stage startup client"}	{"cs": "\\"Potřebovali jsme někoho, kdo zvládne celý stack pod tlakem termínu. Richard to dodal a architektura vydržela i dlouho poté.\\"", "en": "\\"We needed someone who could own the whole stack under a tight deadline. Richard delivered, and the architecture held up long after.\\""}	6	2026-08-18 03:30:01	2026-08-18 03:30:01	{"cs": "architektura vydržela i dlouho poté", "en": "the architecture held up long after"}	Reference	#818CF8
01a012eb-3dde-710b-a67c-b5403e98a95a	Barbora King	{"cs": "Spoluhráčka v šachovém klubu", "en": "Chess club teammate"}	{"cs": "\\"U šachovnice stejně metodické myšlení jako v kódu — plánuje tři tahy dopředu a nikdy nepanikaří.\\"", "en": "\\"Same methodical thinking at the board as in his code — plans three moves ahead and never panics.\\""}	7	2026-08-18 03:30:01	2026-08-18 03:30:01	\N	\N	\N
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.settings (id, key, value, created_at, updated_at) FROM stdin;
15	about_hero_roles	{"cs": ["Život <span>mimo kód</span>", "<span>Arch</span>, mimochodem", "Homelab <span>nadšenec</span>", "Ve dne <span>student</span>", "V noci <span>freelancer</span>", "<span>Šachista</span>"], "en": ["Life <span>outside code</span>", "<span>Arch</span> btw", "<span>Home</span> labber", "Student <span>by day</span>", "Freelancer <span>by night</span>", "<span>Chess</span> player"]}	2026-08-12 12:52:48	2026-08-18 03:30:01
16	experience_hero_suptitle	{"cs": "🗓️ Kudy jsem prošel", "en": "🗓️ Where I’ve been"}	2026-08-12 12:52:48	2026-08-18 03:30:01
20	projects_hero_title	{"cs": "Věci, co jsem <span>postavil</span>,", "en": "Things I’ve <span>shipped</span>,"}	2026-08-12 12:52:48	2026-08-18 03:30:01
21	projects_hero_roles	{"cs": ["Vedlejšáky, co <span>přežily</span>", "Laravel <span>monolity</span>", "Spring Boot <span>API</span>", "Nasazeno přes <span>Docker</span>", "Postaveno, rozbito, <span>opraveno</span>"], "en": ["Side projects that <span>survived</span>", "Laravel <span>monoliths</span>", "Spring Boot <span>APIs</span>", "Shipped with <span>Docker</span>", "Built, broken, <span>rebuilt</span>"]}	2026-08-12 12:52:48	2026-08-18 03:30:01
22	blog_hero_suptitle	{"cs": "📝 Myslím nahlas", "en": "📝 Thinking out loud"}	2026-08-16 21:04:25	2026-08-18 03:30:01
23	blog_hero_title	{"cs": "Zápisky a <span>co se rozbilo</span>,", "en": "Build logs, and <span>what broke</span>,"}	2026-08-16 21:04:25	2026-08-18 03:30:01
2	hero_title	{"cs": "Jsem <span>projektant-pata</span>,", "en": "I’m <span>projektant-pata</span>,"}	2026-07-21 21:29:49	2026-08-18 03:30:01
17	experience_hero_title	{"cs": "Moje <span>cesta</span>,", "en": "My <span>journey</span>,"}	2026-08-12 12:52:48	2026-08-18 03:30:01
18	experience_hero_roles	{"cs": ["Certifikáty a <span>soutěže</span>", "<span>PHP</span> nadšenec", "<span>Javu</span> miluju", "<span>JavaScript</span> nesnáším!!", "Od <span>2021</span> dodnes"], "en": ["Certificates & <span>competitions</span>", "<span>PHP</span> enjoyer", "<span>Java</span> lover", "<span>JavaScript</span> hater!!", "From <span>2021</span> to now"]}	2026-08-12 12:52:48	2026-08-18 03:30:01
19	projects_hero_suptitle	{"cs": "🛠️ Co jsem postavil", "en": "🛠️ What I’ve built"}	2026-08-12 12:52:48	2026-08-18 03:30:01
24	blog_hero_roles	{"cs": ["Píše, jen když se <span>něco rozbije</span>", "Markdown <span>fanatik</span>", "<span>Arch</span>, mimochodem", "Pořád ne <span>frontend vývojář</span>", "Commituje ve 2 ráno, <span>publikuje ve 2 odpoledne</span>"], "en": ["Writes only when <span>something breaks</span>", "Markdown <span>maximalist</span>", "<span>Arch</span> btw", "Still not a <span>frontend dev</span>", "Commits at 2am, <span>posts at 2pm</span>"]}	2026-08-16 21:04:25	2026-08-18 03:30:01
26	about_intro_lead	{"cs": "Stavím backendy, které přežijí demo, a radši <em>vydám něco malého</em>, než abych popisoval něco velkého.", "en": "I build backends that outlive the demo, and I would rather <em>ship something small</em> than describe something big."}	2026-08-18 02:59:54	2026-08-18 03:30:01
28	about_facts	{"cs": [{"label": "Bydliště", "value": "Pardubice, ČR"}, {"label": "Zaměření", "value": "Backend a web"}, {"label": "Jazyky", "value": "Čeština, angličtina"}, {"label": "Stav", "value": "Otevřen nabídkám"}], "en": [{"label": "Based in", "value": "Pardubice, CZ"}, {"label": "Focus", "value": "Backend & web"}, {"label": "Languages", "value": "Czech, English"}, {"label": "Status", "value": "Open to work"}]}	2026-08-18 02:59:54	2026-08-18 03:30:01
29	about_available	{"cs": "Volný pro freelance zakázky", "en": "Available for freelance work"}	2026-08-18 02:59:54	2026-08-18 03:30:01
30	about_cta_title	{"cs": "Máte něco, co je potřeba <em>postavit</em>?", "en": "Have something that needs <em>building</em>?"}	2026-08-18 02:59:54	2026-08-18 03:30:01
31	about_cta_sub	{"cs": "Zakázka, školní projekt nebo nápad, na který chcete druhý názor — napište a ozvu se.", "en": "Client work, a school project, or an idea you want a second opinion on — write and you will hear back."}	2026-08-18 02:59:54	2026-08-18 03:30:01
1	hero_suptitle	{"cs": "👋 Ahoj světe!", "en": "👋 Hello world!"}	2026-07-21 21:29:49	2026-08-18 03:30:01
8	hero_roles	{"cs": ["Full-stack <span>vývojář</span>", "<span>Šachista</span>", "Spring Boot <span>inženýr</span>", "Laravel <span>řemeslník</span>", "Řešitel <span>problémů</span>"], "en": ["Full-stack <span>developer</span>", "Chess <span>player</span>", "Spring Boot <span>engineer</span>", "Laravel <span>craftsman</span>", "Problem <span>solver</span>"]}	2026-07-21 21:33:27	2026-08-18 03:30:01
4	stats_title	{"cs": "Moje statistiky", "en": "My Stats"}	2026-07-21 21:29:49	2026-08-18 03:30:01
5	tools_title	{"cs": "Nástroje", "en": "Tools"}	2026-07-21 21:29:49	2026-08-18 03:30:01
6	reviews_title	{"cs": "Reference", "en": "Reviews"}	2026-07-21 21:29:49	2026-08-18 03:30:01
7	about_title	{"cs": "O mně", "en": "About me"}	2026-07-21 21:29:49	2026-08-18 03:30:01
13	about_hero_suptitle	{"cs": "👤 whoami", "en": "👤 whoami"}	2026-08-12 12:52:48	2026-08-18 03:30:01
14	about_hero_title	{"cs": "Něco <span>o mně</span>,", "en": "A bit <span>about me</span>,"}	2026-08-12 12:52:48	2026-08-18 03:30:01
27	about_intro_body	{"cs": ["Jsem Richard Hývl, student <b>SPŠE Pardubice</b> a freelancer. Většinu týdne tvoří Java a PHP — na jedné straně Spring Boot, na druhé Laravel — plus vývojářská skupina <b>Prezz</b>, kterou pomáhám vést.", "Pohání mě zvědavost a chuť být užitečný lidem, kteří moje věci používají. Nejrychleji se učím tím, že rozbíjím vlastní věci — proto existuje homelab."], "en": ["I am Richard Hývl, a student at <b>SPŠE Pardubice</b> and a freelancer. Most of my week is Java and PHP — Spring Boot on one side, Laravel on the other — plus a web dev group called <b>Prezz</b> that I help lead.", "What drives me is curiosity and the wish to be useful to the people who use what I build. I learn fastest by breaking things I own, which is why the homelab exists."]}	2026-08-18 02:59:54	2026-08-18 03:30:01
\.


--
-- Data for Name: stats; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.stats (id, value, text, value_id, source, sort_order, created_at, updated_at, tier, note) FROM stdin;
01a012eb-3da3-7255-807b-0f152c525df2	{"cs": "5+", "en": "5+"}	{"cs": "Vydaných projektů", "en": "Projects shipped"}	\N	\N	0	2026-08-18 03:30:01	2026-08-18 03:30:01	hero	{"cs": "Klientské zakázky, školní projekty a jedna platforma běžící v produkci.", "en": "Client work, school projects and one platform running in production."}
01a012eb-3da6-7027-b32c-788627ba01ad	\N	{"cs": "Let u kódu", "en": "Years writing code"}	\N	years_experience	1	2026-08-18 03:30:01	2026-08-18 03:30:01	hero	{"cs": "Začal jsem v roce 2020 a zatím nenašel důvod přestat.", "en": "Started in 2020 and have not found a reason to stop."}
01a012eb-3da9-7014-86fc-83c7faaad8c9	{"cs": "Junior", "en": "Junior"}	{"cs": "Profesionální úroveň", "en": "Professional Level"}	\N	\N	2	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3dac-738c-9956-ef736bb808ce	\N	{"cs": "Let věku", "en": "Years old"}	\N	age	3	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3daf-7306-b59b-dcd2c9725a8b	{"cs": "2", "en": "2"}	{"cs": "Země dosaženy", "en": "Countries Reached"}	\N	\N	4	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3db2-739e-a7c2-95e3641a8d66	{"cs": "18", "en": "18"}	{"cs": "GitHub repozitářů", "en": "GitHub repositories"}	github-repos	\N	5	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3db5-7118-a6f5-a46998f83a92	{"cs": "Načítání..", "en": "Loading.."}	{"cs": "Nejvyšší šachové elo", "en": "Highest chess elo"}	elo	\N	6	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3db8-72a9-ad11-cf575075dad2	{"cs": "4", "en": "4"}	{"cs": "Vyhraných hackathonů", "en": "Hackathons won"}	\N	\N	7	2026-08-18 03:30:01	2026-08-18 03:30:01	normal	\N
01a012eb-3dbb-72bb-a1c3-ad75502886ab	{"cs": "∞", "en": "∞"}	{"cs": "Vypitých šálků kávy", "en": "Coffee consumed"}	\N	\N	8	2026-08-18 03:30:01	2026-08-18 03:30:01	soft	\N
01a012eb-3dbe-7011-8924-e2726d5f3478	{"cs": "404", "en": "404"}	{"cs": "Hodin spánku, nenalezeno", "en": "Hours slept, not found"}	\N	\N	9	2026-08-18 03:30:01	2026-08-18 03:30:01	soft	\N
\.


--
-- Data for Name: tools; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tools (id, name, color, frequency, "group", placement, show_on_home, sort_order, created_at, updated_at) FROM stdin;
01a016d7-40f5-7106-b35f-0232663add06	Spring Boot	#6DB33F	daily	{"cs": "Backend", "en": "Backend"}	grid	t	0	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-40ff-7312-bed1-3346d2ad7813	Java	#F89820	daily	{"cs": "Backend", "en": "Backend"}	grid	t	1	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4102-72e8-af79-99a44741d53c	Laravel	#FF2D20	often	{"cs": "Backend", "en": "Backend"}	grid	t	2	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4104-7000-9471-4b4daa141647	PHP	#777BB4	often	{"cs": "Backend", "en": "Backend"}	grid	f	3	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4107-70c3-998d-c8e0727fed80	REST APIs	#A3A3A3	daily	{"cs": "Backend", "en": "Backend"}	grid	f	4	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-410a-724a-8102-7bfee22afcf7	TypeScript	#3178C6	daily	{"cs": "Frontend", "en": "Frontend"}	grid	t	5	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-410d-7279-b1d1-171c2ab4d75d	JavaScript	#F7DF1E	daily	{"cs": "Frontend", "en": "Frontend"}	grid	f	6	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4110-722c-8044-8cd97f29e0b1	HTML & CSS	#E34F26	daily	{"cs": "Frontend", "en": "Frontend"}	grid	f	7	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4113-7007-8126-9dbcd2e8c65e	Tailwind	#06B6D4	often	{"cs": "Frontend", "en": "Frontend"}	grid	f	8	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4116-716f-8a42-ff069e84942c	Figma	#F24E1E	sometimes	{"cs": "Frontend", "en": "Frontend"}	grid	t	9	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4119-72c7-8815-9e63f40133a8	PostgreSQL	#34D399	daily	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	t	10	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-411c-724c-a8e6-712f11a922aa	MySQL	#00758F	often	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	f	11	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-411f-713b-80e6-c7837736212d	Docker	#2496ED	often	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	t	12	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4122-7116-9ec5-cd496fd27b5a	Arch Linux	#1793D1	daily	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	t	13	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4125-70a5-97ff-100dab1aa4c6	Git	#F05032	daily	{"cs": "Data a infrastruktura", "en": "Data & infra"}	grid	f	14	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4128-73d1-a9a2-f7dd3144178d	Raspberry Pi	#F59E0B	often	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	15	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-412b-7245-a3a7-a17cc7b41e13	Home Assistant	#818CF8	often	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	16	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-412e-71a1-bb11-ca89351e23dd	Proxmox	#A3A3A3	sometimes	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	17	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4131-70df-96c5-406b68cbba85	Sensors / MQTT	#34D399	sometimes	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	18	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4134-73e5-be54-4b72f563f1bf	Soldering iron	#EAB308	rarely	{"cs": "Hardware a homelab", "en": "Hardware & homelab"}	grid	f	19	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4137-714d-a3bc-a5b6e6082486	GitHub	\N	\N	\N	rotation	f	20	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-413a-7286-9884-d248bf5923ea	VS Code	\N	\N	\N	rotation	f	21	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-413d-71f5-95de-b45b19062601	Claude Code	\N	\N	\N	rotation	f	22	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4140-7185-8ee2-4671ea51ceae	Kubernetes	\N	\N	\N	learning	f	23	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4143-7309-abf1-ce7782e13211	Kotlin	\N	\N	\N	learning	f	24	2026-08-18 21:46:40	2026-08-18 21:46:40
01a016d7-4146-7177-87cf-93bccfd9cbc0	Grafana	\N	\N	\N	learning	f	25	2026-08-18 21:46:40	2026-08-18 21:46:40
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, remember_token, profile_picture_url, created_at, updated_at, is_admin) FROM stdin;
019f6f4e-4a19-713d-83b1-d375a745aa9f	admin	admin@example.com	2026-07-17 09:00:28	$2y$12$Hf7.EEMXpdFDdaXyYXutIOgYEpFW3qigUre2PoHqiHgLzzczXG9YK	\N	\N	\N	apl5AswCwA	\N	2026-07-17 09:00:29	2026-07-17 09:00:29	f
01a00c44-ad66-706a-b2f5-d6a8d7d9cd20	Berry Reynolds	nya.heller@example.org	2026-08-16 20:30:22	$2y$12$BkPL73pE38kYPfuEevh48O3MD5nKGUffsObtbHC.nhcmyEg8yg1Ca	\N	\N	\N	5ZCnucmHc4	\N	2026-08-16 20:30:22	2026-08-16 20:30:22	t
01a00c44-c915-7019-b119-f7bfce122aa7	Omari Bailey	elton.osinski@example.org	2026-08-16 20:30:29	$2y$12$33WUUkGNyJ7dLh6m0IbPhOXQr3AEyleHfNsJzW5O1lbZy.RjrOcOC	\N	\N	\N	YlMyc11CRS	\N	2026-08-16 20:30:29	2026-08-16 20:30:29	t
01a00c65-89eb-717e-b765-eecc8e3c9cfc	Rosalind Kuhn	vbednar@example.org	2026-08-16 21:06:16	$2y$12$T.c0S098UlG/Zg9NR8t41.snz6zVcs3mPcQxwC5UUcB/ejAI23y0S	\N	\N	\N	jCIFFXqdDX	\N	2026-08-16 21:06:16	2026-08-16 21:06:16	t
01a00c65-89f4-704c-8f1d-57f29fa27814	Novella Watsica	gust61@example.org	2026-08-16 21:06:16	$2y$12$T.c0S098UlG/Zg9NR8t41.snz6zVcs3mPcQxwC5UUcB/ejAI23y0S	\N	\N	\N	BwBj7BLAWY	\N	2026-08-16 21:06:16	2026-08-16 21:06:16	t
01a00c65-89f6-7305-a65c-0ebb21f24e13	Alisha Lowe	smith.rosina@example.com	2026-08-16 21:06:16	$2y$12$T.c0S098UlG/Zg9NR8t41.snz6zVcs3mPcQxwC5UUcB/ejAI23y0S	\N	\N	\N	YVActXilaA	\N	2026-08-16 21:06:16	2026-08-16 21:06:16	t
01a00c65-89f8-7178-a621-77c30a42b868	Karen Mann	susanna31@example.com	2026-08-16 21:06:16	$2y$12$T.c0S098UlG/Zg9NR8t41.snz6zVcs3mPcQxwC5UUcB/ejAI23y0S	\N	\N	\N	DPwVriXrUm	\N	2026-08-16 21:06:16	2026-08-16 21:06:16	t
01a00c65-89fb-7171-b4ab-b1f10df684ed	Dr. Joannie Bednar	braun.dillan@example.net	2026-08-16 21:06:16	$2y$12$T.c0S098UlG/Zg9NR8t41.snz6zVcs3mPcQxwC5UUcB/ejAI23y0S	\N	\N	\N	jM1UClT4Gp	\N	2026-08-16 21:06:16	2026-08-16 21:06:16	t
019f805e-737e-716d-8598-f2bc669c5d32	Richard Hyvl	richard.hyvl@gmail.com	2026-08-18 03:30:01	$2y$12$Lu6SJAWBFXNpZfhuVrDvhe5GCB71qFpCx8fR1G0KhGJJCUReMoxT.	\N	\N	\N	0Bw9WCKfU5FMZZdPIAPdD3B2SvV81ghLW0r6Ukolas51ZtfbrQwv34QFM112	\N	2026-07-20 16:31:41	2026-08-18 03:30:01	t
\.


--
-- Name: experiences_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.experiences_id_seq', 84, true);


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

SELECT pg_catalog.setval('public.migrations_id_seq', 39, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.settings_id_seq', 31, true);


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

\unrestrict XN5lOnrUByfYvsluOSmmuTmjqvfeB2hMCoYpU5tecgTBkopBwPp503xvlxqpxca

