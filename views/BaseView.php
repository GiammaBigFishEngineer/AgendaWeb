<?php

require_once(__ROOT__ . '/vendor/autoload.php');

use Performing\TwigComponents\Configuration;

/*
 * OGNI VIEW è ASSOCIATA AL RENDERING DI UNA PAGINA E AL RELATIVO TEMPLATE
 * L'USO DI TWIG PERMETTE DI PASSARE LE VARIABILI AL TEMPLATE IN MODO FACILE E VELOCE
 * 
 * Il seguente echo dentro ad ogni funzione di classe passa i parametri al tempalte
 * echo $this->twig->render('optometria/list.html.twig', [
            'variabile' => $variabile
        ]);
 */

class BaseView
{
    public $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $this->twig = new \Twig\Environment($loader);

        // If the APP_DEBUG environment variable is set to true, enable debug mode and add the DebugExtension
        if(EnvLoader::getValue("APP_DEBUG") == true){
            $this->twig->enableDebug();
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        
        // If the $_SESSION variable is set, add it as a global variable to Twig
        if (isset($_SESSION)) $this->twig->addGlobal('session', $_SESSION);

        // Add the $_GET and $_POST variables as global variables to Twig
        $this->twig->addGlobal('get', $_GET);
        $this->twig->addGlobal('post', $_POST);
        $this->twig->addGlobal('url', __BASEURL__);


        Configuration::make($this->twig)
        ->setTemplatesPath('components/')
        ->setTemplatesExtension('twig')
        ->useCustomTags()
        ->setup();
    }

    // public function __destruct()
    // {
    //     // clear the consumed sessions
    //     if (session_status() === PHP_SESSION_ACTIVE)
    //         $_SESSION = array();
    // }
}
