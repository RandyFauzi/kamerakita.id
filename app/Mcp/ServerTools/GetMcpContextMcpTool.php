<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\GetMcpContextTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetMcpContextMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return GetMcpContextTool::class;
    }
}