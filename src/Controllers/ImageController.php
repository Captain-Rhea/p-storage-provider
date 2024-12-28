<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;
use Exception;
use Illuminate\Support\Carbon;
use App\Models\Image;
use App\Helpers\ImageProcessor;

class ImageController
{
    // GET /v1/image - Retrieve all images
    public function getImageList(Request $request, Response $response): Response
    {
        try {
            $images = Image::orderBy('uploaded_at', 'desc')->get();
            return ResponseHandle::success($response, $images, 'Image list retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // POST /v1/image - Upload an image
    public function uploadImage(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();

            if (empty($uploadedFiles['file'])) {
                throw new Exception('No file uploaded');
            }

            $file = $uploadedFiles['file'];

            if ($file->getError() !== UPLOAD_ERR_OK) {
                throw new Exception('Upload failed');
            }

            $fileType = $file->getClientMediaType();
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                throw new Exception('Uploaded file is not a valid image');
            }

            $year = Carbon::now()->year;
            $month = strtolower(Carbon::now()->format('F'));
            $uploadDir = __DIR__ . "/../../uploads/$year/$month";

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new Exception('Failed to create upload directory');
            }

            $tempPath = $file->getStream()->getMetadata('uri');

            $baseImage = new ImageProcessor();
            $baseImage->loadImage($tempPath);

            $baseImageName = uniqid('base') . '.webp';
            $basePath = "$uploadDir/$baseImageName";
            $baseImage->saveAsWebP($basePath, 90);
            $baseSize = filesize($basePath);

            $lazyImage = new ImageProcessor();
            $lazyImage->loadImage($tempPath);
            $lazyImage->resizeByWidth(300);

            $lazyImageName = uniqid('lazy') . '.webp';
            $lazyPath = "$uploadDir/$lazyImageName";
            $lazyImage->saveAsWebP($lazyPath, 90);
            $lazySize = filesize($lazyPath);

            $baseUrl = $_ENV['FILE_BASE_DOMAIN'] . "/uploads/$year/$month/$baseImageName";
            $lazyUrl = $_ENV['FILE_BASE_DOMAIN'] . "/uploads/$year/$month/$lazyImageName";

            $originalName = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);

            $imageModel = Image::create([
                'name' => $originalName,
                'path' => "$year/$month",
                'base_url' => $baseUrl,
                'lazy_url' => $lazyUrl,
                'base_size' => $baseSize,
                'lazy_size' => $lazySize,
                'uploaded_by' => 1
            ]);

            return ResponseHandle::success($response, $imageModel, 'Image uploaded successfully', 201);
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // GET /v1/image/{id} - Retrieve an image by ID
    public function getImageById(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];
            $image = Image::find($id);

            if (!$image) {
                throw new Exception("Image with ID $id not found");
            }

            return ResponseHandle::success($response, $image, "Image with ID $id retrieved successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 404);
        }
    }

    // DELETE /v1/image/{id} - Delete an image by ID
    public function deleteImage(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];
            $image = Image::find($id);

            if (!$image) {
                throw new Exception("Image with ID $id not found");
            }

            $filePath = __DIR__ . "/../../uploads/" . $image->path . '/' . basename($image->base_url);
            $thumbnailPath = __DIR__ . "/../../uploads/" . $image->path . '/' . basename($image->lazy_url);

            if (file_exists($filePath) && !unlink($filePath)) {
                throw new Exception("Failed to delete base image file");
            }

            if (file_exists($thumbnailPath) && !unlink($thumbnailPath)) {
                throw new Exception("Failed to delete lazy image file");
            }

            $image->delete();

            return ResponseHandle::success($response, null, "Image with ID $id deleted successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }
}
