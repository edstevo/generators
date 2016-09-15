<?php

namespace FlowflexComponents\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateRequest extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:request {type} {name} {--relation=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates A Request Class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Requests/request-main.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    protected function getTypeInput()
    {
        return $this->argument('type');
    }

    protected function getRelationOption()
    {
        return $this->option('relation');
    }

    protected function getFormattedTypeInput()
    {
        return ucwords($this->getTypeInput());
    }

    protected function getFormattedRelation()
    {
        return ucwords($this->getRelationOption());
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
        $path  = config('generators.requests.path');
        $path  = str_replace("/", "\\", $path);

        if ($this->getRelationOption())
        {
            return $rootNamespace.$path."\\".$this->getFormattedNameInput()."\\".$this->getFormattedRelation();
        }

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
        $name   = str_replace($this->laravel->getNamespace(), '', $name);

        $path               = $this->laravel['path'].'/'.str_replace('\\', '/', $name).'Request.php';
        $pathComponents     = collect(explode("/", $path))->reverse()->values();
        $pathComponents[0]  = $this->getFormattedTypeInput() . $pathComponents[0];

        if ($this->getRelationOption())
        {
            $pathComponents[0]  = str_replace("Request", $this->getFormattedRelation() . "Request", $pathComponents[0]);
            $pathComponents[1]  = $this->getFormattedRelation();
        }

        $path               = $pathComponents->reverse()->values()->implode('/');

        return $path;
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
        $class          = str_replace($this->getNamespace($name).'\\', '', $name);

        $requestName    = $this->getFormattedTypeInput().$this->getFormattedNameInput();
        $modelName      = $this->getFormattedNameInput();
        $modelVariable  = camel_case($this->getFormattedNameInput());

        if ($this->getRelationOption())
        {
            $requestName    = $this->getFormattedTypeInput().$this->getFormattedNameInput().$this->getFormattedRelation();
            $modelName      = $this->getFormattedRelation();
            $modelVariable  = camel_case($this->getFormattedRelation());
        }

        $stub   = str_replace('$REQUESTNAME', $requestName, $stub);

        $stub   = str_replace('$MODELNAME', $modelName, $stub);
        $stub   = str_replace('$MODELVARIABLE', $modelVariable, $stub);

        return $stub;
    }
}
