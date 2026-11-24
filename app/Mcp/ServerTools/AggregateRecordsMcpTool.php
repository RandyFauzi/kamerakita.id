<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\AggregateRecordsTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class AggregateRecordsMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return AggregateRecordsTool::class;
    }
}
