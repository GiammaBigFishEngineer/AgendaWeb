<?php
require_once(__ROOT__ . '/views/BaseView.php');

require_once(__ROOT__ . '/config/EnvLoader.php');

require_once(__ROOT__ . '/vendor/autoload.php');

class UserView extends BaseView
{
    public function renderLogin()
    {
        echo $this->twig->render('authentication/login.html.twig', [
            'action' => '/login',
        ]);
    }

    public function renderHome()
    {
        echo $this->twig->render('dashboard/home.html.twig', [
        ]);
    }
}