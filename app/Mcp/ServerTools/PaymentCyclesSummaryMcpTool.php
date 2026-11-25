<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\PaymentCyclesSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class PaymentCyclesSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return PaymentCyclesSummaryTool::class;
    }
}