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
    public function getFolderList(Request $request, Response $response): Response
    {
        try {
            $folders = FolderModel::all();
            return ResponseHandle::success($response, $folders, 'Data list retrieved successfully');
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // POST /api/v1/folder
    public function createFolder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            $folder = new FolderModel();
            $folder->name = $data['name'] ?? null;
            $folder->parent_id = $data['parent_id'] ?? 0;
            $folder->path = $data['path'] ?? null;
            $folder->created_by = $data['created_by'] ?? 'system';
            $folder->updated_by = $data['created_by'] ?? 'system';
            $folder->save();

            return ResponseHandle::success($response, $folder, 'Folder created successfully', 201);
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // PUT /api/v1/folder/{id}
    public function updateFolder(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();

            $folder = FolderModel::find($id);
            if (!$folder) {
                return ResponseHandle::error($response, 'Folder not found', 404);
            }

            $folder->name = $data['name'] ?? $folder->name;
            $folder->parent_id = $data['parent_id'] ?? $folder->parent_id;
            $folder->path = $data['path'] ?? $folder->path;
            $folder->updated_by = $data['updated_by'] ?? 'system';
            $folder->save();

            return ResponseHandle::success($response, $folder, "Folder updated successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }

    // DELETE /api/v1/folder/{id}
    public function deleteFolder(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];

            $folder = FolderModel::find($id);
            if (!$folder) {
                return ResponseHandle::error($response, 'Folder not found', 404);
            }

            $folder->delete();
            return ResponseHandle::success($response, null, "Folder deleted successfully");
        } catch (Exception $e) {
            return ResponseHandle::error($response, $e->getMessage(), 500);
        }
    }
}
