<?php
namespace Telnet\Net;

use Telnet\Net\Client;


class Server
{
    private $host;
    private $port;
    private $peer;
    private $serverSocket;
    private $clients = [];
    private $pdo;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        
    }

    public function initializeDatabase()
    {
        $dbFile = 'database.sqlite';
        $this->pdo = new \PDO('sqlite:' . $dbFile);
        $this->createUsersTable();
    }

    private function createUsersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    nickname VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL
                )";
        $this->pdo->exec($sql);
    }

    public function start()
    {
        $this->createServerSocket();
        echo "Telnet BBS started at telnet://{$this->host}:{$this->port}\n";

        while (true) {
            
            $this->handleIncomingConnections();
            
            $this->handleClientInteractions();            

        }
    }

    private function createServerSocket()
    {
        try{
            $this->serverSocket = stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);
        }catch(\Exception $e){

            echo $errno." :".$errstr;
            var_dump($e);

        }
      
    }


private function handleIncomingConnections()
{    
    $read[] = $this->serverSocket;    
    
     // look for new connections
     if ($sock = @stream_socket_accept($this->serverSocket, empty($read) ? -1 : 0, $peer)) {
        
        //puntatore allo stream
        echo $peer.' connected'.PHP_EOL; //scrivo sul terminale

        $this->peer = $peer;
        
        $this->clients[] = new Client($sock, $this->pdo);    
        $this->sendBannerMessage($sock);
        
        fwrite($sock, 'Hello '.$peer.PHP_EOL); //scrivo al client

        $connections[] = $sock; //associo il puntatore alla connessione es $connections[127.0.0.1:51575]
        
        $read = $connections; // read = connections  

    }    

    // wait for any stream data
    try {
         $select = @stream_select($read, $write, $except, 5);
    }catch(\Exception $e){

        var_dump($except,$e);

    }
   
    if ($select > 0) {

        foreach ($read as $c) {

            $peer = stream_socket_get_name($c, true);

            if (feof($c)) {

                echo 'Connection closed '.$peer.PHP_EOL;
                fclose($c);
                unset($connections[$peer]);

            } 
        }
    }

}



private function handleClientInteractions()
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


    private function handleAuthenticatedClientInput($client, $input)
    {
        switch ($input) {
            case 'exit':
                fclose($client->getSocket());
                echo "Client disconnected\n";
                unset($this->client);
                break;
            case 'help':
                $client->sendMessage("\nAvailable commands: exit, help, info\n");
                break;
            case 'info':
                $client->sendMessage("\nInfo: " . $this->peer . " " . "\n");
                break;
            default:
                $client->sendMessage("\nUnknown command. Type 'help' for available commands.\n");
                break;
        }
    }

    private function handleUnauthenticatedClientInput($client, $input)
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

    private function sendBannerMessage($client)
    {
        $bannerMessage = "

 
*********************************************************************
*                                                                   *
*   _____ _          _____     _         _      _____ _____ _____   *
*  |  _  | |_ ___   |_   _|___| |___ ___| |_   | __  | __  |   __|  *
*  |   __|   | . |    | | | -_| |   | -_|  _|  | __ -| __ -|__   |  *
*  |__|  |_|_|  _|    |_| |___|_|_|_|___|_|    |_____|_____|_____|  *
*          |_|                                                      *
*                                                                   *
*********************************************************************
*                                                                   *
*                 Welcome to Php Telnet BBS Server!                 *
*                                                                   *
*                   To register, type 'register'.                   *
*                       To login, type 'login'.                     *
*                                                                   *
*********************************************************************
";
        fwrite($client, $bannerMessage);
    }
}