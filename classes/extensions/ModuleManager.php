<?php

namespace System\Classes\Extensions;

class ModuleManager implements ExtensionManager
{
    public function list(): array
    {
        // TODO: Implement list() method.
    }

    public function create(): WinterExtension
    {
        // TODO: Implement create() method.
    }

    public function install(WinterExtension|string $extension): WinterExtension
    {
        // TODO: Implement install() method.
    }

    public function enable(WinterExtension|string $extension): mixed
    {
        // TODO: Implement enable() method.
    }

    public function disable(WinterExtension|string $extension): mixed
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
}
