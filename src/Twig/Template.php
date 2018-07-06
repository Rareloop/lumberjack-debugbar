<?php

namespace Rareloop\Lumberjack\DebugBar\Twig;

use Rareloop\Lumberjack\DebugBar\Facades\DebugBar;

abstract class Template extends \Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        DebugBar::addTwigTemplate($this->getTemplateName(), $context);
        $this->displayWithErrorHandling($this->env->mergeGlobals($context), array_merge($this->blocks, $blocks));
    }
}
