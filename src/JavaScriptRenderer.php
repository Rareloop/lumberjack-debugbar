<?php

namespace Rareloop\Lumberjack\DebugBar;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as BaseJavaScriptRenderer;

/**
 * {@inheritdoc}
 */
class JavaScriptRenderer extends BaseJavaScriptRenderer
{

    public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);

        $this->cssVendors['fontawesome'] = __DIR__ . '/Resources/vendor/font-awesome.css';
    }
}
