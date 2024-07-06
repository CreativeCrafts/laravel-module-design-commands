<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleHttpResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource for a module.';

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

        info('Check if module exist ....👀');

        $modulePath = $basePath.'/'.ucfirst($moduleName);

        if (! File::exists($modulePath)) {
            error('Module does not exists!...😱');

            return 1;
        }

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        $resourceName = text(
            label: 'What is the name of the resource?',
            required: 'Resource name is required.'
        );

        if (str_contains($resourceName, 'Resource') || str_contains($resourceName, 'resource')) {
            $resourceName = str_ireplace('Resource', '', $resourceName);
        }

        $resourcePath = $subDirectoryName !== '' ? $modulePath.'/App/Http/Resources/'.ucfirst(
            $subDirectoryName
        ).'/'.ucfirst(
            $resourceName
        ).'Resource.php' : $modulePath.'/App/Http/Resources/'.ucfirst($resourceName).'Resource.php';
        info('Creating resource ....🤞');

        /** @var string $resourceStubContent */
        $resourceStubContent = file_get_contents($this->getResourceStub());
        if (! File::exists($resourcePath)) {
            $directoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Http/Resources/'.ucfirst(
                $subDirectoryName
            ) : 'App/Http/Resources';

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($resourceName).'Resource';
            $resourceStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$namesSpace, $className],
                $resourceStubContent
            );
            File::put($resourcePath, $resourceStubContent);
            outro('Resource created successfully...🥳💪');
        } else {
            error('Resource already exists!...🥸');

            return 1;
        }

        return 0;
    }

    protected function getResourceStub(): string
    {
        return app()->basePath().'/stubs/resource.stub';
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
