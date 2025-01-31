<?php 

namespace Telnet\Net;

use PDO;
use PDOException;

class Database{

    private $pdo = null; // XXX non dovrei usarlo 

    private $driver;

    function __construct(){

        $env = parse_ini_file('../.env');

        $this->driver = $env['DRIVER'];

        if($this->driver == "sqlite"){

            $dbFile = $env['DBASE'];

            $this->pdo = new \PDO('sqlite:' . $dbFile);

        }
        else if($this->driver == "mysql"){

            $hostdb = $env['HOSTDB']; 
            $portdb = $env['PORTDB']; 
            $databases = $env['DBASEDB']; 

            $user = $env['USER']; 
            $password = $env['PASS']; 
            
            $this->pdo = new \PDO('mysql:host='.$hostdb.':'.$portdb.';dbname='.$databases,$user,$password);

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
              sender_id INTEGER NOT NULL ,
              message VARCHAR(255) NOT NULL,

              FOREIGN KEY(nickname_id) REFERENCES users(id),
              FOREIGN KEY(sender_id) REFERENCES users(id)
          )";

          $mysql = "CREATE TABLE IF NOT EXISTS messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nickname_id INT NOT NULL ,
            sender_id INT NOT NULL ,
            message VARCHAR(255) NOT NULL);
            ALTER TABLE `messages` ADD CONSTRAINT `users_messages` FOREIGN KEY (`nickname_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
            ALTER TABLE `messages` ADD CONSTRAINT `users_messages_senders` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
     
            ";
          
          $this->pdo->exec( $this->driver=='sqlite' ? $sql : $mysql);
          
      }

      public function getPdo():PDO{
        
        return $this->pdo;

      } 

      public function sqlExecute(string $sql, $param=[]):array{

        $stmt = $this->pdo->prepare($sql);

        try{

            $stmt->execute($param);

        }
        catch(PDOException $e){

            echo $e->getMessage();
        }

        $out = [];       

        while($val = $stmt->fetch(PDO::FETCH_NUM)){            
                
            $out[] = $val;                        

        }
       
        return $out;

      } 

}