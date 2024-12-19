<?php

namespace System\Console;

use Symfony\Component\Console\Output\BufferedOutput;
use System\Classes\Extensions\ModuleManager;
use System\Classes\Extensions\PluginManager;
use System\Classes\Extensions\Preserver;
use System\Classes\Extensions\Source\ComposerSource;
use System\Classes\Extensions\Source\ExtensionSource;
use System\Classes\Extensions\Source\LocalSource;
use System\Classes\Packager\Composer;
use Winter\Storm\Console\Command;
use Winter\Storm\Exception\ApplicationException;
use function Termwind\render;
use function Termwind\renderUsing;

class JaxTest extends Command
{
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'jax:test';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'jax:test';

    /**
     * @var string The console command description.
     */
    protected $description = 'Testing, delete before merge.';

    /**
     * Execute the console command.
     * @throws ApplicationException
     */
    public function handle(): int
    {
        
        return 0;
    }
}
