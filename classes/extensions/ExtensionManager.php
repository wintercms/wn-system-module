<?php

namespace System\Classes\Extensions;

use Winter\Storm\Exception\ApplicationException;

interface ExtensionManager
{
    public function list(): array;

    public function create(): WinterExtension;
    /**
     * @throws ApplicationException If the installation fails
     */
    public function install(WinterExtension|string $extension): WinterExtension;
    public function enable(WinterExtension|string $extension): mixed;
    public function disable(WinterExtension|string $extension): mixed;
    public function update(WinterExtension|string $extension): mixed;
    public function refresh(WinterExtension|string $extension): mixed;
    public function rollback(WinterExtension|string $extension, string $targetVersion): mixed;
    public function uninstall(WinterExtension|string $extension): mixed;
}
