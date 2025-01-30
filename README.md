# Telnet PHP BBS Server

This repository cloned from https://github.com/Rotron/telnet-php-bbs-server modified and customized

## Usage

### 1. Download the Script

Ensure you have downloaded the PHP script containing the Telnet BBS server code from this repository.
you can edit the .env file to change host and port

```bash
git clone https://github.com/ManueldG/telnet-php-bbs-server.git
```

```bash
composer create-project manueldg/server-socket=0.0.5beta <folder-name>
```

### 2. Run the Script

Open a terminal or command prompt, navigate to the directory /src, and run the script using the PHP interpreter:

```bash
php index.php
```

### 3. Connect to the Server
Once the server is running, clients can connect to it using Telnet. They can do this by opening a terminal or command prompt on their machine and entering the following command:

```bash
telnet server_ip_address port_number
```
Replace server_ip_address with the IP address where your Telnet BBS server is running, and port_number with the port number specified in your script (default script is 2324 for Telnet).

### 4. Interact with the BBS
For now you can register, log in, call up help, connection information, send read and delete messages and log out

### 5. Terminate the Server
To end the connection you can use ctrl + x

### Customization
it is possible to add new features to the menu

### License
This project is under the GPL 3 license
