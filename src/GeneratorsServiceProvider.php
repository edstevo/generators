<?php

namespace EdStevo\Generators;

use EdStevo\Generators\Commands\Creators\DaoCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{

	protected $commandArray	= [
		'EdStevo\Generators\Commands\GenerateController',
		'EdStevo\Generators\Commands\GenerateDaoCriteria',
		'EdStevo\Generators\Commands\GenerateDaoEvent',
		'EdStevo\Generators\Commands\GenerateDaoRepository',
		'EdStevo\Generators\Commands\GenerateMigration',
		'EdStevo\Generators\Commands\GenerateModel',
		'EdStevo\Generators\Commands\GenerateRequest'
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->publishConfigFiles();
		$this->registerCommands();
	}

	private function publishConfigFiles()
	{
		$this->publishes([
			__DIR__.'/config/generators.php' => config_path('generators.php'),
		]);
	}

	private function registerCommands()
	{
		$this->commands($this->commandArray);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return $this->commandArray;
	}

}