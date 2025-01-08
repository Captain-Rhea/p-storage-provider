<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenUtils
{
    /**
     * สร้าง JWT Token
     *
     * @param array $payload ข้อมูลที่ต้องการใส่ใน Token
     * @param int $expiresIn ระยะเวลาหมดอายุ (วินาที)
     * @return string Token ที่ถูกเข้ารหัส
     */
    public static function generateToken(array $payload, ?int $expiresIn = null): string
    {
        $key = $_ENV['CONNECTION_KEY'];
        $issuedAt = time();
        $payload['iat'] = $issuedAt;

        if ($expiresIn !== null) {
            $payload['exp'] = $issuedAt + $expiresIn;
        }

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * ตรวจสอบและถอดรหัส JWT Token
     *
     * @param string $token JWT Token
     * @return array ข้อมูลที่ถูกถอดรหัสจาก Token
     * @throws \Exception ถ้า Token ไม่ถูกต้องหรือหมดอายุ
     */
    public static function decodeToken(string $token): array
    {
        $key = $_ENV['CONNECTION_KEY'];
        return (array) JWT::decode($token, new Key($key, 'HS256'));
    }

    /**
     * ตรวจสอบความถูกต้องของ Token
     *
     * @param string $token JWT Token
     * @return bool true ถ้า Token ถูกต้อง, false ถ้าไม่ถูกต้อง
     */
    public static function isValidToken(string $token): bool
    {
        try {
            self::decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
