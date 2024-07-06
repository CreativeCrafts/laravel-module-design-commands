<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands\SharedActions;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;

final class CreateSeeder
{
    public static function execute(
        string $modulePath,
        string $modelName,
        string $moduleName,
    ): void {
        $seederPath = $modulePath.'/database/seeders/'.ucfirst($modelName).'Seeder.php';
        $className = ucfirst($modelName).'Seeder';
        $seederNameSpace = (new self())->getNamespace($moduleName, 'database/seeders');

        info('Creating seeder ....👉');

        /** @var string $seederStubContent */
        $seederStubContent = file_get_contents((new self())->getSeederStub());
        if (! File::exists($seederPath)) {
            $seederStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$seederNameSpace, $className],
                $seederStubContent
            );
            File::put($seederPath, $seederStubContent);
            outro('Seeder created successfully. Moving on ....👌');
        }
    }

    protected function getSeederStub(): string
    {
        return app()->basePath().'/stubs/module-seeder.stub';
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
