<?php namespace Louvard\Bigupload;

use Illuminate\Support\ServiceProvider;

class BiguploadServiceProvider extends ServiceProvider
{
    //protected $defer = false;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Allow configuration to be publishable
        $this->publishes([
            __DIR__ . '/Config/config.php' => config_path('bigupload.php'),
        ], 'config');
        include __DIR__ . '/helpers.php';
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'bigupload');

        $this->app->bind('bigupload', function ($app) {
            return new BigUpload($app);
        });
    }

    public function provides()
    {
        return ['bigupload'];
    }
}
