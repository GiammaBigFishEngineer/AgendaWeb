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
    Esempio di routing:
    case '/lista_clienti':
        LeadController::list();
        break;
*/

define('__ROOT__', dirname(__FILE__));
define('__BASEURL__', (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]");

require_once(__ROOT__ . '/models/UserModel.php');
require_once(__ROOT__ . '/controllers/UserController.php');
require_once(__ROOT__ . '/config/EnvLoader.php');

session_start();

$config = new EnvLoader();

class Dispatcher
{
    private $method;
    private $path;

    public function __construct()
    {
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    }
    
    function isRoute(string $method, string $route, array ...$handlers): int
    {
        global $params;
        // $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        $route_rgx = preg_replace('#:(\w+)#','(?<$1>(\S+))', $route);
        return preg_match("#^$route_rgx$#", $this->path, $params);
    }

    public function dispatch()
    {
        if(isset($_SESSION["loggedIn"])) {
            switch ($this->path) {
                case "/":
                    UserController::showHome();
                    break;
                case "/logout":
                    session_unset();
                    header("Location: /login");
                    break;
                default:
                    echo "404 HTML<br>";
                    echo $this->path;
                    break;
            }
        } else {
            switch ($this->path) {
                case "/test":
                    echo "Prova lista utenti";
                    $list = UserModel::all();
                    foreach($list as $user){
                        echo "<br>$user->email";
                    }
                    break;
                case "/login":
                    if($this->method == "POST"){
                        UserController::login();
                    } else if ($this->method == "GET"){
                        UserController::showLogin();
                    }
                    break;
                case "/":
                    header("Location: /login");
                    break;
                default:
                    echo "404 HTML<br>";
                    echo $this->path;
                    break;
            }
        }


    }
}

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
