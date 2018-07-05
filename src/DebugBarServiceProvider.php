<?php

namespace Rareloop\Lumberjack\DebugBar;

use Rareloop\Lumberjack\DebugBar\DebugBar;
use Rareloop\Lumberjack\DebugBar\Responses\CssResponse;
use Rareloop\Lumberjack\DebugBar\Responses\JavaScriptResponse;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Router\Router;
use Zend\Diactoros\Response;

class DebugBarServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (Config::get('app.debug')) {
            $debugbar = $this->app->make(DebugBar::class);

            $this->app->bind('debugbar', $debugbar);
            $this->app->bind('debugbar.messages', $debugbar['messages']);
        }
    }

    public function boot(Router $router)
    {
        if ($this->app->has('debugbar')) {
            // Attempt to add the debug bar to the footer
            add_action('wp_footer', [$this, 'echoDebugBar']);

            // Check to make sure that render has been called. Typical reasons it may not:
            // - WP Class name issue => whitescreen
            add_action('wp_before_admin_bar_render', [$this, 'echoDebugBar']);

            $router->get('debugbar/js', function () {
                $debugbar = $this->app->get('debugbar');

                ob_start();
                $debugbar->getJavascriptRenderer()->dumpJsAssets();
                $output = ob_get_contents();
                ob_end_clean();

                return new JavaScriptResponse(
                    $output
                );
            });

            $router->get('debugbar/css', function () {
                $debugbar = $this->app->get('debugbar');

                ob_start();
                $debugbar->getJavascriptRenderer()->dumpCssAssets();
                $output = ob_get_contents();
                ob_end_clean();

                return new CssResponse(
                    $output
                );
            });
        }

    }

    public function echoDebugBar()
    {
        $debugbar = $this->app->get('debugbar');

        if ($debugbar->hasBeenRendered()) {
            return;
        }

        echo $debugbar->render();
    }
}
