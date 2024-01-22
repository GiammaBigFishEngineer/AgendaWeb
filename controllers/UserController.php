<?php

require_once(__ROOT__ . '/models/UserModel.php');
require_once(__ROOT__ . '/views/UserView.php');

require_once __ROOT__ . "/utils/HttpHandler.php";

class UserController
{
    public static function showLogin(){
        $view = new UserView();
        $view->renderLogin();
    }

    public static function login() {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        // $user = new UserModel();
        UserModel::login($data["password"],$data["email"]);
    }

    public static function create($email, $password, $role = "Utente") {
        // $httpHandler = new HttpHandler;
        // $data = $httpHandler->handleRequest();

        // $user = new UserModel();
        // UserModel::login($data["password"], $data["email"]);

        $user = new UserModel();

        if(UserModel::whereEmail($email)){
            throw new Exception("Questa email è stata già utilizzata");
        }

        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->role = $role;

        $user->save();
    }
}