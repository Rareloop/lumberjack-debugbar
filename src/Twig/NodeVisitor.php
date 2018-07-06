<?php

namespace Rareloop\Lumberjack\DebugBar\Twig;

class NodeVisitor implements \Twig_NodeVisitorInterface
{
    protected $includes = [];

    public function enterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Include) {
            $context = [];

            if ($node->hasNode('variables')) {
                $compiler = new \Twig_Compiler($env);
                $node->getNode('variables')->compile($compiler);
                $source = $compiler->getSource();

                // Urgh!?!?
                $context = eval('return ' . $source . ';');
            }

            $name = $node->getNode('expr')->getAttribute('value');

            $this->includes[] = [
                'name' => $name,
                'context' => $context,
            ];
        }

        return $node;
    }

    public function getIncludes()
    {
        return $this->includes;
    }

    public function leaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
