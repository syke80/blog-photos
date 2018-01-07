<?php
include 'img.php';
include 'config.php';

const INVALID_RESOLUTION_ERROR = 'Invalid resolution';

class App {
  private $config;
  private $fileName;
  private $resolution;
  private $resizedFileName;
  private $resizedFileUrl;
  private $resizedFileDir;
  private $originalFileDir;
  
  function __construct($config) {
      $this->config = $config;
      $this->fileName = $this->getFileName();
      $this->resolution = $this->getResolution();
      $this->resizedFileName = $this->getResizedFileName();
      $this->resizedFileUrl = $this->getResizedFileUrl();
      $this->resizedFileDir = $this->getResizedFileDir();
      $this->originalFileDir = $this->getOriginalFileDir();
  }


  private function pathParts() {
    $relativePath = ltrim($_SERVER['REQUEST_URI'], $this->config->getBasePath());
    return explode('/', trim($relativePath, '/'));
  }

  private function getResizedFileDir() {
    return $this->config->getResizedDir() . $this->resizedFileName;
  }

  private function getOriginalFileDir() {
    return $this->config->getPhotosDir() . $this->fileName;
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
    return $this->config->getResizedFilesUrl() . $this->getResizedFileName();
  }

  private function getResolution() {
    $parts = $this->pathParts();

    if (sizeof($parts) >= 2) {
      return $parts[0];
    }
    
    return null;
  }
  
  public function createResizedFileIfNotExist() {
    if (!in_array($this->resolution, $this->config->getAvailableResolutions())) {
      throw new Exception(INVALID_RESOLUTION_ERROR);
    }

    if (!file_exists($this->config->getResizedDir().$this->resizedFileName)) {
      $resolutionParts = explode('x', $this->resolution);    
      Img::resizeCrop($this->originalFileDir, $this->resizedFileDir, $resolutionParts[0], $resolutionParts[1]);
    }
  }

  public function createOriginalSizedFileIfNotExist() {
    if (!file_exists($this->config->getResizedDir().$this->resizedFileName)) {
      Img::originalSize($this->originalFileDir, $this->resizedFileDir);
    }
  }

  public function getOriginalFileUrl() {
    $originalUrl = $this->config->getOriginalFilesUrl() . $this->fileName;
    return $originalUrl;
  }
  
  public function run() {
    if (!file_exists($this->originalFileDir)) {
        header("Content-Type: text/plain");
	header("HTTP/1.0 404 Not Found");
	echo "file not found \r\n";
        echo "file: " . $this->fileName . "\r\n";
        echo "resolution: " . $this->resolution . "\r\n";
    }

    if (empty($this->resolution)) {
      $this->createOriginalSizedFileIfNotExist();
    }
    else {
      $this->createResizedFileIfNotExist();
    }

    header("Location: " . $this->resizedFileUrl, 301);
  }
}

$config = new Config();
$app = new App($config);
$app->run();
