<?php

namespace Galaxy\FrontendBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class StorageService
{

    private $folder;
    private $relUrl;
    private $minWidth;
    private $minHeight;
    private $width;
    private $height;

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function setMinWidth($minWidth)
    {
        $this->minWidth = $minWidth;
    }

    public function setMinHeight($minHeight)
    {
        $this->minHeight = $minHeight;
    }

    public function save(UploadedFile $file)
    {
        $fileName = $file->getClientOriginalName();
        $file->move($this->folder, $fileName);
        $path = $this->relUrl . $fileName;
        return $path;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    public function setRelUrl($relUrl)
    {
        $this->relUrl = $relUrl;
    }

    public function saveImage(UploadedFile $file)
    {
        $imagine = new Imagine();
        $fileName = $file->getClientOriginalName();
        $file->move($this->folder, $fileName);
        $path = $this->folder . $fileName;
        $pathResize = $this->folder . 'resize_' . $fileName;
        $originalImgSize = $imagine->open($path)
                ->getSize();
        if ($originalImgSize->getHeight() < $this->minHeight && $originalImgSize->getWidth() < $this->minWidth) {
            unlink($path);
            return Null;
        }
        $size = new Box($this->width, $this->height);
        $imagine->open($path)
                ->thumbnail($size, \Imagine\Imagick\Image::THUMBNAIL_INSET)
                ->save($pathResize);
        return $this->relUrl . 'resize_' . $fileName;
    }

    public function deleteImage($imagePath)
    {
        $path = str_replace($this->relUrl, $this->folder, $imagePath);
        $pathOrigin = str_replace("resize_", "", $path);
        unlink($path);
        unlink($pathOrigin);
    }

}