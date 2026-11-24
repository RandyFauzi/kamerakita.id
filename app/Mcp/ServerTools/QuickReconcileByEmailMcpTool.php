<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\QuickReconcileByEmailTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class QuickReconcileByEmailMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return QuickReconcileByEmailTool::class;
    }
}
