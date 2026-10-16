--
-- PostgreSQL database dump
--

\restrict yEv0BQplS0RkJmg9sWmqkbKvJDVkufpj8zjKOt5HS4abXKAr93EflkongqAt8R3

-- Dumped from database version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)

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
-- Name: users_display_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.users_display_status AS ENUM (
    'active',
    'inactive'
);


ALTER TYPE public.users_display_status OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: doc_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.doc_type (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.doc_type OWNER TO postgres;

--
-- Name: doc_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.doc_type_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.doc_type_id_seq OWNER TO postgres;

--
-- Name: doc_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.doc_type_id_seq OWNED BY public.doc_type.id;


--
-- Name: file_document; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.file_document (
    id integer NOT NULL,
    file_id integer NOT NULL,
    doc_type_id integer NOT NULL,
    rule_name character varying(200) NOT NULL,
    doc_date date NOT NULL,
    rule_pdf character varying(100) NOT NULL,
    remarks character varying(1000) NOT NULL,
    is_deleted boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone NOT NULL,
    modified_at timestamp without time zone,
    deleted_at timestamp without time zone
);


ALTER TABLE public.file_document OWNER TO postgres;

--
-- Name: file_document_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.file_document_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.file_document_id_seq OWNER TO postgres;

--
-- Name: file_document_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.file_document_id_seq OWNED BY public.file_document.id;


--
-- Name: file_master; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.file_master (
    id integer NOT NULL,
    file_code character varying(6),
    file_number character varying(15) NOT NULL,
    subject character varying(200) NOT NULL,
    remarks character varying(1000) NOT NULL,
    is_deleted boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone NOT NULL,
    modified_at timestamp without time zone,
    deleted_at timestamp without time zone
);


ALTER TABLE public.file_master OWNER TO postgres;

--
-- Name: file_master_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.file_master_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.file_master_id_seq OWNER TO postgres;

--
-- Name: file_master_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.file_master_id_seq OWNED BY public.file_master.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    user_name character varying(50) NOT NULL,
    password character varying(50) NOT NULL,
    role smallint DEFAULT 2 NOT NULL,
    status public.users_display_status DEFAULT 'active'::public.users_display_status NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone,
    full_name character varying(100),
    CONSTRAINT users_role_check CHECK ((role = ANY (ARRAY[1, 2])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO postgres;

--
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- Name: doc_type id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.doc_type ALTER COLUMN id SET DEFAULT nextval('public.doc_type_id_seq'::regclass);


--
-- Name: file_document id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_document ALTER COLUMN id SET DEFAULT nextval('public.file_document_id_seq'::regclass);


--
-- Name: file_master id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_master ALTER COLUMN id SET DEFAULT nextval('public.file_master_id_seq'::regclass);


--
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- Data for Name: doc_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.doc_type VALUES (1, 'Rules', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (2, 'Procedure', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (3, 'Notesheet', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (4, 'Circular', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (5, 'Notification', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (6, 'Memo', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (7, 'Orders', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (8, 'ACT', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (9, 'Judgement', '2026-10-08 12:22:18');
INSERT INTO public.doc_type VALUES (10, 'GOI_Circular', '2026-10-08 12:22:18');


--
-- Data for Name: file_document; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.file_document VALUES (1, 2, 3, 'notesheet', '2026-10-12', 'uploads/documents/notesheet_00001.pdf', 'test', false, '2026-10-12 05:37:55', NULL, NULL);
INSERT INTO public.file_document VALUES (2, 2, 2, 'precedure', '2026-10-12', 'uploads/documents/procedure_00002.pdf', 'asdasdasda', false, '2026-10-12 06:44:31', NULL, NULL);
INSERT INTO public.file_document VALUES (3, 2, 8, 'adfasdasd', '2026-10-12', 'uploads/documents/act_00003.pdf', 'asdasdasd', false, '2026-10-12 06:49:32', NULL, NULL);
INSERT INTO public.file_document VALUES (4, 3, 4, 'circular', '2026-10-12', 'uploads/documents/circular_00004.pdf', 'test', false, '2026-10-12 07:12:30', NULL, NULL);
INSERT INTO public.file_document VALUES (5, 3, 8, 'act test', '2026-10-12', 'uploads/documents/act_00005.pdf', 'testing', false, '2026-10-12 10:49:18', NULL, NULL);
INSERT INTO public.file_document VALUES (6, 1, 2, 'procedure test', '2026-10-12', 'uploads/documents/procedure_00006.pdf', 'procedure testing', false, '2026-10-12 13:27:12', NULL, NULL);
INSERT INTO public.file_document VALUES (7, 6, 6, 'Ministry of external affairs CPV Division,Govt of india', '2015-05-26', 'uploads/documents/memo_00007.pdf', 'Circular for NOC Identity Certificate and Prior intimation', false, '2026-10-13 06:01:41', NULL, NULL);


--
-- Data for Name: file_master; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.file_master VALUES (1, 'F00001', 'I-8-6/2001', 'State Legal Services Authority', 'testing legal services', false, '2026-10-08 07:36:09', NULL, NULL);
INSERT INTO public.file_master VALUES (7, 'F00007', 'II-02-2002', 'unknown subject', 'test', false, '2026-10-15 07:10:17', NULL, NULL);
INSERT INTO public.file_master VALUES (4, 'F00004', 'II-15-22/2000', 'For Providing Security to Judges', 'security to judges', false, '2026-10-08 07:40:58', NULL, NULL);
INSERT INTO public.file_master VALUES (6, 'F00006', 'II-3-5/2000', 'No Objection Certificate For Passport', 'For Judicial Officer(TEST)', false, '2026-10-13 05:58:48', '2026-10-16 06:19:41', NULL);
INSERT INTO public.file_master VALUES (5, 'F00005', 'file-234', 'confidential', 'confidential test', false, '2026-10-09 05:03:57', '2026-10-16 06:47:52', NULL);
INSERT INTO public.file_master VALUES (3, 'F00003', 'I-8-3/2000', 'Election of Adhoc State Bar Council C.G. and Adhoc High Court Bar Association', 'Adhoc state bar Record Room', false, '2026-10-08 07:39:31', '2026-10-16 06:48:59', NULL);
INSERT INTO public.file_master VALUES (2, 'F00002', 'I-8-4/2001', 'Sad Demise Reference', 'Reference Record Room', false, '2026-10-08 07:37:29', '2026-10-16 06:49:43', NULL);
INSERT INTO public.file_master VALUES (8, 'F00008', 'ABC-121/2026', 'testing', 'a test', false, '2026-10-15 10:31:59', NULL, '2026-10-16 06:12:29');


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users VALUES (1, 'admin', 'admin@123', 1, 'active', '2026-10-06 11:01:15.292771', NULL, 'Admin');


--
-- Name: doc_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.doc_type_id_seq', 10, true);


--
-- Name: file_document_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.file_document_id_seq', 7, true);


--
-- Name: file_master_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.file_master_id_seq', 8, true);


--
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_user_id_seq', 1, true);


--
-- Name: doc_type doc_type_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.doc_type
    ADD CONSTRAINT doc_type_name_key UNIQUE (name);


--
-- Name: doc_type doc_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.doc_type
    ADD CONSTRAINT doc_type_pkey PRIMARY KEY (id);


--
-- Name: file_master file_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_master
    ADD CONSTRAINT file_number_unique UNIQUE (file_number);


--
-- Name: file_master pk_file_master; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_master
    ADD CONSTRAINT pk_file_master PRIMARY KEY (id);


--
-- Name: file_document pk_file_rule; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_document
    ADD CONSTRAINT pk_file_rule PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: users users_user_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_user_name_key UNIQUE (user_name);


--
-- Name: idx_file_document_doc_type_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_file_document_doc_type_id ON public.file_document USING btree (doc_type_id);


--
-- Name: idx_file_document_file_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_file_document_file_id ON public.file_document USING btree (file_id);


--
-- Name: file_document fk_file_document_doc_type; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_document
    ADD CONSTRAINT fk_file_document_doc_type FOREIGN KEY (doc_type_id) REFERENCES public.doc_type(id);


--
-- Name: file_document fk_file_document_file; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.file_document
    ADD CONSTRAINT fk_file_document_file FOREIGN KEY (file_id) REFERENCES public.file_master(id);


--
-- PostgreSQL database dump complete
--

\unrestrict yEv0BQplS0RkJmg9sWmqkbKvJDVkufpj8zjKOt5HS4abXKAr93EflkongqAt8R3

