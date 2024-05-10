<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require_once(__ROOT__ . '/vendor/autoload.php');
// require_once(__ROOT__ . '/config/config.php');

require_once(__ROOT__ . '/config/EnvLoader.php');

//Create an instance; passing `true` enables exceptions
class MailUtils {
    private $mail;
    public $user;
    //private $username = MailConfig::$username;

    public function __construct(){
        $this->mail = new PHPMailer(true);

        $this->user = EnvLoader::getValue("SMTP_USER");

        //Server settings
        if(EnvLoader::getValue("APP_DEBUG") == true){
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                  //Enable verbose debug output
            $this->mail->Debugoutput = 'error_log';
        }

        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->CharSet = "UTF-8";

        $this->mail->Host       = gethostbyname(EnvLoader::getValue("SMTP_HOST"));  //Set the SMTP server to send through
        $this->mail->Port       = EnvLoader::getValue("SMTP_PORT");                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //* Disabilito le funzioni dei certificiati/login per usare MailDev

        if(EnvLoader::getValue("APP_DEBUG") == false){
            $this->mail->SMTPAuth = true;                                       //Enable SMTP authentication
            $this->mail->Username   = EnvLoader::getValue("SMTP_USER");         //SMTP username
            $this->mail->Password   = EnvLoader::getValue("SMTP_PASSWORD");     //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           //Enable implicit TLS encryption
        }


        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            )
        );

    }

    public function sendMail(string $recipient, string $subject, string $body, bool $html = true){
        try {
            //Recipients
            $this->mail->setFrom(EnvLoader::getValue("SMTP_USER"), EnvLoader::getValue("SMTP_NAME"));
            //Add a recipient
            $this->mail->clearAllRecipients();
            $this->mail->addAddress($recipient);

            //Content
            $this->mail->isHTML(true);                             //Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            //self::$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $this->mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            // error_log("Message could not be sent. Mailer Error: " . $this->mail->ErrorInfo);
        }
    }
}
