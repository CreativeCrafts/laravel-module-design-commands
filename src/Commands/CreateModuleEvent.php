<?php

declare(strict_types=1);

namespace CreativeCrafts\ModuleDesignCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class CreateModuleEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new event for a module.';

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $basePath = 'modules';
        $moduleName = text(
            label: 'What is the name of the module?',
            required: 'Module name is required.'
        );

        info('Checking if module exist ....');

        $modulePath = $basePath.'/'.ucfirst($moduleName);

        if (! File::exists($modulePath)) {
            error('Module does not exists!');

            return 1;
        }

        if (! File::exists($modulePath.'/App/Events')) {
            info('Events directory not found. Creating Events directory ....');
            File::makeDirectory($modulePath.'/App/Events', 0777, true);
        }

        $subDirectoryName = text(
            label: 'What is the name of the sub-directory?',
            hint: 'Leave empty if no sub-directory.'
        );

        $eventName = text(
            label: 'What is the name of the event?',
            required: 'Event name is required.'
        );

        $listenerQuestion = text(
            label: 'Do you want to create a listener for this event?',
            required: 'Enter "y" or "n".',
            hint: 'please enter "y" or "n".'
        );

        $canContinueEventCreation = true;
        $eventDirectoryName = $subDirectoryName !== '' ? 'App/Events/'.ucfirst($subDirectoryName) : 'App/Events';
        $eventReferencePath = $this->getReferencePath($moduleName, $eventDirectoryName, $eventName);
        $eventClassReferencePath = $eventReferencePath.'::class';

        $eventListenerReferencePath = '// Add listeners here';

        if ($listenerQuestion === 'y') {
            $listenerName = text(
                label: 'What is the name of the listener?',
                required: 'Listener name is required.'
            );

            if (! File::exists($modulePath.'/App/Listeners')) {
                info('Listener directory not found. Creating Listener directory ....');
                File::makeDirectory($modulePath.'/App/Listeners', 0777, true);
            }

            $canContinueEventCreation = $this->createListener(
                $modulePath,
                $moduleName,
                $subDirectoryName,
                $eventName,
                $listenerName,
                $eventReferencePath
            );
            $eventListenerDirectoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Listeners/'.ucfirst(
                $subDirectoryName
            ) : 'App/Listeners';
            $eventListenerReferencePath = $this->getReferencePath(
                $moduleName,
                $eventListenerDirectoryName,
                $listenerName
            ).'::class';
        }

        if ($canContinueEventCreation) {
            $eventPath = $subDirectoryName !== '' && $subDirectoryName !== '0' ? $modulePath.'/App/Events/'.ucfirst($subDirectoryName).'/'.ucfirst(
                trim($eventName)
            ).'.php' : $modulePath.'/App/Events/'.ucfirst(trim($eventName)).'.php';
            info('Creating event ....');

            if ($subDirectoryName !== '' && $subDirectoryName !== '0') {
                $subDirectoryPath = $modulePath.'/App/Events/'.ucfirst($subDirectoryName);
                if (! File::exists($subDirectoryPath)) {
                    File::makeDirectory($subDirectoryPath, 0777, true);
                }
            }

            /** @var string $eventStubContent */
            $eventStubContent = file_get_contents($this->getEventStub());
            if (! File::exists($eventPath)) {
                $namesSpace = $this->getNamespace($moduleName, $eventDirectoryName);
                $className = ucfirst($eventName);
                $eventStubContent = str_replace(
                    ['{{ namespace }}', '{{ class }}'],
                    [$namesSpace, $className],
                    $eventStubContent
                );
                File::put($eventPath, $eventStubContent);

                // Register the event in the EventServiceProvider
                $eventServiceProviderPath = $modulePath.'/App/Providers/EventServiceProvider.php';
                if (File::exists($eventServiceProviderPath)) {
                    $eventServiceProviderContent = File::get($eventServiceProviderPath);
                    // get the protected $listen array
                    preg_match('/protected \$listen = \[(.*?)];/s', $eventServiceProviderContent, $matches);
                    // check if the event already exists in the EventServiceProvider
                    if (str_contains($matches[1], $eventName)) {
                        info(
                            'Event already exists in the Event Service Provider. Skipping adding the event to the Event Service Provider.'
                        );
                        outro('Event created successfully.');

                        return 0;
                    }
                    $eventServiceProviderContent = str_replace(
                        '];',
                        " \n".($matches[1] ? '        ' : '')."\\{$eventClassReferencePath} => [\n\t\t\t\t \\{$eventListenerReferencePath}\n\t\t],\n\t];",
                        $eventServiceProviderContent
                    );
                    File::put($eventServiceProviderPath, $eventServiceProviderContent);
                } else {
                    error('Event Service Provider does not exists!');

                    return 1;
                }

                outro('Event created successfully.');
            } else {
                error('Event already exists!');

                return 1;
            }
        } else {
            error('Event creation failed!');

            return 1;
        }

        return 0;
    }

    protected function getReferencePath(string $rootNamespace, string $directoryName, string $eventName): string
    {
        if (str_contains($rootNamespace, '/')) {
            $rootNamespace = str_replace('/', '\\', $rootNamespace);
        }

        if (str_contains($directoryName, '/')) {
            $directoryName = str_replace('/', '\\', $directoryName);
        }

        return 'Modules\\'.ucfirst($rootNamespace).'\\'.$directoryName.'\\'.ucfirst(trim($eventName));
    }

    protected function createListener(
        string $modulePath,
        string $moduleName,
        string $subDirectoryName,
        string $eventName,
        string $listenerName,
        string $eventReferencePath
    ): bool {
        $listenerPath = $subDirectoryName !== '' && $subDirectoryName !== '0' ? $modulePath.'/App/Listeners/'.ucfirst($subDirectoryName).'/'.ucfirst(
            trim($listenerName)
        ).'.php' : $modulePath.'/App/Listeners/'.ucfirst(trim($listenerName)).'.php';
        info('Creating listener ....');

        if ($subDirectoryName !== '' && $subDirectoryName !== '0') {
            $subDirectoryPath = $modulePath.'/App/Listeners/'.ucfirst($subDirectoryName);
            if (! File::exists($subDirectoryPath)) {
                File::makeDirectory($subDirectoryPath, 0777, true);
            }
        }

        /** @var string $listenerStubContent */
        $listenerStubContent = file_get_contents($this->getEventListenerStub());
        if (! File::exists($listenerPath)) {
            $directoryName = $subDirectoryName !== '' && $subDirectoryName !== '0' ? 'App/Listeners/'.ucfirst($subDirectoryName) : 'App/Listeners';

            $namesSpace = $this->getNamespace($moduleName, $directoryName);
            $className = ucfirst($listenerName);
            $listenerStubContent = str_replace(
                ['{{ namespace }}', '{{ class }}', '{{ event }}', '{{ eventPath }}'],
                [$namesSpace, $className, ucfirst($eventName), $eventReferencePath],
                $listenerStubContent
            );

            File::put($listenerPath, $listenerStubContent);
            outro('Listener created successfully.');
        } else {
            info('Listener already exists! Skipping creating a new listener.');
        }

        return true;
    }

    protected function getEventListenerStub(): string
    {
        return app()->basePath().'/stubs/event-listener.stub';
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

    protected function getEventStub(): string
    {
        return app()->basePath().'/stubs/event.stub';
    }
}
