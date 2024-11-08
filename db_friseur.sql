CREATE DATABASE db_friseur;

USE db_friseur;

CREATE TABLE tbl_reservierungen (
    id INT AUTO_INCREMENT PRIMARY KEY,    -- Eindeutige ID für jede Reservierung
    name VARCHAR(100) NOT NULL,            -- Name des Kunden
    email VARCHAR(100) NOT NULL,           -- E-Mail des Kunden
    phone VARCHAR(15) NOT NULL,            -- Telefonnummer des Kunden
    service VARCHAR(50) NOT NULL,          -- Dienstleistung (z.B. Haarschnitt, Färben)
    date DATE NOT NULL,                    -- Datum der Reservierung
    time TIME NOT NULL,                    -- Uhrzeit der Reservierung
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Zeitpunkt der Buchung
);
