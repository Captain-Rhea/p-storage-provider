<?php

namespace App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;
use App\Models\FileModel;
use App\Models\FileTypeConfigModel;

class FileController
{
    // GET /api/v1/files
    public function getAll(Request $request, Response $response): Response
    {
        try {
            $page = (int)($request->getQueryParams()['page'] ?? 1);
            $limit = (int)($request->getQueryParams()['per_page'] ?? 10);
            $group = $request->getQueryParams()['group'] ?? null;
            $fileType = $request->getQueryParams()['file_type'] ?? null;
            $createdBy = $request->getQueryParams()['created_by'] ?? null;
            $startDate = $request->getQueryParams()['start_date'] ?? null;
            $endDate = $request->getQueryParams()['end_date'] ?? null;

            $query = FileModel::query();

            if ($group) {
                $query->where('group', $group);
            }

            if ($fileType) {
                $query->where('file_type', $fileType);
            }

            if ($createdBy) {
                $query->where('created_by', $createdBy);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            if ($request->getQueryParams()['search'] ?? null) {
                $search = $request->getQueryParams()['search'];
                $query->where('file_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('file_description', 'LIKE', '%' . $search . '%');
            }

            $files = $query->orderBy('updated_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $transformedData = $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'group' => $file->group,
                    'file_name' => $file->file_name,
                    'file_description' => $file->file_description,
                    'file_url' => $file->file_url,
                    'file_size' => $file->file_size,
                    'file_type' => $file->file_type,
                    'created_by' => $file->created_by,
                    'created_at' => $file->created_at->toDateTimeString(),
                    'updated_by' => $file->updated_by,
                    'updated_at' => $file->updated_at->toDateTimeString(),
                ];
            });

            $data = [
                'pagination' => [
                    'current_page' => $files->currentPage(),
                    'per_page' => $files->perPage(),
                    'total' => $files->total(),
                    'last_page' => $files->lastPage(),
                ],
                'data' => $transformedData
            ];

            return ResponseHandle::success($response, $data, 'File list retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // GET /api/v1/files/{id}
    public function getOne(Request $request, Response $response, array $args): Response
    {
        try {
            $file = FileModel::find($args['id']);

            if (!$file) {
                return ResponseHandle::error($response, "File with ID {$args['id']} not found", 404);
            }

            $data = [
                'id' => $file->id,
                'group' => $file->group,
                'file_name' => $file->file_name,
                'file_description' => $file->file_description,
                'file_url' => $file->file_url,
                'file_size' => $file->file_size,
                'file_type' => $file->file_type,
                'created_at' => $file->created_at->toDateTimeString(),
                'updated_at' => $file->updated_at->toDateTimeString(),
            ];

            return ResponseHandle::success($response, $data, 'File retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // POST /api/v1/files
    public function upload(Request $request, Response $response): Response
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

            $fileNameWithExt = $file->getClientFilename();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $fileExtension = pathinfo($fileNameWithExt, PATHINFO_EXTENSION);

            $fileType = $file->getClientMediaType();

            $safeFileTypes = FileTypeConfigModel::getAll();

            $safeFileTypes = array_column($safeFileTypes, 'mime_type');

            if (!in_array($fileType, $safeFileTypes)) {
                return ResponseHandle::error($response, 'Uploaded file is not a safe file type', 400);
            }

            $uploadDir = __DIR__ . "/../../public/storage";

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new Exception('Failed to create upload directory');
            }

            $uniqueFileName = uniqid($fileName . '_') . '.' . $fileExtension;
            $filePath = "$uploadDir/$uniqueFileName";

            $file->moveTo($filePath);

            $fileUrl = $_ENV['FILE_BASE_DOMAIN'] . "/storage/$uniqueFileName";

            $group = $parsedBody['group'] ?? 'default';
            $createdBy = isset($parsedBody['created_by']) ? $parsedBody['created_by'] : 'system';

            $fileModel = FileModel::create([
                'group' => $group,
                'file_name' => $fileName,
                'file_description' => $parsedBody['file_description'] ?? null,
                'file_path' => $uniqueFileName,
                'file_url' => $fileUrl,
                'file_size' => filesize($filePath),
                'file_type' => $fileType,
                'created_by' => $createdBy
            ]);

            $transformedFileModel = [
                'id' => $fileModel->id,
                'group' => $fileModel->group,
                'file_name' => $fileModel->file_name,
                'file_description' => $fileModel->file_description,
                'file_url' => $fileModel->file_url,
                'file_size' => $fileModel->file_size,
                'file_type' => $fileModel->file_type,
                'created_by' => $fileModel->created_by,
                'created_at' => $fileModel->created_at->toDateTimeString(),
            ];

            return ResponseHandle::success($response, $transformedFileModel, 'File uploaded successfully', 201);
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // PATCH /api/v1/files/{id}
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $file = FileModel::find($args['id']);

            if (!$file) {
                return ResponseHandle::error($response, "File with ID {$args['id']} not found", 404);
            }

            $jsonBody = json_decode((string)$request->getBody(), true);
            $file->file_name = $jsonBody['file_name'] ?? $file->file_name;
            $file->file_description = $jsonBody['file_description'] ?? $file->file_description;
            $file->updated_by = $jsonBody['updated_by'] ?? $file->updated_by;
            $file->save();

            return ResponseHandle::success($response, $file, 'File updated successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // DELETE /api/v1/files/{id}
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $file = FileModel::find($args['id']);

            if (!$file) {
                return ResponseHandle::error($response, "File with ID {$args['id']} not found", 404);
            }

            $filePath = __DIR__ . "/../../public/storage/" . $file->file_path;

            if (file_exists($filePath) && !unlink($filePath)) {
                throw new Exception("Failed to delete file");
            }

            $file->delete();

            return ResponseHandle::success($response, null, 'File deleted successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }
}
