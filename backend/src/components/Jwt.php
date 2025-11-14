<?php
namespace app\components;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;

class Jwt
{
    private static function key(): string
    {
        return getenv('JWT_SECRET') ?: 'secret';
    }

    public static function encode(array $payload): string
    {
        return FirebaseJwt::encode($payload, self::key(), 'HS256');
    }

    public static function decode(string $token): object
    {
        return FirebaseJwt::decode($token, new Key(self::key(), 'HS256'));
    }
}
