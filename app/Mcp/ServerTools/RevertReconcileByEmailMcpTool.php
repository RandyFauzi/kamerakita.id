<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\RevertReconcileByEmailTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class RevertReconcileByEmailMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return RevertReconcileByEmailTool::class;
    }
}
