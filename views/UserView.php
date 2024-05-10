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

    public function renderResetPassword($reset)
    {
        echo $this->twig->render('authentication/reset_password.html.twig', [
            "reset" => $reset
        ]);
    }

    public function renderForgottedPassword()
    {
        echo $this->twig->render('authentication/forgotten_password.html.twig',
        []);
    }
}