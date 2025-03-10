<?php

namespace App\Routes;

use App\Controllers\FolderController;
use App\Middleware\AuthMiddleware;

class FolderRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1', function ($group) {
            $group->get('/folder', [FolderController::class, 'getFolderList']);
            $group->post('/folder', [FolderController::class, 'createFolder']);
            $group->put('/folder/{id}', [FolderController::class, 'updateFolder']);
            $group->delete('/folder/{id}', [FolderController::class, 'deleteFolder']);
        })->add(new AuthMiddleware());
    }
}
