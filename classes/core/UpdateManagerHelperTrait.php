<?php

namespace System\Classes\Core;

trait UpdateManagerHelperTrait
{
    protected string $tempDirectory;

    /**
     * Calculates a file path for a file code
     */
    protected function getFilePath(string $fileCode): string
    {
        $name = md5($fileCode) . '.arc';
        return $this->tempDirectory . '/' . $name;
    }
}
