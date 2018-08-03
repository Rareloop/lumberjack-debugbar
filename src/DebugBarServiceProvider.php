<?php

namespace Rareloop\Lumberjack\DebugBar;

use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\DebugBar\DebugBar;
use Rareloop\Lumberjack\DebugBar\Responses\CssResponse;
use Rareloop\Lumberjack\DebugBar\Responses\JavaScriptResponse;
use Rareloop\Lumberjack\DebugBar\Twig\NodeVisitor;
use Rareloop\Lumberjack\Facades\Config;
use Rareloop\Lumberjack\Providers\ServiceProvider;
use Rareloop\Router\Router;
use Timber\Timber;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

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

            // Also catch any custom routes that are sending back html
            add_action('lumberjack_router_response', function ($response) {
                if ($this->isHtmlResponse($response)) {
                    return $this->injectDebugBarCodeIntoResponse($response);
                }

                return $response;
            });

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

    protected function isHtmlResponse(ResponseInterface $response)
    {
        return strpos($response->getHeaderLine('Content-Type'), 'text/html') > -1;
    }

    protected function injectDebugBarCodeIntoResponse(ResponseInterface $response)
    {
        $debugbarCode = $this->getDebugBar();

        if (!$debugbarCode) {
            return $response;
        }

        $html = $response->getBody()->getContents();

        return new HtmlResponse(
            str_replace('</body>', $debugbarCode . '</body>', $html),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }

    public function extendTwig($twig)
    {
        $twig->addNodeVisitor(new NodeVisitor);

        return $twig;
    }

    public function getDebugBar()
    {
        $debugbar = $this->app->get('debugbar');

        if ($debugbar->hasBeenRendered()) {
            return;
        }

        return $debugbar->render();
    }

    public function echoDebugBar()
    {
        $debugbar = $this->app->get('debugbar');

        if ($debugbar->hasBeenRendered()) {
            return;
        }

        echo $this->getDebugBar();
    }
}
