<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace FlowflexComponents\Generators\Commands\Creators;


use Illuminate\Filesystem\Filesystem;

class DaoCreator
{

    protected $contractDirectory = '/Contracts/Dao';
    protected $daoDirectory = '/Dao';
    protected $exceptionDirectory = '/Dao/Exceptions';

    public function __construct(Filesystem $files)
    {
        $this->files            = $files;
    }

    private function createDirectories()
    {
        // Check if the contract directory exists.
        if(!$this->files->isDirectory(app()->path() . $this->contractDirectory))
        {
            // Create the contract directory if not.
            $this->files->makeDirectory(app()->path() . $this->contractDirectory, 0755, true);
        }

        // Check if the dao directory exists.
        if(!$this->files->isDirectory(app()->path() . $this->daoDirectory))
        {
            // Create the dao directory if not.
            $this->files->makeDirectory(app()->path() . $this->daoDirectory, 0755, true);
        }

        // Check if the exception directory exists.
        if(!$this->files->isDirectory(app()->path() . $this->exceptionDirectory))
        {
            // Create the exception directory if not.
            $this->files->makeDirectory(app()->path() . $this->exceptionDirectory, 0755, true);
        }
    }

    private function installFiles()
    {
        $contractFiles      = $this->files->files(__DIR__.'/../..' . $this->contractDirectory);
        $daoFiles           = $this->files->files(__DIR__.'/../..' . $this->daoDirectory);
        $exceptionFiles     = $this->files->files(__DIR__.'/../..' . $this->exceptionDirectory);

        foreach($contractFiles as $contractFile)
        {
            $filename       = collect(explode("/", $contractFile))->last();
            $file           = $this->files->get($contractFile);
            $contractFile   = $this->renameNamespace($file);

            $this->files->put(app()->path() . $this->contractDirectory . '/' . $filename, $contractFile);
        }

        foreach($daoFiles as $daoFile)
        {
            $filename       = collect(explode("/", $daoFile))->last();
            $file           = $this->files->get($daoFile);
            $daoFile   = $this->renameNamespace($file);

            $this->files->put(app()->path() . $this->daoDirectory . '/' . $filename, $daoFile);
        }

        foreach($exceptionFiles as $exceptionFile)
        {
            $filename       = collect(explode("/", $exceptionFile))->last();
            $file           = $this->files->get($exceptionFile);
            $exceptionFile  = $this->renameNamespace($file);

            $this->files->put(app()->path() . $this->exceptionDirectory . '/' . $filename, $exceptionFile);
        }
    }

    private function renameNamespace($file)
    {
        return str_replace("FlowflexComponents\\Generators\\", app()->getNamespace(), $file);
    }


    public function install()
    {
        $this->createDirectories();
        $this->installFiles();

        return true;
    }

}