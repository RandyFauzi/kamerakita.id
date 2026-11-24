<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\FetchRecordsTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class FetchRecordsMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return FetchRecordsTool::class;
    }
}
