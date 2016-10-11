<?php

namespace EdStevo\Generators\Commands;

use EdStevo\Generators\Commands\Creators\DaoCreator;
use Illuminate\Console\Command;

class InstallDaoSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:dao-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the Dao System';
    protected $daoCreator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DaoCreator $daoCreator)
    {
        parent::__construct();

        $this->daoCreator       = $daoCreator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!config('generators'))
        {
            $this->info("You must publish this package before you can proceed");
        }

        $this->daoCreator->install();
        $this->info("Dao System Installed");
    }
}
