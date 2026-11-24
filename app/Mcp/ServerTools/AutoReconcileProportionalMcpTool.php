<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\AutoReconcileProportionalTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class AutoReconcileProportionalMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return AutoReconcileProportionalTool::class;
    }
}
