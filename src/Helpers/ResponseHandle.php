<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;
use JsonException;

class ResponseHandle
{
    /**
     * ส่ง Response แบบสำเร็จ
     */
    public static function success(Response $response, $data = [], string $message = 'Success', int $code = 200): Response
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        return self::jsonResponse($response, $payload, $code);
    }

    /**
     * ส่ง Response แบบข้อผิดพลาด
     */
    public static function error(Response $response, string $message = 'An error occurred', int $code = 500, array $errors = []): Response
    {
        $payload = [
            'status' => 'error',
            'message' => $message
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return self::jsonResponse($response, $payload, $code);
    }

    /**
     * แปลงข้อมูลเป็น JSON และส่ง Response
     */
    private static function jsonResponse(Response $response, array $payload, int $code): Response
    {
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $response->getBody()->rewind();
            $response->getBody()->write($json);
        } catch (JsonException $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'JSON encoding failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
    }
}
