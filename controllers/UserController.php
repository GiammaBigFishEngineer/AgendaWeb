<?php
require_once('BaseController.php');

require_once(__ROOT__ . '/models/UserModel.php');
require_once(__ROOT__ . '/views/UserView.php');

require_once __ROOT__ . "/utils/HttpHandler.php";

class UserController extends BaseController
{
    protected static $model = UserModel::class;
    public static function showLogin(){
        $view = new UserView();
        $view->renderLogin();
    }

    public static function showHome(){
        $view = new UserView();
        $view->renderHome();
    }

    public static function login() {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        // $user = new UserModel();
        try {
            UserModel::login($data["password"], $data["email"]);
            header("Location: /");
        } catch (Exception $e) {
            $_SESSION["error"] = $e->getMessage();
            header("Location: /login");
        }
    }

    public static function create($email, $password, $role = "Utente") {
        $user = new UserModel();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email format not valid");
        }

        if(UserModel::whereEmail($email)){
            throw new Exception("Email has already been registered");
        }

        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->ruolo = $role;

        $user->save();
    }
}