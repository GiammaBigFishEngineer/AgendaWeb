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
        
        $keys = ["title", "start"];

        foreach ($events as &$event) {
            $event->title = $event->titolo;
            $event->start = $event->partenza;

            foreach ($event as $key => $value) {
                if (!in_array($key, $keys)) {
                    unset($event->$key);
                }
            }
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
        // $data = $httpHandler->handleRequest();

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