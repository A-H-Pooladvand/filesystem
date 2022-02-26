<?php

namespace Tests\Feature;

use Tests\TestCase;

class FileExistsTest extends TestCase
{
    /** @test */
    public function shouldReturnTrueIfAFileExists()
    {
        $this->assertTrue(
            $this->client->fileExists($this->validFile)
        );
    }

    /** @test */
    public function shouldReturnFalseIfAFileDoesNotExists()
    {
        $this->assertFalse(
            $this->client->fileExists($this->invalidFile)
        );
    }
}