DROP DATABASE IF EXISTS my_salone;

CREATE DATABASE my_salone DEFAULT CHARACTER SET = utf8;

USE my_salone;

CREATE TABLE tclienti (
    nome                        VARCHAR(20)         NOT NULL,
    cognome                     VARCHAR(20)         NOT NULL,
    mail                        VARCHAR(40)         NOT NULL    UNIQUE,
    pass                        CHAR(60)            NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F'),
    residenza                   VARCHAR(60),       
    data_nascita                DATE,  
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

DELIMITER $$

CREATE TRIGGER trg_nome_cognome_insert /*NOME*/
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF CHAR_LENGTH(NEW.nome) < 2 AND CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'entrambi';
    END IF;
    IF CHAR_LENGTH(NEW.nome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'nome';
    END IF;
    IF CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cognome';
    END IF;
END $$

CREATE TRIGGER trg_nome_cognome_update /*NOME*/
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF CHAR_LENGTH(NEW.nome) < 2 AND CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'entrambi';
    END IF;
    IF CHAR_LENGTH(NEW.nome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'nome';
    END IF;
    IF CHAR_LENGTH(NEW.cognome) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cognome';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_insert /*DATA*/
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'data';
    END IF;
END $$

CREATE TRIGGER trg_data_nascita_update /*DATA*/
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF NEW.data_nascita > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'data';
    END IF;
END $$

CREATE TRIGGER trg_numero_insert /*NUMERO*/
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'numero';
    END IF;
END $$  

CREATE TRIGGER trg_numero_update /*NUMERO*/
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN   
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'numero';
    END IF;
END $$  

CREATE TRIGGER trg_mail_insert /*MAIL*/
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'mail';
    END IF;
END $$

CREATE TRIGGER trg_mail_update /*MAIL*/
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.mail REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'mail';
    END IF;
END $$

CREATE TRIGGER trg_password_insert /*PASSWORD*/
BEFORE INSERT ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.pass REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}|;:,.<>?/-]).{8,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'password';
    END IF;
END $$

CREATE TRIGGER trg_password_update /*PASSWORD*/
BEFORE UPDATE ON tclienti
FOR EACH ROW
BEGIN
    IF NOT NEW.pass REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}|;:,.<>?/-]).{8,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'password';
    END IF;
END $$

DELIMITER ;