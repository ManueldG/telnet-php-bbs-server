<?php
namespace Telnet;

use Telnet\Net\Server;


 //IN PROGRESS  Pulizia codice  in corso
 //TODO  licenza
 //TODO documentazione 
 //TODO area per altre pagine 
 //TODO implementare funzione messaggi 

/**
 *  
 * 
 * @author Manuel della Gala
 * @license GPL (GNU General Public License)
 * @link www.manueldellagala.it
 * @link https://www.gnu.org/licenses/gpl-3.0.html
 */

 $env = parse_ini_file('../.env');

 $host = $env["HOST"];
 $port = $env["PORT"];

$server = new Server($host, $port);
$server->initializeDatabase();
$server->start();
