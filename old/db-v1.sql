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
    orario_apertura             TIME                NOT NULL, /* Nuovo */
    orario_chiusura             TIME                NOT NULL, /* Nuovo */
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

/*BARBIERE - SALONE*/
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

/*SALONE - SERVIZIO*/
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

/*BARBIERE - SERVIZIO*/
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

CREATE TABLE orari_barbieri (
    id_orario                   INT AUTO_INCREMENT, /*PK*/
    fk_barbiere                 VARCHAR(100)        NOT NULL,
    fk_salone                   INT                 NOT NULL,
    giorno                      DATE                NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    ora_fine                    TIME                NOT NULL,
    PRIMARY KEY(id_orario),
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_salone)      REFERENCES saloni(id_salone)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE appuntamenti (
    id_appuntamento             BIGINT AUTO_INCREMENT, /*PK*/
    fk_cliente                  VARCHAR(100)        NOT NULL,
    fk_barbiere                 VARCHAR(100)        NOT NULL,
    fk_salone                   INT                 NOT NULL,
    fk_servizio                 INT                 NOT NULL,
    data_app                    DATE                NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    ora_fine                    TIME,
    PRIMARY KEY(id_appuntamento),
    FOREIGN KEY(fk_cliente)     REFERENCES clienti(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_salone)      REFERENCES saloni(id_salone)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_servizio)    REFERENCES servizi(id_servizio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

DELIMITER $$

/* Validazioni per la tabella clienti */
CREATE TRIGGER trg_password_insert
BEFORE INSERT ON clienti
FOR EACH ROW
BEGIN
    IF NOT NEW.password REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La password deve contenere almeno una lettera maiuscola, una minuscola e un numero';
    END IF;
END $$

CREATE TRIGGER trg_password_update
BEFORE UPDATE ON clienti
FOR EACH ROW
BEGIN
    IF NOT NEW.password REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La password deve contenere almeno una lettera maiuscola, una minuscola e un numero';
    END IF;
END $$

CREATE TRIGGER trg_mail_insert
BEFORE INSERT ON clienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Formato della mail non valido';
    END IF;
END $$

CREATE TRIGGER trg_mail_update
BEFORE UPDATE ON clienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Formato della mail non valido';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_insert
BEFORE INSERT ON clienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La data di nascita non può essere futura';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_update
BEFORE UPDATE ON clienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La data di nascita non può essere futura';
    END IF;
END $$

CREATE TRIGGER trg_check_orari_barbieri
BEFORE INSERT ON orari_barbieri
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM orari_barbieri
        WHERE fk_barbiere = NEW.fk_barbiere
        AND fk_salone = NEW.fk_salone
        AND giorno = NEW.giorno
        AND (
            (NEW.ora_inizio BETWEEN ora_inizio AND ora_fine)
            OR (NEW.ora_fine BETWEEN ora_inizio AND ora_fine)
            OR (ora_inizio BETWEEN NEW.ora_inizio AND NEW.ora_fine)
        )
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il barbiere ha già un orario che si sovrappone in questo salone';
    END IF;
END $$

CREATE TRIGGER trg_check_orari_salone
BEFORE INSERT ON orari_barbieri
FOR EACH ROW
BEGIN
    DECLARE apertura TIME;
    DECLARE chiusura TIME;

    SELECT orario_apertura, orario_chiusura INTO apertura, chiusura
    FROM saloni WHERE id_salone = NEW.fk_salone;

    IF NEW.ora_inizio < apertura OR NEW.ora_fine > chiusura THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Il turno del barbiere deve rientrare negli orari del salone';
    END IF;
END $$

CREATE TRIGGER trg_check_orario_appuntamento_salone
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    DECLARE apertura TIME;
    DECLARE chiusura TIME;

    SELECT orario_apertura, orario_chiusura INTO apertura, chiusura
    FROM saloni WHERE id_salone = NEW.fk_salone;

    IF NEW.ora_inizio < apertura OR NEW.ora_fine > chiusura THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'L’appuntamento deve essere dentro gli orari del salone';
    END IF;
END $$

CREATE TRIGGER trg_check_orario_appuntamento_barbiere
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM orari_barbieri
        WHERE fk_barbiere = NEW.fk_barbiere
        AND fk_salone = NEW.fk_salone
        AND giorno = NEW.data_app
        AND NEW.ora_inizio >= ora_inizio
        AND NEW.ora_fine <= ora_fine
    ) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Il barbiere non è disponibile in questo orario';
    END IF;
END $$

CREATE TRIGGER trg_check_conflitto_appuntamenti
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM appuntamenti
        WHERE fk_barbiere = NEW.fk_barbiere
        AND data_app = NEW.data_app
        AND (
            (NEW.ora_inizio BETWEEN ora_inizio AND ora_fine)
            OR (NEW.ora_fine BETWEEN ora_inizio AND ora_fine)
            OR (ora_inizio BETWEEN NEW.ora_inizio AND NEW.ora_fine)
        )
    ) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Il barbiere ha già un appuntamento in questo orario';
    END IF;
END $$

CREATE TRIGGER trg_check_barbiere_salone
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM lavora
        WHERE fk_barbiere = NEW.fk_barbiere
        AND fk_salone = NEW.fk_salone
    ) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Il barbiere non lavora in questo salone';
    END IF;
END $$  

CREATE TRIGGER trg_check_cliente_conflitto
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM appuntamenti
        WHERE fk_cliente = NEW.fk_cliente
        AND data_app = NEW.data_app
        AND (
            (NEW.ora_inizio BETWEEN ora_inizio AND ora_fine)
            OR (NEW.ora_fine BETWEEN ora_inizio AND ora_fine)
            OR (ora_inizio BETWEEN NEW.ora_inizio AND NEW.ora_fine)
        )
    ) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Il cliente ha già un appuntamento in questo orario';
    END IF;
END $$ 

CREATE TRIGGER trg_set_ora_fine
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    DECLARE durata_servizio INT;
    
    SELECT durata INTO durata_servizio
    FROM servizi WHERE id_servizio = NEW.fk_servizio;
    
    SET NEW.ora_fine = ADDTIME(NEW.ora_inizio, SEC_TO_TIME(durata_servizio * 60));

    IF NEW.ora_fine > (SELECT orario_chiusura FROM saloni WHERE id_salone = NEW.fk_salone) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'L’appuntamento non può terminare dopo la chiusura del salone';
    END IF;

END $$  

DELIMITER ;