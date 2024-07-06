<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

class CreateModuleController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller in a module';

    /**
     * Execute the console command.
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

        if (! File::exists($modulePath)) {
            error('Module does not exists!');

            return 1;
        }
        info('Module exist.');

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        if ($subDirectoryName !== '' && ! File::exists(
            $modulePath.'/App/Http/Controllers/'.ucfirst($subDirectoryName)
        )) {
            info('Sub directory not found. Creating sub directory ....');
            File::makeDirectory($modulePath.'/App/Http/Controllers/'.ucfirst($subDirectoryName), 0777, recursive: true);
            outro('Sub directory created successfully. Moving on ....');
        }

        $controllerName = text(
            label: 'What is the name of the controller?',
            required: 'Controller name is required.'
        );

        if (str_contains($controllerName, 'Controller')) {
            $controllerName = str_ireplace('Controller', '', $controllerName);
        }

        $controllerPath = $subDirectoryName !== '' ? $modulePath.'/App/Http/Controllers/'.ucfirst(
            $subDirectoryName
        ).'/'.ucfirst(
            $controllerName
        ).'Controller.php' : $modulePath.'/App/Http/Controllers/'.ucfirst($controllerName).'Controller.php';

        info('Creating controller ....');

        /** @var string $controllerStubContent */
        $controllerStubContent = file_get_contents($this->getControllerStub());
        if (! File::exists($controllerPath)) {
            $directoryName = $subDirectoryName !== '' ? 'App/Http/Controllers/'.ucfirst(
                $subDirectoryName
            ) : 'App/Http/Controllers';

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($controllerName).'Controller';
            $rootNamespace = 'App\\';
            $controllerStubContent = str_replace(
                ['{{ namespace }}', '{{ rootNamespace }}', '{{ class }}'],
                [$namesSpace, $rootNamespace, $className],
                $controllerStubContent
            );
            File::put($controllerPath, $controllerStubContent);
            outro('Controller created successfully.');
        } else {
            error('Controller already exists!');

            return 1;
        }

        return 0;
    }

    protected function getControllerStub(): string
    {
        return app()->basePath().'/stubs/module-controller.api.stub';
    }

    protected function getNamespace(string $rootNamespace, string $directoryName): string
    {
        if (str_contains($rootNamespace, '/')) {
            $rootNamespace = str_replace('/', '\\', $rootNamespace);
        }

        if (str_contains($directoryName, '/')) {
            $directoryName = str_replace('/', '\\', $directoryName);
        }

        return 'Modules\\'.ucfirst($rootNamespace).'\\'.$directoryName;
    }
}
