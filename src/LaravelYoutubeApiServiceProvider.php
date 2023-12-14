<?php

namespace Mokhosh\LaravelYoutubeApi;

use Illuminate\Support\ServiceProvider;

class LaravelYoutubeApiServiceProvider extends ServiceProvider {
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->publishes(array(__DIR__ . '/config/youtube-api.php' => config_path('youtube-api.php')), 'youtube-api');
	}

	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
