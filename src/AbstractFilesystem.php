<?php

namespace Ahp\Filesystem;

use Aws\Result;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToReadFile;
use Ahp\Filesystem\Traits\ParameterTrait;

abstract class AbstractFilesystem
{
    use ParameterTrait;

    /**
     * Reads the object.
     *
     * @param  string  $location
     *
     * @return \Aws\Result
     * @throws \League\Flysystem\FilesystemException
     */
    protected function readObject(string $location): Result
    {
        if (! $this->fileExists($location)) {
            throw new UnableToReadFile("{$this->bucket}/$location does not exists.");
        }

        $this->setKey($location);

        return $this->client->getObject($this->getParams());
    }

    /**
     * Creates Attribute class file from array.
     *
     * @param  array  $item
     *
     * @return \League\Flysystem\FileAttributes
     */
    protected function toFileAttribute(array $item): FileAttributes
    {
        return new FileAttributes(
            $item['Key'],
            $item['Size'],
            null,
            $item['LastModified']->getTimestamp(),
            null
        );
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  mixed  $data
     *
     * @return string
     */
    protected function toJson(mixed $data): string
    {
        return json_encode($data);
    }
}
