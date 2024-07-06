<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands\SharedActions;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;

final class CreateController
{
    public static function execute(
        string $controllerName,
        string $modulePath,
        string $moduleName,
        string $subDirectoryName = ''
    ): void {
        if (str_contains($controllerName, 'Controller')) {
            $controllerName = str_ireplace('Controller', '', $controllerName);
        }

        $controllerPath = $subDirectoryName !== '' ? $modulePath.'/App/Http/Controllers/'.ucfirst(
            $subDirectoryName
        ).'/'.ucfirst(
            $controllerName
        ).'Controller.php' : $modulePath.'/App/Http/Controllers/'.ucfirst($controllerName).'Controller.php';

        info('Creating controller ....👉');

        /** @var string $controllerStubContent */
        $controllerStubContent = file_get_contents((new self())->getControllerStub());
        if (! File::exists($controllerPath)) {
            $directoryName = $subDirectoryName !== '' ? 'App/Http/Controllers/'.ucfirst(
                $subDirectoryName
            ) : 'App/Http/Controllers';

            $namesSpace = (new self())->getNamespace($moduleName, $directoryName);
            $className = ucfirst($controllerName).'Controller';
            $rootNamespace = 'App\\';
            $controllerStubContent = str_replace(
                ['{{ namespace }}', '{{ rootNamespace }}', '{{ class }}'],
                [$namesSpace, $rootNamespace, $className],
                $controllerStubContent
            );
            File::put($controllerPath, $controllerStubContent);
            outro('Controller created successfully...🙌');
        } else {
            error('Controller already exists!...🤯');
        }
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
