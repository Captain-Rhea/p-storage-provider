<?php

namespace App\Middleware;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        $isGuardEnabled = filter_var($_ENV['API_GUARD'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$isGuardEnabled) {
            return $handler->handle($request);
        }

        if (empty($authHeader) || !$this->isValidToken($authHeader)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request);
    }

    private function isValidToken(string $token): bool
    {
        $token = str_replace('Bearer ', '', $token);
        $url = $_ENV['IDP_API'] . '/v1/auth/verify-token';

        try {
            $client = new Client();
            $response = $client->post($url, [
                'json' => [
                    'token' => $token,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody()->getContents(), true);
                return $body['status'] === 'success' ?? false;
            }
        } catch (\Exception $e) {
            error_log('Token validation error: ' . $e->getMessage());
        }

        return false;
    }
}
