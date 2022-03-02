<?php

namespace Tests\Feature;

use Tests\TestCase;

class HasTest extends TestCase
{
    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_true_if_a_directory_exists()
    {
        $this->createSampleFile();

        $this->assertTrue(
            $this->client->has($this->validDirectory)
        );
    }

    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_a_directory_does_not_exists()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->has($this->invalidDirectory)
        );
    }

    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_true_if_a_file_exists()
    {
        $this->createSampleFile();

        $this->assertTrue(
            $this->client->has($this->validFile)
        );
    }

    /** @test
     * @throws \League\Flysystem\FilesystemException
     */
    public function it_should_return_false_if_a_file_does_not_exists()
    {
        $this->createSampleFile();

        $this->assertFalse(
            $this->client->has($this->invalidFile)
        );
    }
}