<?php

namespace System\Classes\Extensions;

use Cms\Classes\Theme;
use System\Classes\Core\InteractsWithZip;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Traits\Singleton;

class Preserver
{
    use Singleton;
    use InteractsWithZip;

    public const ROOT_PATH = 'archive';

    protected array $classMap = [
        PluginBase::class => 'plugins',
        Theme::class => 'themes',
    ];

    public function store(WinterExtension $extension): string
    {
        $this->ensureDirectory(static::ROOT_PATH);

        if (!($type = $this->resolveType($extension))) {
            throw new ApplicationException('Unable to resolve class type: ' . $extension::class);
        }

        $this->ensureDirectory(static::ROOT_PATH . DIRECTORY_SEPARATOR . $type);

        $extensionArchiveDir = sprintf(
            '%s%4$s%s%4$s%s',
            static::ROOT_PATH,
            $type,
            $extension->extensionIdentifier(),
            DIRECTORY_SEPARATOR
        );

        $this->ensureDirectory($extensionArchiveDir);

        return $this->packArchive(
            $extension->extensionPath(),
            Storage::path($extensionArchiveDir . DIRECTORY_SEPARATOR . $extension->extensionVersion())
        );
    }

    protected function ensureDirectory(string $path): bool
    {
        if (!Storage::directoryExists($path)) {
            return Storage::makeDirectory($path);
        }

        return true;
    }

    public function resolveType(WinterExtension $extension): ?string
    {
        foreach ($this->classMap as $class => $type) {
            if ($extension instanceof $class) {
                return $type;
            }
        }

        return null;
    }
}
