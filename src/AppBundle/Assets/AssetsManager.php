<?php

namespace AppBundle\Assets;

use Exception;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Handle assets upload and deletion
 */
class AssetsManager
{
    /**
     * Resources directory path
     *
     * @var null|string
     */
    protected $resourcesPath;

    /**
     * File system
     *
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * AssetsManager constructor.
     *
     * @param null|string $resourcesPath
     */
    public function __construct($resourcesPath = null)
    {
        // default resources path
        if (!$resourcesPath) {
            $resourcesPath = realpath(__DIR__ . '/../../../resources/');
        }
        // resources path should end with a slash
        if (substr($resourcesPath, -1, 1) != '/') {
            $resourcesPath .= '/';
        }
        $this->resourcesPath = $resourcesPath;
        $this->fileSystem = new Filesystem();
    }

    /**
     * Add a new file on the server, and return the corresponding slug
     *
     * @param $application
     * @param SplFileInfo $file
     * @return string
     */
    public function add($application, SplFileInfo $file)
    {
        // destination directory
        $targetDirectory = $this->resourcesPath . $application . '/';

        // create target directory if not exists
        if (!$this->fileSystem->exists($targetDirectory)) {
            $this->fileSystem->mkdir($targetDirectory);
        }
        // create file slug
        $slug = $this->slug($file);

        // move uploaded file to the resources directory
        $this
            ->fileSystem
            ->rename($file->getRealPath(), $targetDirectory . $slug);

        return $slug;
    }

    /**
     * Update a existing file with a new content. The slug will ne be changed
     *
     * @param $application
     * @param SplFileInfo $file
     * @param $slug
     * @return mixed
     * @throws Exception
     */
    public function update($application, SplFileInfo $file, $slug)
    {
        // file MUST exists in the server for the given application
        if (!$this->exists($application, $slug)) {
            throw new Exception('File to update not found on the server');
        }
        // remove the old file
        $this
            ->fileSystem
            ->remove($this->path($application, $slug));

        // move the new one
        $this
            ->fileSystem
            ->rename($file->getRealPath(), $this->path($application, $slug));

        return $slug;

    }

    /**
     * Remove an existing file from the server
     *
     * @param $application
     * @param $slug
     * @throws Exception
     */
    public function remove($application, $slug)
    {
        // file MUST exists in the server for the given application
        if (!$this->exists($application, $slug)) {
            throw new Exception('File to update not found on the server');
        }
        // remove the file from the server
        $this
            ->fileSystem
            ->remove($this->path($application, $slug));
    }

    /**
     * Generate a slug for file
     *
     * @param SplFileInfo $file
     * @return string
     */
    public function slug(SplFileInfo $file)
    {
        $originalNameArray = explode('.', $file->getFilename());
        array_pop($originalNameArray);

        return uniqid(implode('.', $originalNameArray) . '_') . '.' . $file->getExtension();
    }

    /**
     * Return true if a file exists for a given slug and application
     *
     * @param $application
     * @param $slug
     * @return bool
     */
    public function exists($application, $slug)
    {
        return $this
            ->fileSystem
            ->exists($this->path($application, $slug));
    }

    /**
     * Return the real path of a file for a given slug and application
     *
     * @param $application
     * @param $slug
     * @return string
     */
    public function path($application, $slug)
    {
        return $this->resourcesPath . $application . '/' . $slug;
    }
}
