CREATE DATABASE IF NOT EXISTS devcoffee
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE devcoffee;

CREATE TABLE IF NOT EXISTS drinks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  caffeine_mg INT NOT NULL,
  volume_ml INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consumptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  drink_id INT NOT NULL,
  developer_name VARCHAR(100) NOT NULL,
  consumed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_consumptions_drinks
    FOREIGN KEY (drink_id)
    REFERENCES drinks(id)
    ON DELETE CASCADE,
  INDEX idx_consumptions_developer_date (developer_name, consumed_at),
  INDEX idx_consumptions_date (consumed_at)
) ENGINE=InnoDB;

-- Jeu de test initial (inséré uniquement si le catalogue est vide)
INSERT INTO drinks (name, caffeine_mg, volume_ml)
SELECT seed.name, seed.caffeine_mg, seed.volume_ml
FROM (
  SELECT 'Espresso' AS name, 80 AS caffeine_mg, 30 AS volume_ml
  UNION ALL SELECT 'Café Allongé', 110, 120
  UNION ALL SELECT 'Monster Energy', 160, 500
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM drinks LIMIT 1);
