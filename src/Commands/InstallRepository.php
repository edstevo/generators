<?php

namespace App\Console\Commands;

use FlowflexComponents\Generators\Commands\Creators\DaoCreator;
use Illuminate\Console\Command;

class InstallRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the base repository for all subsequent dao generators';
    protected $daoCreator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DaoCreator $daoCreator)
    {
        parent::__construct();

        $this->daoCreator   = $daoCreator;
        $this->composer     = app()['composer'];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Create the repository.
        if($this->daoCreator->install())
        {
            // Information message.
            $this->info("Successfully installed the dao repository");
        }
    }
}
