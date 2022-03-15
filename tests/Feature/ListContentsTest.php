<?php

namespace Tests\Feature;

use Tests\TestCase;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\DirectoryListing;
use League\Flysystem\StorageAttributes;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToCheckExistence;

class ListContentsTest extends TestCase
{
    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_is_instance_of_directory_listing()
    {
        $this->createSampleFile();
        $response = $this->client->listContents($this->validDirectory);

        $this->assertInstanceOf(DirectoryListing::class, $response);
    }

    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_throws_exception_when_invalid_directory_given()
    {
        $this->createSampleFile();
        $this->expectException(UnableToCheckExistence::class);
        $this->client->listContents($this->invalidDirectory);
    }

    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function assert_each_item_instance()
    {
        $this->createSampleFile();
        $contents = $this->client->listContents($this->validDirectory);

        foreach ($contents as $content) {
            $this->assertInstanceOf(FileAttributes::class, $content);
            $this->assertInstanceOf(StorageAttributes::class, $content);
        }
    }
}
