<?php

namespace System\Classes\Extensions;

use Illuminate\Console\OutputStyle;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\App;
use System\Classes\Extensions\Source\ExtensionSource;
use System\Classes\UpdateManager;
use System\Helpers\Cache as CacheHelper;
use System\Models\Parameter;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Foundation\Extension\WinterExtension;
use Winter\Storm\Packager\Composer;
use Winter\Storm\Support\Facades\Config;
use Winter\Storm\Support\Facades\Schema;

class ModuleManager extends ExtensionManager implements ExtensionManagerInterface
{
    protected Migrator $migrator;
    protected DatabaseMigrationRepository $repository;

    protected function init(): void
    {
        $this->migrator = App::make('migrator');

        $this->migrator->setOutput($this->output);

        $this->repository = App::make('migration.repository');
    }

    public function setOutput(OutputStyle $output): static
    {
        $this->output = $output;

        $this->migrator->setOutput($this->output);

        return $this;
    }

    public function list(): array
    {
        return Config::get('cms.loadModules', []);
    }

    public function create(string $extension): WinterExtension
    {
        throw new ApplicationException('Support for creating extensions needs implementing');
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

    /**
     * @throws ApplicationException
     */
    public function update(WinterExtension|string|null $extension = null, bool $migrationsOnly = false): ?bool
    {
        $modules = $this->getModuleList($extension);

        $firstUp = UpdateManager::instance()->isSystemSetup();

        if ($firstUp) {
            $this->repository->createRepository();
            $this->output->info('Migration table created');
        }

        if (!$migrationsOnly) {
            foreach ($modules as $module) {
                $extension = $this->get($module);
                if (
                    !Config::get('cms.disableCoreUpdates')
                    && ($composerPackage = Composer::getPackageNameByExtension($extension))
                    && Composer::updateAvailable($composerPackage)
                ) {
                    $this->output->info(sprintf(
                        'Performing composer update for %s (%s) module...',
                        $module,
                        $composerPackage
                    ));

                    Preserver::instance()->store($extension);
                    $update = Composer::update(dryRun: true, package: $composerPackage);

                    $versions = $update->getUpgraded()[$composerPackage] ?? null;

                    $this->output->{$versions ? 'info' : 'error'}(
                        $versions
                            ? sprintf('Updated module %s (%s) from v%s => v%s', $module, $composerPackage, $versions[0], $versions[1])
                            : sprintf('Failed to module %s (%s)', $module, $composerPackage)
                    );
                } elseif (false /* Detect if market */) {
                    Preserver::instance()->store($extension);
                    // @TODO: Update files from market
                }
            }
        }

        foreach ($modules as $module) {
            $this->output->info(sprintf('Migrating %s module...', $module));
            $this->migrator->run(base_path() . '/modules/' . strtolower($module) . '/database/migrations');

            if ($firstUp) {
                $className = '\\' . $module . '\Database\Seeds\DatabaseSeeder';
                if (class_exists($className)) {
                    $this->output->info(sprintf('Seeding %s module...', $module));

                    $seeder = App::make($className);
                    $return = $seeder->run();

                    if ($return && (is_string($return) || is_array($return))) {
                        $return = is_string($return) ? [$return] : $return;
                        foreach ($return as $item) {
                            $this->output->info(sprintf('[%s]: %s', $className, $item));
                        }
                    }

                    $this->output->info(sprintf('Seeded %s', $module));
                }
            }
        }

        Parameter::set('system::update.count', 0);
        CacheHelper::clear();

        return true;
    }

    public function refresh(WinterExtension|string|null $extension = null): mixed
    {
        // TODO: Implement refresh() method.
    }

    /**
     * @throws ApplicationException
     */
    public function rollback(WinterExtension|string|null $extension = null, ?string $targetVersion = null): mixed
    {
        $modules = $this->getModuleList($extension);

        $paths = [];
        foreach ($modules as $module) {
            $paths[] = base_path() . '/modules/' . strtolower($module) . '/database/migrations';
        }

        while (true) {
            $rolledBack = $this->migrator->rollback($paths, ['pretend' => false]);

            if (count($rolledBack) == 0) {
                break;
            }
        }

        Schema::dropIfExists(UpdateManager::instance()->getMigrationTableName());

        return true;
    }

    public function uninstall(WinterExtension|string|null $extension = null): mixed
    {
        // TODO: Implement uninstall() method.
    }

    public function isInstalled(WinterExtension|ExtensionSource|string $extension): bool
    {
        // TODO: Implement isInstalled() method.
    }

    public function get(WinterExtension|ExtensionSource|string $extension): ?WinterExtension
    {
        if ($extension instanceof WinterExtension) {
            return $extension;
        }

        // @TODO: improve
        try {
            if (is_string($extension) && ($resolved = App::get($extension . '\\ServiceProvider'))) {
                return $resolved;
            }
        } catch (\Throwable $e) {
//            $this->output->error($e->getMessage());
        }

        try {
            return App::get(ucfirst($extension) . '\\ServiceProvider');
        } catch (\Throwable $e) {
            $this->output->error($e->getMessage());
            return null;
        }
    }

    public function availableUpdates(WinterExtension|string|null $extension = null): ?array
    {
        // TODO: Implement availableUpdates() method.
    }

    public function tearDown(): static
    {
        // TODO: Implement tearDown() method.
    }

    protected function getModuleList(WinterExtension|string|null $extension = null): array
    {
        if (!$extension) {
            return $this->list();
        }

        if (!($resolved = $this->resolveIdentifier($extension))) {
            throw new ApplicationException('Unable to locate extension');
        }

        return [$resolved];
    }
}
