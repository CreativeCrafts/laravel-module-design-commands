<?php

namespace CreativeCrafts\ModuleDesignCommands;

use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleCommand;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleController;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleDomainCollection;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleEvent;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleHttpRequest;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleHttpResource;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleJob;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleMigration;
use CreativeCrafts\ModuleDesignCommands\Commands\CreateModuleModel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModuleDesignCommandsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-module-design-commands')
            ->hasConfigFile()
            ->hasCommands([
                CreateModuleCommand::class,
                CreateModuleController::class,
                CreateModuleDomainCollection::class,
                CreateModuleEvent::class,
                CreateModuleHttpRequest::class,
                CreateModuleHttpResource::class,
                CreateModuleJob::class,
                CreateModuleMigration::class,
                CreateModuleModel::class,
            ]);
    }
}
