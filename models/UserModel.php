<?php

require_once('BaseModel.php');

/*
Questo user model definsice una linea generale sulla realizazzione User
Cambiare in base alle esigenze.
Il metodo login salva in sessione delle variabili, ATTENZIONE: la sessione deve
partire dall'index.php anche se l'utente non è loggato. I dati dell'utente saranno estratti
dalla sessione.
Signup Control controlla la validità della registrazione.
*/
class UserModel extends BaseModel
{
    public static string $nome_tabella = 'Users';
    protected array $_fields = [
        "id",
        "email",
        "password",
        "ruolo",
    ];

    public array $hidden_fields = [
        "password"
    ];

    public static function whereEmail(string $email): ?UserModel {
        $results = UserModel::where(array("email" => $email));
        return $results[0] ?? null;
    }

    public static function login($password,$email) {
        // Query per recuperare l'hash della password dal database
        $stmt = DB::get()->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se è stata trovata una corrispondenza nella tabella degli utenti
        if ($user) {
            $password_hash = $user["password"];

            // Verifica se la password immessa corrisponde all'hash memorizzato
            if (password_verify($password, $password_hash)) {
                // Autenticazione riuscita
                // Crea una sessione o imposta un cookie per mantenere l'autenticazione
                
                // $_SESSION['email'] = $email;
                $user = UserModel::whereEmail($email);

                $_SESSION['user'] = $user;
                $_SESSION['loggedIn'] = true;

                // Risponde con un codice di successo e i dati dell'utente
                
                echo json_encode(array("message" => "Autenticazione riuscita", "email" => $email));
                //header("Location: "."");
            } else {
                // Autenticazione non riuscita
                // Risponde con un codice di errore
                http_response_code(401);
                throw new Exception("Password non corretta");
            }
        } else {
            // Autenticazione non riuscita
            // Risponde con un codice di errore
            throw new Exception("Email non esistente");
        }
    }  

    public static function signup_control ($email): int
    {
        
        $control_qr = "SELECT * FROM Users WHERE email = :email";
        $stmt = DB::get()->prepare($control_qr);
        $stmt->execute([
            'email'=>$email,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            $message = "Questa email é già utilizzata";
            echo $message;
            return 1;
        }
        return 0;
    }
}