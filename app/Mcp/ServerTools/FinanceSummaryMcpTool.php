<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\FinanceSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class FinanceSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return FinanceSummaryTool::class;
    }
}