<?php
require_once(__ROOT__ . '/vendor/autoload.php');
use Dotenv\Dotenv;
use Dazet\TypeUtil\BooleanUtil;

class EnvLoader {
    
    public static Dotenv $dotenv;
    private static array $values;

    public function __construct(){

        $debug = getenv('APP_DEBUG');
        if($debug == "true"){
            self::$dotenv = Dotenv::createMutable(__ROOT__, '.dev.env');   
        }
        else {
            self::$dotenv = Dotenv::createMutable(__ROOT__);
        }
        self::$values = self::$dotenv->load();
    }

    public static function getValue($key){

        if( getenv($key) != null ){
            $key = getenv($key);
        } else {
            $key = self::$values[$key];
        }

        if(BooleanUtil::canBeBool($key)){
            $key = BooleanUtil::toBool($key);
        }

        return $key;
    }
}
