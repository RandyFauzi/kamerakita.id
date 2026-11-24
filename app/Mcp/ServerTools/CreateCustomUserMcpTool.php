<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\CreateCustomUserTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class CreateCustomUserMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return CreateCustomUserTool::class;
    }
}
