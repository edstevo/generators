<?php

namespace EdStevo\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateDaoRepository extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dao-repository {name} {--relation=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates A Dao Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Repositories/Dao/eloquent-dao-main.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dao Repository';

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
        $path   = config('generators.dao.path');
        $path   = str_replace("/", "\\", $path);

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

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
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
        $stub   = str_replace('$CLASSNAME', $class, $stub);

        $stub   = str_replace('$MODELNAME', $this->getFormattedNameInput(), $stub);

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
        $this->call('generate:dao-event', ['name' => $this->getNameInput(), 'type' => 'Create']);
        $this->call('generate:dao-event', ['name' => $this->getNameInput(), 'type' => 'Update']);
        $this->call('generate:dao-event', ['name' => $this->getNameInput(), 'type' => 'Destroy']);

        return parent::buildClass($name);
    }
}
