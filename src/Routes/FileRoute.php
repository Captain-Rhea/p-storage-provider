<?php

namespace App\Routes;

use App\Controllers\FileController;
use App\Middleware\AuthMiddleware;

class FileRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1', function ($group) {
            $group->get('/files', [FileController::class, 'getAll']);
            $group->get('/files/{id}', [FileController::class, 'getOne']);
            $group->post('/files', [FileController::class, 'upload']);
            $group->patch('/files/{id}', [FileController::class, 'update']);
            $group->delete('/files/{id}', [FileController::class, 'delete']);
        })->add(new AuthMiddleware());
    }
}
