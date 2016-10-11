<?php

namespace EdStevo\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateDaoCriteria extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:criteria {name} {criteria}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Some Dao Criteria';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Criteria/criteria.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Dao Criteria';

    protected function getCriteriaInput()
    {
        return $this->argument('criteria');
    }

    protected function getFormattedCriteriaInput()
    {
        return ucwords($this->getCriteriaInput());
    }

    protected function getFormattedNameInput()
    {
        return ucwords($this->getNameInput());
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

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name)."/".$this->getFormattedCriteriaInput().'.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $path  = config('generators.dao.path');
        $path  = str_replace("/", "\\", $path);

        return $rootNamespace.$path."\\Criteria";
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
        $stub   = str_replace('$MODELNAME', $this->getFormattedNameInput(), $stub);
        $stub   = str_replace('$CRITERIANAME', $this->getFormattedCriteriaInput(), $stub);

        return $stub;
    }
}