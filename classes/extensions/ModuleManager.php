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
use System\ServiceProvider;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\Config;

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

    public function update(WinterExtension|string|null $extension): ?bool
    {
        $firstUp = UpdateManager::instance()->isSystemSetup();

        if ($extension && !($resolved = $this->resolve($extension))) {
            throw new ApplicationException('Unable to locate extension');
        }

        $modules = $extension
            ? [$resolved->getIdentifier()]
            : $this->list();

        if ($firstUp) {
            $this->repository->createRepository();
            $this->output->info('Migration table created');
        }

        foreach ($modules as $module) {
            $this->output->info(sprintf('<info>Migrating %s module...</info>', $module));
            $this->migrator->run(base_path() . '/modules/' . strtolower($module) . '/database/migrations');
        }

        if ($firstUp) {
            $className = '\\' . $module . '\Database\Seeds\DatabaseSeeder';
            if (class_exists($className)) {
                $this->output->info(sprintf('<info>Seeding %s module...</info>', $module));

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

        Parameter::set('system::update.count', 0);
        CacheHelper::clear();

        return true;
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

    protected function resolve(WinterExtension|ExtensionSource|string $extension): ?WinterExtension
    {
        if ($extension instanceof WinterExtension) {
            return $extension;
        }

        return $this->get($extension instanceof ExtensionSource ? $extension->getCode() : $extension);
    }
}
