<?php
namespace Telnet\Net;
class Client
{
    private $socket;
    private $authenticated = false;
    private $pdo;

    public function __construct($socket, $pdo)
    {
        $this->socket = $socket;
        $this->pdo = $pdo;
    }

    public function getSocket()
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

    public function sendMessage($message)
    {
        fwrite($this->socket, $message);
    }

public function register()
{
    $this->sendMessage("Enter nickname: ");
    $nickname = rtrim(fgets($this->socket, 1024));

    $this->sendMessage("Enter password: ");
    $password = rtrim(fgets($this->socket, 1024));

    if (!empty($nickname) && !empty($password)) {

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE nickname = ?");

        $stmt->execute([$nickname]);

        $existingUser = $stmt->fetch();

        if ($existingUser) {

            $this->sendMessage("Nickname already exists. Please choose a different one.\n");

        }
        else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("INSERT INTO users (nickname, password) VALUES (?, ?)");

            $stmt->execute([$nickname, $hashedPassword]);

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
    $nickname = rtrim(fgets($this->socket, 1024));

    $this->sendMessage("Enter password: ");
    $password = rtrim(fgets($this->socket, 1024));

    if (!empty($nickname) && !empty($password)) {

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE nickname = ?");
        $stmt->execute([$nickname]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $this->authenticate();
            $this->sendMessage("Login successful. Welcome, $nickname!\n");
            $this->sendMessage("\nType 'help' for available commands.\n");
            
        } else {

            $this->sendMessage("Invalid nickname or password. Please try again.\n");
            fclose($this->socket); // Close client connection

        }
    } else {

        $this->sendMessage("Invalid input. Please try again.\n");
        fclose($this->socket); // Close client connection
        
    }
}

}