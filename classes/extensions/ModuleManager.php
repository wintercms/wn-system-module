<?php

namespace System\Classes\Extensions;

use System\Classes\Extensions\Source\ExtensionSource;

class ModuleManager implements ExtensionManagerInterface
{
    public function list(): array
    {
        // TODO: Implement list() method.
    }

    public function create(string $extension): WinterExtension
    {
        // TODO: Implement create() method.
    }

    public function install(WinterExtension|ExtensionSource|string $extension): WinterExtension
    {
        // TODO: Implement install() method.
    }

    public function enable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed
    {
        // TODO: Implement enable() method.
    }

    public function disable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed
    {
        // TODO: Implement disable() method.
    }

    public function update(WinterExtension|string $extension): mixed
    {
        // TODO: Implement update() method.
    }

    public function refresh(WinterExtension|string $extension): mixed
    {
        // TODO: Implement refresh() method.
    }

    public function rollback(WinterExtension|string $extension, string $targetVersion): mixed
    {
        // TODO: Implement rollback() method.
    }

    public function uninstall(WinterExtension|string $extension): mixed
    {
        // TODO: Implement uninstall() method.
    }

    public function isInstalled(WinterExtension|ExtensionSource|string $extension): bool
    {
        // TODO: Implement isInstalled() method.
    }

    public function get(WinterExtension|ExtensionSource|string $extension): ?WinterExtension
    {
        // TODO: Implement get() method.
    }
}
