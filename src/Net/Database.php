<?php 

namespace Telnet\Net;

use PDO;

class Database{

    private $pdo = null;

    private $driver;

    function __construct(){

        $env = parse_ini_file('../.env');

        $this->driver = $env['DRIVER'];

        if($this->driver == "sqlite"){

            $dbFile = $env['DBASE'];

            $this->pdo = new \PDO('sqlite:' . $dbFile);

        }
        else{
            
            $this->pdo = new \PDO('mysql:host=localhost:3306;dbname=terminal',"root","");

        }
      }

      public function createUsersTable():void
      {
          $sql = "CREATE TABLE IF NOT EXISTS users (
                      id INTEGER PRIMARY KEY AUTOINCREMENT,
                      nickname VARCHAR(255) NOT NULL UNIQUE,
                      password VARCHAR(255) NOT NULL
                  )";

          $mysql = "CREATE TABLE IF NOT EXISTS users (
                      id INT PRIMARY KEY AUTO_INCREMENT,
                      nickname VARCHAR(255) NOT NULL UNIQUE,
                      password VARCHAR(255) NOT NULL
                    )";
                  
          $this->pdo->exec( $this->driver=='sqlite' ? $sql : $mysql);
          
      }

      public function createMessageTable():void
      {
  
          $sql = "CREATE TABLE IF NOT EXISTS messages (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              nickname_id INTEGER NOT NULL ,
              message VARCHAR(255) NOT NULL
          )";

          $mysql = "CREATE TABLE IF NOT EXISTS messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nickname_id INT NOT NULL ,
            message VARCHAR(255) NOT NULL
          )";
          
          $this->pdo->exec( $this->driver=='sqlite' ? $sql : $mysql);
          
      }

      public function getPdo():PDO{
        
        return $this->pdo;

      } 

      public function sqlExecute(string $sql, $param=[]):string{

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($param);

        $out = "";
        $x = 0;

        while($existingUser = $stmt->fetch()){

            //$out .= $existingUser[0].PHP_EOL;
            while($existingUser[$x]){

                $out .= $existingUser[$x].PHP_EOL;
                $x++;

            }
            $x=0;

        }

        echo $out;
        
        return $out;

      } 

}