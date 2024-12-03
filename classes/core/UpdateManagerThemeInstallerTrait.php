<?php

namespace System\Classes\Core;

use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;

trait UpdateManagerThemeInstallerTrait
{
    /**
     * Downloads a theme from the update server.
     */
    public function downloadTheme(string $name, string $hash): static
    {
        $fileCode = $name . $hash;
        $this->api->fetchFile('theme/get', $fileCode, $hash, ['name' => $name]);
        return $this;
    }

    /**
     * Extracts a theme after it has been downloaded.
     */
    public function extractTheme(string $name, string $hash): void
    {
        $fileCode = $name . $hash;
        $filePath = $this->getFilePath($fileCode);

        $this->extractArchive($filePath, themes_path());

        if ($this->themeManager) {
            $this->themeManager->setInstalled($name);
        }
    }
}
