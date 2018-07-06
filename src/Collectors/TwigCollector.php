<?php

namespace Rareloop\Lumberjack\DebugBar\Collectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Rareloop\Lumberjack\DebugBar\Twig\NodeVisitor;

class TwigCollector extends DataCollector implements Renderable
{
    protected $nodeVisitor;

    public function __construct()
    {
        $this->nodeVisitor = new NodeVisitor;

        add_filter('timber/twig', [$this, 'extendTwig']);
    }

    public function extendTwig($twig)
    {
        $twig->addNodeVisitor($this->nodeVisitor);

        return $twig;
    }

    public function getName()
    {
        return 'Twig';
    }

    public function collect()
    {
        // dd($this->nodeVisitor->getIncludes());
        $includes = collect($this->nodeVisitor->getIncludes())->map(function ($include) {
            $contextText = $include['context'];
            $contextHtml = null;

            if (!is_string($contextText)) {
                // Send both text and HTML representations; the text version is used for searches
                $contextText = $this->getDataFormatter()->formatVar($contextText);

                if ($this->isHtmlVarDumperUsed()) {
                    $contextHtml = $this->getVarDumper()->renderVar($contextText);
                }
            }

            return [
                // Twig file
                [
                    'message' => $include['name'],
                    'message_html' => '',
                    'is_string' => true,
                    'label' => 'template',
                    'time' => microtime(),
                ],

                // Context
                [
                    'message' => $contextText,
                    'message_html' => $contextHtml,
                    'is_string' => false,
                    'label' => 'context',
                    'time' => microtime(),
                ],
            ];
        })->flatten(1)->toArray();

        return [
            'messages' => $includes,
            'count' => count($includes),
        ];
    }

    public function getWidgets()
    {
        $name = $this->getName();

        return array(
            "$name" => array(
                'icon' => 'list-alt',
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "$name.messages",
                "default" => "[]"
            ),
            "$name:badge" => array(
                "map" => "$name.count",
                "default" => "null"
            )
        );
    }

    /**
     * @return DataFormatterInterface
     */
    public function getDataFormatter()
    {
        if ($this->dataFormater === null) {
            $this->dataFormater = DataCollector::getDefaultDataFormatter();
        }
        return $this->dataFormater;
    }

    /**
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return mixed
     */
    public function isHtmlVarDumperUsed()
    {
        return false;
    }
}
