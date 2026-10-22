CREATE DATABASE IF NOT EXISTS ecopark CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE ecopark;

CREATE TABLE IF NOT EXISTS stations(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_name VARCHAR(150) NOT NULL,
  power_kw INT NOT NULL,
  is_available TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS charge_sessions(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  station_id INT NOT NULL,
  user_name VARCHAR(100) NOT NULL,
  kwh_requested INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id) REFERENCES stations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO 
stations(location_name, power_kw, is_available) 
VALUES 
("Station Centre-Ville", 50, 1),
("Station Gare Centrale", 150, 1),
("Station Eco-Quartier", 22, 0),
("Station Parking Relais", 11, 1);