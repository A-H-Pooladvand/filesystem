<?php

namespace Tests\Feature;

use Tests\TestCase;

class DirectoryExistsTest extends TestCase
{
    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_true_if_a_directory_exists()
    {
        $this->createSampleFile();

        $this->assertTrue(
            $this->client->directoryExists($this->validDirectory)
        );
    }

    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_a_directory_does_not_exists()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->directoryExists($this->invalidDirectory)
        );
    }

    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_it_is_file()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->directoryExists($this->validFile)
        );
    }
}