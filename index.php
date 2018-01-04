<?php

include 'img.php';

const PHOTOS_DIR = '../fileserver/original/';
const RESIZED_DIR = '../fileserver/resized/';
const ORIGINAL_FILES_URL = 'http://localhost:3003/original/';
const RESIZED_FILES_URL = 'http://localhost:3003/resized/';
const AVAILABLE_RESOLUTIONS = [
    '100x100', '300x300', '640x480'
];
const INVALID_RESOLUTION_ERROR = 'Invalid resolution';

class App {
  private $filename;
  private $resolution;
  private $resizedFileName;
  private $resizedFileUrl;
  private $resizedFileDir;
  private $originalFileDir;
  
  function __construct() {
      $this->fileName = $this->getFileName();
      $this->resolution = $this->getResolution();
      $this->resizedFileName = $this->getResizedFileName();
      $this->resizedFileUrl = $this->getResizedFileUrl();
      $this->resizedFileDir = $this->getResizedFileDir();
      $this->originalFileDir = $this->getOriginalFileDir();
  }


  private function pathParts() {
    return explode('/', trim($_SERVER['REQUEST_URI'], '/'));
  }

  private function getResizedFileDir() {
    return RESIZED_DIR . '/' . $this->resizedFileName;
  }

  private function getOriginalFileDir() {
    return PHOTOS_DIR . '/' . $this->fileName;
  }
  
  private function getFileName() {
    $parts = $this->pathParts();

    if (sizeof($parts) === 0) {
      throw new Exception('Filename is not set');
      // www.exmaple.com/[resolution]/<filename>
      // www.exmaple.com/100x100/myimage.jpg
      // www.exmaple.com/myimage.jpg
    }

    if (sizeof($parts) === 1) {
      return $parts[0];
    }

    return $parts[1];
  }
  
  public function getResizedFileName() {
    if (empty($this->resolution)) {
      return strToLower('original-' . $this->fileName);
    }
    else {
      return strToLower($this->resolution . '-' . $this->fileName);
    }
  }

  public function getResizedFileUrl() {
    return RESIZED_FILES_URL . $this->getResizedFileName();
  }

  private function getResolution() {
    $parts = $this->pathParts();

    if (sizeof($parts) >= 2) {
      return $parts[0];
    }
    
    return null;
  }
  
  public function createResizedFileIfNotExist() {
    if (!in_array($this->resolution, AVAILABLE_RESOLUTIONS)) {
      throw new Exception(INVALID_RESOLUTION_ERROR);
    }

    if (!file_exists(RESIZED_DIR.$this->resizedFileName)) {
      $resolutionParts = explode('x', $this->resolution);    
      Img::resizeCrop($this->originalFileDir, $this->resizedFileDir, $resolutionParts[0], $resolutionParts[1]);
    }
  }

  public function createOriginalSizedFileIfNotExist() {
    if (!file_exists(RESIZED_DIR.$this->resizedFileName)) {
      Img::originalSize($this->originalFileDir, $this->resizedFileDir);
    }
  }

  public function getOriginalFileUrl() {
    $originalUrl = ORIGINAL_FILES_URL . $this->fileName;
    return $originalUrl;
  }
  

  public function run() {
    if (empty($this->resolution)) {
      $this->createOriginalSizedFileIfNotExist();
    }
    else {
      $this->createResizedFileIfNotExist();
    }

    header("Content-Type: image/jpeg");
    header("Location: " . $this->resizedFileUrl, 301);
  }
}

$app = new App();
$app->run();
