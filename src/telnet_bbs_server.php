<?php
namespace Telnet;

use Telnet\Net\Server;


 //IN PROGRESS  Pulizia codice  in corso divisione in classi
 //DONE  licenza
 //TODO documentazione fatta ma da aggiornare con la nuova versione
 //TODO area per altre pagine 
 //DONE implementare funzione messaggi fatta manca quella per togliere i vecchi messaggi

 

/**  
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
