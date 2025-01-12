<?php

namespace App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;
use Illuminate\Support\Carbon;
use App\Helpers\ImageProcessor;
use App\Models\Image;

class ImageController
{
    // GET /v1/storage
    public function getStorageUsed(Request $request, Response $response): Response
    {
        try {
            $images = Image::all();

            $baseSizeTotal = $images->sum('base_size');
            $lazySizeTotal = $images->sum('lazy_size');
            $totalSize = $baseSizeTotal + $lazySizeTotal;

            return ResponseHandle::success($response, [
                'base_size_total' => $baseSizeTotal,
                'lazy_size_total' => $lazySizeTotal,
                'total_size' => $totalSize
            ], 'Storage size retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // GET /v1/image
    public function getImageList(Request $request, Response $response): Response
    {
        try {
            $page = (int)($request->getQueryParams()['page'] ?? 1);
            $limit = (int)($request->getQueryParams()['per_page'] ?? 10);
            $imageId = $request->getQueryParams()['image_id'] ?? null;
            $group = $request->getQueryParams()['group'] ?? null;
            $name = $request->getQueryParams()['name'] ?? null;
            $startDate = $request->getQueryParams()['start_date'] ?? null;
            $endDate = $request->getQueryParams()['end_date'] ?? null;

            $query = Image::orderBy('image_id', 'desc');

            if ($imageId) {
                $query->where('image_id', $imageId);
            }

            if ($group) {
                $query->where('group', $group);
            }

            if ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            }

            if ($startDate) {
                $query->whereDate('uploaded_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('uploaded_at', '<=', $endDate);
            }

            $images = $query->paginate($limit, ['*'], 'page', $page);

            $transformedData = $images->map(function ($image) {
                return [
                    'image_id' => $image->image_id,
                    'group' => $image->group,
                    'name' => $image->name,
                    'base_url' => $image->base_url,
                    'lazy_url' => $image->lazy_url,
                    'base_size' => $image->base_size,
                    'lazy_size' => $image->lazy_size,
                    'uploaded_by' => $image->uploaded_by,
                    'uploaded_at' => $image->uploaded_at->toDateTimeString(),
                    'updated_at' => $image->updated_at->toDateTimeString()
                ];
            });

            $data = [
                'pagination' => [
                    'current_page' => $images->currentPage(),
                    'per_page' => $images->perPage(),
                    'total' => $images->total(),
                    'last_page' => $images->lastPage(),
                ],
                'data' => $transformedData
            ];

            return ResponseHandle::success($response, $data, 'Image list retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // POST /v1/image
    public function uploadImage(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $parsedBody = $request->getParsedBody();

            if (empty($uploadedFiles['file'])) {
                return ResponseHandle::error($response, 'No file uploaded', 400);
            }

            $file = $uploadedFiles['file'];

            if ($file->getError() !== UPLOAD_ERR_OK) {
                return ResponseHandle::error($response, 'Upload failed', 400);
            }

            $fileType = $file->getClientMediaType();
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                return ResponseHandle::error($response, 'Uploaded file is not a valid image', 400);
            }

            $tempPath = $file->getStream()->getMetadata('uri');
            $imageSize = getimagesize($tempPath);
            if ($imageSize === false) {
                return ResponseHandle::error($response, 'Uploaded file is not a valid image', 400);
            }

            [$width, $height] = $imageSize;
            if ($width > 1920 || $height > 1920) {
                return ResponseHandle::error($response, 'Image dimensions exceed the maximum allowed size of 1920x1920 pixels', 400);
            }

            $year = Carbon::now('Asia/Bangkok')->year;
            $month = Carbon::now('Asia/Bangkok')->format('m');
            $uploadDir = __DIR__ . "/../../public/uploads/$year/$month";

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
            $group = $parsedBody['group'] ?? 'default';
            $uploadedBy = isset($parsedBody['uploaded_by']) ? (int)$parsedBody['uploaded_by'] : 1;

            $imageModel = Image::create([
                'group' => $group,
                'name' => $originalName,
                'path' => "$year/$month",
                'base_url' => $baseUrl,
                'lazy_url' => $lazyUrl,
                'base_size' => $baseSize,
                'lazy_size' => $lazySize,
                'uploaded_by' => $uploadedBy
            ]);

            $transformedImageModel = [
                'image_id' => $imageModel->image_id,
                'group' => $imageModel->group,
                'name' => $imageModel->name,
                'base_url' => $imageModel->base_url,
                'lazy_url' => $imageModel->lazy_url,
                'base_size' => $imageModel->base_size,
                'lazy_size' => $imageModel->lazy_size,
                'uploaded_at' => $imageModel->uploaded_at->toDateTimeString(),
                'uploaded_by' => $imageModel->uploaded_by
            ];

            return ResponseHandle::success($response, $transformedImageModel, 'Image uploaded successfully', 201);
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // PUT /v1/image
    public function updateImageName(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $image = Image::find($id);

            if (!$image) {
                return ResponseHandle::error($response, "Image with ID $id not found", 404);
            }

            $body = json_decode((string)$request->getBody());
            $newName = $body->new_name ?? null;

            if (empty($newName)) {
                return ResponseHandle::error($response, "New name is required", 400);
            }

            $image->name = $newName;
            $image->save();

            $updatedImage = [
                'image_id' => $image->image_id,
                'name' => $image->name,
                'updated_at' => $image->updated_at->toDateTimeString()
            ];

            return ResponseHandle::success($response, $updatedImage, "Image name updated successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // DELETE /v1/image
    public function deleteImages(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $ids = $queryParams['ids'] ?? null;

            if (empty($ids)) {
                return ResponseHandle::error($response, "Image IDs are required", 400);
            }

            $imageIds = explode(',', $ids);

            $images = Image::whereIn('image_id', $imageIds)->get();

            if ($images->isEmpty()) {
                return ResponseHandle::error($response, "No images found for the provided IDs", 404);
            }

            $errors = [];
            foreach ($images as $image) {
                try {
                    $filePath = __DIR__ . "/../../public/uploads/" . $image->path . '/' . basename($image->base_url);
                    $thumbnailPath = __DIR__ . "/../../public/uploads/" . $image->path . '/' . basename($image->lazy_url);

                    if (file_exists($filePath) && !unlink($filePath)) {
                        throw new Exception("Failed to delete base image file for ID {$image->id}");
                    }

                    if (file_exists($thumbnailPath) && !unlink($thumbnailPath)) {
                        throw new Exception("Failed to delete lazy image file for ID {$image->id}");
                    }

                    $image->delete();
                } catch (Exception $e) {
                    $errors[] = [
                        'image_id' => $image->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            if (!empty($errors)) {
                return ResponseHandle::success($response, [
                    'deleted' => $images->count() - count($errors),
                    'errors' => $errors,
                ], "Some images could not be deleted");
            }

            return ResponseHandle::success($response, [
                'deleted' => $images->count(),
            ], "All images deleted successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }
}
