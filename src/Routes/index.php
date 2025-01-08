<?php

use Slim\App;
use App\Helpers\ResponseHandle;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Routes\ImageRoute;
use App\Routes\ConnectionRoute;

return function (App $app) {
    (new ImageRoute($app))->register();
    $connectionRouteEnabled = filter_var($_ENV['CONNECTION_ROUTE'] ?? false, FILTER_VALIDATE_BOOLEAN);
    if ($connectionRouteEnabled) {
        (new ConnectionRoute($app))->register();
    }

    $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{routes:.+}', function (Request $request, Response $response) {
        return ResponseHandle::error($response, 'Route not found', 404);
    });
};
