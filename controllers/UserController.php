<?php
require_once('BaseController.php');

require_once(__ROOT__ . '/models/UserModel.php');
require_once(__ROOT__ . '/models/PasswordResetModel.php');
require_once(__ROOT__ . '/views/UserView.php');
require_once(__ROOT__ . '/views/MailView.php');
require_once(__ROOT__ . '/utils/MailUtils.php');

require_once __ROOT__ . "/utils/HttpHandler.php";

class UserController extends BaseController
{
    protected static $model = UserModel::class;
    public static function showLogin() {
        $view = new UserView();
        $view->renderLogin();
    }

    public static function showHome() {
        $view = new UserView();
        $view->renderHome();
    }

    public static function showForgotPassword() {
        $view = new UserView();
        $view->renderForgottedPassword();
    }

    public static function showResetPassword() {
        $httpHandler = new HttpHandler;
        $data = $_GET;

        if (isset($data["token"])) {
            $reset = PasswordResetModel::where(["token" => $data["token"], "approved" => 1])[0];
        }

        if(!isset($reset) || !$reset) {
            // $_SESSION['error'] = "Link di reset non valido";
        }

        $view = new UserView();
        $view->renderResetPassword($reset);
    }

    public static function resetPassword()
    {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        $password_reset = PasswordResetModel::where(["token" => $data["token"]])[0];
        if(!isset($password_reset) || !$password_reset) {
            $_SESSION['error'] = "Link di reset non valido";
            return header("Location: /");
        }

        if($password_reset->approved != 1) {
            $_SESSION['error'] = "Link di reset non valido";
            return header("Location: /");
        }

        if(strlen($data['new_password']) < 5) {
            $_SESSION['error'] = "Password troppo corta";
            if(isset($_SERVER['HTTP_REFERER'])){
                return header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                return header("Location: /");
            }
        }

        //Get user by token
        $id_user = $password_reset->id_user;

        $user = UserModel::get($id_user);
        $user->password = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $user->save();

        //Password has been changed, delete
        PasswordResetModel::delete($password_reset->id);

        $_SESSION['success'] = "Password cambiata con successo!";
        header("Location: /login");
    }

    public static function requestPasswordReset()
    {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();
        $reset = new PasswordResetModel();

        if($user = UserModel::whereEmail($data["email"])) {
            $reset->id_user = $user->id;
            $reset->token = PasswordResetModel::generateKey();
            $reset->authorize_token = PasswordResetModel::generateKey();
            $reset->approved = 0;
            $reset->requested_at = date('Y-m-d H:i:s');

            $reset->save();
            $_SESSION['success'] = "Richiesta inviata, controlla la tua casella di posta";

            #Send Emails
            $mailView = new MailView();
            $mail = new MailUtils();
            $mail->sendMail($user->email, "Reset della password", $mailView->renderForgotten($reset->token));
            $mail->sendMail(EnvLoader::getValue("ADMIN"), "Richiesta reset della password", $mailView->renderAuthorizeRequest($reset->id_user, $user->email, $reset->requested_at, $reset->token, $reset->authorize_token));
            // ob_clean();

        } else {
            $_SESSION['error'] = "Email non trovata";
        }



        header("Location: /forgot_password");
    }

    public static function authorizeReset()
    {
        $httpHandler = new HttpHandler;
        // $data = $httpHandler->handleRequest();
        $data = $_GET;

        $reset = PasswordResetModel::where(["token" => $data["token"], "authorize_token" => $data["authorize_token"]])[0];
        if(isset($reset) || $reset) {
            $reset->approved = 1;
            $reset->save();
        }

        header("Location: /");
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