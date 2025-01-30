<?php
namespace Telnet\Net;

use Telnet\Net\Client;
use Telnet\Net\Database;


class Server
{
    private string $host;
    private int $port;
    
    private $serverSocket;
    private $clients = [];
    private $pdo;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        
    }

    /**
     * 
     * create database
     * @return void
     */
    public function initializeDatabase():void
    { /*
        $dbFile = 'database.sqlite';
        //$this->pdo = new \PDO('sqlite:' . $dbFile);

        $this->pdo = new \PDO('mysql:host=localhost:3306;dbname=terminal',"root","");
        */

        $db = new Database();

        $this->pdo = $db->getPdo();        

        $db->createUsersTable();
        $db->createMessageTable();
    }   

    public function start():void
    {
        $this->createServerSocket();

        echo "Telnet BBS started at telnet://{$this->host}:{$this->port}\n";

        while (true) {
            
            $this->handleIncomingConnections();
            
            $this->handleClientInteractions();            

        }
    }

    private function createServerSocket():void
    {
        try{

            $this->serverSocket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);

        }catch(\Exception $e){

            echo $errno." :".$errstr;
            var_dump($e);

        }
      
    }


private function handleIncomingConnections():void
{        
    $connections[] = $this->serverSocket;
    
     // look for new connections     
     if ($sock = @stream_socket_accept($this->serverSocket, empty($connections) ? -1 : 0, $peer)) {
        
        //puntatore allo stream
        echo $peer.' connected'.PHP_EOL; //scrivo sul terminale
        
        $this->clients[] = new Client($sock, $this->pdo);   

        $this->sendBannerMessage($sock);
        
        fwrite($sock, 'Hello '.$peer.PHP_EOL); //scrivo al client

        $connections[] = $sock;         

    }    

    // wait for any stream data
    try {

         $select = @stream_select($connections, $write, $except, 5);

    }catch(\Exception $e){

        var_dump($except,$e);

    }
   
    if ($select > 0) {

        foreach ($connections as $c) {

            $peer = stream_socket_get_name($c, true);

            if (feof($c)) {

                echo 'Connection closed '.$peer.PHP_EOL;
                fclose($c);
                unset($connections[$peer]);

            } 
        }
    }

}

private function handleClientInteractions():void
{
    foreach ($this->clients as $key => $client) {

        $socket = $client->getSocket();

        if (is_resource($socket) && !feof($socket)) {

            $input = rtrim(fgets($socket, 1024));

            if ($client->isAuthenticated()) {

                $this->handleAuthenticatedClientInput($client, $input);

            } 
            else {

                $this->handleUnauthenticatedClientInput($client, $input);

            }
        }
        else {

            // Client disconnected, clean up
            if (is_resource($socket)) {

                fclose($socket);

            }
            unset($this->clients[$key]);
            echo "Client disconnected\n";
        }
  }

}


    private function handleAuthenticatedClientInput($client, $input):void
    {
        switch ($input) {
            case 'exit':
                fclose($client->getSocket());
                echo "Client disconnected\n";
                unset($this->client);
                break;
            case 'help':
                $client->sendMessage(PHP_EOL.
                "Available commands: ".PHP_EOL.
                "exit:close connection ".PHP_EOL.
                "help: commands list".PHP_EOL.
                "info: print detail connection".PHP_EOL.
                "users: list users".PHP_EOL.
                "message: to send a message to another user".PHP_EOL.
                "read: to read messages".PHP_EOL.
                "del: to delete message".PHP_EOL                
            );
                break;
            case 'info':
                $client->sendMessage(PHP_EOL."Info: " . stream_socket_get_name($client->getSocket(), true) . " " . "\n"); 
                break;
            case 'users':
                $client->sendMessage(PHP_EOL."Users: ".PHP_EOL .$this->listUsers(). " " . PHP_EOL); 
                break;
            case 'message':
                $client->sendMessage(PHP_EOL." " .$client->storeMessage(). " " . PHP_EOL); 
                break;
            case 'read':
                $client->sendMessage(PHP_EOL." " .$client->readMessage(). " " . PHP_EOL); 
                break;
            case 'del':
                $client->sendMessage(PHP_EOL." " .$client->delMessage(). " " . PHP_EOL); 
                break;
            default:
                $client->sendMessage("\nUnknown command. Type 'help' for available commands.\n");
                break;
        }
    }

    private function handleUnauthenticatedClientInput($client, $input):void
    {
       
        switch ($input) {
            case 'register':
                $client->register();
                break;
            case 'login':
                $client->login();
                break;
            default:
                $client->sendMessage("Invalid input. Please try again.\n");
                break;
        }
    }

    private function sendBannerMessage($client):void
    {
        $bannerMessage = <<< DOC

                                                                                                       
                                                                                                       
__________     ___                                   ____                                              
MMMMMMMMMM     `MM                                  6MMMMb\                                            
/   MM   \      MM                     /           6M'    `                                            
    MM   ____   MM ___  __     ____   /M           MM         ____  ___  __ ____    ___  ____  ___  __ 
    MM  6MMMMb  MM `MM 6MMb   6MMMMb /MMMMM        YM.       6MMMMb `MM 6MM `MM(    )M' 6MMMMb `MM 6MM 
    MM 6M'  `Mb MM  MMM9 `Mb 6M'  `Mb MM            YMMMMb  6M'  `Mb MM69 "  `Mb    d' 6M'  `Mb MM69 " 
    MM MM    MM MM  MM'   MM MM    MM MM                `Mb MM    MM MM'      YM.  ,P  MM    MM MM'    
    MM MMMMMMMM MM  MM    MM MMMMMMMM MM                 MM MMMMMMMM MM        MM  M   MMMMMMMM MM     
    MM MM       MM  MM    MM MM       MM                 MM MM       MM        `Mbd'   MM       MM     
    MM YM    d9 MM  MM    MM YM    d9 YM.  ,       L    ,M9 YM    d9 MM         YMP    YM    d9 MM     
   _MM_ YMMMM9 _MM__MM_  _MM_ YMMMM9   YMMM9       MYMMMM9   YMMMM9 _MM_         M      YMMMM9 _MM_    
                                                                                                       
                                                                                                       
                                     Welcome to Php Telnet BBS Server!                 
                                                                   
                                      To register, type 'register'.                   
                                      To login, type 'login'.                     
                                                                   

DOC;
        fwrite($client, $bannerMessage);
    }

    private function listUsers():string{ 
        
        $out = "";

        $db = new Database();

        $line = $db->sqlExecute("SELECT nickname FROM users" );

        foreach($line as $val){
            var_dump($val);
            $out .= $val[0].PHP_EOL;
        }

        return $out;

    }

}