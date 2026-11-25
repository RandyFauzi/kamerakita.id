<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\ListPasswordRecoveriesTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListPasswordRecoveriesMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return ListPasswordRecoveriesTool::class;
    }
}