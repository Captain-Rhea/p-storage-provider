<?php

namespace App\Routes;

use App\Controllers\FileController;
use App\Middleware\AuthMiddleware;

class FileRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1', function ($group) {
            $group->get('/file', [FileController::class, 'getFileList']);
            $group->post('/file', [FileController::class, 'uploadFile']);
            $group->put('/file/{id}', [FileController::class, 'updateFile']);
            $group->delete('/file', [FileController::class, 'deleteFile']);
        })->add(new AuthMiddleware());
    }
}
