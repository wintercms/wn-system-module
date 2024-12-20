<?php

namespace System\Classes\Extensions;

use System\Classes\Extensions\Source\ExtensionSource;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Foundation\Extension\WinterExtension;

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
    public function install(ExtensionSource|WinterExtension|string $extension ): WinterExtension;

    public function isInstalled(ExtensionSource|WinterExtension|string $extension): bool;

    public function get(ExtensionSource|WinterExtension|string $extension): ?WinterExtension;

    public function enable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed;

    public function disable(WinterExtension|string $extension, string|bool $flag = self::DISABLED_BY_USER): mixed;

    public function update(WinterExtension|string|null $extension = null, bool $migrationsOnly = false): mixed;

    public function availableUpdates(WinterExtension|string|null $extension = null): ?array;

    public function refresh(WinterExtension|string|null $extension = null): mixed;

    public function rollback(WinterExtension|string|null $extension = null, ?string $targetVersion = null): mixed;

    public function uninstall(WinterExtension|string|null $extension = null): mixed;

    public function tearDown(): static;
}
