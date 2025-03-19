<?php

namespace App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;
use App\Models\FolderModel;

class FolderController
{
    // GET /api/v1/folder
    public function getAll(Request $request, Response $response): Response
    {
        try {
            $folders = FolderModel::with('children')->where('parent_id', 0)->get();
            return ResponseHandle::success($response, $folders, 'Data list retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, 'Failed to retrieve folders: ' . $e->getMessage());
        }
    }

    // GET /api/v1/folder/{id}
    public function getOne(Request $request, Response $response, array $args): Response
    {
        try {
            $folder = FolderModel::with(['files', 'children'])->findOrFail($args['id']);
            return ResponseHandle::success($response, $folder, 'Data retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHandle::error($response, 'Folder not found', 404);
        } catch (Exception $e) {
            return ResponseHandle::error($response, 'Failed to retrieve folder: ' . $e->getMessage());
        }
    }

    // POST /api/v1/folder
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('service_detail')['sub'] ?? 'system';

            // Manual validation
            if (empty($data['name']) || !is_string($data['name'])) {
                return ResponseHandle::error($response, 'Name is required and must be a string', 400);
            }
            if (isset($data['parent_id']) && (!is_int($data['parent_id']) || $data['parent_id'] < 0)) {
                return ResponseHandle::error($response, 'Parent ID must be a non-negative integer', 400);
            }
            if (isset($data['path']) && !is_string($data['path'])) {
                return ResponseHandle::error($response, 'Path must be a string', 400);
            }

            $folder = FolderModel::create([
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? 0,
                'path' => $data['path'] ?? null,
                'created_by' => $user,
                'updated_by' => $user,
            ]);

            return ResponseHandle::success($response, $folder, 'Folder created successfully', 201);
        } catch (Exception $e) {
            return ResponseHandle::error($response, 'Failed to create folder: ' . $e->getMessage());
        }
    }

    // PUT /api/v1/folder/{id}
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('service_detail')['sub'] ?? 'system';

            // Manual validation (optional fields)
            if (isset($data['name']) && (empty($data['name']) || !is_string($data['name']))) {
                return ResponseHandle::error($response, 'Name must be a non-empty string', 400);
            }
            if (isset($data['parent_id']) && (!is_int($data['parent_id']) || $data['parent_id'] < 0)) {
                return ResponseHandle::error($response, 'Parent ID must be a non-negative integer', 400);
            }
            if (isset($data['path']) && !is_string($data['path'])) {
                return ResponseHandle::error($response, 'Path must be a string', 400);
            }

            $folder = FolderModel::findOrFail($args['id']);
            $folder->update([
                'name' => $data['name'] ?? $folder->name,
                'parent_id' => $data['parent_id'] ?? $folder->parent_id,
                'path' => $data['path'] ?? $folder->path,
                'updated_by' => $user,
            ]);

            return ResponseHandle::success($response, $folder, 'Folder updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHandle::error($response, 'Folder not found', 404);
        } catch (Exception $e) {
            return ResponseHandle::error($response, 'Failed to update folder: ' . $e->getMessage());
        }
    }

    // DELETE /api/v1/folder/{id}
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $folder = FolderModel::findOrFail($args['id']);
            $folder->delete();
            return ResponseHandle::success($response, [], 'Folder deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHandle::error($response, 'Folder not found', 404);
        } catch (Exception $e) {
            return ResponseHandle::error($response, 'Failed to delete folder: ' . $e->getMessage());
        }
    }
}
