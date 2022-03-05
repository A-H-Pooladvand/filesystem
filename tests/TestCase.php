<?php

namespace Tests;

use Ahp\Filesystem\Filesystem;
use Tests\lib\Traits\RefreshMinioDatabase;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use RefreshMinioDatabase;

    public Filesystem $client;

    public string $bucket;

    public string $validFile = 'animals/cat.jpg';

    public string $invalidFile = 'animals/dog.jpg';

    public string $validDirectory = 'animals';

    public string $invalidDirectory = 'shops';

    /**
     * This method is called before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->bucket = $_ENV['MINIO_BUCKET'];
        $this->client = $this->filesystem();
    }

    /**
     * New Filesystem client.
     *
     * @return \Ahp\Filesystem\Filesystem
     */
    public function filesystem(): Filesystem
    {
        return new Filesystem(
            $this->s3Client(),
            $this->bucket
        );
    }

    /**
     * Creates a sample image file.
     *
     * @return void
     */
    public function createSampleFile()
    {
        $content = file_get_contents(__DIR__.'/lib/Files/cat.jpg');

        $this->s3Client->upload(
            $this->bucket,
            'animals/cat.jpg',
            $content
        );
    }
}
