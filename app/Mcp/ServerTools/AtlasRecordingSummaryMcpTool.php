<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\AtlasRecordingSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class AtlasRecordingSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return AtlasRecordingSummaryTool::class;
    }
}