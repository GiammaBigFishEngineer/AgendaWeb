<?php
require_once(__ROOT__ . '/models/BaseModel.php');

require_once(__ROOT__ . '/vendor/autoload.php');
require_once(__ROOT__ . "/utils/HttpHandler.php");


class BaseController
{
    protected static $model = BaseModel::class;

    public static function get(int $id)
    {
        $httpHandler = new HttpHandler;
        // $data = $httpHandler->handleRequest();

        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return json_encode(static::$model::get($id));
        }

    }


}