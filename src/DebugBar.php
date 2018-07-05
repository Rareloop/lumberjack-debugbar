<?php

namespace Rareloop\Lumberjack\DebugBar;

use DebugBar\StandardDebugBar;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\DebugBar\Collectors\LogsCollector;
use Rareloop\Lumberjack\DebugBar\Collectors\WPDBCollector;
use Rareloop\Lumberjack\DebugBar\JavaScriptRenderer;

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

    public function render()
    {
        if ($this->hasBeenRendered) {
            return;
        }

        $this->hasBeenRendered = true;

        $renderer = $this->getJavascriptRenderer();

        return '<script type="text/javascript" src="/debugbar/js"></script><link rel="stylesheet" type="text/css" href="/debugbar/css">' . $renderer->render();
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
}
