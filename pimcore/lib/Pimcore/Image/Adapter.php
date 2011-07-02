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
 
abstract class Pimcore_Image_Adapter {

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;


    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }


    /**
     * @param  $colorhex
     * @return array
     */
    public function colorhex2colorarray($colorhex) {
        $r = hexdec(substr($colorhex, 1, 2));
        $g = hexdec(substr($colorhex, 3, 2));
        $b = hexdec(substr($colorhex, 5, 2));
        return array($r, $g, $b, 'type' => 'RGB');
    }


    /**
     * @param  $width
     * @param  $height
     * @return Pimcore_Image_Adapter
     */
    public function resize ($width, $height) {

        return $this;
    }

    /**
     * @param  $width
     * @return Pimcore_Image_Adapter
     */
    public function scaleByWidth ($width) {

        $height = round(($width / $this->getWidth()) * $this->getHeight(), 0);
        $this->resize(max(1, $width), max(1, $height));

        return $this;
    }

    /**
     * @param  $height
     * @return Pimcore_Image_Adapter
     */
    public function scaleByHeight ($height) {

        $width = round(($height / $this->getHeight()) * $this->getWidth(), 0);
        $this->resize(max(1, $width), max(1, $height));

        return $this;
    }

    /**
     * @param  $width
     * @param  $height
     * @return Pimcore_Image_Adapter
     */
    public function contain ($width, $height) {

        $x = $this->getWidth() / $width;
        $y = $this->getHeight() / $height;
        if ($x <= 1 && $y <= 1) {
            return $this;
        } elseif ($x > $y) {
            $this->scaleByWidth($width);
        } else {
            $this->scaleByHeight($height);
        }

        return $this;
    }

    /**
     * @param  $width
     * @param  $height
     * @param string $orientation
     * @return Pimcore_Image_Adapter
     */
    public function cover ($width, $height, $orientation = "center") {

        $ratio = $this->getWidth() / $this->getHeight();

        if (($width / $height) > $ratio) {
           $this->scaleByWidth($width);
        } else {
           $this->scaleByHeight($height);
        }

        if($orientation == "center") {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == "topleft") {
            $cropX = 0;
            $cropY = 0;
        } else if ($orientation == "topright") {
            $cropX = $this->getWidth() - $width;
            $cropY = 0;
        } else if ($orientation == "bottomleft") {
            $cropX = 0;
            $cropY = $this->getHeight() - $height;
        } else if ($orientation == "bottomright") {
            $cropX = $this->getWidth() - $width;
            $cropY = $this->getHeight() - $height;
        } else if ($orientation == "centerleft") {
            $cropX = 0;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == "centerright") {
            $cropX = $this->getWidth() - $width;
            $cropY = ($this->getHeight() - $height)/2;
        } else if ($orientation == "topcenter") {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = 0;
        } else if ($orientation == "bottomcenter") {
            $cropX = ($this->getWidth() - $width)/2;
            $cropY = $this->getHeight() - $height;
        }

        $this->crop($cropX, $cropY, $width, $height);

        return $this;
    }

    /**
     * @param  $width
     * @param  $height
     * @param string $color
     * @param string $orientation
     * @return Pimcore_Image_Adapter
     */
    public function frame ($width, $height) {
        
        return $this;
    }

    /**
     * @param  $angle
     * @param bool $autoResize
     * @param string $color
     * @return Pimcore_Image_Adapter
     */
    public function rotate ($angle) {

        return $this;
    }

    /**
     * @param  $x
     * @param  $y
     * @param  $width
     * @param  $height
     * @return Pimcore_Image_Adapter
     */
    public function crop ($x, $y, $width, $height) {

        return $this;
    }


    /**
     * @param  $color
     * @return Pimcore_Image_Adapter
     */
    public function setBackgroundColor ($color) {

        return $this;
    }


    /**
     * @param  $x
     * @param  $y
     * @return Pimcore_Image_Adapter
     */
    public function roundCorners ($x, $y) {

        return $this;
    }


    /**
     * @abstract
     * @param  $imagePath
     * @return Pimcore_Image_Adapter
     */
    public abstract function load ($imagePath);


    /**
     * @abstract
     * @param  $format
     * @return Pimcore_Image_Adapter
     */
    public abstract function setFormat ($format);

    /**
     * @abstract
     * @param  $path
     * @return Pimcore_Image_Adapter
     */
    public abstract function save ($path, $quality = null);
}