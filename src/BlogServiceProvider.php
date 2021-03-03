<?php

namespace Edgewizz\Blog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;


class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //register Controller
        $this->app->make('Edgewizz\Blog\BlogController');
        // $this->app->make('Edgewizz\Blog\CalculatorController');
        
        $this->loadViewsFrom(__DIR__.'/views', 'blog');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

// dd($this);
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadViewsFrom(__DIR__ . '/components', 'blog');

        Blade::component('blog::blog.index', 'blog.index');
        /*Blade::component('blog::blog.index', 'blog.index');
        Blade::component('blog::blog.edit', 'blog.edit');*/
        

    }
}
