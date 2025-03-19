<?php

namespace App\Routes;

use App\Controllers\FolderController;
use App\Middleware\AuthMiddleware;

class FolderRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1', function ($group) {
            $group->get('/folder', [FolderController::class, 'getAll']);
            $group->get('/folder/{id}', [FolderController::class, 'getOne']);
            $group->post('/folder', [FolderController::class, 'create']);
            $group->put('/folder/{id}', [FolderController::class, 'update']);
            $group->delete('/folder/{id}', [FolderController::class, 'delete']);
        })->add(new AuthMiddleware());
    }
}
