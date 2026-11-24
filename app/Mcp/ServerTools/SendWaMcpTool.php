<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\SendWaTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class SendWaMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return SendWaTool::class;
    }
}
