<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\RecruiterCommissionsSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class RecruiterCommissionsSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return RecruiterCommissionsSummaryTool::class;
    }
}