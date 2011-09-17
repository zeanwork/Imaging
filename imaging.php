<?php
/**
 * Imaging
 * http://github.com/josemarluedke/Imaging
 * 
 * Copyright 2011, Josemar Davi Luedke <josemarluedke@gmail.com>
 * 
 * Licensed under the MIT license
 * Redistributions of part of code must retain the above copyright notice.
 * 
 * @author Josemar Davi Luedke <josemarluedke@gmail.com>
 * @version 0.1.0
 * @copyright Copyright 2010, Josemar Davi Luedke <josemarluedke.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Imaging {
    private $imgInput;
    private $imgOutput;
    private $imgSrc;
    private $format;
    private $quality = 80;
    private $xInput;
    private $yInput;
    private $xOutput;
    private $yOutput;
    private $resize;

    public function setImg($img){
        // Find format
        $ext = strtoupper(pathinfo($img, PATHINFO_EXTENSION));
        // JPEG image
        if(is_file($img) && ($ext == "JPG" OR $ext == "JPEG")){
            $this->format = $ext;
            $this->imgInput = ImageCreateFromJPEG($img);
            $this->imgSrc = $img;
        }
        // PNG image
        elseif(is_file($img) && $ext == "PNG"){
            $this->format = $ext;
            $this->imgInput = ImageCreateFromPNG($img);
            $this->imgSrc = $img;
        }
        // GIF image
        elseif(is_file($img) && $ext == "GIF"){
            $this->format = $ext;
            $this->imgInput = ImageCreateFromGIF($img);
            $this->imgSrc = $img;
        }
        // Get dimensions
        $this->xInput = imagesx($this->imgInput);
        $this->yInput = imagesy($this->imgInput);
    }
	
    public function setSize($maxX = 1000, $maxY = 1000){
    	//Set maximum image size (pixels)
        // Resize
        if($this->xInput > $maxX || $this->yInput > $maxY){
            $a= $maxX / $maxY;
            $b= $this->xInput / $this->yInput;
            if ($a<$b){
                $this->xOutput = $maxX;
                $this->yOutput = ($maxX / $this->xInput) * $this->yInput;
            }else{
                $this->yOutput = $maxY;
                $this->xOutput = ($maxY / $this->yInput) * $this->xInput;
            }
            // Ready
            $this->resize = TRUE;
        }
        // Don't resize      
        else { $this->resize = FALSE; }
    }
    
    public function setQuality($quality){
        if(is_int($quality)){
            $this->quality = $quality;
        }
    }
    
    public function saveImg($path){
        // Resize
        if($this->resize){
            $this->imgOutput = ImageCreateTrueColor($this->xOutput, $this->yOutput);
            ImageCopyResampled($this->imgOutput, $this->imgInput, 0, 0, 0, 0, $this->xOutput, $this->yOutput, $this->xInput, $this->yInput);
        }
        // Save JPEG
        if($this->format == "JPG" OR $this->format == "JPEG"){
            if($this->resize)
            	imageJPEG($this->imgOutput, $path, $this->quality);
            else
            	copy($this->imgSrc, $path);
        }
        // Save PNG
        elseif($this->format == "PNG"){
            if($this->resize)
            	imagePNG($this->imgOutput, $path);
            else
            	copy($this->imgSrc, $path);
        }
        // Save GIF
        elseif($this->format == "GIF"){
            if($this->resize) 
            	imageGIF($this->imgOutput, $path);
            else
            	copy($this->imgSrc, $path);
        }
    }
    
    public function getWidth(){
        return $this->xInput;
    }
    
    public function getHeight(){
        return $this->yInput;
    }
    
    public function clearCache(){
        @ImageDestroy($this->imgInput);
        @ImageDestroy($this->imgOutput);
    }

    public function cropImage($path, $top, $left){
        $this->imgOutput = ImageCreateTrueColor($this->xOutput, $this->yOutput);
        imagecopy($this->imgOutput, $this->imgInput, 0, 0, $top, $left, $this->xOutput.'x', $this->yOutput.'_crop_');

        // Save JPEG
        if($this->format == "JPG" OR $this->format == "JPEG"){
            imageJPEG($this->imgOutput, $path, $this->quality);
        }
        // Save PNG
        elseif($this->format == "PNG"){
            imagePNG($this->imgOutput, $path);
        }
        // Save GIF
        elseif($this->format == "GIF"){
            imageGIF($this->imgOutput, $path);
        }
    }

    public function setSizeCrop($width, $height){
        $this->xOutput = $width;
        $this->yOutput = $height;
    }
}

class CreateThumbnail extends Imaging {
    private $image;
   
	function __construct($image, $width = 1000, $height = 1000) {
		parent::setImg($image);
		parent::setQuality(80);
		parent::setSize($width,$height);
		parent::saveImg(dirname($image).'/Thumbnails/'.basename($image));
		parent::clearCache();
	}
}

class ResizeImage extends Imaging {
    private $image;
   
	function __construct($image, $width = 1000, $height = 1000) {
		parent::setImg($image);
		parent::setQuality(80);
		parent::setSize($width, $height);
		parent::saveImg($image);
		parent::clearCache();
	}
}

class CropImage extends Imaging {
    private $image;
   
    function __construct($image, $width = 1000, $height = 1000, $top = 0, $left = 0) {
        parent::setImg($image);
        parent::setQuality(100);
        parent::setSizeCrop($width, $height);
        parent::cropImage($image, $top, $left);
        parent::clearCache();
    }
}