<?php

namespace CreativeCrafts\ModuleDesignCommands\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CreativeCrafts\ModuleDesignCommands\ModuleDesignCommands
 */
class ModuleDesignCommands extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CreativeCrafts\ModuleDesignCommands\ModuleDesignCommands::class;
    }
}
