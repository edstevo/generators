<?php

namespace EdStevo\Generators\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\MigrationCreator;

class GenerateMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:migration {table} {--type=} {--fields=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate A Migration';
    protected $files;
    protected $creator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MigrationCreator $creator, Filesystem $files)
    {
        parent::__construct();

        $this->creator          = $creator;
        $this->files            = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path       = $this->definePath();

        $this->files->put($path, $this->buildMigration());

        $this->info($this->getFormattedTableName() . ' migration created successfully.');
    }

    /**
     * Get the migration stub file.
     *
     * @return string
     */
    private function getStub()
    {
        if ($this->getTableType() == 'update')
        {
            return $this->getStubPath() . 'update.stub';
        }

        return $this->getStubPath() . 'create.stub';
    }

    /**
     * Get the defined type of migration if specified by the user
     *
     * @return array
     */
    private function getTableType()
    {
        if ($this->option('type'))
        {
            return $this->option('type');
        }

        return "Create";
    }

    /**
     * Return a formatted version of the table type defined by the user
     *
     * @return string
     */
    private function getFormattedTableType()
    {
        return ucwords($this->getTableType());
    }

    /**
     * Get the path to the migration stubs
     *
     * @return string
     */
    private function getStubPath()
    {
        return __DIR__.'/../stubs/Migrations/';
    }

    /**
     * Retrieve the user defined table name
     *
     * @return array
     */
    private function getTableName()
    {
        return $this->argument('table');
    }

    /**
     * Retrieve the user defined table name
     *
     * @return array
     */
    private function buildMigration()
    {
        $stub           = $this->files->get($this->getStub());

        $tableName      = str_plural(strtolower($this->getFormattedTableName()));
        $migrationName  = $this->getFormattedTableType() . str_plural($this->getFormattedTableName()) . "Table";

        $stub           = str_replace("DummyClass", $migrationName, $stub);
        $stub           = str_replace("DummyTable", $this->getVariableTableName(), $stub);

        $fields         = $this->buildFields($this->getFields());

        if ($fields)
        {
            $stub           = str_replace("// FIELDS", $fields, $stub);
        }

        return $stub;
    }

    /**
     * Define the path for the new migration
     *
     * @return string
     */
    private function definePath()
    {
        $migrationPath  = database_path() . '/migrations';

        $tableName  = date('Y_m_d_His') . '_create_' . $this->getVariableTableName() . "_table.php";

        if ($this->getTableType() == 'update')
        {
            $tableName  = str_replace("_create", "_update", $tableName);
        }

        return $migrationPath . "/" . $tableName;
    }

    /**
     * Return a formatted version of the table name defined by the user
     *
     * @return string
     */
    private function getFormattedTableName()
    {
        return ucwords($this->getTableName());
    }

    /**
     * Return a formatted version of the table name defined by the user
     *
     * @return string
     */
    private function getVariableTableName()
    {
        return str_plural(snake_case(ucwords($this->getTableName())));
    }

    /**
     * Return the fields specified by the user
     *
     * @return array|string
     */
    private function getFields()
    {
        return $this->option('fields');
    }

    private function buildFields($encodedFields)
    {
        if (!$encodedFields)
            return;

        $fieldString    = "";
        $fields         = collect(explode(",", $encodedFields));

        foreach($fields as $key => $field)
        {
            $components     = explode(":", $field);
            $fieldName      = $components[0];

            if ($key != 0)
            {
                $fieldString .= "\t\t\t";
            }

            if ($fieldName == 'timestamps')
            {

                $fieldString    .= '$table->timestamps()';

            } else {

                $fieldType      = $components[1];

                unset($components[0]);
                unset($components[1]);

                array_values($components);

                $fieldString    .= '$table->' . $fieldType . '("' . $fieldName . '")';

                foreach($components as $function)
                {
                    $fieldString .= '->' . $function . "()";
                }

            }

            $fieldString    .= ";";

            if ($key != $fields->count() - 1)
            {
                $fieldString .= PHP_EOL;
            }

        }

        return $fieldString;
    }
}
