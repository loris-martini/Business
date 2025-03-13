DROP DATABASE IF EXISTS my_salone;

CREATE DATABASE my_salone DEFAULT CHARACTER SET = utf8;

USE my_salone;

CREATE TABLE clienti (
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    mail                        VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    password                    CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

CREATE TABLE barbieri (
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    mail                        VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    password                    CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

CREATE TABLE saloni (
    indirizzo                   VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    posti                       INT NOT NULL,
    disponibilita               INT NOT NULL,
    PRIMARY KEY(indirizzo)
) ENGINE = InnoDB;

CREATE TABLE appuntamenti (
    id_appuntamento             BIGINT          AUTO_INCREMENT, /*PK*/
    servizio                    VARCHAR(50)     NOT NULL,
    data_app                    DATE            NOT NULL,
    ora_inizio                  TIME            NOT NULL,
    ora_fine                    TIME,
    fk_cliente                  VARCHAR(100)    NOT NULL,
    fk_barbiere                 VARCHAR(100)    NOT NULL,
    fk_salone                   VARCHAR(100)    NOT NULL,
    PRIMARY KEY(id_appuntamento),
    FOREIGN KEY(fk_cliente)     REFERENCES clienti(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_barbiere)    REFERENCES barbieri(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY(fk_salone)      REFERENCES saloni(indirizzo)
        ON UPDATE CASCADE
        ON DELETE CASCADE        
) ENGINE = InnoDB;

DELIMITER $$

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

CREATE TRIGGER trg_numero_insert
BEFORE INSERT ON clienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il numero di telefono deve essere composto da 10 cifre';
    END IF;
END $$  

CREATE TRIGGER trg_numero_update
BEFORE UPDATE ON clienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il numero di telefono deve essere composto da 10 cifre';
    END IF;
END $$  

DELIMITER ;