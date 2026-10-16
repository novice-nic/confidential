INSERT INTO
    users (user_name, password, role, full_name)
VALUES
    ('admin', 'admin@1', 1, 'Admin');

CREATE TYPE users_display_status AS ENUM ('active', 'inactive');

CREATE TABLE
    users (
        user_id SERIAL PRIMARY KEY,
        user_name VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(50) NOT NULL,
        role SMALLINT NOT NULL DEFAULT 2 CHECK (role IN (1, 2)),
        status users_display_status NOT NULL DEFAULT 'active',
        created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL,
        full_name VARCHAR(100)
    );

CREATE TABLE
    file_master (
        id serial NOT NULL,
        file_code varchar(6) NOT NULL,
        file_number varchar(15) NOT NULL,
        subject varchar(200) NOT NULL,
        remarks varchar(1000) NOT NULL,
        is_deleted boolean NOT NULL default FALSE,
        created_at timestamp without time zone NOT NULL,
        modified_at timestamp without time zone NULL,
        deleted_at timestamp without time zone NULL,
        CONSTRAINT PK_file_master PRIMARY KEY (id)
    );

-- CREATE TABLE
--     file_rule (
--         id serial NOT NULL,
--         file_id integer NOT NULL,
--         rule_name varchar(200) NOT NULL,
--         rule_date date NOT NULL,
--         rule_pdf varchar(100) NOT NULL,
--         remarks varchar(1000) NOT NULL,
--         is_deleted boolean NOT NULL default FALSE,
--         created_at timestamp without time zone NOT NULL,
--         modified_at timestamp without time zone NULL,
--         deleted_at timestamp without time zone NULL,
--         CONSTRAINT PK_file_rule PRIMARY KEY (id)
--     );

CREATE TABLE
    file_document (
        id serial NOT NULL,
        file_id integer NOT NULL,
        doc_type_id integer NOT NULL,
        rule_name varchar(200) NOT NULL,
        rule_date date NOT NULL,
        rule_pdf varchar(100) NOT NULL,
        remarks varchar(1000) NOT NULL,
        is_deleted boolean NOT NULL default FALSE,
        created_at timestamp without time zone NOT NULL,
        modified_at timestamp without time zone NULL,
        deleted_at timestamp without time zone NULL,
        CONSTRAINT PK_file_rule PRIMARY KEY (id)
    );

CREATE TABLE
    doc_type (
        id SERIAL NOT NULL,
        name VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

INSERT INTO doc_type (name)
VALUES
    ('Rules'),
    ('Procedure'),
    ('Notesheet'),
    ('Circular'),
    ('Notification'),
    ('Memo'),
    ('Orders'),
    ('ACT'),
    ('Judgement'),
    ('GOI Circular');

UPDATE users SET password = 'admin@123' WHERE user_id = 1;