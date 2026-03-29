<?php

declare(strict_types=1);

namespace Semmelsamu\Imgs;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Semmelsamu\Imgs\Commands\Optimize;
use Semmelsamu\Imgs\Components\Image;

class ImgsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge default config so users only need to override what they change
        $this->mergeConfigFrom(__DIR__.'/../config/imgs.php', 'imgs');

        // Bind Imgs as a singleton, resolved from config at runtime
        $this->app->singleton(Imgs::class, function () {
            return new Imgs(
                INPUT_DIR: config('imgs.input_dir'),
                OUTPUT_DIR: config('imgs.output_dir'),
                OUTPUT_SIZES: config('imgs.sizes'),
                OUTPUT_FORMAT: config('imgs.format'),
                OUTPUT_QUALITY: config('imgs.quality'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load views under the "imgs" namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'imgs');

        // Register <x-imgs> Blade component
        Blade::component('imgs', Image::class);

        if ($this->app->runningInConsole()) {
            // Register the artisan command
            $this->commands([Optimize::class]);

            // Allow users to publish the config
            $this->publishes([
                __DIR__.'/../config/imgs.php' => config_path('imgs.php'),
            ], 'imgs-config');

            // Allow users to publish and customise the views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/imgs'),
            ], 'imgs-views');
        }
    }
}
