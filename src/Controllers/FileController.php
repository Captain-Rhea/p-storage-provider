<?php

namespace App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;
use App\Models\FileModel;
use App\Models\FileTypeConfigModel;
use Illuminate\Database\Capsule\Manager as DB;

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
                $query->where(function ($q) use ($search) {
                    $q->where('file_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('file_description', 'LIKE', '%' . $search . '%');
                });
            }

            $files = $query->orderBy('updated_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            $transformedData = $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'group' => $file->group,
                    'file_name' => $file->file_name,
                    'file_description' => $file->file_description,
                    'file_url' => $_ENV['FILE_BASE_DOMAIN'] . $file->file_url,
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
                'file_url' => $_ENV['FILE_BASE_DOMAIN'] . $file->file_url,
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

            $fileSize = $file->getSize();

            $storageLimitGB = (float) ($_ENV['STORAGE_LIMIT_GB'] ?? 10);
            $storageLimitBytes = $storageLimitGB * 1024 * 1024 * 1024;
            $usedBytes = $this->getFolderSize(__DIR__ . "/../../public/storage");
            $remainingBytes = $storageLimitBytes - $usedBytes;

            if ($fileSize > $remainingBytes) {
                return ResponseHandle::error($response, 'Not enough storage space', 400, [
                    'file_size' => $this->formatBytes($fileSize),
                    'remaining_space' => $this->formatBytes($remainingBytes)
                ]);
            }

            $fileType = $file->getClientMediaType();

            $safeFileTypes = array_column(FileTypeConfigModel::getAll(), 'mime_type');

            if (!in_array($fileType, $safeFileTypes)) {
                return ResponseHandle::error($response, 'Uploaded file is not a safe file type', 400);
            }

            $uploadBaseDir = __DIR__ . "/../../public/storage";
            $year = date('Y');
            $month = date('n');
            $uploadDir = "$uploadBaseDir/$year/$month";

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new Exception('Failed to create upload directory');
            }

            $fileExtension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $uniqueFileName = $this->generateUniqueFileName($fileExtension);
            $relativeFilePath = "/$year/$month/$uniqueFileName";
            $absoluteFilePath = "$uploadBaseDir$relativeFilePath";

            DB::beginTransaction();

            $file->moveTo($absoluteFilePath);

            $fileUrl = "/storage" . $relativeFilePath;

            $fileModel = FileModel::create([
                'group' => $parsedBody['group'] ?? 'default',
                'file_name' => pathinfo($file->getClientFilename(), PATHINFO_FILENAME),
                'file_description' => $parsedBody['file_description'] ?? null,
                'file_path' => $relativeFilePath,
                'file_url' => $fileUrl,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'created_by' => $parsedBody['created_by'] ?? 'system'
            ]);

            DB::commit();

            return ResponseHandle::success($response, [
                'id' => $fileModel->id,
                'file_name' => $fileModel->file_name,
                'file_url' => $_ENV['FILE_BASE_DOMAIN'] . $fileModel->file_url,
                'file_size' => $this->formatBytes($fileModel->file_size),
                'file_type' => $fileModel->file_type,
                'remaining_space' => $this->formatBytes($remainingBytes - $fileSize),
            ], 'File uploaded successfully', 201);
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($absoluteFilePath) && file_exists($absoluteFilePath)) {
                unlink($absoluteFilePath);
            }
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
            DB::beginTransaction();

            $file = FileModel::find($args['id']);

            if (!$file) {
                return ResponseHandle::error($response, "File with ID {$args['id']} not found", 404);
            }

            $uploadBaseDir = __DIR__ . "/../../public/storage";
            $filePath = $uploadBaseDir . $file->file_path;

            if (is_file($filePath) && file_exists($filePath)) {
                if (!unlink($filePath)) {
                    DB::rollBack();
                    return ResponseHandle::error($response, "Failed to delete file", 500);
                }
            }

            $file->delete();

            $this->cleanupEmptyDirs(dirname($filePath));

            DB::commit();

            return ResponseHandle::success($response, null, 'File deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    private function getFolderSize(string $folder): int
    {
        $size = 0;
        if (!is_dir($folder)) {
            return $size;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }

    private function generateUniqueFileName(string $extension): string
    {
        return uniqid(bin2hex(random_bytes(4)), true) . '.' . $extension;
    }

    private function cleanupEmptyDirs(string $dir): void
    {
        while (is_dir($dir) && count(scandir($dir)) === 2) {
            rmdir($dir);
            $dir = dirname($dir);
        }
    }
}
