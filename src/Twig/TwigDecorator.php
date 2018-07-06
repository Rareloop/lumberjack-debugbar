<?php

namespace Rareloop\Lumberjack\DebugBar\Twig;

use Rareloop\Lumberjack\DebugBar\Collectors\TwigCollector;
use Rareloop\Lumberjack\DebugBar\Twig\Template;

class TwigDecorator
{
    protected $twig;
    protected $collector;

    public function __construct($twig, TwigCollector $collector)
    {
        $this->twig = $twig;
        $this->collector = $collector;

        $this->twig->setBaseTemplateClass(Template::class);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->twig->$method(...$parameters);
    }

    /**
     * Displays a template.
     *
     * @param string $name    The template name
     * @param array  $context An array of parameters to pass to the template
     *
     * @throws Twig_Error_Loader  When the template cannot be found
     * @throws Twig_Error_Syntax  When an error occurred during compilation
     * @throws Twig_Error_Runtime When an error occurred during rendering
     */
    public function display($name, array $context = array())
    {
        $this->collector->addTemplate($name, $context);

        return $this->twig->loadTemplate($name)->display($context);
    }
}
