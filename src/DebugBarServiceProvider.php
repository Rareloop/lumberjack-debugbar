<?php

namespace Rareloop\Lumberjack\DebugBar;

use Rareloop\Lumberjack\DebugBar\DebugBar;
use Rareloop\Lumberjack\DebugBar\Responses\CssResponse;
use Rareloop\Lumberjack\DebugBar\Responses\JavaScriptResponse;
use Rareloop\Lumberjack\DebugBar\Twig\NodeVisitor;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Router\Router;
use Timber\Timber;
use Zend\Diactoros\Response;

class DebugBarServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (Config::get('app.debug') && !is_admin()) {
            $debugbar = $this->app->make(DebugBar::class);

            $this->app->bind('debugbar', $debugbar);
        }
    }

    public function boot(Router $router, Timber $timber)
    {
        if ($this->app->has('debugbar')) {
            // Attempt to add the debug bar to the footer
            add_action('wp_footer', [$this, 'echoDebugBar']);

            // Check to make sure that render has been called. Typical reasons it may not:
            // - WP Class name issue => whitescreen
            add_action('wp_before_admin_bar_render', [$this, 'echoDebugBar']);

            $router->group('debugbar', function ($group) {
                $debugbar = $this->app->get('debugbar');

                $group->get('debugbar.js', function () use ($debugbar) {
                    return new JavaScriptResponse($debugbar->getJavascriptRenderer()->getJsAssetsDump());
                })->name('debugbar.js');

                $group->get('debugbar.css', function () use ($debugbar) {
                    return new CssResponse($debugbar->getJavascriptRenderer()->getCssAssetsDump());
                })->name('debugbar.css');
            });
        }
    }

    public function extendTwig($twig)
    {
        $twig->addNodeVisitor(new NodeVisitor);

        return $twig;
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
