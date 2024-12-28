<?php

namespace App\Helpers;

use Exception;

class ImageProcessor
{
    private $image;
    private $imageType;

    /**
     * Load an image from a file.
     *
     * @param string $filename Path to the image file.
     * @throws Exception
     */
    public function loadImage(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new Exception("File not found: $filename");
        }

        $info = getimagesize($filename);
        if ($info === false) {
            throw new Exception("Invalid image file: $filename");
        }

        $this->imageType = $info[2];

        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filename);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filename);
                break;
            default:
                throw new Exception("Unsupported image type: $filename");
        }
    }

    /**
     * Resize the image by specific dimensions.
     *
     * @param int $newWidth New width for the image.
     * @param int $newHeight New height for the image.
     */
    public function resize(int $newWidth, int $newHeight): void
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($this->imageType === IMAGETYPE_PNG || $this->imageType === IMAGETYPE_GIF) {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $resizedImage,
            $this->image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        $this->image = $resizedImage;
    }

    /**
     * Resize the image by scaling width while maintaining aspect ratio.
     *
     * @param int $newWidth New width for the image.
     */
    public function resizeByWidth(int $newWidth): void
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $scale = $newWidth / $width;
        $newHeight = intval($height * $scale);

        $this->resize($newWidth, $newHeight);
    }

    /**
     * Resize the image by scaling height while maintaining aspect ratio.
     *
     * @param int $newHeight New height for the image.
     */
    public function resizeByHeight(int $newHeight): void
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $scale = $newHeight / $height;
        $newWidth = intval($width * $scale);

        $this->resize($newWidth, $newHeight);
    }

    /**
     * Save the image as WebP format.
     *
     * @param string $destination Path to save the WebP image.
     * @param int $quality Quality of the WebP image (0-100).
     * @throws Exception
     */
    public function saveAsWebP(string $destination, int $quality = 80): void
    {
        if (!imagewebp($this->image, $destination, $quality)) {
            throw new Exception("Failed to save image as WebP: $destination");
        }
    }

    /**
     * Destroy the image resource when the object is destroyed.
     */
    public function __destruct()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }
}
