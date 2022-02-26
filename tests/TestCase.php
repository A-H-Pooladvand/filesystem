<?php

namespace Tests;

use Aws\S3\S3Client;
use Ahp\Filesystem\Filesystem;
use Aws\Credentials\Credentials;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public Filesystem $client;

    private S3Client $s3Client;

    public string $bucket = 'sample';
    public string $validFile = 'animals/cat.jpg';
    public string $invalidFile = 'animals/dog.jpg';

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->s3Client = $this->client();
        $this->client = $this->filesystem();
    }

    protected function setUp(): void
    {
        if (! $this->s3Client->doesBucketExist($this->bucket)) {
            $this->createSamples();
        }
    }

    public function filesystem(): Filesystem
    {
        return new Filesystem($this->client(), $this->bucket);
    }

    public function client(): S3Client
    {
        return new S3Client([
            'version'                 => $config['version'] ?? 'latest',
            'region'                  => $config['region'] ?? 'me-south-1',
            'bucket_endpoint'         => false,
            'use_path_style_endpoint' => true,
            'endpoint'                => '192.168.1.196:9000',
            'credentials'             => new Credentials(
                'minioadmin',
                'minioadmin'
            ),
        ]);
    }

    private function createSamples()
    {
        $this->s3Client->createBucket(['Bucket' => $this->bucket]);

        $content = file_get_contents(__DIR__ . '/Files/cat.jpg');

        $this->s3Client->upload(
            $this->bucket,
            'animals/cat.jpg',
            $content
        );
    }

    private function truncateSample()
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => 'animals/cat.jpg',
        ]);

        $this->s3Client->deleteBucket([
            'Bucket' => $this->bucket,
        ]);
    }

    protected function tearDown(): void
    {
        $this->truncateSample();
    }
}