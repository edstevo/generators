<?php

namespace FlowflexComponents\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateDaoEvent extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dao-event {type} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates A Dao Event';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Events/Dao/dao-event-main.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dao Event';

    protected function getTypeInput()
    {
        return $this->argument('type');
    }

    protected function getFormattedTypeInput()
    {
        return ucwords($this->getTypeInput());
    }

    protected function getFormattedNameInput()
    {
        return ucwords($this->getNameInput());
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $path  = config('generators.events.path');
        $path  = str_replace("/", "\\", $path);

        return $rootNamespace.$path."\\".$this->getFormattedNameInput()."\\".$this->getFormattedNameInput();
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).$this->getEventName().'.php';
    }

    protected function getEventName()
    {
        $eventName  = $this->getTypeInput() . "ed";
        return ucwords(str_replace("eed", "ed", $eventName));
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class  = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub   = str_replace('$EVENTTYPE', $this->getEventName(), $stub);

        $stub   = str_replace('$MODELNAME', $this->getFormattedNameInput(), $stub);
        $stub   = str_replace('$MODELVARIABLE', strtolower($this->getFormattedNameInput()), $stub);

        return $stub;
    }
}
