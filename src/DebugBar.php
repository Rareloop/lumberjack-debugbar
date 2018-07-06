<?php

namespace Rareloop\Lumberjack\DebugBar;

use DebugBar\StandardDebugBar;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\DebugBar\Collectors\LogsCollector;
use Rareloop\Lumberjack\DebugBar\Collectors\WPDBCollector;
use Rareloop\Lumberjack\DebugBar\Collectors\TwigCollector;
use Rareloop\Lumberjack\DebugBar\JavaScriptRenderer;
use Rareloop\Lumberjack\Facades\Router;
use Timber\Timber;

class DebugBar extends StandardDebugBar
{
    protected $app;
    protected $hasBeenRendered = false;

    public function __construct(Application $app, Config $config)
    {
        parent::__construct();

        $this->app = $app;

        $this->addLogCollector();
        $this->addQueryCollector();
        $this->addTwigCollector();
    }

    public function addLogCollector()
    {
        if ($this->app->has('logger')) {
            $logger = $this->app->get('logger');

            $this->addCollector(new LogsCollector($logger));
        }
    }

    public function addQueryCollector()
    {
        if (!defined('SAVEQUERIES')) {
            define('SAVEQUERIES', true);
        }

        $this->addCollector(new WPDBCollector());
    }

    public function addTwigCollector()
    {
        if ($this->app->has(Timber::class)) {
            $this->addCollector(new TwigCollector());
        }
    }

    public function render()
    {
        if ($this->hasBeenRendered) {
            return;
        }

        $this->hasBeenRendered = true;

        $renderer = $this->getJavascriptRenderer();

        return  '<script type="text/javascript" src="' . rtrim(Router::url('debugbar.js'), '/') . '"></script>' .
                '<link rel="stylesheet" type="text/css" href="' . rtrim(Router::url('debugbar.css'), '/') . '">' .
                $renderer->render();
    }

    public function hasBeenRendered()
    {
        return $this->hasBeenRendered;
    }

    public function getJavascriptRenderer($baseUrl = null, $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavaScriptRenderer($this, $baseUrl, $basePath);
        }

        return $this->jsRenderer;
    }

    /**
     * Magic calls for adding messages
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param string $method
     * @param array $args
     * @return mixed|void
     */
    public function __call($method, $args)
    {
        $messageLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'];

        if (in_array($method, $messageLevels)) {
            foreach($args as $arg) {
                $this->addMessage($arg, $method);
            }
        }
    }

    /**
     * Adds a message to the MessagesCollector
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * A message can be anything from an object to a string
     *
     * @param mixed $message
     * @param string $label
     */
    public function addMessage($message, $label = 'info')
    {
        if ($this->hasCollector('messages')) {
            /** @var \DebugBar\DataCollector\MessagesCollector $collector */
            $collector = $this->getCollector('messages');
            $collector->addMessage($message, $label);
        }
    }

    /**
     * Starts a measure
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Stops a measure
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param string $name
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            try {
                $collector->stopMeasure($name);
            } catch (\Exception $e) {
                //  $this->addThrowable($e);
            }
        }
    }

    /**
     * Adds a measure
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param string $label
     * @param float $start
     * @param float $end
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param string $label
     * @param \Closure $closure
     */
    public function measure($label, \Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        } else {
            $closure();
        }
    }

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param Exception $e
     * @deprecated in favor of addThrowable
     */
    public function addException(Exception $e)
    {
        return $this->addThrowable($e);
    }

    /**
     * Adds an exception to be profiled in the debug bar
     *
     * Inspired by Laravel DebugBar: https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
     *
     * @param Exception $e
     */
    public function addThrowable($e)
    {
        if ($this->hasCollector('exceptions')) {
            /** @var \DebugBar\DataCollector\ExceptionsCollector $collector */
            $collector = $this->getCollector('exceptions');
            $collector->addThrowable($e);
        }
    }
}
