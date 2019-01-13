<?php

namespace DRL\AMFL;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Exception\InvalidArgumentException;

trait TraitCommand
{
    /**
     * The CommandSetup instance.
     *
     * @return CommandSetup
     */
    protected $amflSetup;

    /**
     * Configure the options.
     *
     * @return void
     */
    abstract protected function amflInit(): void;

    /**
     * Initializes the settings.
     *
     * @param  string  $command
     * @return CommandSetup
     */
    public function amflCommandSetup(string $command): CommandSetup
    {
        $this->amflSetup = new CommandSetup($this, $command);

        return $this->amflSetup;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function amflCustomNamespace($rootNamespace): string
    {
        return $this->amflSetup->replace(
            'root',
            $rootNamespace,
            $this->amflSetup->getFormattedConfig()
        );
    }

    /**
     * Get the default path for the file.
     *
     * @param  string  $rootPath
     * @param  string  $name
     * @return string
     */
    protected function amflCustomPath($rootPath, $name = ''): string
    {
        $path = $this->amflSetup->replace(
            'root',
            $rootPath,
            $this->amflSetup->getFormattedConfig()
        );

        $path = $this->amflSetup->replace(
            'name',
            $name,
            $path
        );

        return $path;
    }

    /**
     * Adds the options.
     *
     * @return void
     */
    protected function amflOptions(): void
    {
        $this->amflSetup->loadOptions();

        foreach ($this->amflSetup->getOptions() as $option => $description) {
            $data = $this->amflSetup->getOptionData($option);
            $default = !$data['required'] ? $data['default'] : null;

            $this->addOption($option, null, InputOption::VALUE_REQUIRED, $description, $default);
        }
    }

    /**
     * Get the CommandSetup instance.
     *
     * @return CommandSetup
     */
    protected function getCommandSetup(): CommandSetup
    {
        return $this->amflSetup;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $this->amflInit();
        $this->amflOptions();

        return parent::getOptions();
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        if (config('amfl.use_module', false)) {
            $vendor = config('amfl.module.vendor');
            $package = config('amfl.module.package');
            $name = str_replace("{$vendor}\\${package}\\", '', $name);

            return base_path(config('amfl.module.srcPath')) . '/' . str_replace('\\', '/', $name) . '.php';
        } else {
            $name = Str::replaceFirst($this->rootNamespace(), '', $name);

            return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
        }
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        if (config('amfl.use_module', false)) {
            $vendor = config('amfl.module.vendor');
            $package = config('amfl.module.package');

            $name = ltrim($name, '\\/');

            $rootNamespace = "{$vendor}\\${package}";

            if (Str::startsWith($name, $rootNamespace)) {
                return $name;
            }

            $name = str_replace('/', '\\', $name);

            return $this->qualifyClass(
                $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
            );
        } else {
            $name = ltrim($name, '\\/');

            $rootNamespace = $this->rootNamespace();

            if (Str::startsWith($name, $rootNamespace)) {
                return $name;
            }

            $name = str_replace('/', '\\', $name);

            return $this->qualifyClass(
                $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
            );
        }
    }
}
