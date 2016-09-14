<?php

namespace FlowflexComponents\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateController extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:controller {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates A Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Controllers/API/controller-main.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

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
        $path  = config('generators.controllers.path').config('generators.controllers.API.path');
        $path  = str_replace("/", "\\", $path);

        return $rootNamespace.$path;
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

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'/'.$this->getFormattedNameInput().'Controller.php';
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
        $stub   = str_replace('$CONTROLLERNAMESPACE', $this->getNamespace($name) . "\\" . $this->getFormattedNameInput(), $stub);
        $stub   = str_replace('$ROOTNAMESPACE', app()->getNamespace(), $stub);

        $stub   = str_replace('$MODELNAME', $this->getFormattedNameInput(), $stub);
        $stub   = str_replace('$MODELVARIABLE', strtolower($this->getFormattedNameInput()), $stub);

        return $stub;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Create']);
        $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Update']);
        $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Destroy']);

        return parent::buildClass($name);
    }
}
