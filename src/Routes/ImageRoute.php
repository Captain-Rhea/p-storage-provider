<?php

namespace App\Routes;

use App\Controllers\ImageController;

class ImageRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/v1', function ($group) {
            $group->get('/image', [ImageController::class, 'getImageList']);
            $group->post('/image', [ImageController::class, 'uploadImage']);
            $group->put('/image/{id}', [ImageController::class, 'updateImageName']);
            $group->get('/image/{id}', [ImageController::class, 'getImageById']);
            $group->delete('/image/{id}', [ImageController::class, 'deleteImage']);
        });
    }
}
