<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use CreativeCrafts\ModuleDesignCommands\Commands\SharedActions\CreateController;
use CreativeCrafts\ModuleDesignCommands\Commands\SharedActions\CreateMigration;
use CreativeCrafts\ModuleDesignCommands\Commands\SharedActions\CreateModelFactory;
use CreativeCrafts\ModuleDesignCommands\Commands\SharedActions\CreateSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for a module.';

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

        info('Checking if module exist ....👀');

        $modulePath = $basePath.'/'.ucfirst($moduleName);

        if (! File::exists($modulePath)) {
            error('Module does not exists!...😱');

            return 1;
        }
        outro('Module found.....👍');

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        if ($subDirectoryName !== '' && ! File::exists($modulePath.'/App/Models/'.ucfirst($subDirectoryName))) {
            info('Sub directory not found. Creating sub directory ....');
            File::makeDirectory($modulePath.'/App/Models/'.ucfirst($subDirectoryName), 0777, recursive: true);
            outro('Sub directory created successfully. Moving on ....🤟');
        }

        $modelName = text(
            label: 'What is the name of the model?',
            required: 'Model name is required.'
        );

        if (str_contains($modelName, 'Model') || str_contains($modelName, 'model')) {
            $modelName = str_ireplace('Model', '', $modelName);
        }

        $modelPath = $subDirectoryName !== '' ? $modulePath.'/App/Models/'.ucfirst($subDirectoryName).'/'.ucfirst(
            $modelName
        ).'.php' : $modulePath.'/App/Models/'.ucfirst($modelName).'.php';

        $options = multiselect(
            label: 'Select what should be included with the model.👇',
            options: [
                'migration' => 'Migration',
                'factory' => 'Factory',
                'seeder' => 'Seeder',
                'controller' => 'Controller',
            ],
            default: ['migration'],
            hint: 'All options can be added later. use space bar to select multiple options.'
        );

        info('Check if model directory exists ....👀');
        if (! File::exists($modulePath.'/App/Models')) {
            info('Model directory not found. Creating model directory ....👉');
            File::makeDirectory($modulePath.'/App/Models', 0777, true, true);
        }

        if (in_array('migration', $options, true)) {
            CreateMigration::execute($modulePath, $modelName);
        }

        if (in_array('factory', $options, true)) {
            CreateModelFactory::execute($modulePath, $modelName, $moduleName, $subDirectoryName);
        }
        if (in_array('seeder', $options, true)) {
            CreateSeeder::execute($modulePath, $modelName, $moduleName);
        }

        if (in_array('controller', $options, true)) {
            $controllerName = ucfirst($modelName).'Controller';
            CreateController::execute($controllerName, $modulePath, $moduleName, $subDirectoryName);
        }

        info('Creating model ....💪');

        /** @var string $modelStubContent */
        $modelStubContent = file_get_contents($this->getModelStub());
        if (! File::exists($modelPath)) {
            $directoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Models/'.ucfirst($subDirectoryName) : 'App/Models';

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($modelName);
            $modelStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$namesSpace, $className],
                $modelStubContent
            );
            File::put($modelPath, $modelStubContent);
            outro('Model created successfully...🥳🤠');
        } else {
            error('Model already exists!...🤯');

            return 1;
        }

        return 0;
    }

    protected function getModelStub(): string
    {
        return app()->basePath().'/stubs/model.stub';
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
