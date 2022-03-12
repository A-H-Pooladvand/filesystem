<?php

namespace Tests\Feature;

use Tests\TestCase;

class FileExistsTest extends TestCase
{
    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_true_if_a_file_exists()
    {
        $this->createSampleFile();

        $this->assertTrue(
            $this->client->fileExists($this->validFile)
        );
    }

    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_a_file_does_not_exists()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->fileExists($this->invalidFile)
        );
    }

    /**
     * @test
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_it_is_a_directory()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->fileExists($this->validDirectory)
        );
    }
}
