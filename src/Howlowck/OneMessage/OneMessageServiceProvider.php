<?php namespace Howlowck\OneMessage;

use Illuminate\Support\ServiceProvider;

class OneMessageServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->app['config']->package('howlowck/one-message', __DIR__.'/../../config');

		$this->app->bindShared('onemessage', function($app) {
			$session = $app->make('session');
			return new OneMessage($session, $app['config']);
    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('onemessage');
	}

}