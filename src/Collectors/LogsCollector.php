<?php

namespace Rareloop\Lumberjack\DebugBar\Collectors;

use DebugBar\Bridge\MonologCollector;

class LogsCollector extends MonologCollector
{
    public function getName()
    {
        return 'Logs';
    }
}
