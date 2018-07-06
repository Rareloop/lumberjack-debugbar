<?php

namespace Rareloop\Lumberjack\DebugBar\Facades;

use Blast\Facades\AbstractFacade;

class DebugBar extends AbstractFacade
{
    protected static function accessor()
    {
        return 'debugbar';
    }
}
