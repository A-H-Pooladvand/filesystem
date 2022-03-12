<?php

namespace Ahp\Filesystem;

use Aws\Api\DateTimeResult;
use Aws\S3\S3Client;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use Throwable;

/**
 * This is MinIO implementation of \League\Flysystem\FilesystemOperator.
 *
 * @author https://github.com/A-H-Pooladvand
 * @email a.h.pooladvand@gmail.com
 */
class Filesystem extends AbstractFilesystem implements FilesystemOperator
{
    public function __construct(
        protected S3Client $client,
        protected string $bucket
    ) {
    }

    /**
     * Determines if a file exists.
     *
     * @param  string  $location
     * @return bool
     *
     * @throws UnableToCheckExistence
     * @throws FilesystemException
     */
    public function fileExists(string $location): bool
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $location);
        } catch (Throwable $exception) {
            throw UnableToCheckExistence::forLocation($location, $exception);
        }
    }

    /**
     * Determines if a directory exists.
     *
     * @param  string  $location
     * @return bool
     *
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function directoryExists(string $location): bool
    {
        try {
            $location = rtrim($location, '/') . '/';

            $this->setMaxKeys(1);
            $this->setPrefix($location);

            $result = $this->client->listObjectsV2($this->getParams());

            return $result->hasKey('Contents');
        } catch (Throwable $exception) {
            throw UnableToCheckExistence::forLocation($location, $exception);
        }
    }

    /**
     * Determines if a file or directory exists.
     *
     * @param  string  $location
     * @return bool
     *
     * @throws FilesystemException
     * @throws UnableToCheckExistence
     */
    public function has(string $location): bool
    {
        if ($this->fileExists($location)) {
            return true;
        }

        return $this->directoryExists($location);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $location
     * @return string
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function read(string $location): string
    {
        return $this->toJson(
            $this->readObject($location)->toArray()
        );
    }

    /**
     * Get a resource to read the file.
     *
     * @param  string  $location
     * @return resource
     *
     * @throws UnableToReadFile
     * @throws FilesystemException
     */
    public function readStream(string $location)
    {
        return $this->readObject($location)->get('Body');
    }

    /**
     * Get an all files in a directory.
     *
     * @param  string  $location
     * @param  bool  $deep
     * @return DirectoryListing<StorageAttributes>
     *
     * @throws FilesystemException
     */
    public function listContents(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing
    {
        $items = [];

        $this->setMaxKeys(1000);
        $this->setPrefix($location);

        try {
            do {
                $response = $this->client->listObjectsV2($this->getParams());
                $this->setToken($response->get('NextContinuationToken'));

                foreach ($response->get('Contents') as $item) {
                    $items[] = $this->toFileAttribute($item);
                }

                if ($deep === false) {
                    return new DirectoryListing($items);
                }
            } while ($response->get('IsTruncated'));

            return new DirectoryListing($items);
        } catch (Throwable $exception) {
            throw UnableToCheckExistence::forLocation($location, $exception);
        }
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     *
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function lastModified(string $path): int
    {
        /** @var DateTimeResult $mod */
        $date = $this->readObject($path)->get('LastModified');

        try {
            return $date->getTimestamp();
        } catch (UnableToRetrieveMetadata $exception) {
            throw UnableToRetrieveMetadata::lastModified($path, $exception->reason(), $exception);
        }
    }

    /**
     * Get the file's filesize.
     *
     * @param  string  $path
     * @return int
     *
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function fileSize(string $path): int
    {
        try {
            return $this->readObject($path)->get('ContentLength');
        } catch (UnableToRetrieveMetadata $exception) {
            throw UnableToRetrieveMetadata::fileSize($path, $exception->reason(), $exception);
        }
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function mimeType(string $path): string
    {
        try {
            return $this->readObject($path)->get('ContentType');
        } catch (UnableToRetrieveMetadata $exception) {
            throw UnableToRetrieveMetadata::mimeType($path, $exception->reason(), $exception);
        }
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string  $path
     * @return string
     *
     * @throws UnableToRetrieveMetadata
     * @throws FilesystemException
     */
    public function visibility(string $path): string
    {
        // Todo: Implement

        try {
            $this->setKey($path);

            $this->client->getObjectAcl($this->getParams());

            return '';
        } catch (UnableToRetrieveMetadata $exception) {
            throw UnableToRetrieveMetadata::visibility($path, $exception->reason(), $exception);
        } catch (Throwable $exception) {
            throw UnableToRetrieveMetadata::visibility($path, $exception->getMessage(), $exception);
        }
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $location
     * @param  string  $contents
     * @param  array|[]  $options
     * @return void
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function write(string $location, string $contents, array $config = []): void
    {
        if ($this->directoryExists($location)) {
            if (! file_exists($contents)) {
                throw UnableToWriteFile::atLocation($location, 'The contents must be a valid file location.');
            }

            $location = $location . '/' . basename($contents);
            $contents = file_get_contents($contents);
        } else {
            $info = pathinfo($location);
            $filename = $info['filename'];
            $dirname = $info['dirname'];

            $dirname = $dirname === '.' ? '' : "$dirname/";
            if (file_exists($contents)) {
                $extension = pathinfo($contents)['extension'] ?? null;
                $extension = is_null($extension) ? '' : ".$extension";
                $location = "$dirname$filename$extension";
                $contents = file_get_contents($contents);
            } else {
                $location = "$dirname$filename.txt";
            }
        }

        try {
            $this->client->upload(
                $this->$this->bucket,
                $location,
                $contents,
                'private',
                $config
            );
        } catch (UnableToWriteFile $exception) {
            throw UnableToWriteFile::atLocation($location, $exception->reason(), $exception);
        }
    }

    /**
     * Write a new file using a stream.
     *
     * @param  string  $location  The path of the new file.
     * @param  resource  $contents  The file handle.
     * @param  array  $config  An optional configuration array.
     * @return void
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function writeStream(string $location, $contents, array $config = []): void
    {
        $this->write($location, $contents, $config);
    }

    /**
     * Set the visibility for a file.
     *
     * @param  string  $path  The path to the file.
     * @param  string  $visibility  One of 'public' or 'private'.
     * @return void
     *
     * @throws UnableToSetVisibility
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        try {
            $this->setKey($path);
            $this->setAcl($visibility);

            $this->client->putObjectAcl($this->getParams());
        } catch (UnableToSetVisibility $exception) {
            throw UnableToSetVisibility::atLocation($path, $exception->reason());
        } catch (Throwable $exception) {
            throw UnableToSetVisibility::atLocation($path, $exception->getMessage());
        }
    }

    /**
     * Delete a file.
     *
     * @param  string  $location
     * @return void
     *
     * @throws UnableToDeleteFile
     * @throws FilesystemException
     */
    public function delete(string $location): void
    {
        try {
            $this->setKey($location);
            $this->client->deleteObject($this->getParams());
        } catch (UnableToDeleteFile $exception) {
            throw UnableToDeleteFile::atLocation($location, '', $exception);
        } catch (Throwable $exception) {
            throw UnableToDeleteFile::atLocation($location, $exception->getMessage());
        }
    }

    /**
     * Delete a directory.
     *
     * @param  string  $location
     * @return void
     *
     * @throws UnableToDeleteDirectory
     * @throws FilesystemException
     */
    public function deleteDirectory(string $location): void
    {
        try {
            $this->client->deleteMatchingObjects($this->$this->bucket, $location);
        } catch (UnableToDeleteDirectory|Throwable $exception) {
            throw UnableToDeleteDirectory::atLocation($location, '', $exception);
        }
    }

    /**
     * Create a directory.
     *
     * @param  string  $location  The name of the new directory.
     * @param  array  $config  An optional configuration array.
     * @return void
     *
     * @throws UnableToCreateDirectory
     * @throws FilesystemException
     */
    public function createDirectory(string $location, array $config = []): void
    {
        $this->write($location, '', $config);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $source
     * @param  string  $destination
     * @param  array  $config
     * @return void
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function move(string $source, string $destination, array $config = []): void
    {
        $this->copy($source, $destination, $config);
        $this->deleteDirectory($source);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $source
     * @param  string  $destination
     * @param  array  $config
     * @return void
     *
     * @throws UnableToCopyFile
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, array $config = []): void
    {
        try {
            $this->client->copy(
                $this->$this->bucket,
                $source,
                $this->$this->bucket,
                $destination,
                'private',
                $config
            );
        } catch (Throwable) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }
}
