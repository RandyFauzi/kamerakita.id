<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\OperationsOverviewTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class OperationsOverviewMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return OperationsOverviewTool::class;
    }
}