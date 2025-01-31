<?php
namespace Telnet;

use Telnet\Net\Server;


 //DONE  Pulizia codice  in corso divisione in classi ok
 //DONE  licenza
 //DONE implementare funzione messaggi fatta 
 //TODO gestione messaggi in caso di successo o fallimento comando
 //TODO documentazione fatta ma da aggiornare con la nuova versione
 //TODO documentare le funzioni classi
 //TODO area per altre pagine 
 //TODO gestire funzioni in caso di uso mysql 

/**  
 * For now you can register, log in, call up help, connection information, send read and delete messages and log out
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
