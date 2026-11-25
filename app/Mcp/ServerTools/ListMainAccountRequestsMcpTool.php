<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\ListMainAccountRequestsTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListMainAccountRequestsMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return ListMainAccountRequestsTool::class;
    }
}