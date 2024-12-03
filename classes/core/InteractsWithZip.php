<?php

namespace System\Classes\Core;

use Illuminate\Support\Facades\Lang;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Filesystem\Zip;

trait InteractsWithZip
{
    /**
     * Extract the provided archive
     *
     * @throws ApplicationException if the archive failed to extract
     */
    public function extractArchive(string $archive, string $destination): void
    {
        if (!Zip::extract($archive, $destination)) {
            throw new ApplicationException(Lang::get('system::lang.zip.extract_failed', ['file' => $archive]));
        }

        @unlink($archive);
    }
}
