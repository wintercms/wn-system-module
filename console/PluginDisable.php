<?php namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\PluginManager;
use System\Models\PluginVersion;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;

/**
 * Console command to disable a plugin.
 *
 * @package winter\wn-system-module
 * @author Lucas Zamora
 */
class PluginDisable extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'plugin:disable';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Disable an existing plugin.';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $pluginManager = PluginManager::instance();
        $pluginName = $this->argument('name');
        $pluginName = $pluginManager->normalizeIdentifier($pluginName);

        if (!$pluginManager->hasPlugin($pluginName)) {
            return $this->error(sprintf('Unable to find a registered plugin called "%s"', $pluginName));
        }

        // Disable this plugin
        $pluginManager->disablePlugin($pluginName);

        $plugin = PluginVersion::where('code', $pluginName)->first();
        $plugin->is_disabled = true;
        $plugin->save();

        $this->output->writeln(sprintf('<info>%s:</info> disabled.', $pluginName));
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the plugin. Eg: AuthorName.PluginName'],
        ];
    }

    /**
     * Provide autocompletion for this command's input
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('name')) {
            $plugins = array_keys(PluginManager::instance()->getPlugins());
            $suggestions->suggestValues($plugins);
        }
    }
}
