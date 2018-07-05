<?php

namespace Rareloop\Lumberjack\DebugBar\Collectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class WPDBCollector extends DataCollector implements Renderable
{
    public function getName()
    {
        return 'WPDB';
    }

    public function getQueries()
    {
        global $wpdb;

        return collect($wpdb->queries)->map(function ($query) {
            $sql = $query[0];
            $timeInSeconds = $query[1]; // I'm guessing this is what it is - can't find any docs!?!
            $stack = $query[2];

            return [
                'message' => $sql,
                'message_html' => '',
                'is_string' => true,
                'label' => round($timeInSeconds * 1000, 1) . 'ms',
                'time' => microtime(),
            ];
        })->toArray();
    }

    public function collect()
    {
        $queries = $this->getQueries();

        return [
            'messages' => $queries,
            'count' => count($queries),
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
}
