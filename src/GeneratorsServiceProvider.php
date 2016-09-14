<?php

namespace Flowflex\Generators;

use Flowflex\Generators\Commands\Creators\DaoCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{

	protected $commandArray	= [
		'Flowflex\Generators\Commands\GenerateController',
		'Flowflex\Generators\Commands\GenerateDaoEvent',
		'Flowflex\Generators\Commands\GenerateDaoRepository',
		'Flowflex\Generators\Commands\GenerateModel',
		'Flowflex\Generators\Commands\GenerateRequest',
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		require __DIR__ . '/../vendor/autoload.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->publishDaoFiles();
		$this->publishConfigFiles();
		$this->registerCommands();
	}

	private function publishDaoFiles()
	{
		$daoCreator	 = new DaoCreator(new Filesystem());
		$daoCreator->install();
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