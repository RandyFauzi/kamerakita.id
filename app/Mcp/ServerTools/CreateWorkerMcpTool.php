<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\CreateWorkerTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class CreateWorkerMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return CreateWorkerTool::class;
    }
}
