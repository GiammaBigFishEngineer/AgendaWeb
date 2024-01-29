<?php

require_once(__ROOT__ . '/config/EnvLoader.php');
require_once(__ROOT__ . "/utils/HttpHandler.php");

require_once(__ROOT__ . "/models/BaseModel.php");

class FileController
{
    protected $basePath;
    protected $objPath;

    protected array $limits;
    // protected static $model = BaseModel::class;

    public function __construct($model) {
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
                    'link' => $this->objPath . '/' . $file['file']
                ];
            }
        } catch (Exception $e) {
            $documents = [];
        }

        return $documents;
    }

    public function addFiles($file){
        // $documents = [];
        $fileInfo = new FileInfo($this->objPath);
        
        $fileInfo->addFileInfo($file['id'], $file['name'], $file['file'], $file['type'], $file['upload_date']);
        move_uploaded_file($file["tmp_name"], $this->objPath . $file["name"]);

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

    public function deleteFile($fileName)
    {
        $fileInfo = $this->readFileInfo();
        foreach ($fileInfo as $key => $file) {
            if ($file['name'] === $fileName) {
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
}