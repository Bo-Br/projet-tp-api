<?php

namespace App\Core;

use PDO;
use PDOException;

class Database{

  private static ?PDO $instance = null;

  private function __construct(){}
  private function __clone(){}

  public static function getInstance(): PDO{
    if(self::$instance === null){
      $host = $_ENV["DB_HOST"];
      $dbname = $_ENV["DB_NAME"];
      $username = $_ENV["DB_USERNAME"];
      $password = $_ENV["DB_PASSWORD"];
      $charset = "utf8mb4";

      $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

      try{
        self::$instance = new PDO(
          $dsn,
          $username,
          $password,
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
          ]
        );
      }catch(PDOException $e){
        die("Erreur de connexion BDD :" . $e->getMessage());
      }
    }

    return self::$instance;
  }

}