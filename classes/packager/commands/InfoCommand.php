<?php

namespace System\Classes\Packager\Commands;

use Illuminate\Support\Facades\Cache;
use System\Classes\Packager\Composer;
use Winter\Packager\Commands\BaseCommand;
use Winter\Packager\Exceptions\CommandException;
use Winter\Packager\Exceptions\WorkDirException;

class InfoCommand extends BaseCommand
{
    protected ?string $package = null;

    /**
     * Command handler.
     *
     * @param string|null $package
     * @param boolean $dryRun
     * @param boolean $dev
     * @return void
     * @throws CommandException
     */
    public function handle(?string $package = null): void
    {
        if (!$package) {
            throw new CommandException('Must provide a package');
        }

        $this->package = $package;
    }

    /**
     * @inheritDoc
     */
    public function arguments(): array
    {
        return [
            'packages' => [$this->package]
        ];
    }

    /**
     * @throws CommandException
     * @throws WorkDirException
     */
    public function execute(): string
    {
        $output = $this->runComposerCommand();
        $message = implode(PHP_EOL, $output['output']);

        if ($output['code'] !== 0) {
            throw new CommandException($message);
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'info';
    }
}
