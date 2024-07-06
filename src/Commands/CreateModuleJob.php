<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new job for a module.';

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
        outro('Module exist. Moving on ....');

        $jobName = text(
            label: 'What is the name of the job?',
            required: 'Job name is required.'
        );

        // check if job directory exist and create if not
        if (! File::exists($modulePath.'/App/Jobs')) {
            info('Jobs directory not found. Creating Jobs directory ....');
            outro('Job directory created successfully. Moving on ....');
            File::makeDirectory($modulePath.'/App/Jobs', 0777, true, true);
        }

        $jobPath = $modulePath.'/App/Jobs/'.ucfirst(trim($jobName)).'.php';

        /* @var string $jobStubContent */
        $jobStubContent = file_get_contents($this->getJobStub());
        if (is_string($jobStubContent) && ! File::exists($jobPath)) {
            $nameSpace = $this->getNamespace($moduleName, 'App/Jobs');
            $className = ucfirst(trim($jobName));
            $jobStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$nameSpace, $className],
                $jobStubContent
            );

            File::put($jobPath, $jobStubContent);

            outro('Job created successfully.');
        } else {
            error('Job already exists!');

            return 1;
        }

        return 0;
    }

    protected function getJobStub(): string
    {
        return app()->basePath().'/stubs/job.queued.stub';
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
