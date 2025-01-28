<?php
namespace Telnet;

use Telnet\Net\Server;


 //IN PROGRESS  Pulizia codice  in corso
 //TODO  licenza
 //TODO documentazione 
 //TODO area per altre pagine 

/**
 * PHP Telnet BBS Server
 * 
 * This/ code is part of a Telnet Bulletin Board System (BBS) server implementation.
 * It allows multiple clients to connect over Telnet, register, and log in to the BBS.
 * Registered users can execute commands such as help and exit.
 * 
 * This specific implementation is based on the GPL-licensed code written by Federico Saccà in 2024.
 * 
 * 
 * 
 * @author Federico Saccà
 * @license GPL (GNU General Public License)
 * @link https://www.federicosacca.it
 * @link https://www.gnu.org/licenses/gpl-3.0.html
 */

 $env = parse_ini_file('../.env');

 $host = $env["HOST"];
 $port = $env["PORT"];

$server = new Server($host, $port);
$server->initializeDatabase();
$server->start();
