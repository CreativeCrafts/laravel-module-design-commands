<?php

namespace CreativeCrafts\ModuleDesignCommands;

use CreativeCrafts\ModuleDesignCommands\Commands\ModuleDesignCommands;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModuleDesignCommandsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-module-design-commands')
            ->hasConfigFile()
            ->hasCommand(ModuleDesignCommands::class);
    }
}
