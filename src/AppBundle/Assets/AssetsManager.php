<?php

namespace AppBundle\Assets;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetsManager
{
    /**
     * @var null|string
     */
    protected $resourcesPath;

    public function __construct($resourcesPath = null)
    {
        if (!$resourcesPath) {
            $resourcesPath = __DIR__ . '/../../../resources/';
        }
        if (substr($resourcesPath, -1, 1) != '/') {
            $resourcesPath .= '/';
        }
        $this->resourcesPath = $resourcesPath;
    }

    public function add($application, UploadedFile $file)
    {
        $target = $this->resourcesPath . $application . '/';
        $file->move($target);

        return $file->getFilename();
    }

    public function exists($application, $filename)
    {
        return file_exists($this->path($application, $filename));
    }

    public function path($application, $filename)
    {
        return $this->resourcesPath . $application . '/' . $filename;
    }
}
