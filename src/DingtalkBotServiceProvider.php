<?php

namespace U9\DingtalkBot;

use Illuminate\Support\ServiceProvider;

class DingtalkBotServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton('dingtalkbot',function(){
            return $this->app->make('U9\DingtalkBot\DingtalkBot');
        });
    }
}
