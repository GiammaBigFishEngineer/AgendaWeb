<?php

require_once('EnvLoader.php');

class DB
{
    private static $instance = null;

    public static function get()
    {
        // $config = new EnvLoader();
        if (self::$instance == null) {
            $uri = "mysql:host=" . EnvLoader::getValue("DB_HOST") . 
            ";dbname=" . EnvLoader::getValue("DB_NAME") . 
            ";port=" . EnvLoader::getValue("DB_PORT");

            try {
                self::$instance = new PDO(
                    $uri,
                    EnvLoader::getValue("DB_USERNAME"),
                    EnvLoader::getValue("DB_PASSWORD"),
                    array(
                        PDO::ATTR_PERSISTENT => true
                    )
                );

            } catch (PDOException $e) {
                // Handle this properly
                throw $e;
            }
        }

        return self::$instance;
    }
}
