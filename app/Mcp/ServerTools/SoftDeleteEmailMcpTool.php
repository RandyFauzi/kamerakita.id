<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\SoftDeleteEmailTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class SoftDeleteEmailMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return SoftDeleteEmailTool::class;
    }
}
