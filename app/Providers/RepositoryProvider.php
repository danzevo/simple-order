<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //Auth
		$this->app->bind("App\Interfaces\AuthInterface","App\Repositories\AuthRepository");
        //Article
		$this->app->bind("App\Interfaces\Article\ArticleInterface","App\Repositories\Article\ArticleRepository");
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
