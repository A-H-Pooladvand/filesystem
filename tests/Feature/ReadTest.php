<?php

namespace Tests\Feature;

use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use Tests\TestCase;

class ReadTest extends TestCase
{
    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_has_attributes_if_queried_correctly()
    {
        $this->createSampleFile();
        $response = $this->client->read($this->validFile);

        $this->assertJson($response, 'effectiveUri');
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
