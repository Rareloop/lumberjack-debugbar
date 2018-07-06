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
        $this->cssVendors['lumberjack'] = __DIR__ . '/Resources/lumberjack.css';
    }

    public function getCssAssetsDump()
    {
        ob_start();
        $this->dumpCssAssets();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function getJsAssetsDump()
    {
        ob_start();
        $this->dumpJsAssets();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
