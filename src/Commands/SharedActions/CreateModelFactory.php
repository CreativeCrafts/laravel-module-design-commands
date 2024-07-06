<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands\SharedActions;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;

final class CreateModelFactory
{
    public static function execute(
        string $modulePath,
        string $modelName,
        string $moduleName,
        string $subDirectoryName = ''
    ): void {
        $factoryPath = $modulePath.'/database/factories/'.ucfirst($modelName).'Factory.php';
        $modelDirectoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Models/'.ucfirst(
            $subDirectoryName
        ) : 'App/Models';
        $getNamespace = (new self())->getNamespace($moduleName, $modelDirectoryName);
        $namespacedModelPath = $getNamespace.'\\'.ucfirst($modelName);

        $factoryNameSpace = (new self())->getNamespace($moduleName, 'database/factories');

        info('Creating factory ....👉');

        /** @var string $factoryStubContent */
        $factoryStubContent = file_get_contents((new self())->getFactoryStub());
        if (! File::exists($factoryPath)) {
            $factoryStubContent = str_replace(
                ['{{ factoryNamespace }}', '{{ namespacedModelPath }}', '{{ namespacedModel }}', '{{ factory }}'],
                [$factoryNameSpace, $namespacedModelPath, $modelName, $modelName],
                $factoryStubContent
            );
            File::put($factoryPath, $factoryStubContent);
            outro('Factory created successfully. Moving on ....👌');
        }
    }

    protected function getFactoryStub(): string
    {
        return app()->basePath().'/stubs/module-factory.stub';
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
