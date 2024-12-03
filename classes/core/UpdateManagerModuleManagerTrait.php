<?php

namespace System\Classes\Core;

use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Facades\App;

trait UpdateManagerModuleManagerTrait
{
    protected DatabaseMigrationRepository $repository;

    public function setupMigrations(): static
    {
        $this->repository->createRepository();
        $this->addMessage(Info::class, 'Migration table created');

        return $this;
    }

    public function migrateModules(array $modules): static
    {
        foreach ($modules as $module) {
            $this->migrateModule($module);
        }

        return $this;
    }

    public function seedModules(array $modules): static
    {
        foreach ($modules as $module) {
            $this->seedModule($module);
        }

        return $this;
    }

    /**
     * Run migrations on a single module
     */
    public function migrateModule(string $module): static
    {
        if (isset($this->notesOutput)) {
            $this->migrator->setOutput($this->notesOutput);
        }

        $this->out('', true);
        $this->out(sprintf('<info>Migrating %s module...</info>', $module), true);
        $this->out('', true);

        $this->migrator->run(base_path() . '/modules/' . strtolower($module) . '/database/migrations');

        return $this;
    }

    /**
     * Run seeds on a module
     */
    public function seedModule(string $module): static
    {
        $className = '\\' . $module . '\Database\Seeds\DatabaseSeeder';
        if (!class_exists($className)) {
            return $this;
        }

        $this->out('', true);
        $this->out(sprintf('<info>Seeding %s module...</info>', $module), true);
        $this->out('', true);

        $seeder = App::make($className);
        $return = $seeder->run();

        if (isset($return) && (is_string($return) || is_array($return))) {
            $this->addMessage($className, $return);
        }

        $this->write(Info::class, sprintf('Seeded %s', $module));

        return $this;
    }

    /**
     * Downloads the core from the update server.
     * @param string $hash Expected file hash.
     */
    public function downloadCore(string $hash): void
    {
        $this->api->fetchFile('core/get', 'core', $hash, ['type' => 'update']);
    }

    /**
     * Extracts the core after it has been downloaded.
     */
    public function extractCore(): void
    {
        $filePath = $this->getFilePath('core');

        $this->extractArchive($filePath, $this->baseDirectory);
    }
}
