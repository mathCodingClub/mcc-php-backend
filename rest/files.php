<?php

namespace mcc\rest;

class files extends \mcc\obj\slimClass\service {

  private $user;
  private $param;

  public function __construct($path, $param) {
    parent::__construct($path);
    $this->param = $param;
  }

  public function middleware() {
    $this->user = \mcc\obj\user\services\user::initByCookie();
  }

  public function post() {
    if (array_key_exists('servers', $this->param) &&
        array_key_exists($_SERVER['SERVER_NAME'], $this->param['servers'])) {
      $base = $this->param['servers'][$_SERVER['SERVER_NAME']]['path'];
      $relPath = $this->param['servers'][$_SERVER['SERVER_NAME']]['relpath'];
    } else {      
      $base = $this->param['path'];
      $relPath = $this->param['relpath'];
    }
    $prefix = date('Y-m-d');
    $relFileName = $relPath . $prefix . '-' . $_FILES['file']['name'];
    $fileName = $base . $relFileName;
    $newFileName = isset($_POST['name']) ? $relPath . $prefix . '-' . $_POST['name'] : null;
    error_log($newFileName);
    move_uploaded_file($_FILES['file']['tmp_name'], $fileName);
    $isImage = $this->isImage($fileName);
    $sizeToReturn = null;
    if ($isImage) {
      $size = getimagesize($fileName);
      $maxSize = isset($_POST['resize']) ? (int) $_POST['resize'] : 1000;
     
      if (isset($_POST['crop'])) {
        $crop = json_decode($_POST['crop'], true);
        if (count($crop) == 2) {
          $cropCommand = $this->getCropCommand($crop, $fileName);
          error_log($cropCommand);
          shell_exec($cropCommand);
        }
      }
      if ($size[0] > $maxSize || $size[1] > $maxSize) {
        $command = "convert \"$fileName\" -resize {$maxSize}x$maxSize \"$fileName\"";
        shell_exec($command);
        $size = getimagesize($fileName);
      }
      $sizeToReturn = array('width' => $size[0], 'height' => $size[1]);
    }
    chmod($fileName, 0666);
    if (!is_null($newFileName)) {
      rename($fileName, $base . $newFileName);
      $relFileName = $newFileName;
    }

    $reply = array('isImage' => $isImage,
        'relPath' => $relFileName,
        'size' => $sizeToReturn);
    $this->sendArrayAsJSON($reply);
  }

  private function isImage($file) {
    if (is_array(getimagesize($file))) {
      $image = true;
    } else {
      $image = false;
    }
    return $image;
  }

  private function getCropCommand($crop, $imageName) {
    $width = abs($crop[0]['x'] - $crop[1]['x']);
    $height = abs($crop[0]['y'] - $crop[1]['y']);
    $offsetx = min(array($crop[0]['x'], $crop[1]['x']));
    $offsety = min(array($crop[0]['y'], $crop[1]['y']));
    return "convert -crop {$width}x$height+$offsetx+$offsety \"$imageName\" \"$imageName\"";
  }

}
