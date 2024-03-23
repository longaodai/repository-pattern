<?php

namespace LongAoDai\Repositories;

use Illuminate\Support\ServiceProvider;
use LongAoDai\Repositories\Console\CreatePatternCommand;

/**
 * Class RepositoryServiceProvider
 *
 * @package LongAoDai\Repositories
 *
 * @author vochilong <vochilong.work@gmail.com>
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @author vochilong <vochilong.work@gmail.com>
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/pattern.php' => config_path('pattern.php'),
        ], 'longaodai-pattern');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreatePatternCommand::class,
            ]);
        }
    }
}
