<?php

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;

class ModuleDesignCommands extends Command
{
    public $signature = 'laravel-module-design-commands';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
