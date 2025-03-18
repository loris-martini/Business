DROP DATABASE IF EXISTS my_salone;
CREATE DATABASE my_salone DEFAULT CHARACTER SET = utf8;
USE my_salone;

-- Tabella Clienti
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

-- Tabella Saloni
CREATE TABLE saloni (
    id_salone                   INT AUTO_INCREMENT, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    indirizzo                   VARCHAR(100)        NOT NULL UNIQUE,
    posti                       INT                 NOT NULL,
    posti_disponibili           INT                 NOT NULL,
    orario_apertura             TIME                NOT NULL, 
    orario_chiusura             TIME                NOT NULL, 
    PRIMARY KEY(id_salone)
) ENGINE = InnoDB;

-- Tabella Barbieri
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

-- Relazione Barbieri-Salone (Un barbiere può lavorare in più saloni)
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

-- Tabella Servizi
CREATE TABLE servizi (
    id_servizio                 INT AUTO_INCREMENT, /*PK*/
    nome                        VARCHAR(50)         NOT NULL UNIQUE,
    durata                      INT                 NOT NULL, /* Durata in minuti */
    prezzo                      DECIMAL(10,2)       NOT NULL DEFAULT 0.00, /* Nuovo */
    PRIMARY KEY(id_servizio)
) ENGINE = InnoDB;

-- Relazione Salone-Servizio
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

-- Relazione Barbiere-Servizio
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

-- Tabella Turni Barbieri (per sapere dove e quando lavora un barbiere)
CREATE TABLE turni_barbieri (
    id_turno        BIGINT AUTO_INCREMENT, /*PK*/
    fk_barbiere     VARCHAR(100) NOT NULL, /* FK */
    fk_salone       INT NOT NULL, /* FK */
    giorno          ENUM("Lu","Ma","Me","Gi","Ve","Sa","Do") NOT NULL,
    ora_inizio      TIME NOT NULL,
    ora_fine        TIME NOT NULL,
    PRIMARY KEY(id_turno),
    FOREIGN KEY(fk_barbiere) REFERENCES barbieri(mail)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY(fk_salone) REFERENCES saloni(id_salone)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Tabella Appuntamenti
CREATE TABLE appuntamenti (
    id_appuntamento             BIGINT AUTO_INCREMENT, /*PK*/
    fk_cliente                  VARCHAR(100)        NOT NULL,
    fk_turno                    BIGINT              NOT NULL, /* FK - Nuovo */
    fk_servizio                 INT                 NOT NULL,
    data_app                    DATE                NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    ora_fine                    TIME,
    stato                       ENUM('IN_ATTESA', 'CONFERMATO', 'COMPLETATO', 'CANCELLATO') DEFAULT 'IN_ATTESA', /* Nuovo */
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