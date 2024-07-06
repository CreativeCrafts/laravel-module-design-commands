<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleDomainCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-domain-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new domain collection for the module.';

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

        info('Module exist!...🙏');

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        $resourceCollectionName = text(
            label: 'What is the name of the resource collection?',
            required: 'Resource Collection name is required.'
        );

        if (str_contains($resourceCollectionName, 'Collection')) {
            $resourceCollectionName = str_ireplace('Collection', '', $resourceCollectionName);
        }

        $resourceCollectionPath = $subDirectoryName !== '' ? $modulePath.'/Domain/Collections/'.ucfirst(
            $subDirectoryName
        ).'/'.ucfirst(
            $resourceCollectionName
        ).'Collection.php' : $modulePath.'/Domain/Collections/'.ucfirst($resourceCollectionName).'Collection.php';

        info('Creating resource collection ....🤞');

        /** @var string $resourceCollectionStubContent */
        $resourceCollectionStubContent = file_get_contents($this->getResourceCollectionStub());
        if (! File::exists($resourceCollectionPath)) {
            $directoryName = $subDirectoryName !== '' ? 'Domain/Collections/'.ucfirst(
                $subDirectoryName
            ) : 'Domain/Collections';

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($resourceCollectionName).'Collection';
            $resourceCollectionStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$namesSpace, $className],
                $resourceCollectionStubContent
            );
            File::put($resourceCollectionPath, $resourceCollectionStubContent);
            outro('Resource collection created successfully...🥳💪');
        } else {
            error('Resource collection already exists!...🥸');

            return 1;
        }

        return 0;
    }

    protected function getResourceCollectionStub(): string
    {
        return app()->basePath().'/stubs/module-resource-collection.stub';
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
