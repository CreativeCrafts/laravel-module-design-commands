<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands\SharedActions;

use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;

final class CreateMigration
{
    public static function execute(string $modulePath, string $modelName): void
    {
        /** @var string $modelName */
        $modelName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $modelName);

        $tableName = strtolower($modelName);
        if (! str_ends_with($tableName, 's')) {
            $tableName .= 's';
        }

        if (preg_match('/^[A-Z][a-z]*(?:[A-Z][a-z]+)+$/', $tableName)) {
            $tableName = preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $tableName);
        }

        $migrationPath = $modulePath.'/database/migrations/'.date('Y_m_d_His').'_create_'.$tableName.'_table.php';

        info('Creating migration ....👉');

        /** @var string $createMigrationStubContent */
        $createMigrationStubContent = file_get_contents((new self())->getCreateMigrationStub());
        if (! File::exists($migrationPath)) {
            $createMigrationStubContent = str_replace(
                ['{{ table }}'],
                [$tableName],
                $createMigrationStubContent
            );
            File::put($migrationPath, $createMigrationStubContent);
            outro('Migration created successfully. Moving on ....🙌');
        }
    }

    protected function getCreateMigrationStub(): string
    {
        return app()->basePath().'/stubs/migration.create.stub';
    }
}
