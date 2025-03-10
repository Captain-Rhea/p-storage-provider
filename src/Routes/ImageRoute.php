<?php

namespace App\Routes;

use App\Controllers\ImageController;
use App\Middleware\AuthMiddleware;

class ImageRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1', function ($group) {
            $group->get('/image', [ImageController::class, 'getImageList']);
            $group->post('/image', [ImageController::class, 'uploadImage']);
            $group->put('/image/{id}', [ImageController::class, 'updateImage']);
            $group->delete('/image', [ImageController::class, 'deleteImage']);
        })->add(new AuthMiddleware());
    }
}
