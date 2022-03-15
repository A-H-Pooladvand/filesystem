<?php

namespace Tests\Feature;

use Tests\TestCase;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\FilesystemException;

class ReadStreamTest extends TestCase
{
    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_is_instance_of_resource_stream()
    {
        $this->createSampleFile();
        $response = $this->client->readStream($this->validFile);

        $this->assertInstanceOf(Stream::class, $response);
    }

    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_throws_exception_if_the_file_does_not_exists()
    {
        $this->createSampleFile();
        $this->expectException(FilesystemException::class);
        $this->expectException(UnableToReadFile::class);
        $this->client->read($this->invalidFile);
    }
}
