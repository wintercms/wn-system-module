<?php

namespace System\Classes\Core;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Winter\Storm\Support\Facades\File;

trait UpdateManagerFileSystemTrait
{
    /**
     * @var string Used during download of files
     */
    protected string $tempDirectory;

    /**
     * @var string Directs the UpdateManager where to unpack archives to
     */
    protected string $baseDirectory;

    /**
     * Set the temp directory used by the UpdateManager. Defaults to `temp_path()` but can be overwritten if required.
     *
     * @param string $tempDirectory
     * @return $this
     */
    public function setTempDirectory(string $tempDirectory): static
    {
        $this->tempDirectory = $tempDirectory;

        // Ensure temp directory exists
        if (!File::isDirectory($this->tempDirectory) && File::isWritable($this->tempDirectory)) {
            File::makeDirectory($this->tempDirectory, recursive: true);
        }

        return $this;
    }

    /**
     * Set the base directory used by the UpdateManager. Defaults to `base_path()` but can be overwritten if required.
     *
     * @param string $baseDirectory
     * @return $this
     */
    public function setBaseDirectory(string $baseDirectory): static
    {
        $this->baseDirectory = $baseDirectory;

        // Ensure temp directory exists
        if (!File::isDirectory($this->baseDirectory)) {
            throw new \RuntimeException('The base directory "' . $this->baseDirectory . '" does not exist.');
        }

        return $this;
    }

    /**
     * Calculates a file path for a file code
     */
    protected function getFilePath(string $fileCode): string
    {
        return $this->tempDirectory . '/' . md5($fileCode) . '.arc';
    }


}
