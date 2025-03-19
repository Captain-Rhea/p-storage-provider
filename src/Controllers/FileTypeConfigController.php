<?php

namespace App\Controllers;

use App\Models\FileTypeConfigModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;

class FileTypeConfigController
{
    public function getAll(Request $request, Response $response, array $args): Response
    {
        try {
            $fileTypes = FileTypeConfigModel::getAll();
            return ResponseHandle::success($response, $fileTypes);
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to fetch file types', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;

        if (!$id) {
            return ResponseHandle::error($response, 'ID is required', 400);
        }

        try {
            $fileType = FileTypeConfigModel::find($id);
            if ($fileType) {
                return ResponseHandle::success($response, $fileType);
            } else {
                return ResponseHandle::error($response, 'File type not found', 404);
            }
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to fetch file type', 500, ['error' => $e->getMessage()]);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['file_type']) || empty($data['mime_type'])) {
            return ResponseHandle::error($response, 'file_type and mime_type are required', 400);
        }

        try {
            $fileType = FileTypeConfigModel::create([
                'file_type' => $data['file_type'],
                'mime_type' => $data['mime_type'],
                'description' => $data['description'] ?? null
            ]);
            return ResponseHandle::success($response, $fileType, 'File type created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to create file type', 500, ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            return ResponseHandle::error($response, 'ID is required', 400);
        }

        $data = $request->getParsedBody();

        try {
            $fileType = FileTypeConfigModel::find($id);
            if (!$fileType) {
                return ResponseHandle::error($response, 'File type not found', 404);
            }

            $fileType->update([
                'file_type' => $data['file_type'] ?? $fileType->file_type,
                'mime_type' => $data['mime_type'] ?? $fileType->mime_type,
                'description' => $data['description'] ?? $fileType->description,
            ]);

            return ResponseHandle::success($response, $fileType, 'File type updated successfully');
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to update file type', 500, ['error' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            return ResponseHandle::error($response, 'ID is required', 400);
        }

        try {
            $fileType = FileTypeConfigModel::find($id);
            if ($fileType) {
                $fileType->delete();
                return ResponseHandle::success($response, [], 'File type deleted successfully');
            } else {
                return ResponseHandle::error($response, 'File type not found', 404);
            }
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to delete file type', 500, ['error' => $e->getMessage()]);
        }
    }
}
