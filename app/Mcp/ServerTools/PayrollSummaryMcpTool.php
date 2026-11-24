<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\PayrollSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PayrollSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return PayrollSummaryTool::class;
    }
}