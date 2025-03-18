DROP DATABASE IF EXISTS my_salone;
CREATE DATABASE my_salone DEFAULT CHARACTER SET = utf8;
USE my_salone;

CREATE TABLE clienti (
    mail                        VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    password                    CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F', 'Altro'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

CREATE TABLE saloni (
    id_salone                   INT AUTO_INCREMENT, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    indirizzo                   VARCHAR(100)        NOT NULL UNIQUE,
    posti                       INT                 NOT NULL,
    orario_apertura             TIME                NOT NULL,
    orario_chiusura             TIME                NOT NULL,
    PRIMARY KEY(id_salone)
) ENGINE = InnoDB;

CREATE TABLE barbieri (
    mail                        VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    password                    CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

/* Relazione tra barbieri e saloni */
CREATE TABLE lavora (
    fk_barbiere                 VARCHAR(100)        NOT NULL, /* FK */
    fk_salone                   INT                 NOT NULL, /* FK */
    PRIMARY KEY(fk_barbiere, fk_salone),
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_salone)      REFERENCES saloni(id_salone)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE servizi (
    id_servizio                 INT AUTO_INCREMENT, /*PK*/
    nome                        VARCHAR(50)         NOT NULL UNIQUE,
    durata                      INT                 NOT NULL, /* Durata in minuti */
    PRIMARY KEY(id_servizio)
) ENGINE = InnoDB;

/* Relazione tra saloni e servizi offerti */
CREATE TABLE salone_servizio (
    fk_salone                 INT                 NOT NULL, /* FK */
    fk_servizio               INT                 NOT NULL, /* FK */
    PRIMARY KEY(fk_salone, fk_servizio),
    FOREIGN KEY(fk_salone)    REFERENCES saloni(id_salone)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_servizio)    REFERENCES servizi(id_servizio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

/* Relazione tra barbieri e servizi che offrono */
CREATE TABLE barbiere_servizio (
    fk_barbiere                 VARCHAR(100)        NOT NULL, /* FK */
    fk_servizio                 INT                 NOT NULL, /* FK */
    PRIMARY KEY(fk_barbiere, fk_servizio),
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_servizio)    REFERENCES servizi(id_servizio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

/* ORARI DI LAVORO DEI BARBIERI */
CREATE TABLE turni_barbieri (
    id_turno                    INT AUTO_INCREMENT, /*PK*/
    fk_barbiere                 VARCHAR(100)        NOT NULL,
    fk_salone                   INT                 NOT NULL,
    giorno                      ENUM("Lu","Ma","Me","Gi","Ve","Sa","Do") NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    ora_fine                    TIME                NOT NULL,
    PRIMARY KEY(id_turno),
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_salone)      REFERENCES saloni(id_salone)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

/* APPUNTAMENTI */
CREATE TABLE appuntamenti (
    id_appuntamento             BIGINT AUTO_INCREMENT, /*PK*/
    fk_cliente                  VARCHAR(100)        NOT NULL,
    fk_turno                    INT                 NOT NULL,
    fk_servizio                 INT                 NOT NULL,
    data_app                    DATE                NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    ora_fine                    TIME,
    PRIMARY KEY(id_appuntamento),
    FOREIGN KEY(fk_cliente)     REFERENCES clienti(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_turno)       REFERENCES turni_barbieri(id_turno)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_servizio)    REFERENCES servizi(id_servizio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;
