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
        $res = null;
        $etag = null;
    
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $cachedEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ? $_SERVER['HTTP_IF_NONE_MATCH'] : null;
    
            try {
                $model = static::$model::get($id);
                $res = json_encode($model);
                
                $etag = md5($res);
    
                if (isset($cachedEtag) && $cachedEtag === $etag) {
                    //Cache is still correct
                    $httpHandler->sendResponse(body: null, status: 304, headers: [ "ETag" => $etag ]);
                } else {
                    //Cache is not correct
                    $httpHandler->sendResponse(body: $res, headers: [ 'Content-Type' => 'application/json; charset=utf-8', "ETag" => $etag ]);
                }
            } catch (Exception $e) {
                $httpHandler->sendResponse(body: $res, status: 500);
            }
        }
    }

    private static function etag(string $json)
    {
        return md5($json);
    }

    public static function delete(int $id)
    {
        $httpHandler = new HttpHandler;
        $res = null;

        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            try {
                $res = static::$model::delete($id);
                $httpHandler->sendResponse(body: $res, status: 204);
            } catch (Exception $e) {
                $httpHandler->sendResponse(body: $res, status: 404);
            }
            
        }

    }

    public static function save(?int $id = null, ?array $extra_data = null)
    {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        //Remove empty value strings
        $data = array_filter($data, function($value) {
            return $value !== '';
        });

        $res = null;

        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            try {
                if ($id === null) {
                    $obj = new static::$model($data);
                    
                    if (isset($extra_data)) {
                        foreach ($extra_data as $key => $value) {
                            $obj->$key = $value;
                        }
                    }

                    if ($obj->validate()){
                        $obj->save();
                        $httpHandler->sendResponse(body: $res, status: 201);    
                    }
                } else if ($id !== null) {
                    $obj = static::$model::get($id);
                    $obj->update($data);

                    if (isset($extra_data)) {
                        foreach ($extra_data as $key => $value) {
                            $obj->$key = $value;
                        }
                    }

                    if ($obj->validate()){
                        $obj->save();
                        $httpHandler->sendResponse(body: $res, status: 204); 
                    }
                }
            } catch (Exception $e) {
                $res = $e->getMessage();

                $res = "Error updating";
                $httpHandler->sendResponse(body: $res, status: 500);
            }

        }
    }
}