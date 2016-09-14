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
    protected $contractName = 'DaoBase.php';
    protected $daoDirectory = '/Dao';
    protected $daoName = 'DaoBase.php';
    protected $exceptionDirectory = '/Dao/Exceptions';
    protected $exceptionName = 'RepositoryException.php';

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
        $contract   = $this->files->get(__DIR__.'/../..' . $this->contractDirectory . '/' . $this->contractName);
        $daoBase    = $this->files->get(__DIR__.'/../..' . $this->daoDirectory . '/' . $this->daoName);
        $exception  = $this->files->get(__DIR__.'/../..' . $this->exceptionDirectory . '/' . $this->exceptionName);

        $contract   = $this->renameNamespace($contract);
        $daoBase    = $this->renameNamespace($daoBase);
        $exception  = $this->renameNamespace($exception);

        $this->files->put(app()->path() . $this->contractDirectory . '/' . $this->contractName, $contract);
        $this->files->put(app()->path() . $this->daoDirectory . '/' . $this->daoName, $daoBase);
        $this->files->put(app()->path() . $this->exceptionDirectory . '/' . $this->exceptionName, $exception);
    }

    private function renameNamespace($file)
    {
        return str_replace("FlowflexComponents\Generators\\", app()->getNamespace(), $file);
    }

    public function install()
    {
        $this->createDirectories();
        $this->installFiles();

        return true;
    }

}