<?php
require_once('BaseController.php');
require_once('FileController.php');


require_once(__ROOT__ . '/models/EventModel.php');

require_once __ROOT__ . "/utils/HttpHandler.php";

use Carbon\Carbon;

class EventController extends BaseController
{
    protected static $model = EventModel::class;
    
    //3MB
    protected static $limits = ["max_files" => 5, "max_size" => 3 * 1024 * 1024];

    public static function save(?int $id = null, ?array $extra_data = null){
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        $extraData = [];
        $caparre = [];
    
        $caparraData = array();

        foreach ($data as $key => $value) {
            // Extract the index after "caparra-value-"
            if (strpos($key, 'caparra-value-') === 0) {
                $index = substr($key, 14); 
                $caparraData[(int)$index]["value"] = $value;
            } 
            if (strpos($key, 'caparra-type-') === 0) {
                $index = substr($key, 13);
                $caparraData[(int)$index]["type"] = $value;
            }
        }

        foreach ($caparraData as $index => $caparra) {
            if ($caparra["type"] === "" || $caparra["value"] === "") {
                unset($caparraData[$index]);
            }
        }
        
        $extraData["caparre"] = json_encode($caparraData);
        
        parent::save($id, $extraData);
    }
    
    public static function create($title, $arrival, $departure, $leader, $email, $phone, $notes) {
        $event = new EventModel();
        
        $format = 'Y-m-d';

        if (Carbon::createFromFormat($format, $arrival) === false) {
            throw new Exception('The date format for arrival is not correct.');
        }

        if (Carbon::createFromFormat($format, $departure) === false) {
            throw new Exception('The date format for arrival is not correct.');
        }

        if (!Carbon::parse($departure)->lt(Carbon::parse($arrival))) {
            // echo '$departure is not before $arrival.';
            throw new Exception('The departure cannot be before the arrival.');
        }

        // Set the properties of the event
        $event->titolo = $title;
        $event->arrivo = $arrival;
        $event->partenza = $departure;
        $event->capo_gruppo = $leader;
        $event->email = $email;
        $event->telefono = $phone;
        $event->note = $notes;
    
        // Save the event to the database
        $event->save();
    }

    public static function byDate(){
        $httpHandler = new HttpHandler;
        $data = $_GET;

        $start = Carbon::parse($data["start"]);
        $end = Carbon::parse($data["end"]);

        $events = EventModel::getByDates($start, $end);
        
        $keys = ["id", "title", "start", "end", "color"];

        foreach ($events as &$event) {
            $event->title = $event->titolo;
            $event->end = $event->arrivo;
            $event->start = $event->partenza;
            
            // $darken;
            // $event->stato == 1 ? $darken = 35 : $darken = 0;
            $event->color = EventColor::convert($event->colore)->toHex(0);
            
            $properties = (array)$event->getData();

            foreach ($properties as $key => $value) {
                if (!in_array($key, $keys)) {
                    unset($properties[$key]);
                }
            }

            $event = (array) $properties;
        }    
        
        $httpHandler->sendResponse(body: $events, status: 200);
    }

    public static function getFiles(?int $event_id = null, ?int $file_id = null){
        $httpHandler = new HttpHandler;
        $files = null;

        if($event_id != null) {
            $event = EventModel::get($event_id);
        }
        
        $fileController = new FileController($event, self::$limits);

        $files = $fileController->getFiles($file_id);
        
        $httpHandler->sendResponse($files, 200);
    }

    public static function addFile(?int $event_id = null, ?int $file_id = null)
    {
        $httpHandler = new HttpHandler;
        $data = $httpHandler->handleRequest();

        $file = (isset($_FILES['file'])) ? $_FILES['file'] : null;

        $event = EventModel::get($event_id);

        try {
            $fileController = new FileController($event, self::$limits);
            $fileController->addFiles($file, $data);

            $httpHandler->sendResponse("File added successfully", 200);
        } catch (Exception $e) {
            $httpHandler->sendResponse("Failed to add file", 500);
        }

    }

    public static function deleteFiles(int $event_id = null, int $file_id = null){
        $httpHandler = new HttpHandler;

        $event = EventModel::get($event_id);

        try {
            $fileController = new FileController($event, self::$limits);
            $fileController->deleteFile($file_id);

            $httpHandler->sendResponse("File deleted successfully", 200);
        } catch (Exception $e) {
            $httpHandler->sendResponse("Failed to delete file", 500);
        }
    }
}