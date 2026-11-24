<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\BatchReconcileByEmailTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class BatchReconcileByEmailMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return BatchReconcileByEmailTool::class;
    }
}
