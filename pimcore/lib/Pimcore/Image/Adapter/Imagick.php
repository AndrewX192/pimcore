<?php 
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @copyright  Copyright (c) 2009-2010 elements.at New Media Solutions GmbH (http://www.elements.at)
 * @license    http://www.pimcore.org/license     New BSD License
 */
 
class Pimcore_Image_Adapter_Imagick extends Pimcore_Image_Adapter {


    /**
     * @var Imagick
     */
    protected $resource;

    public function load ($imagePath) {

        $this->resource = new Imagick();
        $this->resource->readImage($imagePath);

        // set dimensions
        $this->setWidth($this->resource->getImageWidth());
        $this->setHeight($this->resource->getImageHeight());

        return $this;
    }

    /**
     * @param  $path
     * @return void
     */
    public function save ($path, $quality = null) {

        if($quality) {
            $this->resource->setCompressionQuality($quality);
            $this->resource->setImageCompressionQuality($quality);
        }
        
        $this->resource->writeImage($path);

        return $this;
    }


    /**
     * @param  $format
     * @return void
     */
    public function setFormat($format) {
        $this->resource->setImageFormat($format);

        return $this;
    }

    /**
     * @param  $width
     * @param  $height
     * @return Pimcore_Image_Adapter
     */
    public function resize ($width, $height) {
        $this->resource->resizeimage($width, $height, Imagick::FILTER_UNDEFINED, 0);

        $this->setWidth($width);
        $this->setHeight($height);

        return $this;
    }

    /**
     * @param  $x
     * @param  $y
     * @param  $width
     * @param  $height
     * @return Pimcore_Image_Adapter_Imagick
     */
    public function crop($x, $y, $width, $height) {
        $this->resource->cropImage($width, $height, $x, $y);

        $this->setWidth($width);
        $this->setHeight($height);

        return $this;
    }


    /**
     * @param  $width
     * @param  $height
     * @param string $color
     * @param string $orientation
     * @return Pimcore_Image_Adapter_Imagick
     */
    public function frame ($width, $height) {

        $this->contain($width, $height);

        $x = ($width - $this->getWidth()) / 2;
        $y = ($height - $this->getHeight()) / 2;


        $newImage = new Imagick();
        $newImage->newimage($width, $height, "transparent");
        $newImage->compositeImage($this->resource, Imagick::COMPOSITE_DEFAULT , $x, $y);
        $this->resource = $newImage;

        $this->setWidth($width);
        $this->setHeight($height);

        return $this;
    }

    /**
     * @param  $color
     * @return Pimcore_Image_Adapter
     */
    public function setBackgroundColor ($color) {

        $newImage = new Imagick();
        $newImage->newimage($this->getWidth(), $this->getHeight(), $color);
        $newImage->compositeImage($this->resource, Imagick::COMPOSITE_DEFAULT , $x, $y);
        $this->resource = $newImage;

        return $this;
    }


    /**
     * @param  $angle
     * @param bool $autoResize
     * @param string $color
     * @return Pimcore_Image_Adapter_Imagick
     */
    public function rotate ($angle) {

        $this->resource->rotateImage(new ImagickPixel('none'), $angle);
        $this->setWidth($this->resource->getimagewidth());
        $this->setHeight($this->resource->getimageheight());

        return $this;
    }


    /**
     * @param  $x
     * @param  $y
     * @return Pimcore_Image_Adapter_Imagick
     */
    public function roundCorners ($x, $y) {

        $this->resource->roundCorners($x, $y);

        return $this;
    }
}