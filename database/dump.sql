--
-- PostgreSQL database dump
--

-- Dumped from database version 15.10 (Debian 15.10-0+deb12u1)
-- Dumped by pg_dump version 15.10 (Debian 15.10-0+deb12u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry and geography spatial types and functions';


--
-- Name: notify_messenger_messages(); Type: FUNCTION; Schema: public; Owner: operateur
--

CREATE FUNCTION public.notify_messenger_messages() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$;


ALTER FUNCTION public.notify_messenger_messages() OWNER TO operateur;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: bus_stop; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.bus_stop (
    id integer NOT NULL,
    line_number character varying(50) NOT NULL
);


ALTER TABLE public.bus_stop OWNER TO operateur;

--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.doctrine_migration_versions (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO operateur;

--
-- Name: electrical; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.electrical (
    id integer NOT NULL,
    capacity character varying(50) NOT NULL
);


ALTER TABLE public.electrical OWNER TO operateur;

--
-- Name: log; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.log (
    id integer NOT NULL,
    user_id integer NOT NULL,
    table_name character varying(50) NOT NULL,
    id_element integer NOT NULL,
    old_data json NOT NULL,
    new_data json NOT NULL,
    updated_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.log OWNER TO operateur;

--
-- Name: log_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.log_id_seq OWNER TO operateur;

--
-- Name: log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.log_id_seq OWNED BY public.log.id;


--
-- Name: messenger_messages; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.messenger_messages (
    id bigint NOT NULL,
    body text NOT NULL,
    headers text NOT NULL,
    queue_name character varying(190) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    available_at timestamp(0) without time zone NOT NULL,
    delivered_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.messenger_messages OWNER TO operateur;

--
-- Name: COLUMN messenger_messages.created_at; Type: COMMENT; Schema: public; Owner: operateur
--

COMMENT ON COLUMN public.messenger_messages.created_at IS '(DC2Type:datetime_immutable)';


--
-- Name: COLUMN messenger_messages.available_at; Type: COMMENT; Schema: public; Owner: operateur
--

COMMENT ON COLUMN public.messenger_messages.available_at IS '(DC2Type:datetime_immutable)';


--
-- Name: COLUMN messenger_messages.delivered_at; Type: COMMENT; Schema: public; Owner: operateur
--

COMMENT ON COLUMN public.messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)';


--
-- Name: messenger_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.messenger_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.messenger_messages_id_seq OWNER TO operateur;

--
-- Name: messenger_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.messenger_messages_id_seq OWNED BY public.messenger_messages.id;


--
-- Name: network; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.network (
    id integer NOT NULL,
    name character varying(50) NOT NULL
);


ALTER TABLE public.network OWNER TO operateur;

--
-- Name: network_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.network_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.network_id_seq OWNER TO operateur;

--
-- Name: network_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.network_id_seq OWNED BY public.network.id;


--
-- Name: part_of; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.part_of (
    id integer NOT NULL
);


ALTER TABLE public.part_of OWNER TO operateur;

--
-- Name: part_of_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.part_of_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.part_of_id_seq OWNER TO operateur;

--
-- Name: part_of_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.part_of_id_seq OWNED BY public.part_of.id;


--
-- Name: path; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.path (
    id integer NOT NULL,
    network_id integer,
    name character varying(50) NOT NULL,
    color character varying(50) NOT NULL,
    path public.geometry(LineString) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    deleted_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.path OWNER TO operateur;

--
-- Name: path_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.path_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.path_id_seq OWNER TO operateur;

--
-- Name: path_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.path_id_seq OWNED BY public.path.id;


--
-- Name: path_part_of; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.path_part_of (
    path_id integer NOT NULL,
    part_of_id integer NOT NULL
);


ALTER TABLE public.path_part_of OWNER TO operateur;

--
-- Name: structure; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.structure (
    id integer NOT NULL,
    network_id integer NOT NULL,
    type_id integer NOT NULL,
    name character varying(128) NOT NULL,
    location public.geography NOT NULL,
    discriminator character varying(255) NOT NULL
);


ALTER TABLE public.structure OWNER TO operateur;

--
-- Name: COLUMN structure.location; Type: COMMENT; Schema: public; Owner: operateur
--

COMMENT ON COLUMN public.structure.location IS '(DC2Type:geography)';


--
-- Name: structure_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.structure_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.structure_id_seq OWNER TO operateur;

--
-- Name: structure_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.structure_id_seq OWNED BY public.structure.id;


--
-- Name: structure_part_of; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.structure_part_of (
    structure_id integer NOT NULL,
    part_of_id integer NOT NULL
);


ALTER TABLE public.structure_part_of OWNER TO operateur;

--
-- Name: type; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.type (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    network_id integer
);


ALTER TABLE public.type OWNER TO operateur;

--
-- Name: type_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.type_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.type_id_seq OWNER TO operateur;

--
-- Name: type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.type_id_seq OWNED BY public.type.id;


--
-- Name: user; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    email character varying(180) NOT NULL,
    role character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    created_at timestamp(0) without time zone NOT NULL,
    modified_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    deleted_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    network_id integer
);


ALTER TABLE public."user" OWNER TO operateur;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: operateur
--

CREATE SEQUENCE public.user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO operateur;

--
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: operateur
--

ALTER SEQUENCE public.user_id_seq OWNED BY public."user".id;


--
-- Name: water; Type: TABLE; Schema: public; Owner: operateur
--

CREATE TABLE public.water (
    id integer NOT NULL,
    water_pressure character varying(50) DEFAULT NULL::character varying,
    is_open boolean DEFAULT true NOT NULL
);


ALTER TABLE public.water OWNER TO operateur;

--
-- Name: log id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.log ALTER COLUMN id SET DEFAULT nextval('public.log_id_seq'::regclass);


--
-- Name: messenger_messages id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.messenger_messages ALTER COLUMN id SET DEFAULT nextval('public.messenger_messages_id_seq'::regclass);


--
-- Name: network id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.network ALTER COLUMN id SET DEFAULT nextval('public.network_id_seq'::regclass);


--
-- Name: part_of id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.part_of ALTER COLUMN id SET DEFAULT nextval('public.part_of_id_seq'::regclass);


--
-- Name: path id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path ALTER COLUMN id SET DEFAULT nextval('public.path_id_seq'::regclass);


--
-- Name: structure id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure ALTER COLUMN id SET DEFAULT nextval('public.structure_id_seq'::regclass);


--
-- Name: type id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.type ALTER COLUMN id SET DEFAULT nextval('public.type_id_seq'::regclass);


--
-- Name: user id; Type: DEFAULT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public."user" ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);


--
-- Data for Name: bus_stop; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.bus_stop (id, line_number) FROM stdin;
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20250513064808	2025-05-15 16:54:52	113
DoctrineMigrations\\Version20250515165511	2025-05-15 16:55:20	1
DoctrineMigrations\\Version20250515171109	2025-05-15 17:11:28	26
DoctrineMigrations\\Version20250515172635	2025-05-15 17:26:39	23
DoctrineMigrations\\Version20250515172907	2025-05-15 17:29:10	105
\.


--
-- Data for Name: electrical; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.electrical (id, capacity) FROM stdin;
\.


--
-- Data for Name: log; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.log (id, user_id, table_name, id_element, old_data, new_data, updated_at) FROM stdin;
\.


--
-- Data for Name: messenger_messages; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.messenger_messages (id, body, headers, queue_name, created_at, available_at, delivered_at) FROM stdin;
\.


--
-- Data for Name: network; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.network (id, name) FROM stdin;
1	Electricité
2	Eau
3	Bus
4	Admin
5	Electricité
6	Eau
7	Bus
8	Admin
\.


--
-- Data for Name: part_of; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.part_of (id) FROM stdin;
\.


--
-- Data for Name: path; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.path (id, network_id, name, color, path, created_at, updated_at, deleted_at) FROM stdin;
1	\N	Un chemin de test	#2e2e6b	0102000020E6100000060000002FFFFF676B23F83FB2C29D692539484075FFFF7F2920F83F05C648CC21394840B3FFFFA74C20F83F7E264BC11C394840D0FFFF67F520F83F2F0A71ED19394840B5FFFFC76523F83F459879101B3948402FFFFF676B23F83F5F66276225394840	2025-05-18 22:25:11	\N	2025-05-18 22:26:25
\.


--
-- Data for Name: path_part_of; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.path_part_of (path_id, part_of_id) FROM stdin;
\.


--
-- Data for Name: spatial_ref_sys; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text) FROM stdin;
\.


--
-- Data for Name: structure; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.structure (id, network_id, type_id, name, location, discriminator) FROM stdin;
\.


--
-- Data for Name: structure_part_of; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.structure_part_of (structure_id, part_of_id) FROM stdin;
\.


--
-- Data for Name: type; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.type (id, name, network_id) FROM stdin;
1	Arrêt de bus	3
2	Poteau haute tension	1
3	Fontaine	2
4	Arrêt de bus	3
5	Poteau haute tension	1
6	Fontaine	2
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public."user" (id, email, role, password, created_at, modified_at, deleted_at, network_id) FROM stdin;
40	aon@gmail.com	ROLE_FILIBUS	$2y$13$cBvr34OPprou881Fsg5nreCakbzFIQXjyaCD4a0Tr7Bcz4DxG7yq2	2025-05-18 22:14:21	\N	\N	3
\.


--
-- Data for Name: water; Type: TABLE DATA; Schema: public; Owner: operateur
--

COPY public.water (id, water_pressure, is_open) FROM stdin;
\.


--
-- Name: log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.log_id_seq', 1, false);


--
-- Name: messenger_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.messenger_messages_id_seq', 1, false);


--
-- Name: network_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.network_id_seq', 8, true);


--
-- Name: part_of_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.part_of_id_seq', 1, false);


--
-- Name: path_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.path_id_seq', 1, true);


--
-- Name: structure_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.structure_id_seq', 1, false);


--
-- Name: type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.type_id_seq', 6, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: operateur
--

SELECT pg_catalog.setval('public.user_id_seq', 40, true);


--
-- Name: bus_stop bus_stop_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.bus_stop
    ADD CONSTRAINT bus_stop_pkey PRIMARY KEY (id);


--
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: electrical electrical_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.electrical
    ADD CONSTRAINT electrical_pkey PRIMARY KEY (id);


--
-- Name: log log_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.log
    ADD CONSTRAINT log_pkey PRIMARY KEY (id);


--
-- Name: messenger_messages messenger_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.messenger_messages
    ADD CONSTRAINT messenger_messages_pkey PRIMARY KEY (id);


--
-- Name: network network_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.network
    ADD CONSTRAINT network_pkey PRIMARY KEY (id);


--
-- Name: part_of part_of_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.part_of
    ADD CONSTRAINT part_of_pkey PRIMARY KEY (id);


--
-- Name: path_part_of path_part_of_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path_part_of
    ADD CONSTRAINT path_part_of_pkey PRIMARY KEY (path_id, part_of_id);


--
-- Name: path path_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path
    ADD CONSTRAINT path_pkey PRIMARY KEY (id);


--
-- Name: structure_part_of structure_part_of_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure_part_of
    ADD CONSTRAINT structure_part_of_pkey PRIMARY KEY (structure_id, part_of_id);


--
-- Name: structure structure_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_pkey PRIMARY KEY (id);


--
-- Name: type type_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.type
    ADD CONSTRAINT type_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: water water_pkey; Type: CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.water
    ADD CONSTRAINT water_pkey PRIMARY KEY (id);


--
-- Name: idx_2f15fd772534008b; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_2f15fd772534008b ON public.structure_part_of USING btree (structure_id);


--
-- Name: idx_2f15fd77c97ef49f; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_2f15fd77c97ef49f ON public.structure_part_of USING btree (part_of_id);


--
-- Name: idx_3301bb6fc97ef49f; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_3301bb6fc97ef49f ON public.path_part_of USING btree (part_of_id);


--
-- Name: idx_3301bb6fd96c566b; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_3301bb6fd96c566b ON public.path_part_of USING btree (path_id);


--
-- Name: idx_6f0137ea34128b91; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_6f0137ea34128b91 ON public.structure USING btree (network_id);


--
-- Name: idx_6f0137eac54c8c93; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_6f0137eac54c8c93 ON public.structure USING btree (type_id);


--
-- Name: idx_75ea56e016ba31db; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_75ea56e016ba31db ON public.messenger_messages USING btree (delivered_at);


--
-- Name: idx_75ea56e0e3bd61ce; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_75ea56e0e3bd61ce ON public.messenger_messages USING btree (available_at);


--
-- Name: idx_75ea56e0fb7336f0; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_75ea56e0fb7336f0 ON public.messenger_messages USING btree (queue_name);


--
-- Name: idx_8cde572934128b91; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_8cde572934128b91 ON public.type USING btree (network_id);


--
-- Name: idx_8d93d64934128b91; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_8d93d64934128b91 ON public."user" USING btree (network_id);


--
-- Name: idx_8f3f68c5a76ed395; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_8f3f68c5a76ed395 ON public.log USING btree (user_id);


--
-- Name: idx_b548b0f34128b91; Type: INDEX; Schema: public; Owner: operateur
--

CREATE INDEX idx_b548b0f34128b91 ON public.path USING btree (network_id);


--
-- Name: uniq_identifier_email; Type: INDEX; Schema: public; Owner: operateur
--

CREATE UNIQUE INDEX uniq_identifier_email ON public."user" USING btree (email);


--
-- Name: messenger_messages notify_trigger; Type: TRIGGER; Schema: public; Owner: operateur
--

CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON public.messenger_messages FOR EACH ROW EXECUTE FUNCTION public.notify_messenger_messages();


--
-- Name: structure_part_of fk_2f15fd772534008b; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure_part_of
    ADD CONSTRAINT fk_2f15fd772534008b FOREIGN KEY (structure_id) REFERENCES public.part_of(id);


--
-- Name: structure_part_of fk_2f15fd77c97ef49f; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure_part_of
    ADD CONSTRAINT fk_2f15fd77c97ef49f FOREIGN KEY (part_of_id) REFERENCES public.structure(id);


--
-- Name: path_part_of fk_3301bb6fc97ef49f; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path_part_of
    ADD CONSTRAINT fk_3301bb6fc97ef49f FOREIGN KEY (part_of_id) REFERENCES public.path(id);


--
-- Name: path_part_of fk_3301bb6fd96c566b; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path_part_of
    ADD CONSTRAINT fk_3301bb6fd96c566b FOREIGN KEY (path_id) REFERENCES public.part_of(id);


--
-- Name: electrical fk_6bba6b48bf396750; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.electrical
    ADD CONSTRAINT fk_6bba6b48bf396750 FOREIGN KEY (id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: structure fk_6f0137ea34128b91; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT fk_6f0137ea34128b91 FOREIGN KEY (network_id) REFERENCES public.network(id);


--
-- Name: structure fk_6f0137eac54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT fk_6f0137eac54c8c93 FOREIGN KEY (type_id) REFERENCES public.type(id);


--
-- Name: type fk_8cde572934128b91; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.type
    ADD CONSTRAINT fk_8cde572934128b91 FOREIGN KEY (network_id) REFERENCES public.network(id);


--
-- Name: user fk_8d93d64934128b91; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT fk_8d93d64934128b91 FOREIGN KEY (network_id) REFERENCES public.network(id);


--
-- Name: log fk_8f3f68c5a76ed395; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.log
    ADD CONSTRAINT fk_8f3f68c5a76ed395 FOREIGN KEY (user_id) REFERENCES public."user"(id);


--
-- Name: path fk_b548b0f34128b91; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.path
    ADD CONSTRAINT fk_b548b0f34128b91 FOREIGN KEY (network_id) REFERENCES public.network(id);


--
-- Name: bus_stop fk_e65b69fcbf396750; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.bus_stop
    ADD CONSTRAINT fk_e65b69fcbf396750 FOREIGN KEY (id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: water fk_fb3314dabf396750; Type: FK CONSTRAINT; Schema: public; Owner: operateur
--

ALTER TABLE ONLY public.water
    ADD CONSTRAINT fk_fb3314dabf396750 FOREIGN KEY (id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

