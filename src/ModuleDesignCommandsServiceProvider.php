<?php

namespace CreativeCrafts\ModuleDesignCommands;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CreativeCrafts\ModuleDesignCommands\Commands\ModuleDesignCommandsCommand;

class ModuleDesignCommandsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-module-design-commands')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-module-design-commands_table')
            ->hasCommand(ModuleDesignCommandsCommand::class);
    }
}
