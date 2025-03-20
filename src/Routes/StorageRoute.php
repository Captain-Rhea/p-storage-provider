<?php

namespace App\Routes;

use App\Controllers\StorageController;
use App\Middleware\AuthMiddleware;

class StorageRoute extends BaseRoute
{
    public function register(): void
    {
        $this->app->group('/api/v1/storage', function ($group) {
            $group->get('/help-check', [StorageController::class, 'storageHelpCheck']);
        })->add(new AuthMiddleware());
    }
}
