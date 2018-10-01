<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //

		$this->app->singleton('UrlLinker', function($app) {
			return new \Youthweb\UrlLinker\UrlLinker([
				'htmlLinkCreator' => function($url, $content) {
					return '<a href="'.$url.'" title="'.$url.'" target="_blank">'.$content.'</a>';
				},
			]);
		});
    }
}
