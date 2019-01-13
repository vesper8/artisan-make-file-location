<?php

namespace DRL\AMFL\Commands;

use DRL\AMFL\TraitCommand;
use Illuminate\Database\Console\Factories\FactoryMakeCommand as OriginalFactoryMakeCommand;

class FactoryMakeCommand extends OriginalFactoryMakeCommand
{
    use TraitCommand;

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name): string
    {
        $name = str_replace(
            ['\\', '/'], '', $this->argument('name')
        );

        if (config('amfl.use_module', false)) {
            return $this->amflCustomPath(config('amfl.module.dbPath'), $name);
        } else {
            return $this->amflCustomPath($this->laravel->basePath(), $name);
        }
    }

    /**
     * Configure the options.
     *
     * @return void
     */
    protected function amflInit(): void
    {
        $this->amflCommandSetup('factory');
    }
}
