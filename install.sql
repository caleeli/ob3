-- Postgres version: 11.5
DROP TABLE IF EXISTS "adrol";

DROP SEQUENCE IF EXISTS adrol_id_seq;

CREATE SEQUENCE adrol_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "adrol" (
    "adrolcodi" integer DEFAULT nextval('adrol_id_seq') NOT NULL,
    "adrolnomb" varchar(128) NOT NULL,
    "dashboard" varchar(128) NOT NULL DEFAULT 'dashboard',
    PRIMARY KEY ("adrolcodi")
);

INSERT INTO
    "adrol" ("adrolcodi", "adrolnomb", "dashboard")
VALUES
    (1, 'Admin', '');

DROP TABLE IF EXISTS "menus";

CREATE TABLE "menus" (
    "ID" varchar(32) NOT NULL,
    "TEXT" varchar(255) NOT NULL,
    "LEAF" integer NOT NULL,
    "PARENT" varchar(32) NOT NULL,
    "PAGE" varchar(255) NOT NULL,
    "TYPE" varchar(128) NOT NULL DEFAULT 'formulario',
    "POSITION" integer NOT NULL,
    "role_id" integer DEFAULT NULL,
    "params" varchar(1024) DEFAULT NULL,
    PRIMARY KEY ("ID")
);

DROP TABLE IF EXISTS "users";

DROP SEQUENCE IF EXISTS users_id_seq;

CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "users" (
    "adusrcorr" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "adusrcodi" integer DEFAULT NULL,
    "adusrnomb" char(100) DEFAULT NULL,
    "adusrapod" char(30) DEFAULT NULL,
    "adusrlogi" char(30) DEFAULT NULL,
    "adusrpass" char(32) DEFAULT NULL,
    "adusrdir1" char(100) DEFAULT NULL,
    "adusrtel1" char(100) DEFAULT NULL,
    "adusrplaz" integer DEFAULT NULL,
    "adusragen" integer DEFAULT NULL,
    "adusrfech" date DEFAULT NULL,
    "adusrstat" integer DEFAULT NULL,
    "adrolcodi" integer DEFAULT NULL,
    "desc_regi" varchar(30) DEFAULT NULL,
    "desc_agen" varchar(30) DEFAULT NULL,
    "adusrarea" varchar(32) NOT NULL DEFAULT 'EMP',
    PRIMARY KEY ("adusrcorr")
);

INSERT INTO
    "users" (
        "adusrcorr",
        "adusrcodi",
        "adusrnomb",
        "adusrapod",
        "adusrlogi",
        "adusrpass",
        "adusrdir1",
        "adusrtel1",
        "adusrplaz",
        "adusragen",
        "adusrfech",
        "adusrstat",
        "adrolcodi",
        "desc_regi",
        "desc_agen",
        "adusrarea"
    )
VALUES
    (
        1,
        20001,
        'ADMINISTRADOR DE SISTEMA',
        '',
        'admin',
        '5e8ff9bf55ba3508199d22e984129be6',
        '',
        '',
        20,
        1,
        NULL,
        NULL,
        1,
        'LA PAZ',
        'CENTRAL',
        'EMP'
    );

DROP TABLE IF EXISTS login;

DROP SEQUENCE IF EXISTS login_id_seq;

CREATE SEQUENCE login_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE login (
    id integer DEFAULT nextval('login_id_seq') NOT NULL,
    user_id integer NOT NULL,
    username varchar(100) NOT NULL,
    created_at timestamp(0),
    CONSTRAINT login_ibfk_1 PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS permissions;

DROP SEQUENCE IF EXISTS permissions_id_seq;

CREATE SEQUENCE permissions_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE permissions (
    id integer DEFAULT nextval('permissions_id_seq') NOT NULL,
    role_id integer NOT NULL,
    permission integer NOT NULL,
    PRIMARY KEY (role_id, permission)
);