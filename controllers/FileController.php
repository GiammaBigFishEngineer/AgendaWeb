<?php

require_once(__ROOT__ . '/config/EnvLoader.php');
require_once(__ROOT__ . "/utils/HttpHandler.php");

require_once(__ROOT__ . "/models/BaseModel.php");
use Carbon\Carbon;

class FileController
{
    protected $basePath;
    protected $objPath;

    protected ?array $limits;
    // protected static $model = BaseModel::class;

    public function __construct($model, ?array $limits = null) {
        $this->limits = $limits;
        $env_dir = EnvLoader::getValue("FILE_DIR");

        if (realpath($env_dir)) {
            //Relative
            $this->basePath = $env_dir;
        } else {
            //Absolute
            $this->basePath = __ROOT__ . '/' . $env_dir;
        }

        $this->objPath = $this->basePath . '/' . $model::$nome_tabella . "/" . $model->id . '/';

        if (!file_exists($this->objPath)) {
            mkdir($this->objPath, recursive: true);
        }

        isset($limits) ? $this->limits = $limits : null;
    }

    public function getFiles(?string $subfolder = null) {
        $documents = [];
        $fileInfo = new FileInfo($this->objPath);

        try {
            if (!file_exists($this->objPath)) {
                mkdir($this->objPath, recursive: true);
            }

            $files = $fileInfo->getFiles();

            foreach ($files as $file) {
                $documents[] = [
                    'file' => $file,
                    'link' => $this->objPath . $file['file']
                ];
            }
        } catch (Exception $e) {
            $documents = [];
        }

        return $documents;
    }

    public function addFiles($file, $data){
        $fileInfo = new FileInfo($this->objPath);

        $maxFileSize = ini_get('upload_max_filesize');
        $maxFileSizeBytes = self::return_bytes($maxFileSize);

        if ($file["error"] != 0) {
            if($file["size"] > $maxFileSizeBytes) {
                throw new Exception("Il file caricato supera la dimensione massima consentita.");
            } else{
                throw new Exception("Errore durante il caricamento file");

            }
        }

        $this->checkFileRequirements($file);
        
        if ($file && $data) {
            if (!isset($data['id'])) {
                $data['id'] = $fileInfo->getHighestId() + 1;
            }

            if (!isset($file['type'])) {
                $file['type'] = "none";
            }

            if (!isset($data['upload_date'])) {
                $data['upload_date'] = Carbon::now()->format('Y-m-d H:i:s');
            }

            $fileInfo->addFileInfo($data['id'], $data['name'], $file['name'], $file['type'], $data['upload_date']);
            move_uploaded_file($file["tmp_name"], $this->objPath . $file["name"]);
        } else {
            throw new Exception("Errore durante il caricamento file");
        }

    }

    function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
    
        return $val;
    }

    public function deleteFile($id)
    {
        $fileInfo = new FileInfo($this->objPath);

        try {
            $file = $fileInfo->searchFileById($id);
            $fileInfo->deleteFile($id);
            unlink($this->objPath . $file["file"]);    
        } catch (Exception $e) {
            
        }

    }

    public function checkFileRequirements($file): void {
        $fileInfo = new FileInfo($this->objPath);

        // Check the number of files
        if (isset($this->limits['max_files']) && $fileInfo->fileCount() >= $this->limits['max_files']) {
            // throw new \Exception("Number of files exceeds the limit");
            throw new \Exception("Numero dei file supera il limite");
        }
    
        // Check the size of the file
        if (isset($this->limits['max_size'])) {
            if ($file['size'] > $this->limits['max_size']) {
                // throw new \Exception("File size exceeds the limit");
                throw new \Exception("Il file caricato supera la dimensione massima consentita.");


            }
        }
    }

    public function deleteParentFolder()
    {
        $files = glob($this->objPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->objPath);
    }
}

class FileInfo
{
    private $folderPath;
    private $jsonFilePath;

    public function __construct($folderPath)
    {
        $this->folderPath = $folderPath;
        $this->jsonFilePath = $folderPath . '/file_info.json';
    }

    private function readFileInfo()
    {
        if (file_exists($this->jsonFilePath)) {
            $jsonContent = file_get_contents($this->jsonFilePath);
            return json_decode($jsonContent, true);
        } else {
            return [];
        }
    }

    private function saveFileInfo($fileInfo)
    {
        $jsonContent = json_encode($fileInfo, JSON_PRETTY_PRINT);
        file_put_contents($this->jsonFilePath, $jsonContent);
    }

    public function searchFile($fileName)
    {
        $fileInfo = $this->readFileInfo();
        foreach ($fileInfo as $file) {
            if ($file['name'] === $fileName) {
                return $file;
            }
        }

        return null; // File not found
    }

    public function searchFileById($id)
    {
        $fileInfo = $this->readFileInfo();
        foreach ($fileInfo as $file) {
            if ($file['id'] === $id) {
                return $file;
            }
        }

        return null; // File not found
    }

    public function addFileInfo($id, $name, $file, $type, $uploadDate)
    {
        $fileInfo = $this->readFileInfo();
        $newFileInfo = [
            'id' => $id,
            'name' => $name,
            'file' => $file,
            'type' => $type,
            'upload_date' => $uploadDate
        ];

        $fileInfo[] = $newFileInfo;
        $this->saveFileInfo($fileInfo);
    }

    public function deleteFile($id)
    {
        $fileInfo = $this->readFileInfo();
        foreach ($fileInfo as $key => $file) {
            if ($file['id'] === $id) {
                unset($fileInfo[$key]);
                $this->saveFileInfo(array_values($fileInfo)); // Re-index the array
                return true; // File deleted
            }
        }

        return false;
    }

    public function hasFile($fileName)
    {
        $fileInfo = $this->readFileInfo();
        foreach ($fileInfo as $file) {
            if ($file['name'] === $fileName) {
                return true; // File found
            }
        }
        return false; // File not found
    }

    public function getFiles()
    {
        $fileInfo = $this->readFileInfo();
        $files = [];
        foreach ($fileInfo as $file) {
            $files[] = [
                'id' => $file['id'],
                'name' => $file['name'],
                'file' => $file['file']
            ];
        }
        return $files;
    }

    public function getHighestId()
    {
        $fileInfo = $this->readFileInfo();
        $highestId = 0;
        foreach ($fileInfo as $file) {
            $highestId = max($highestId, $file['id']);
        }

        return $highestId;
    }

    public function fileCount()
    {
        $fileInfo = $this->readFileInfo();
        return count($fileInfo);
    }
}