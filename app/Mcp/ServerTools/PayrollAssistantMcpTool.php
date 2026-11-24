<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\PayrollAssistantTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PayrollAssistantMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return PayrollAssistantTool::class;
    }
}
