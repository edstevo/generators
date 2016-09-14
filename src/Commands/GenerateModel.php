<?php

namespace FlowflexComponents\Generators\Commands;

use Illuminate\Console\GeneratorCommand;

class GenerateModel extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:model {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Model For A Resource';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/Models/model-main.stub';
    }

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $modelPath  = config('generators.models.path');
        $modelPath  = str_replace("/", "\\", $modelPath);

        return $rootNamespace.$modelPath;
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
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('$CLASSNAME', $class, $stub);
    }
}
