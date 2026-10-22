
CREATE DATABASE IF NOT EXISTS devcoffee 
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE devcoffee;

CREATE TABLE drinks ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    caffeine_mg INT NOT NULL,
    volume_ml INT NOT NULL
) ENGINE=InnoDB;


CREATE TABLE consumptions ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    drink_id INT,
    developer_name VARCHAR(100) NOT NULL,
    consumed_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_consumptions_drinks
        FOREIGN KEY (drink_id)
        REFERENCES drinks(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO drinks (name, caffeine_mg, volume_ml)
VALUES
    ('Espresso', 80, 30),
    ('Café Allongé', 110, 120),
    ('Monster Energy', 160, 500);