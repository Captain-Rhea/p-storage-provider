<?php

namespace App\Routes;

use App\Controllers\FileTypeConfigController;
use App\Middleware\AuthMiddleware;

class FileTypeConfigRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1/file/type', function ($group) {
            $group->get('/config', [FileTypeConfigController::class, 'getAll']);
            $group->get('/config/{id}', [FileTypeConfigController::class, 'getOne']);
            $group->post('/config', [FileTypeConfigController::class, 'create']);
            $group->patch('/config/{id}', [FileTypeConfigController::class, 'update']);
            $group->delete('/config/{id}', [FileTypeConfigController::class, 'delete']);
        })->add(new AuthMiddleware());
    }
}
