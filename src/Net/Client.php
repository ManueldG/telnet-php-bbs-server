<?php
namespace Telnet\Net;

use PDOException;

class Client
{
    private $socket;
    private $authenticated = false;
    private $nickname;
    private $pdo;

    public function __construct($socket, $pdo)
    {
        $this->socket = $socket;
        $this->pdo = $pdo;
    }

    public function getSocket():mixed
    {
        return $this->socket;
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    public function authenticate()
    {
        $this->authenticated = true;
    }

    public function sendMessage(String $message)
    {
        fwrite($this->socket, $message.PHP_EOL);
    }

public function register()
{
    $this->sendMessage("Enter nickname: ");
    $this->nickname = rtrim(fgets($this->socket, 1024));

    $this->sendMessage("Enter password: ");
    $password = rtrim(fgets($this->socket, 1024));


    if (!empty($this->nickname) && !empty($password)) {

        $db = new Database();
        try{

            $existingUser = $db->sqlExecute("SELECT * FROM users WHERE nickname LIKE ? ",[$this->nickname] );
        }
        catch(\PDOException $e){
            var_dump($e);
        };
       
        if ($existingUser) {

            $this->sendMessage("Nickname already exists. Please choose a different one.\n");

        }
        else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            /*

            $stmt = $this->pdo->prepare("INSERT INTO users (nickname, password) VALUES (?, ?)");

            $stmt->execute([$nickname, $hashedPassword]);

            */

            $db->sqlExecute("INSERT INTO users (nickname, password) VALUES (?, ?)",[$this->nickname,$hashedPassword]);

            $this->sendMessage("\nRegistration successful. You are now logged in.\n");

            $this->sendMessage("\nType 'help' for available commands.\n");
            
            $this->authenticate(); // Automatically log in after registration
        }
    }
    else {

        $this->sendMessage("Invalid input. Please try again.\n");

    }
}

public function login()
{
    $this->sendMessage("Enter nickname: ");
    $this->nickname = rtrim(fgets($this->socket, 1024));

    $this->sendMessage("Enter password: ");
    $password = rtrim(fgets($this->socket, 1024));

    if (!empty($this->nickname) && !empty($password)) {

        
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE nickname = ?");
            $stmt->execute([$this->nickname]);
            $user = $stmt->fetch();       

        if ($user && password_verify($password, $user['password'])) {

            $this->authenticate();
            $this->sendMessage("Login successful. Welcome, ". $this->nickname."\n");
            $this->sendMessage("\nType 'help' for available commands.\n");
            
        } else {

            $this->sendMessage("Invalid nickname or password. Please try again.\n");
            fclose($this->socket); // Close client connection

        }
    } else {

        $this->sendMessage("Invalid input. Please try again.\n".PHP_EOL);
        fclose($this->socket); // Close client connection
        
    }
}

public function storeMessage(){

    $this->sendMessage("Enter nickname recipient: ");
    $nickname = rtrim(fgets($this->socket, 1024));

    $this->sendMessage("Enter message: ");
    $message = rtrim(fgets($this->socket, 1024));

    if (!empty($nickname) && !empty($message)) {       

        $stmt = $this->pdo->prepare("INSERT INTO messages ( nickname_id , sender_id, message  ) VALUES( 
            (
                SELECT users.id FROM  users WHERE users.nickname LIKE ?
                ),(
                SELECT users.id FROM  users WHERE users.nickname LIKE ?
                ),?
            )");

        try{
            $stmt->execute([$nickname,$this->nickname ,$message]);
        }
        catch(\PDOException $e){

            echo($e->getMessage());

            $this->sendMessage("Invalid nickname");

            return;

        }

        $this->sendMessage("\nMessage sended.\n");       
    
    
        }
    }

    
    public function readMessage():string{

        $stmt = $this->pdo->prepare(" 
        
        SELECT messages.id as sender, messages.message, dest.nickname FROM messages 
                
        JOIN users as sender ON messages.sender_id = sender.id 
        JOIN users as dest ON messages.nickname_id = dest.id 
        
        WHERE dest.nickname = ?;
        
        ");

        
        $stmt->execute([$this->nickname]);

        $out = PHP_EOL;

        while($existingUser = $stmt->fetch()){

            var_dump($existingUser);

            $out .= $existingUser[0].") ".$existingUser[1]." from ".$existingUser[2].PHP_EOL;

        }

        echo $out;

        return $out;


    }

    public function delMessage(){
            
        $this->sendMessage("Enter n° message to delete: ");
        $id = rtrim(fgets($this->socket, 1024));

        $db = new Database();

        
        try{
            
            $db->sqlExecute("DELETE FROM messages WHERE messages.id = ? ;",[$id]);

        }
        catch(\Exception $e){

            echo $e->getMessage();
            $this->sendMessage("Not deleted"); 

            return;

        }

        $this->sendMessage("Message deleted"); 

        /*

        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE messages.id = ? ;");
        
        try{

            $stmt->execute([$id]);

        }
        catch(PDOException $e){

            echo $e->getMessage();
            $this->sendMessage("Not deleted"); 

            return;

        }

        $this->sendMessage("Message deleted"); */

    }
    
    

}

/**
 * INSERT INTO messages (nickname_id, messages) 
 * 
 * VALUES(
 * SELECT 
 *   user.id, 
 *   user.nickname,
 *   messages.nickname_id
 *   
 *   FROM messages
 *  JOIN user on messages.nickname_id = user.id;
 * 
 * )
 * 
 * SELECT users.id FROM  users where users.nickname LIKE "admin" ;
 * 
 * INSERT INTO massages ( nickname_id , message  ) VALUE( 
*(SELECT users.id FROM  users where users.nickname LIKE "admin" ;),"ciao"
**)
*
 * INSERT INTO massages ( nickname_id , message  ) VALUES( 
*(
*SELECT users.id FROM  users where users.nickname LIKE "admin" 
*),"ciao"
*)
 */


    