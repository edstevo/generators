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
    protected $signature = 'generate:model {name} {--fields=}';

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
        $this->call("generate:migration", ['table' => $this->getNameInput(), '--fields' => $this->option('fields')]);

        $class  = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub  = str_replace('$CLASSNAME', $class, $stub);

        $fields = collect($this->getFields());

        if ($fields)
        {
            $fieldString    = "'".$fields->implode("', '")."'";

            $stub  = str_replace('protected $fillable = [\'*\'];', 'protected $fillable = [' . $fieldString . '];', $stub);
        }

        $rules  = $this->buildRules();

        if ($fields)
        {
            $stub = str_replace('return [];', 'return [' . PHP_EOL . $rules . PHP_EOL . "\t\t];", $stub);
        }

        return $stub;
    }

    private function getFields()
    {
        if (!$this->option('fields'))
            return;

        $fieldComponents        = explode(",", $this->option('fields'));
        $fields                 = [];

        foreach($fieldComponents as $key => $field)
        {
            $components     = explode(":", $field);
            array_push($fields, $components[0]);
        }

        return $fields;
    }

    private function buildRules()
    {
        if (!$this->option('fields'))
            return;

        $fieldComponents        = collect(explode(",", $this->option('fields')));
        $rules                  = "";

        foreach($fieldComponents as $key => $field)
        {
            $components     = explode(":", $field);
            $fieldName      = $components[0];
            unset($components[0]);

            array_values($components);

            $rules  .= "\t\t\t";
            $rules  .= "'" . $fieldName . "' => 'required|" . implode($components, "|") . "'";

            if (in_array('nullable', $components))
            {
                $rules = str_replace("required|", "", $rules);
                $rules = str_replace("|nullable", "", $rules);
            }

            if (in_array('unique', $components))
            {
                $rules = str_replace("unique", "unique:" . $this->getModelTableName(), $rules);
            }

            if ($key != $fieldComponents->count() - 1)
            {
                $rules .= "," . PHP_EOL;
            }
        }

        return $rules;
    }

    private function getModelTableName()
    {
        return str_plural(snake_case(ucwords($this->getNameInput())));
    }
}