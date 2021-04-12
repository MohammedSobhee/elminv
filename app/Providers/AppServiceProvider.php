<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

use App\Traits\CopyrightTrait;



class AppServiceProvider extends ServiceProvider {
    use CopyrightTrait;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        if ($this->app->environment() === 'local' && \Config::get('app.ide_helper')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {

        // Continue using bootstrap pagination (TailwindCSS is used by default in Laravel 8)
        Paginator::useBootstrap();

        // Observers
        \App\Schools::observe(\App\Observers\SchoolsObserver::class);
        \App\User::observe(\App\Observers\UserObserver::class);
        \App\Classes::observe(\App\Observers\ClassesObserver::class);
        \App\Teams::observe(\App\Observers\TeamsObserver::class);
        \App\Projects::observe(\App\Observers\ProjectsObserver::class);
        \App\Grades::observe(\App\Observers\GradesObserver::class);
        \App\AssignmentSubmitted::observe(\App\Observers\AssignmentSubmittedObserver::class);

        // Blade components
        Blade::aliasComponent('components.notification', 'notification');
        Blade::aliasComponent('components.modal', 'modal');

        // Common blade vars
        \View::composer('*', function ($view) {
            \View::share('uri', $_SERVER['REQUEST_URI'] ?? '');
            \View::share('avatar', \Auth::check() ? auth()->user()->getAvatar() : '');
            \View::share('copyright', $this->copyright());
        });
    }
}
