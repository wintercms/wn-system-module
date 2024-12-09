<?php

namespace System\Classes\Extensions;

use System\Classes\Extensions\Source\ExtensionSource;
use Winter\Storm\Exception\ApplicationException;

interface ExtensionManagerInterface
{
    public const EXTENSION_NAME = '';

    //
    // Disabled by system
    //

    public const DISABLED_MISSING = 'disabled-missing';
    public const DISABLED_REPLACED = 'disabled-replaced';
    public const DISABLED_REPLACEMENT_FAILED = 'disabled-replacement-failed';
    public const DISABLED_MISSING_DEPENDENCIES = 'disabled-dependencies';

    //
    // Explicitly disabled for a reason
    //

    public const DISABLED_REQUEST = 'disabled-request';
    public const DISABLED_BY_USER = 'disabled-user';
    public const DISABLED_BY_CONFIG = 'disabled-config';

    public function list(): array;

    public function create(string $extension): WinterExtension;

    /**
     * @throws ApplicationException If the installation fails
     */
    public function install(ExtensionSource|WinterExtension|string $extension): WinterExtension;

    public function isInstalled(ExtensionSource|WinterExtension|string $extension): bool;

    public function get(ExtensionSource|WinterExtension|string $extension): ?WinterExtension;

    public function enable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed;

    public function disable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed;

    public function update(WinterExtension|string $extension): mixed;

    public function refresh(WinterExtension|string $extension): mixed;

    public function rollback(WinterExtension|string $extension, string $targetVersion): mixed;

    public function uninstall(WinterExtension|string $extension): mixed;
}
