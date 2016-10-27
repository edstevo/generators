<?php

namespace EdStevo\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateDaoEvent extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dao-event {type} {name} {--relation}';

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

    protected function getNonDestroyUses()
    {
        return __DIR__.'/../stubs/Events/Uses/non-destroy.stub';
    }

    protected function getDestroyUses()
    {
        return __DIR__.'/../stubs/Events/Uses/destroy.stub';
    }

    protected function getDoubleVariableConstruct()
    {
        return __DIR__.'/../stubs/Events/Constructs/double-variable.stub';
    }

    protected function getSingleVariableConstruct()
    {
        return __DIR__.'/../stubs/Events/Constructs/single-variable.stub';
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

    protected function getRelationOption()
    {
        return $this->option('relation');
    }

    protected function getFormattedRelationOption()
    {
        return ucwords($this->getRelationOption());
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

        if ($this->getFormattedTypeInput() == 'Destroy' || $this->getFormattedTypeInput() == 'Detach')
        {
            $text   = $this->files->get($this->getDestroyUses());
            $stub   = str_replace('$USES', $text, $stub);
        } else {
            $text   = $this->files->get($this->getNonDestroyUses());
            $stub   = str_replace('$USES', $text, $stub);
        }

        if ($this->getFormattedTypeInput() == 'Attach' || $this->getFormattedTypeInput() == 'Detach')
        {
            $construct  = $this->files->get($this->getDoubleVariableConstruct());
            $construct  = str_replace('$VARIABLE_ONE', strtolower($this->getFormattedNameInput()), $construct);
            $construct  = str_replace('$VARIABLE_TWO', strtolower($this->getFormattedRelationOption()), $construct);
        } else {
            $construct  = $this->files->get($this->getSingleVariableConstruct());
            $construct  = str_replace('$VARIABLE', strtolower($this->getFormattedNameInput()), $construct);
        }

        $stub   = str_replace('$CONSTRUCTS', $construct, $stub);
        $stub   = str_replace('$MODELNAME', $this->getFormattedNameInput(), $stub);
        $stub   = str_replace('$MODELVARIABLE', strtolower($this->getFormattedNameInput()), $stub);

        return $stub;
    }
}
