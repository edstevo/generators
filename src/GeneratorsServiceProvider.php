<?php

namespace FlowflexComponents\Generators;

use FlowflexComponents\Generators\Commands\Creators\DaoCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{

	protected $commandArray	= [
		'FlowflexComponents\Generators\Commands\GenerateController',
		'FlowflexComponents\Generators\Commands\GenerateDaoEvent',
		'FlowflexComponents\Generators\Commands\GenerateDaoRepository',
		'FlowflexComponents\Generators\Commands\GenerateModel',
		'FlowflexComponents\Generators\Commands\GenerateRequest',
		'FlowflexComponents\Generators\Commands\InstallDaoSystem'
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