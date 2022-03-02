<?php

namespace Tests\lib\Traits;

use Throwable;
use Aws\S3\S3Client;
use RuntimeException;
use Aws\Credentials\Credentials;

trait RefreshMinioDatabase
{
    private S3Client $s3Client;

    private string $b;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->s3Client = $this->s3Client();
        $this->b = $_ENV['MINIO_BUCKET'];
    }

    /**
     * Provides AWS PHP SDK Client.
     *
     * @return \Aws\S3\S3Client
     */
    public function s3Client(): S3Client
    {
        return new S3Client([
            'version'                 => $_ENV['MINIO_VERSION'],
            'region'                  => $_ENV['MINIO_REGION'],
            'bucket_endpoint'         => false,
            'use_path_style_endpoint' => true,
            'endpoint'                => $_ENV['MINIO_ENDPOINT'],
            'credentials'             => new Credentials(
                $_ENV['MINIO_KEY'],
                $_ENV['MINIO_SECRET']
            ),
        ]);
    }

    /**
     * Truncates the entire testing bucket.
     *
     * @return void
     */
    public function truncate()
    {
        $env = $_ENV['APP_ENV'];

        if ($env !== 'testing') {
            throw new RuntimeException("You are not in the testing environment you are in $env environment. if you are in testing environment please first check the phpunit environment variables then change APP_ENV to testing.");
        }

        $this->s3Client->deleteMatchingObjects($this->b, '', '/.*/');
    }

    /**
     * This method is called after each test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->truncate();
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @throws Throwable
     */
    public function onNotSuccessfulTest(Throwable $t): void
    {
        $this->truncate();
        parent::onNotSuccessfulTest($t);
    }
}