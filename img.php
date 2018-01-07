<?php

class CropFrame {
    public $x;
    public $y;
    public $width;
    public $height;

    function __construct($x, $y, $width, $height) {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

}

class Img {
    private static function getRotationAngle($filename) {
        $exif = exif_read_data($filename);

        $angle = 0;

        if (!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $angle = 90;
                break;
                case 3:
                    $angle = 180;
                break;
                case 6:
                    $angle = -90;
                break;
           }
        }

        return $angle;
    }

    private static function getCropFrame($width, $height, $outputWidth, $outputHeight) {
        $ratio = $width / $height;
        $outputRatio = $outputWidth / $outputHeight;

        if ($ratio == $outputRatio) {
            return new CropFrame(0, 0, $width, $height);
        }
        else {
            if ($outputRatio > $ratio) {
                $cropFrameHeight = round($height / ($outputRatio / $ratio));
                $cropFrameY = round(($height - $cropFrameHeight) / 2);

                return new CropFrame(0, $cropFrameY, $width, $cropFrameHeight);
            }
            else {
                $cropFrameWidth = round($width / ($ratio / $outputRatio));
                $cropFrameX = round(($width - $cropFrameWidth) / 2);
                return new CropFrame($cropFrameX, 0, $cropFrameWidth, $height);
            }
        }
    }

    public static function resizeCrop($filename, $outputFilename, $outputWidth, $outputHeight) {
        $image = imagecreatefromjpeg($filename);

        $angle = self::getRotationAngle($filename);
        if ($angle != 0) {
            $image = imagerotate($image, $angle, 0);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $cropFrame = self::getCropFrame($width, $height, $outputWidth, $outputHeight);

        $outputImage = imagecreatetruecolor($outputWidth, $outputHeight);
        imagecopyresampled($outputImage, $image, 0, 0, $cropFrame->x, $cropFrame->y, $outputWidth, $outputHeight, $cropFrame->width, $cropFrame->height);
    
        imagesavealpha($outputImage, TRUE);

        imagejpeg($outputImage, $outputFilename);
    }

    public static function originalSize($filename, $outputFilename) {
        $image = imagecreatefromjpeg($filename);

        $angle = self::getRotationAngle($filename);
        if ($angle != 0) {
            $image = imagerotate($image, $angle, 0);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $outputImage = imagecreatetruecolor($width, $height);
        imagecopy($outputImage, $image, 0, 0, 0, 0, $width, $height);
    
        imagesavealpha($outputImage, TRUE);

        imagejpeg($outputImage, $outputFilename);
    }

}