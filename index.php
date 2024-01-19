<?php
/*
 * DISPATCHER BASATO SU MVC, OGNI URL USA UN CONTROLLER PER ACCEDERE 
 * AL MODELLO E INTERFACCIARSI CON UNA VIEW
*/
/*
    Mostra errori se online:
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
*/
/*
    ESempio di routing:
    case '/lista_clienti':
        LeadController::list();
        break;
*/

define('__ROOT__', dirname(__FILE__));

session_start();

class Dispatcher
{
    private $method;
    private $path;

    public function __construct()
    {
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    }

    public function dispatch()
    {
        switch ($this->path) {
            
            case "/test_user":
                echo User::list();
                break;
            default:
                echo "404 HTML<br>";
                echo $this->path;
                break;
        }
    }
}

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
