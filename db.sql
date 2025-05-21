DROP DATABASE IF EXISTS my_salone;
CREATE DATABASE my_salone DEFAULT CHARACTER SET = utf8;
USE my_salone;

-- Tabella Utenti
CREATE TABLE utenti (
    mail                        VARCHAR(100)        NOT NULL UNIQUE, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    cognome                     VARCHAR(50)         NOT NULL,
    password                    VARCHAR(255)        NOT NULL,
    numero_telefono             CHAR(10)            NOT NULL,
    genere                      ENUM('M', 'F', 'Altro'),
    residenza                   VARCHAR(100),       
    data_nascita                DATE,  
    ruolo                       ENUM('CLIENTE', 'BARBIERE', 'ADMIN') DEFAULT 'CLIENTE' NOT NULL,
    CHECK (ruolo IN ('CLIENTE', 'BARBIERE', 'ADMIN')),
    PRIMARY KEY(mail)
) ENGINE = InnoDB;

-- Tabella Saloni
CREATE TABLE saloni (
    id_salone                   INT AUTO_INCREMENT, /*PK*/
    nome                        VARCHAR(50)         NOT NULL,
    indirizzo                   VARCHAR(100)        NOT NULL UNIQUE,
    posti                       INT                 NOT NULL,
    orario_apertura             TIME                NOT NULL, 
    orario_chiusura             TIME                NOT NULL, 
    PRIMARY KEY(id_salone)
) ENGINE = InnoDB;

-- Relazione Barbieri-Salone (Un barbiere può lavorare in più saloni)
CREATE TABLE barbiere_salone (
    fk_barbiere                 VARCHAR(100)        NOT NULL, /* FK */
    fk_salone                   INT                 NOT NULL, /* FK */
    PRIMARY KEY(fk_barbiere, fk_salone),
    FOREIGN KEY(fk_barbiere)    REFERENCES utenti(mail)
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
    FOREIGN KEY(fk_barbiere)    REFERENCES utenti(mail)
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
    giorno          ENUM("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday") NOT NULL,
    ora_inizio      TIME NOT NULL,
    ora_fine        TIME NOT NULL,
    PRIMARY KEY(id_turno),
    FOREIGN KEY(fk_barbiere) REFERENCES utenti(mail)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY(fk_salone) REFERENCES saloni(id_salone)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Tabella Appuntamenti
CREATE TABLE appuntamenti (
    id_appuntamento             BIGINT AUTO_INCREMENT, /*PK*/
    fk_cliente                  VARCHAR(100),
    fk_turno                    BIGINT, /* FK - Nuovo */
    fk_servizio                 INT,
    data_app                    DATE                NOT NULL,
    ora_inizio                  TIME                NOT NULL,
    codice                      CHAR(6)             NOT NULL UNIQUE,
    stato                       ENUM('IN_ATTESA', 'CONFERMATO', 'COMPLETATO', 'CANCELLATO') DEFAULT 'IN_ATTESA', /* Nuovo */
    PRIMARY KEY(id_appuntamento),
    FOREIGN KEY(fk_cliente)     REFERENCES utenti(mail)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    FOREIGN KEY(fk_turno)       REFERENCES turni_barbieri(id_turno)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    FOREIGN KEY(fk_servizio)    REFERENCES servizi(id_servizio)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE = InnoDB;

-- INSERT --

INSERT INTO `saloni` (`id_salone`, `nome`, `indirizzo`, `posti`, `orario_apertura`, `orario_chiusura`) VALUES (NULL, 'Pergine', 'Via Dante 3', '2', '08:00:00', '18:00:00');
INSERT INTO `servizi` (`id_servizio`, `nome`, `durata`, `prezzo`) VALUES (NULL, 'Taglio', '30', '10'), (NULL, 'Colore', '45', '20');
INSERT INTO `barbiere_salone` (`fk_barbiere`, `fk_salone`) VALUES ('fra.conci@gmail.com', '1');
INSERT INTO `barbiere_servizio` (`fk_barbiere`, `fk_servizio`) VALUES ('fra.conci@gmail.com', '1');
INSERT INTO `salone_servizio` (`fk_salone`, `fk_servizio`) VALUES ('1', '1'), ('1', '2');
INSERT INTO `turni_barbieri` (`id_turno`, `fk_barbiere`, `fk_salone`, `giorno`, `ora_inizio`, `ora_fine`) VALUES (NULL, 'fra.conci@gmail.com', '1', 'Monday', '08:00:00', '12:00:00');


-- TRIGGER --

DELIMITER //

-- Trigger prima dell'inserimento di un nuovo utente
CREATE TRIGGER before_utenti_insert
BEFORE INSERT ON utenti
FOR EACH ROW
BEGIN
    -- Validazione dell'email
    IF NEW.mail NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Formato email non valido';
    END IF;

    -- Validazione della password
    IF NEW.password NOT REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+={}|;:,.<>?/-]).{8,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La password deve contenere almeno 8 caratteri, una lettera minuscola, una maiuscola, un numero e un carattere speciale';
    END IF;

    -- Verifica che il numero di telefono sia valido (10 cifre, solo numeri)
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il numero di telefono deve contenere esattamente 10 cifre';
    END IF;
END//



-- Trigger prima dell'aggiornamento di un utente
CREATE TRIGGER before_utenti_update
BEFORE UPDATE ON utenti
FOR EACH ROW
BEGIN
    -- Validazione dell'email
    IF NEW.mail NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Formato email non valido';
    END IF;

    -- Validazione della password (solo se viene modificata)
    IF NEW.password != OLD.password AND NEW.password NOT REGEXP '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+={}|;:,.<>?/-]).{8,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La password deve contenere almeno 8 caratteri, una lettera minuscola, una maiuscola, un numero e un carattere speciale';
    END IF;

    -- Verifica che il numero di telefono sia valido
    IF NEW.numero_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il numero di telefono deve contenere esattamente 10 cifre';
    END IF;
END//



-- Trigger prima dell'inserimento di un salone
CREATE TRIGGER before_saloni_insert
BEFORE INSERT ON saloni
FOR EACH ROW
BEGIN
    -- Verifica che orario_apertura < orario_chiusura
    IF NEW.orario_apertura >= NEW.orario_chiusura THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'orario di apertura deve essere inferiore all'orario di chiusura";
    END IF;

    -- Verifica che i posti siano positivi
    IF NEW.posti <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il numero di posti deve essere maggiore di zero';
    END IF;
END//



-- Trigger prima dell'aggiornamento di un salone
CREATE TRIGGER before_saloni_update
BEFORE UPDATE ON saloni
FOR EACH ROW
BEGIN
    -- Verifica che orario_apertura < orario_chiusura
    IF NEW.orario_apertura >= NEW.orario_chiusura THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'orario di apertura deve essere inferiore all'orario di chiusura";
    END IF;

    -- Verifica che i posti siano positivi
    IF NEW.posti <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il numero di posti deve essere maggiore di zero';
    END IF;
END//




-- Trigger prima dell'inserimento di un turno
CREATE TRIGGER before_turni_barbieri_insert
BEFORE INSERT ON turni_barbieri
FOR EACH ROW
BEGIN
    DECLARE salone_apertura TIME;
    DECLARE salone_chiusura TIME;

    -- Verifica che ora_inizio < ora_fine
    IF NEW.ora_inizio >= NEW.ora_fine THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'orario di inizio del turno deve essere inferiore all\'orario di fine";
    END IF;

    -- Recupera gli orari di apertura e chiusura del salone
    SELECT orario_apertura, orario_chiusura
    INTO salone_apertura, salone_chiusura
    FROM saloni
    WHERE id_salone = NEW.fk_salone;

    -- Verifica che il turno sia all'interno degli orari del salone
    IF NEW.ora_inizio < salone_apertura OR NEW.ora_fine > salone_chiusura THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Il turno deve essere all'interno degli orari di apertura del salone";
    END IF;

    -- Verifica sovrapposizioni di turni per lo stesso barbiere nello stesso giorno e salone 
    IF EXISTS (
        SELECT 1
        FROM turni_barbieri
        WHERE fk_barbiere = NEW.fk_barbiere
        AND fk_salone = NEW.fk_salone
        AND giorno = NEW.giorno
        AND (
            (NEW.ora_inizio <= ora_fine AND NEW.ora_fine >= ora_inizio)
        )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Sovrapposizione di turni per lo stesso barbiere nello stesso giorno e salone';
    END IF;
END//

-- Trigger prima dell'aggiornamento di un turno
CREATE TRIGGER before_turni_barbieri_update
BEFORE UPDATE ON turni_barbieri
FOR EACH ROW
BEGIN
    DECLARE salone_apertura TIME;
    DECLARE salone_chiusura TIME;

    -- Verifica che ora_inizio < ora_fine
    IF NEW.ora_inizio >= NEW.ora_fine THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'orario di inizio del turno deve essere inferiore all\'orario di fine";
    END IF;

    -- Recupera gli orari di apertura e chiusura del salone
    SELECT orario_apertura, orario_chiusura
    INTO salone_apertura, salone_chiusura
    FROM saloni
    WHERE id_salone = NEW.fk_salone;

    -- Verifica che il turno sia all'interno degli orari del salone
    IF NEW.ora_inizio < salone_apertura OR NEW.ora_fine > salone_chiusura THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Il turno deve essere all'interno degli orari di apertura del salone";
    END IF;

    -- Verifica sovrapposizioni di turni per lo stesso barbiere nello stesso giorno e salone
    IF EXISTS (
        SELECT 1
        FROM turni_barbieri
        WHERE fk_barbiere = NEW.fk_barbiere
        AND fk_salone = NEW.fk_salone
        AND giorno = NEW.giorno
        AND id_turno != OLD.id_turno
        AND (
            (NEW.ora_inizio <= ora_fine AND NEW.ora_fine >= ora_inizio)
        )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Sovrapposizione di turni per lo stesso barbiere nello stesso giorno e salone';
    END IF;
END//



-- Trigger prima dell'inserimento di un appuntamento
CREATE TRIGGER before_appuntamenti_insert
BEFORE INSERT ON appuntamenti
FOR EACH ROW
BEGIN
    DECLARE turno_inizio TIME;
    DECLARE turno_fine TIME;
    DECLARE turno_giorno ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
    DECLARE barbiere VARCHAR(100);
    DECLARE salone INT;
    DECLARE durata_servizio INT;

    -- Recupera informazioni sul turno
    SELECT ora_inizio, ora_fine, giorno, fk_barbiere, fk_salone
    INTO turno_inizio, turno_fine, turno_giorno, barbiere, salone
    FROM turni_barbieri
    WHERE id_turno = NEW.fk_turno;

    -- Verifica che il turno esista
    IF turno_inizio IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Turno non valido';
    END IF;

    -- Verifica che il giorno dell'appuntamento corrisponda al giorno del turno
    IF turno_giorno != DAYNAME(NEW.data_app) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Il giorno dell'appuntamento non corrisponde al giorno del turno";
    END IF;

    -- Recupera la durata del servizio
    SELECT durata INTO durata_servizio
    FROM servizi
    WHERE id_servizio = NEW.fk_servizio;

    -- Verifica che l'orario dell'appuntamento sia all'interno del turno
    IF NEW.ora_inizio < turno_inizio OR 
       ADDTIME(NEW.ora_inizio, SEC_TO_TIME(durata_servizio * 60)) > turno_fine THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'appuntamento non è compatibile con gli orari del turno";
    END IF;

    -- Verifica che il barbiere sia associato al salone
    IF NOT EXISTS (
        SELECT 1
        FROM barbiere_salone
        WHERE fk_barbiere = barbiere
        AND fk_salone = salone
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il barbiere non è associato al salone del turno';
    END IF;

    -- Verifica che il servizio sia offerto dal salone
    IF NOT EXISTS (
        SELECT 1
        FROM salone_servizio
        WHERE fk_salone = salone
        AND fk_servizio = NEW.fk_servizio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il servizio non è offerto dal salone';
    END IF;

    -- Verifica che il barbiere offra il servizio
    IF NOT EXISTS (
        SELECT 1
        FROM barbiere_servizio
        WHERE fk_barbiere = barbiere
        AND fk_servizio = NEW.fk_servizio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il barbiere non offre il servizio selezionato';
    END IF;

    -- Verifica sovrapposizioni di appuntamenti per lo stesso barbiere
    IF EXISTS (
        SELECT 1
        FROM appuntamenti a
        JOIN servizi s ON a.fk_servizio = s.id_servizio
        JOIN turni_barbieri t ON a.fk_turno = t.id_turno
        WHERE t.fk_barbiere = barbiere
        AND a.data_app = NEW.data_app
        AND a.stato != 'CANCELLATO'
        AND (
            (NEW.ora_inizio <= ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) AND 
             ADDTIME(NEW.ora_inizio, SEC_TO_TIME(durata_servizio * 60)) >= a.ora_inizio)
        )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Sovrapposizione di appuntamenti per lo stesso barbiere';
    END IF;
END//

-- Trigger prima dell'aggiornamento di un appuntamento
CREATE TRIGGER before_appuntamenti_update
BEFORE UPDATE ON appuntamenti
FOR EACH ROW
BEGIN
    DECLARE turno_inizio TIME;
    DECLARE turno_fine TIME;
    DECLARE turno_giorno ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
    DECLARE barbiere VARCHAR(100);
    DECLARE salone INT;
    DECLARE durata_servizio INT;

    -- Recupera informazioni sul turno
    SELECT ora_inizio, ora_fine, giorno, fk_barbiere, fk_salone
    INTO turno_inizio, turno_fine, turno_giorno, barbiere, salone
    FROM turni_barbieri
    WHERE id_turno = NEW.fk_turno;

    -- Verifica che il turno esista
    IF turno_inizio IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Turno non valido';
    END IF;

    -- Verifica che il giorno dell'appuntamento corrisponda al giorno del turno
    IF turno_giorno != DAYNAME(NEW.data_app) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Il giorno dell'appuntamento non corrisponde al giorno del turno";
    END IF;

    -- Recupera la durata del servizio
    SELECT durata INTO durata_servizio
    FROM servizi
    WHERE id_servizio = NEW.fk_servizio;

    -- Verifica che l'orario dell'appuntamento sia all'interno del turno
    IF NEW.ora_inizio < turno_inizio OR 
       ADDTIME(NEW.ora_inizio, SEC_TO_TIME(durata_servizio * 60)) > turno_fine THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "L'appuntamento non è compatibile con gli orari del turno";
    END IF;

    -- Verifica che il barbiere sia associato al salone
    IF NOT EXISTS (
        SELECT 1
        FROM barbiere_salone
        WHERE fk_barbiere = barbiere
        AND fk_salone = salone
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il barbiere non è associato al salone del turno';
    END IF;

    -- Verifica che il servizio sia offerto dal salone
    IF NOT EXISTS (
        SELECT 1
        FROM salone_servizio
        WHERE fk_salone = salone
        AND fk_servizio = NEW.fk_servizio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il servizio non è offerto dal salone';
    END IF;

    -- Verifica che il barbiere offra il servizio
    IF NOT EXISTS (
        SELECT 1
        FROM barbiere_servizio
        WHERE fk_barbiere = barbiere
        AND fk_servizio = NEW.fk_servizio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Il barbiere non offre il servizio selezionato';
    END IF;

    -- Verifica sovrapposizioni di appuntamenti per lo stesso barbiere
    IF EXISTS (
        SELECT 1
        FROM appuntamenti a
        JOIN servizi s ON a.fk_servizio = s.id_servizio
        JOIN turni_barbieri t ON a.fk_turno = t.id_turno
        WHERE t.fk_barbiere = barbiere
        AND a.data_app = NEW.data_app
        AND a.id_appuntamento != OLD.id_appuntamento
        AND a.stato != 'CANCELLATO'
        AND (
            (NEW.ora_inizio <= ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) AND 
             ADDTIME(NEW.ora_inizio, SEC_TO_TIME(durata_servizio * 60)) >= a.ora_inizio)
        )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Sovrapposizione di appuntamenti per lo stesso barbiere';
    END IF;
END//

DELIMITER ;