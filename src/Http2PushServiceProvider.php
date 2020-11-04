<?php

namespace TomKeyte\LaravelHttp2Push;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TomKeyte\LaravelHttp2Push\Middleware\AddLinkHeader;

class Http2PushServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->addMiddleware();

        $this->registerBladeDirective();
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                __DIR__ . '/config/config.php' => config_path('http2push.php'),
                ],
                'config'
            );
        }
    }

    /**
     * Add the push middleware to the web group
     */
    private function addMiddleware(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', AddLinkHeader::class);
    }

    /**
     * Register the @push blade directive
     */
    private function registerBladeDirective(): void
    {
        Blade::directive(
            'h2push',
            function ($arguments) {
                return "<?php echo h2push({$arguments}); ?>";
            }
        );
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'http2push');

        // Register the main class to use with the facade
        $this->app->singleton(
            'http2push',
            function () {
                return new Http2Push;
            }
        );
    }
}
