<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

class CreateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module directory structure';

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $basePath = 'modules';
        $moduleName = text(
            label: 'What is the name of the module?',
            required: 'Module name is required.'
        );

        info('Check if module exist ....');
        $modulePath = $basePath.'/'.ucfirst($moduleName);
        if (File::exists($modulePath)) {
            error('Module already exists!');

            return 1;
        }
        info('Module does not exist. Creating module main directory ....');
        File::makeDirectory($modulePath, 0777, true, true);

        $codeDirectoryNames = [
            'config',
            'routes',
            'database',
            'domain',
            'app',
            'tests',
        ];

        info('Creating the necessary files for each directories ....');
        foreach ($codeDirectoryNames as $directory) {
            File::makeDirectory($modulePath.'/'.$directory, 0777, true, true);
        }

        info('Creating the module config file ....');
        $configFilePath = $modulePath.'/config/config.php';
        $configStubContent = file_get_contents($this->getConfigStub());
        if ($configStubContent === false) {
            error('Unable to read the config stub file.');

            return 1;
        }
        File::put($configFilePath, $configStubContent);

        info('Setting up the web route files ....');
        $webRoutePath = $modulePath.'/routes/web.php';
        $webRouteStubContent = file_get_contents($this->getRouteWebStub());
        if ($webRouteStubContent === false) {
            error('Unable to read the web route stub file.');

            return 1;
        }
        File::put($webRoutePath, $webRouteStubContent);

        info('Setting up the api route files ....');
        $apiRoutePath = $modulePath.'/routes/api.php';
        $apiRouteStubContent = file_get_contents($this->getRouteApiStub());
        if ($apiRouteStubContent === false) {
            error('Unable to read the api route stub file.');

            return 1;
        }
        File::put($apiRoutePath, $apiRouteStubContent);

        info('Creating the module database directory ....');
        $databasePath = $modulePath.'/database';
        $databaseDirectoryNames = [
            'factories',
            'migrations',
            'seeders',
        ];
        foreach ($databaseDirectoryNames as $dbDirectories) {
            File::makeDirectory($databasePath.'/'.$dbDirectories, 0777, true, true);
        }

        info('Creating the domain directory....');
        $domainPath = $modulePath.'/domain';
        $domainDirectoryNames = [
            'Actions',
            'Aggregates',
            'Collections',
            'Contracts',
            'DataFactories',
            'DataTransferObjects',
            'Queries',
            'QueryBuilders',
        ];
        foreach ($domainDirectoryNames as $domainDirectories) {
            File::makeDirectory($domainPath.'/'.$domainDirectories, 0777, true, true);
        }

        info('Creating the module test directory ....');
        $testPath = $modulePath.'/tests';
        $testDirectoryNames = [
            'ArchTest',
            'Feature',
            'Unit',
        ];
        foreach ($testDirectoryNames as $testDirectories) {
            File::makeDirectory($testPath.'/'.$testDirectories, 0777, true, true);
        }

        $includeProcessManagerDirectory = text(
            label: 'Do you want to include a process manager directory? yes/no',
            required: 'Required.'
        );

        if ($includeProcessManagerDirectory === 'yes') {
            info('Creating the process manager directory....');
            $processManagerPath = $domainPath.'/Processes';
            File::makeDirectory($processManagerPath, 0777, true, true);
        } else {
            info('Skipping Process manager directory...');
        }

        info('Creating the module source directory ....');
        $srcPath = $modulePath.'/app';
        info('Adding module app path to autoload in composer.json ....');

        if (app()->environment() === 'development') {
            $composerFilePath = app()->basePath().'/composer.json';
            $composerFileContent = File::get($composerFilePath);
            // Parse the JSON into an array
            /** @var array<int, string> $composerArray */
            $composerArray = json_decode($composerFileContent, true);
            // Get the PSR-4 autoload section from the array
            $psr4Autoload = fluent($composerArray)->scope('autoload.psr-4')->toArray();
            // app path
            $path = 'modules/'.ucfirst($moduleName).'/app';
            $key = 'Modules\\'.ucfirst($moduleName).'\\App\\';
            // Add the path to the PSR-4 autoload section
            $psr4Autoload[$key] = $path;
            // domain path
            $domainPath = 'modules/'.ucfirst($moduleName).'/domain';
            $domainKey = 'Modules\\'.ucfirst($moduleName).'\\Domain\\';
            // Add the path to the PSR-4 autoload section
            $psr4Autoload[$domainKey] = $domainPath;
            // Overwrite the PSR-4 autoload section in the array with the updated array
            $composerArray['autoload']['psr-4'] = $psr4Autoload;
            // Convert the updated array back to JSON and write it to the composer.json file
            /** @var string $newComposerContents */
            $newComposerContents = json_encode($composerArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            File::put($composerFilePath, $newComposerContents);

            info('Running composer dump-autoload ....');
            exec('composer dump-autoload');
        }

        $srcDirectoryNames = [
            'Exceptions',
            'Http',
            'Models',
            'Providers',
        ];
        foreach ($srcDirectoryNames as $srcDirectories) {
            File::makeDirectory($srcPath.'/'.$srcDirectories, 0777, true, true);
        }

        $includeEventDirectory = text(
            label: 'Do you want to include an events directory? yes/no',
            required: 'Required.'
        );

        if ($includeEventDirectory === 'yes') {
            info('Creating the module event directory ....');
            $eventPath = $srcPath.'/Events';
            File::makeDirectory($eventPath, 0777, true, true);
        } else {
            info('Skipping Events directory...');
        }

        $includeListenersDirectory = text(
            label: 'Do you want to include an event listeners directory? yes/no',
            required: 'Required.'
        );

        if ($includeListenersDirectory === 'yes') {
            info('Creating the module event listeners directory ....');
            $listenersPath = $srcPath.'/Listeners';
            File::makeDirectory($listenersPath, 0777, true, true);
        } else {
            info('Skipping event Listeners directory...');
        }

        info('Creating the module Http directory ....');
        $httpPath = $srcPath.'/Http';
        $httpDirectoryNames = [
            'Controllers',
            'Requests',
            'Resources',
        ];
        foreach ($httpDirectoryNames as $httpDirectories) {
            File::makeDirectory($httpPath.'/'.$httpDirectories, 0777, true, true);
        }

        $includeMiddlewareDirectory = text(
            label: 'Do you want to include a middleware directory? yes/no',
            required: 'Required.'
        );

        if ($includeMiddlewareDirectory === 'yes') {
            info('Creating the module middleware directory ....');
            $middlewarePath = $httpPath.'/Middleware';
            File::makeDirectory($middlewarePath, 0777, true, true);
        } else {
            info('Skipping Middleware directory...');
        }

        $providerNamespace = $this->getNamespace($moduleName, 'Providers');

        info('Setting up the route service provider ....');
        $routeServiceProviderPath = $srcPath.'/Providers/RouteServiceProvider.php';
        $routeServiceProviderStubContent = file_get_contents($this->getRouteServiceProviderStub());
        if ($routeServiceProviderStubContent === false) {
            error('Unable to read the route service provider stub file.');

            return 1;
        }
        $this->configureStub(
            $routeServiceProviderStubContent,
            $providerNamespace,
            'RouteServiceProvider',
            $routeServiceProviderPath,
            strtolower($moduleName)
        );

        info('Setting up the event service provider ....');
        $eventServiceProviderPath = $srcPath.'/Providers/EventServiceProvider.php';
        $eventServiceProviderStubContent = file_get_contents($this->getEventServiceProviderStub());
        if ($eventServiceProviderStubContent === false) {
            error('Unable to read the event service provider stub file.');

            return 1;
        }
        $this->configureStub(
            $eventServiceProviderStubContent,
            $providerNamespace,
            'EventServiceProvider',
            $eventServiceProviderPath,
            strtolower($moduleName)
        );

        info('Setting up the module service provider ....');
        $moduleServiceProviderPath = $srcPath.'/Providers/'.$moduleName.'ServiceProvider.php';
        $moduleServiceProviderStubContent = file_get_contents($this->getModuleServiceProviderStub());
        if ($moduleServiceProviderStubContent === false) {
            error('Unable to read the module service provider stub file.');

            return 1;
        }
        $this->configureStub(
            $moduleServiceProviderStubContent,
            $providerNamespace,
            $moduleName.'ServiceProvider',
            $moduleServiceProviderPath,
            strtolower($moduleName)
        );
        info('Registering the module service provider in the bootstrap app file ....');
        if (app()->environment() === 'development') {
            $providerNamespace = $this->getNamespace($moduleName, 'Providers');
            $appConfigPath = app()->basePath().'/bootstrap/app.php';
            $appConfigContent = File::get($appConfigPath);
            $newProvider = PHP_EOL.'\\'.$providerNamespace.'\\'.$moduleName.'ServiceProvider::class,';
            $needle = 'withProviders([';
            // Find the position where to insert the new provider
            $withProviderPosition = strrpos(
                $appConfigContent,
                $needle
            );
            if ($withProviderPosition !== false) {
                // Insert the new provider just before the last closing bracket
                $appConfigContent = substr_replace(
                    $appConfigContent,
                    $newProvider,
                    $withProviderPosition + strlen($needle),
                    0
                );
                File::put($appConfigPath, $appConfigContent);
                outro(
                    'Registration of the module service provider in the bootstrap app file was successful. Please check it and format file.'
                );
            } else {
                error('Unable to find the position to insert the new provider.');

                return 1;
            }
        }

        outro('Module : ['.$moduleName.']'.' created successfully.');

        return 0;
    }

    protected function configureStub(string $stubContent, string $namespace, string $className, string $filePath, ?string $key): int
    {
        $stubContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}'],
            [$namespace, $className, $key],
            $stubContent,
        );
        File::put($filePath, $stubContent);

        return 0;
    }

    protected function getConfigStub(): string
    {
        return base_path('stubs/module-config.stub');
        // return app()->basePath().'/stubs/module-config.stub';
    }

    protected function getRouteServiceProviderStub(): string
    {
        return app()->basePath().'/stubs/module-route-service-provider.stub';
    }

    protected function getEventServiceProviderStub(): string
    {
        return app()->basePath().'/stubs/module-event-service-provider.stub';
    }

    protected function getModuleServiceProviderStub(): string
    {
        return app()->basePath().'/stubs/module-service-provider.stub';
    }

    protected function getRouteWebStub(): string
    {
        return app()->basePath().'/stubs/module-route-web.stub';
    }

    protected function getRouteApiStub(): string
    {
        return app()->basePath().'/stubs/module-route-api.stub';
    }

    protected function getNamespace(string $rootNamespace, string $directoryName): string
    {
        // check if the root namespace is a nested directory
        if (str_contains($rootNamespace, '/')) {
            $rootNamespace = str_replace('/', '\\', $rootNamespace);
        }

        return 'Modules\\'.ucfirst($rootNamespace).'\\App\\'.$directoryName;
    }
}
