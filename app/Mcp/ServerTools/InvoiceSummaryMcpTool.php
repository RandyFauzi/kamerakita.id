<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\InvoiceSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class InvoiceSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return InvoiceSummaryTool::class;
    }
}