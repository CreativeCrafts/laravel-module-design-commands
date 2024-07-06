<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleHttpRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new request for a module.';

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

        info('Module exist!');

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        if ($subDirectoryName !== '' && ! File::exists(
            $modulePath.'/App/Http/Requests/'.ucfirst($subDirectoryName)
        )) {
            info('Sub directory not found. Creating sub directory ....');
            File::makeDirectory($modulePath.'/App/Http/Requests/'.ucfirst($subDirectoryName), 0777, recursive: true);
            outro('Sub directory created successfully. Moving on ....');
        }

        $requestName = text(
            label: 'What is the name of the request?',
            required: 'Request name is required.'
        );

        if (str_contains($requestName, 'Request') || str_contains($requestName, 'request')) {
            $requestName = str_ireplace('Request', '', $requestName);
        }

        $requestPath = $subDirectoryName !== '' ? $modulePath.'/App/Http/Requests/'.ucfirst(
            $subDirectoryName
        ).'/'.ucfirst(
            $requestName
        ).'Request.php' : $modulePath.'/App/Http/Requests/'.ucfirst($requestName).'Request.php';
        info('Creating request ....');

        /** @var string $requestStubContent */
        $requestStubContent = file_get_contents($this->getRequestStub());
        if (! File::exists($requestPath)) {
            $directoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Http/Requests/'.ucfirst($subDirectoryName) : 'App/Http/Requests';

            if (! File::exists($modulePath.'/'.$directoryName)) {
                info('Request directory does not exist! Creating request directory ....');
                File::makeDirectory($modulePath.'/'.$directoryName, 0777, true, true);
            }

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($requestName).'Request';
            $requestStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$namesSpace, $className],
                $requestStubContent
            );
            info('Writing request file....');
            File::put($requestPath, $requestStubContent);
            outro('Request created successfully.');
        } else {
            error('Request already exists!');

            return 1;
        }

        return 0;
    }

    protected function getRequestStub(): string
    {
        return app()->basePath().'/stubs/request.stub';
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
