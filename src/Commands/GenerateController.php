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
    protected $signature = 'generate:controller {name} {--relation=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates A Controller';

    protected $hasManyRelationship      = 'has-many';
    protected $manyToManyRelationship   = 'many-to-many';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $relation   = $this->getRelationOption();

        if ($relation)
        {
            if ($relation['type'] == $this->hasManyRelationship)
            {
                return __DIR__.'/../stubs/Controllers/API/controller-has-many.stub';
            }

            return __DIR__.'/../stubs/Controllers/API/controller-many-to-many.stub';
        }

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

    protected function getRelationOption()
    {
        if (!$this->option('relation'))
            return null;

        $relationComponents = collect(explode(":", $this->option('relation')));

        if ($relationComponents->last() != $this->hasManyRelationship && $relationComponents->last() != $this->manyToManyRelationship)
        {
            abort(400,'Relationship not recognised.  Known relationships: has-many, many-to-many');
        }

        return [
            'relation' => $relationComponents->first(),
            'type' => $relationComponents->last()
        ];
    }

    protected function getFormattedRelationName()
    {
        return ucwords($this->getRelationOption()['relation']);
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
        $name           = str_replace($this->laravel->getNamespace(), '', $name);
        $controllerName = $this->getFormattedNameInput().'Controller.php';

        if ($this->getRelationOption())
        {
            $controllerName = $this->getFormattedNameInput().$this->getFormattedRelationName().'Controller.php';
        }

        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '/' . $controllerName;
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
        $stub   = str_replace('$MODELVARIABLE', camel_case($this->getFormattedNameInput()), $stub);

        if ($this->getRelationOption())
        {
            $stub   = str_replace('$RELATIONNAME', $this->getFormattedRelationName(), $stub);
            $stub   = str_replace('$RELATIONVARIABLE', camel_case($this->getFormattedRelationName()), $stub);
        }

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
        $relationship   = $this->getRelationOption();

        if ($relationship)
        {
            $this->info('Relationship Controller Being Generated');

            if ($relationship['type'] == $this->hasManyRelationship)
            {
                $this->call('generate:request', ['name' => $this->getNameInput(), '--relation' => $this->getRelationOption()['relation'], 'type' => 'Create']);
                $this->call('generate:request', ['name' => $this->getNameInput(), '--relation' => $this->getRelationOption()['relation'], 'type' => 'Update']);
                $this->call('generate:request', ['name' => $this->getNameInput(), '--relation' => $this->getRelationOption()['relation'], 'type' => 'Destroy']);
            }

            if ($relationship['type'] == $this->manyToManyRelationship)
            {
                $this->call('generate:request', ['name' => $this->getNameInput(), '--relation' => $this->getRelationOption()['relation'], 'type' => 'Attach']);
                $this->call('generate:request', ['name' => $this->getNameInput(), '--relation' => $this->getRelationOption()['relation'], 'type' => 'Detach']);
            }
        }

        if (!$relationship)
        {
            $this->info('No Relationship Found');

            $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Create']);
            $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Update']);
            $this->call('generate:request', ['name' => $this->getNameInput(), 'type' => 'Destroy']);
        }

        return parent::buildClass($name);
    }
}
