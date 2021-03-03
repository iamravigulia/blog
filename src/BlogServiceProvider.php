<?php

namespace Edgewizz\Blog;

use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Blade;


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
        
        $this->loadViewsFrom(__DIR__.'/views', 'blogView');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        // $this->loadViewsFrom(__DIR__ . '/components', 'blogView');

        /*Blade::component('blogView::blogView.addBlog', 'blogView.addBlog');
        Blade::component('blogView::blogView.draft', 'blogView.draft');
        Blade::component('blogView::blogView.published', 'blogView.published');*/
        

    }
}
