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
require_once(__ROOT__ . '/controllers/EventController.php');

require_once(__ROOT__ . '/config/EnvLoader.php');

session_start();

$config = new EnvLoader();

class Dispatcher
{
    private $method;
    private $path;
    private $path_segments;

    public function __construct()
    {
        $this->method = RequestMethod::convert($_SERVER["REQUEST_METHOD"]);
        $this->path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        // $this->path_segments = explode('/', $this->path);
    }
    
    public function isRoute(string $method, string $route, array ...$handlers): int
    {
        global $params;
        $route_rgx = preg_replace('#:(\w+)#','(?<$1>(\S+))', $route);
        return preg_match("#^$route_rgx$#", $this->path, $params);
    }

    public function dispatch()
    {
        if(isset($_SESSION["loggedIn"])) {
            $routeHandlers = [
                "/api/event/:id/file/:file_id" => function($params) {
                    if($this->method == RequestMethod::GET) {
                        // EventController::getFiles($params['id']);
                    }

                    if($this->method == RequestMethod::DELETE) { 
                        EventController::deleteFiles($params['id'], $params['file_id']);
                    }
                },
                "/api/event/:id/files" => function($params) {
                    if($this->method == RequestMethod::GET) {
                        EventController::getFiles($params['id']);
                    }
                    if($this->method == RequestMethod::POST) {
                        EventController::addFile($params['id']);
                    }
                },
                "/api/events" => function($params) {
                    // if ($this->method == RequestMethod::GET) {
                    //     EventController::list();
                    // }
                    if ($this->method == RequestMethod::POST) {
                        EventController::save();
                    }
                },
                "/api/events/date/" => function ($params) {
                    if($this->method == RequestMethod::GET) {
                        EventController::byDate();
                    }
                },
                "/api/event/:id" => function($params) {
                    $eventId = $params['id'];
                    if ($this->method == RequestMethod::GET) {
                        EventController::get($eventId);
                    }
                    if ($this->method == RequestMethod::DELETE) {
                        EventController::delete($eventId);
                    }
                    if ($this->method == RequestMethod::POST) {
                        EventController::save($eventId);
                    }
                },
                "/" => function($params) {
                    UserController::showHome();
                },
                "/logout" => function($params) {
                    session_unset();
                    header("Location: /login");
                }
            ];
        } else {
            $routeHandlers = [
                "/" => function($params) {
                    header("Location: /login");
                },
                "/api/events/:id/files" => function($params) {
                    if($this->method == RequestMethod::GET) {
                        EventController::getFiles($params['id']);
                    }
                },
                "/login" => function($params) {
                    if ($this->method == RequestMethod::POST) {
                        UserController::login();
                    }
                    if($this->method == RequestMethod::GET) {
                        UserController::showLogin();
                    }
                }
            ];
        }

        $this->route($routeHandlers);
    }

    private function route($routeHandlers){
        $matched = false;
    
        //Cerca se il path attuale corrisponde un pattern
        foreach ($routeHandlers as $routePattern => $handler) {
            $params = $this->matchRoutePattern($routePattern);

            if ($params !== null) {
                $handler($params);
                $matched = true;
                break;
            }
        }
    
        if (!$matched) {
            $this->page_404();
        }
    }

    private function matchRoutePattern($pattern): ?array {
        $pathSegments = explode('/', $this->path);
        $patternSegments = explode('/', $pattern);
    
        $params = [];
    
        //If the parameter count is not the same
        if (count($pathSegments) !== count($patternSegments)) {
            return null;
        }

        //Per ogni segmento del path attuale
        foreach ($pathSegments as $index => $segment) {
            //Se questa parte del pattern è un parametro
            if (isset($patternSegments[$index][0]) && $patternSegments[$index][0] === ":") {
                $paramName = substr($patternSegments[$index], 1);
                $params[$paramName] = $segment;
            //Se il segmento non combacia
            } else if ($segment !== $patternSegments[$index]) {
                return null;
            }
        }

        return $params;
    }
    

    function json(mixed $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function page_404()
    {
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->json(['err' => 'Route not found!']);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "404 HTML<br>";
            echo $this->path;
        }
    }

    //! ONLY AVAILABLE FOR PHP 8.2
    // public function dispatch(){
    //     (match(1) {
    //         $this->isRoute('GET', '/') => function () {
    //             $this->json(['msg' => 'Hello!']);
    //         },
    //         $this->isRoute('POST', '/api/posts') => function () {
    //             $this->json(['msg' => 'Created post']);
    //         },
    //         $this->isRoute('GET', '/api/posts/:id') => function () {
    //           global $params;
    //           $this->json(['id' => $params['id']]);
    //         },
    //         $this->isRoute('GET', '/api/users/:id') => function () {
    //             global $params;
    //             echo(UserController::get($params['id']));
    //         },
    //         $this->isRoute('GET', '/api/events/:id') => function () {
    //             global $params;
    //             echo(EventController::get($params['id']));
    //         },
    //         default => fn() => $this->json(['err' => 'Route not found!'])
    //     })();        
    // }
}

enum RequestMethod: string {
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';

    public static function convert(string $val): self {
        return match ($val) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'DELETE' => self::DELETE,
            default => throw new InvalidArgumentException("Invalid HTTP method: $val"),
        };
    }
}

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
