<?php

use Slim\App;
use App\Helpers\ResponseHandle;

use App\Routes\ImageRoute;

return function (App $app) {
    $app->get('/', function ($request, $response) {
        return ResponseHandle::success($response, [], 'Welcome to the API!');
    });

    (new ImageRoute($app))->register();

    $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{routes:.+}', function ($request, $response) {
        return ResponseHandle::error($response, 'Route not found', 404);
    });
};
