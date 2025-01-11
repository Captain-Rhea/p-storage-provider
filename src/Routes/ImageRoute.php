<?php

namespace App\Routes;

use App\Controllers\ImageController;
use App\Middleware\AuthMiddleware;

class ImageRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/v1', function ($group) {
            $group->get('/image', [ImageController::class, 'getImageList']);
            $group->post('/image', [ImageController::class, 'uploadImage']);
            $group->put('/image/{id}', [ImageController::class, 'updateImageName']);
            $group->delete('/image/{id}', [ImageController::class, 'deleteImage']);
            $group->get('/storage', [ImageController::class, 'getStorageUsed']);
        })->add(new AuthMiddleware());
    }
}
