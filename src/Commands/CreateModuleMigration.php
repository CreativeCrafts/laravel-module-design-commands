<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

class CreateModuleMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration for the module.';

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

        info('Module exists!');

        $tableName = text(
            label: 'What is the name of the table?',
            required: 'Table name is required.',
            hint: 'Table name should be plural.'
        );

        if (! str_ends_with($tableName, 's')) {
            $tableName .= 's';
        }

        if (preg_match('/^[A-Z][a-z]*(?:[A-Z][a-z]+)+$/', $tableName)) {
            $tableName = preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $tableName);
        }

        if ($tableName !== '' && $tableName !== null) {
            $tableName = strtolower((string) $tableName);
        } else {
            error('Table name is required!');

            return 1;
        }

        $migrationPath = $modulePath.'/database/migrations/'.date('Y_m_d_His').'_create_'.$tableName.'_table.php';

        info('Creating migration ....');

        /** @var string $createMigrationStubContent */
        $createMigrationStubContent = file_get_contents($this->getCreateMigrationStub());
        if (! File::exists($migrationPath)) {
            $createMigrationStubContent = str_replace(
                ['{{ table }}'],
                [$tableName],
                $createMigrationStubContent
            );
            File::put($migrationPath, $createMigrationStubContent);
            outro('Migration created successfully.');
        } else {
            error('Migration table already exists!');

            return 1;
        }

        return 0;
    }

    protected function getCreateMigrationStub(): string
    {
        return app()->basePath().'/stubs/migration.create.stub';
    }
}
