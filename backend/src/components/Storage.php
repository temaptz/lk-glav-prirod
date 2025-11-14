<?php
namespace app\components;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Aws\S3\S3Client;

class Storage
{
    private static ?Filesystem $fs = null;

    public static function fs(): Filesystem
    {
        if (self::$fs === null) {
            $client = new S3Client([
                'region' => 'us-east-1',
                'version' => 'latest',
                'endpoint' => getenv('MINIO_ENDPOINT') ?: 'http://minio:9000',
                'credentials' => [
                    'key' => getenv('MINIO_ROOT_USER') ?: 'minioadmin',
                    'secret' => getenv('MINIO_ROOT_PASSWORD') ?: 'minioadmin',
                ],
                'use_path_style_endpoint' => true,
            ]);
            $bucket = getenv('MINIO_BUCKET') ?: 'artifacts';
            $adapter = new AwsS3V3Adapter($client, $bucket);
            self::$fs = new Filesystem($adapter);
        }
        return self::$fs;
    }

    /**
     * @throws FilesystemException
     */
    public static function put(string $path, string $content): void
    {
        self::fs()->write($path, $content);
    }

    /**
     * @throws FilesystemException
     */
    public static function url(string $path): string
    {
        $bucket = getenv('MINIO_BUCKET') ?: 'artifacts';
        $endpoint = getenv('MINIO_ENDPOINT_PUBLIC') ?: 'http://localhost:9000';
        return $endpoint . '/' . $bucket . '/' . $path;
    }
}
