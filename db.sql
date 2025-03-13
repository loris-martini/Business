DROP DATABASE IF EXISTS my_salone;

CREATE DATABASE my_salone DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE my_salone;

CREATE TABLE tclienti (
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    mail                        VARCHAR(100)        NOT NULL UNIQUE,
    pass                        CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

CREATE TABLE tappuntamenti (
    id_appuntamento             BIGINT          AUTO_INCREMENT,
    servizio                    VARCHAR(50)     NOT NULL,
    data_app                    DATE            NOT NULL,
    ora_inizio                  TIME            NOT NULL,
    ora_fine                    TIME,
    fk_cliente                  VARCHAR(100)    NOT NULL,
    PRIMARY KEY(id_appuntamento),
    FOREIGN KEY(fk_cliente) REFERENCES tclienti(mail)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

DELIMITER $$

CREATE TRIGGER trg_nome_cognome_insert
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF CHAR_LENGTH(NEW.nome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il nome deve contenere almeno 2 caratteri';
    END IF;
    IF CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il cognome deve contenere almeno 2 caratteri';
    END IF;
END $$

CREATE TRIGGER trg_nome_cognome_update
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF CHAR_LENGTH(NEW.nome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il nome deve contenere almeno 2 caratteri';
    END IF;
    IF CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il cognome deve contenere almeno 2 caratteri';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_insert
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La data di nascita non può essere nel futuro';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_update
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La data di nascita non può essere nel futuro';
    END IF;
END $$

CREATE TRIGGER trg_numero_insert
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il numero di telefono deve essere composto da 10 cifre';
    END IF;
END $$  

CREATE TRIGGER trg_numero_update
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Il numero di telefono deve essere composto da 10 cifre';
    END IF;
END $$  

CREATE TRIGGER trg_mail_insert
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Formato email non valido';
    END IF;
END $$

CREATE TRIGGER trg_mail_update
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Formato email non valido';
    END IF;
END $$

DELIMITER ;
