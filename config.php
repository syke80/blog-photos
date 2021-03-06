<?php
class Config {
  private $basePath;
  private $photosDir;
  private $resizedDir;
  private $originalFilesUrl;
  private $resizedFilesUrl;
  private $availableResolutions;

  function __construct() {
    $settings = json_decode(file_get_contents('./settings.json'));
    $this->basePath = $settings->{'basePath'};
    $this->photosDir = $settings->{'photosDir'};
    $this->resizedDir = $settings->{'resizedDir'};
    $this->originalFilesUrl = $settings->{'originalFilesUrl'};
    $this->resizedFilesUrl = $settings->{'resizedFilesUrl'};
    $this->availableResolutions = $settings->{'availableResolutions'};
  }
  
  public function getBasePath() {
    return $this->basePath;
  }

  public function getPhotosDir() {
    return $this->photosDir;
  }

  public function getResizedDir() {
    return $this->resizedDir;
  }

  public function getOriginalFilesUrl() {
    return $this->originalFilesUrl;
  }

  public function getResizedFilesUrl() {
    return $this->resizedFilesUrl;
  }

  public function getAvailableResolutions() {
    return $this->availableResolutions;
  }
}